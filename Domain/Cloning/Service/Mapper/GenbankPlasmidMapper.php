<?php
/**
 * Transforms an already-parsed circular GenBank record into a Plasmid
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 2 October 2026
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
 * Known, documented limitation : `ParseDbAbstractManager::parseLocationBounds()` (shared by the
 * GenBank and EMBL parsers) discards the raw location text and takes the min/max across every
 * `join(...)` segment. A `join()` location that crosses the origin therefore collapses into a
 * plain, non-crossing range that is silently wrong rather than absent - nothing on `Feature`
 * distinguishes it from a genuine simple feature, so this mapper cannot detect or warn about that
 * specific case without changes to the shared parser, which is out of scope here. A single-segment
 * `complement(high..low)` origin crossing is unaffected and handled correctly. Any feature that does
 * come through with missing or non-representable coordinates (e.g. from a location segment the
 * parser could not read) is skipped and reported in GenbankImportResult::getWarnings(), never
 * silently dropped or truncated.
 * Class GenbankPlasmidMapper
 * @package Amelaye\BioPHP\Domain\Cloning\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class GenbankPlasmidMapper implements GenbankPlasmidMapperInterface
{
    private const CIRCULAR_TOPOLOGY = "CIRCULAR";

    /**
     * GenBank feature keys that map unambiguously onto a FeatureType. Anything else (including
     * "gene", which names a broader region than any single FeatureType here) falls back to
     * FeatureType::MISC_FEATURE, with the original key preserved in metadata rather than guessed at.
     * @var     array<string,string>
     */
    private const FEATURE_TYPE_BY_GENBANK_KEY = [
        "CDS" => FeatureType::CDS,
        "promoter" => FeatureType::PROMOTER,
        "terminator" => FeatureType::TERMINATOR,
        "rep_origin" => FeatureType::ORIGIN_OF_REPLICATION,
        "oriT" => FeatureType::ORIGIN_OF_REPLICATION,
        "misc_feature" => FeatureType::MISC_FEATURE,
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

        if ($aGroup["strand"] === "+") {
            $sStrand = Strand::FORWARD;
        } elseif ($aGroup["strand"] === "-") {
            $sStrand = Strand::REVERSE;
        } else {
            $sStrand = Strand::NONE;
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
            [
                "genbankKey" => $sKey,
                "gene" => $sGene,
                "product" => $sProduct,
                "label" => $sLabel,
            ]
        );
    }
}
