<?php
/**
 * Transforms an already-parsed circular GenBank record into a Plasmid
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 8 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Cloning\Service\Mapper;

use Amelaye\BioPHP\Domain\Cloning\Interfaces\GenbankPlasmidMapperInterface;
use Amelaye\BioPHP\Domain\Cloning\Service\Writer\GenbankWriter;
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
 * A `join()` crossing the origin (`join(4900..5000,1..100)`, or its complement) of a record whose
 * LOCUS line says "circular" comes through as from > to as well :
 * `ParseDbAbstractManager::parseLocationBounds()` follows the order in which the segments are
 * transcribed. GenbankWriter writes such a feature back as that join(). A plain range is read on the
 * direct strand, as INSDC defines it, unless it carries /biophp_strand="none", GenbankWriter's mark
 * of a Strand::NONE feature ; a location lying on both strands is Strand::NONE as well. Any feature that comes through with missing or non-representable
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
     * Qualifiers a feature holds once at most (INSDC Feature Table Definition) : seen again among
     * rows sharing a location, they open the next feature.
     * @var     string[]
     */
    private const SINGLE_VALUED_QUALIFIERS = [
        "gene", "label", "locus_tag", "codon_start", "regulatory_class", GenbankWriter::UNSTRANDED_QUALIFIER,
    ];

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

        $aGroups = $this->groupFeatures($aFeatures);

        // The source feature describes the molecule, not a region of it : its /organism and
        // /mol_type go to the plasmid's metadata, from which GenbankWriter writes them back.
        $aMetadata = [];
        foreach ($aGroups as $aGroup) {
            if ($aGroup["key"] === "source") {
                foreach (["organism" => "organism", "mol_type" => "molType"] as $sQualifier => $sMetadataKey) {
                    if (isset($aGroup["qualifiers"][$sQualifier][0])) {
                        $aMetadata[$sMetadataKey] = $aGroup["qualifiers"][$sQualifier][0];
                    }
                }
                break;
            }
        }

        $oPlasmid = new Plasmid(
            $oSequence->getPrimAcc() !== "" ? $oSequence->getPrimAcc() : "plasmid",
            new CircularDnaSequence($oSequence->getSequence()),
            [],
            $oSequence->getDescription(),
            $oSequence->getPrimAcc() !== "" ? $oSequence->getPrimAcc() : null,
            $aMetadata
        );

        $aWarnings = [];

        foreach ($aGroups as $aGroup) {
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

            if ($this->isSplitLocation($aGroup["location"], $oPlasmid->getLength())) {
                $aWarnings[] = sprintf(
                    'A "%s" feature joins separate segments (%s) : it was imported as the single span'
                    . ' from its first to its last base, the gaps between them included.',
                    $aGroup["key"],
                    $aGroup["location"]
                );
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
     * Groups the flat, one-row-per-qualifier Feature list back into one entry per GenBank feature.
     * The parser writes the rows of a feature one after the other, sharing its key, location and
     * strand - there is no other feature-instance identifier - so a group is a run of consecutive
     * rows with the same (ftKey, ftFrom, ftTo, strand, ftLocation). Two features at the same
     * location follow each other : the second one starts where the first qualifier of the group
     * comes again, or where a qualifier a feature holds once at most (SINGLE_VALUED_QUALIFIERS : a
     * second /gene, /label, /codon_start...) does. Every qualifier value of a group is collected.
     * @param   Feature[]   $aFeatures
     * @return  array       Each entry: ["key" => string, "from" => ?int, "to" => ?int,
     * "strand" => ?string, "location" => ?string, "qualifiers" => array<string,string[]>]
     */
    private function groupFeatures(array $aFeatures): array
    {
        $aGroups = [];
        $sCurrentKey = null;
        $sFirstQualifier = null;

        foreach ($aFeatures as $oFeature) {
            if (!$oFeature instanceof Feature) {
                throw new \InvalidArgumentException("GenBank features must be Feature instances.");
            }

            $sGroupKey = implode("|", [
                $oFeature->getFtKey(),
                (string) $oFeature->getFtFrom(),
                (string) $oFeature->getFtTo(),
                (string) $oFeature->getStrand(),
                (string) $oFeature->getFtLocation(),
            ]);

            $bRepeatsASingleValue = $aGroups !== []
                && in_array($oFeature->getFtQual(), self::SINGLE_VALUED_QUALIFIERS, true)
                && isset($aGroups[count($aGroups) - 1]["qualifiers"][$oFeature->getFtQual()]);
            if ($sGroupKey !== $sCurrentKey || $oFeature->getFtQual() === $sFirstQualifier || $bRepeatsASingleValue) {
                $aGroups[] = [
                    "key" => $oFeature->getFtKey(),
                    "from" => $oFeature->getFtFrom(),
                    "to" => $oFeature->getFtTo(),
                    "strand" => $oFeature->getStrand(),
                    "location" => $oFeature->getFtLocation(),
                    "qualifiers" => [],
                ];
                $sCurrentKey = $sGroupKey;
                $sFirstQualifier = $oFeature->getFtQual();
            }

            $aGroups[count($aGroups) - 1]["qualifiers"][$oFeature->getFtQual()][] = $oFeature->getFtValue();
        }

        return $aGroups;
    }

    /**
     * Tells whether a location joins segments with a gap between them (the exons of a spliced
     * CDS), which a PlasmidFeature, one start and one end, cannot hold : only a join() crossing
     * the origin, its two segments meeting there, can be represented.
     * @param   string|null     $sLocation      The location as written
     * @param   int             $iLength        The plasmid length
     * @return  bool
     */
    private function isSplitLocation(?string $sLocation, int $iLength): bool
    {
        if ($sLocation === null || !preg_match('/join\(|order\(/', $sLocation)) {
            return false;
        }
        preg_match_all('/(\d+)(?:\.\.(\d+))?/', preg_replace('/[^,(]*:[^,)]*/', "", $sLocation), $aMatches, PREG_SET_ORDER);
        $aSegments = array_map(fn($aMatch) => [(int) $aMatch[1], (int) ($aMatch[2] ?? $aMatch[1])], $aMatches);
        usort($aSegments, fn($a, $b) => $a[0] <=> $b[0]);
        for ($i = 1; $i < count($aSegments); $i++) {
            if ($aSegments[$i][0] !== $aSegments[$i - 1][1] + 1) {
                $bWrap = count($aSegments) === 2 && $aSegments[0][0] === 1 && $aSegments[1][1] === $iLength;
                return !$bWrap;
            }
        }
        return false;
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

        // A plain range is the direct strand, unless GenbankWriter marked the feature as lying on
        // no particular strand, which INSDC cannot write.
        $bUnstranded = ($aQualifiers[GenbankWriter::UNSTRANDED_QUALIFIER][0] ?? null) === "none";
        if ($aGroup["strand"] === "+" && !$bUnstranded) {
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
