<?php
/**
 * Transforms an already-parsed circular GenBank record into a Plasmid
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 7 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Cloning\Service\Mapper;

use Amelaye\BioPHP\Domain\Cloning\Interfaces\GenbankPlasmidMapperInterface;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\FeatureType;
use Amelaye\BioPHP\Domain\Cloning\Result\GenbankImportResult;
use Amelaye\BioPHP\Domain\Cloning\Aggregate\Plasmid;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\PlasmidFeature;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\Strand;
use Amelaye\BioPHP\Domain\Sequence\Entity\Feature;
use Amelaye\BioPHP\Domain\Sequence\Entity\GbSequence;
use Amelaye\BioPHP\Domain\Sequence\Entity\Sequence;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\CircularDnaSequence;

/**
 * `ParseGenbankManager` emits one `Feature` row per `/qualifier=value` line, several rows sharing the
 * same key/coordinates/strand for a single GenBank feature ; this class groups them back into one
 * PlasmidFeature each. Coordinates from `Feature::getFtFrom()/getFtTo()` are already 1-based
 * inclusive, GenBank's own convention, and a single-segment `complement(4900..100)` wrap already
 * comes through as `from > to`, matching PlasmidFeature's own origin-crossing convention : both pass
 * straight through with no conversion.
 *
 * A `join()` crossing the origin (`join(4900..5000,1..100)`, or its complement) comes through as
 * from > to as well : `ParseDbAbstractManager::parseLocationBounds()` follows the order in which the
 * segments are transcribed. Any feature that comes through with missing or non-representable
 * coordinates (e.g. a location made only of segments of another entry) is skipped and reported in
 * GenbankImportResult::getWarnings(), never silently dropped or truncated.
 * Class GenbankPlasmidMapper
 * @package Amelaye\BioPHP\Domain\Cloning\Service\Mapper
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class GenbankPlasmidMapper implements GenbankPlasmidMapperInterface
{
    private const CIRCULAR_TOPOLOGY = "CIRCULAR";

    /**
     * GenBank feature keys that map unambiguously onto a FeatureType. Anything else (including
     * "gene", which names a broader region than any single FeatureType here) falls back to
     * FeatureType::MISC_FEATURE, with the original key preserved in metadata rather than guessed at.
     * "oriT" in particular is the origin of transfer, where conjugative transfer starts, not an
     * origin of replication : it is deliberately left out and stays a MISC_FEATURE.
     * @var     array<string,string>
     */
    private const FEATURE_TYPE_BY_GENBANK_KEY = [
        "CDS" => FeatureType::CDS,
        "promoter" => FeatureType::PROMOTER,
        "terminator" => FeatureType::TERMINATOR,
        "rep_origin" => FeatureType::ORIGIN_OF_REPLICATION,
        "misc_feature" => FeatureType::MISC_FEATURE,
    ];

    /**
     * Since 15-DEC-2014 INSDC annotates a promoter or a terminator as a "regulatory" feature with a
     * /regulatory_class qualifier, the old "promoter"/"terminator" keys being deprecated ; any other
     * class (enhancer, ribosome_binding_site...) has no FeatureType here and stays a MISC_FEATURE.
     * @var     array<string,string>
     */
    private const FEATURE_TYPE_BY_REGULATORY_CLASS = [
        "promoter" => FeatureType::PROMOTER,
        "terminator" => FeatureType::TERMINATOR,
    ];

    /**
     * "source" describes the whole input molecule, not a plasmid annotation worth its own feature.
     * @var     string[]
     */
    private const SKIPPED_GENBANK_KEYS = ["source"];

    /**
     * @inheritDoc
     */
    public function map(Sequence $oSequence, GbSequence $oGbSequence, array $aFeatures) : GenbankImportResult
    {
        $sTopology = strtoupper((string) $oGbSequence->getTopology());

        if ($sTopology !== self::CIRCULAR_TOPOLOGY) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Cannot map a GenBank record with topology "%s" to a Plasmid; only %s records are supported.',
                    $oGbSequence->getTopology() ?? "(none)",
                    self::CIRCULAR_TOPOLOGY
                )
            );
        }

        $oPlasmid = new Plasmid(
            $oSequence->getPrimAcc() !== "" ? $oSequence->getPrimAcc() : "plasmid",
            new CircularDnaSequence($oSequence->getSequence()),
            [],
            $oSequence->getDescription(),
            $oSequence->getPrimAcc() !== "" ? $oSequence->getPrimAcc() : null
        );

        $aWarnings = [];

        foreach ($this->groupFeatures($aFeatures) as $aGroup) {
            if (in_array($aGroup["key"], self::SKIPPED_GENBANK_KEYS, true)) {
                continue;
            }

            if ($aGroup["from"] === null || $aGroup["to"] === null) {
                $aWarnings[] = sprintf(
                    'Skipped a "%s" feature with an unrepresentable location (missing coordinates).',
                    $aGroup["key"]
                );
                continue;
            }

            try {
                $oPlasmid = $oPlasmid->withFeature($this->mapFeature($aGroup));
            } catch (\InvalidArgumentException $ex) {
                $aWarnings[] = sprintf(
                    'Skipped a "%s" feature with an unrepresentable location: %s',
                    $aGroup["key"],
                    $ex->getMessage()
                );
            }
        }

        return new GenbankImportResult($oPlasmid, $aWarnings);
    }

    /**
     * Groups the flat, one-row-per-qualifier Feature list back into one entry per GenBank feature,
     * keyed by (ftKey, ftFrom, ftTo, strand) - there is no other feature-instance identifier - and
     * collects every qualifier value seen for that group.
     * @param   Feature[]   $aFeatures
     * @return  array       Each entry: ["key" => string, "from" => ?int, "to" => ?int,
     * "strand" => ?string, "qualifiers" => array<string,string[]>]
     */
    private function groupFeatures(array $aFeatures): array
    {
        $aGroups = [];

        foreach ($aFeatures as $oFeature) {
            if (!$oFeature instanceof Feature) {
                throw new \InvalidArgumentException("GenBank features must be Feature instances.");
            }

            $sGroupKey = implode("|", [
                $oFeature->getFtKey(),
                (string) $oFeature->getFtFrom(),
                (string) $oFeature->getFtTo(),
                (string) $oFeature->getStrand(),
            ]);

            if (!array_key_exists($sGroupKey, $aGroups)) {
                $aGroups[$sGroupKey] = [
                    "key" => $oFeature->getFtKey(),
                    "from" => $oFeature->getFtFrom(),
                    "to" => $oFeature->getFtTo(),
                    "strand" => $oFeature->getStrand(),
                    "qualifiers" => [],
                ];
            }

            $aGroups[$sGroupKey]["qualifiers"][$oFeature->getFtQual()][] = $oFeature->getFtValue();
        }

        return array_values($aGroups);
    }

    /**
     * @param   array   $aGroup     One entry produced by groupFeatures(), with non-null from/to
     * @return  PlasmidFeature
     */
    private function mapFeature(array $aGroup): PlasmidFeature
    {
        $sKey = $aGroup["key"];
        $aQualifiers = $aGroup["qualifiers"];

        $sGene = $aQualifiers["gene"][0] ?? null;
        $sLabel = $aQualifiers["label"][0] ?? null;
        $sProduct = $aQualifiers["product"][0] ?? null;
        $sNote = $aQualifiers["note"][0] ?? null;

        $sName = $sGene ?? $sLabel ?? $sProduct ?? $sKey;
        $sType = self::FEATURE_TYPE_BY_GENBANK_KEY[$sKey] ?? FeatureType::MISC_FEATURE;

        $sRegulatoryClass = $aQualifiers["regulatory_class"][0] ?? null;
        if ($sKey === "regulatory" && $sRegulatoryClass !== null) {
            $sType = self::FEATURE_TYPE_BY_REGULATORY_CLASS[$sRegulatoryClass] ?? FeatureType::MISC_FEATURE;
        }

        if ($aGroup["strand"] === "+") {
            $sStrand = Strand::FORWARD;
        } elseif ($aGroup["strand"] === "-") {
            $sStrand = Strand::REVERSE;
        } else {
            $sStrand = Strand::NONE;
        }

        $aMetadata = [
            "genbankKey" => $sKey,
            "gene" => $sGene,
            "product" => $sProduct,
            "label" => $sLabel,
        ];
        if ($sRegulatoryClass !== null) {
            $aMetadata["regulatoryClass"] = $sRegulatoryClass;
        }

        // /codon_start (1, 2 or 3) counts from the feature's own 5' end, as GFF3's phase (0, 1 or 2)
        // does, on either strand : phase = codon_start - 1.
        $iPhase = null;
        $sCodonStart = isset($aQualifiers["codon_start"][0]) ? trim($aQualifiers["codon_start"][0]) : null;
        if ($sType === FeatureType::CDS && $sCodonStart !== null) {
            if (!in_array($sCodonStart, ["1", "2", "3"], true)) {
                throw new \InvalidArgumentException(
                    sprintf('invalid /codon_start "%s", expected 1, 2 or 3.', $sCodonStart)
                );
            }
            $iPhase = (int) $sCodonStart - 1;
        }

        return new PlasmidFeature(
            $sName,
            $sType,
            $aGroup["from"],
            $aGroup["to"],
            $sStrand,
            null,
            $sNote,
            null,
            $aMetadata,
            $iPhase
        );
    }
}
