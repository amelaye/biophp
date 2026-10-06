<?php
/**
 * Serializes a Plasmid into GenBank flat-file text
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Cloning\Service\Writer;

use Amelaye\BioPHP\Domain\Cloning\Aggregate\Plasmid;
use Amelaye\BioPHP\Domain\Cloning\Interfaces\GenbankWriterInterface;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\FeatureType;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\PlasmidFeature;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\Strand;

/**
 * Covers the subset of the GenBank flat-file format GenbankPlasmidMapper reads back : LOCUS,
 * DEFINITION, ACCESSION, a FEATURES table (a synthetic "source 1..length" line plus one entry per
 * PlasmidFeature) and an ORIGIN sequence block. It does not attempt REFERENCE, COMMENT, VERSION, a
 * real division code or submission date - Plasmid carries none of those, and inventing plausible-
 * looking values for them would misrepresent the record rather than describe it. The LOCUS line's
 * column widths are a reasonable approximation of NCBI's own layout, not a guaranteed byte-for-byte
 * match for every edge case (e.g. a locus name longer than 20 characters is written in full, which
 * shifts the "bp" column rather than truncating the name and losing information).
 *
 * A REVERSE-strand feature is written as "complement(start..end)" with start <= end, the standard
 * GenBank convention - a feature crossing the origin (start > end) has no such representation and is
 * rejected by throwing, same as GffFeatureWriter and BedFeatureWriter. This is spec-correct output ;
 * it is a separate, pre-existing limitation of this project's own GenBank parser
 * (`ParseDbAbstractManager::parseLocationBounds()`, not touched here) that reading a complement()
 * location back does not fully restore the original coordinates, documented on
 * GenbankPlasmidMapper's own class docblock.
 *
 * A feature's GenBank key and its /gene, /label, /product, /note qualifiers are read back from
 * `getMetadata()`/`getNote()` exactly as GenbankPlasmidMapper wrote them when importing, so a
 * read-then-write round trip of a feature that originated from GenBank is lossless. A feature with
 * no such metadata (hand-built, imported from GFF3/BED...) falls back to a FeatureType-derived key ;
 * FeatureType's richer vocabulary (INSERT, MARKER, REPORTER, TAG, RESTRICTION_SITE...) has no GenBank
 * feature-key equivalent for most of its members, so those honestly fall back to "misc_feature"
 * rather than a plausible-looking but wrong guess.
 * Class GenbankWriter
 * @package Amelaye\BioPHP\Domain\Cloning\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class GenbankWriter implements GenbankWriterInterface
{
    private const BASES_PER_LINE = 60;

    private const BASES_PER_GROUP = 10;

    /**
     * @var     array<string,string>
     */
    private const GENBANK_KEY_BY_FEATURE_TYPE = [
        FeatureType::CDS => "CDS",
        FeatureType::PROMOTER => "promoter",
        FeatureType::TERMINATOR => "terminator",
        FeatureType::ORIGIN_OF_REPLICATION => "rep_origin",
    ];

    /**
     * @param   Plasmid     $oPlasmid
     * @return  string
     */
    public function write(Plasmid $oPlasmid): string
    {
        $iLength = $oPlasmid->getLength();

        $sOutput = sprintf("LOCUS       %-20s%d bp    DNA     circular\n", $oPlasmid->getName(), $iLength);

        if ($oPlasmid->getDescription() !== null && $oPlasmid->getDescription() !== "") {
            $sOutput .= "DEFINITION  " . $oPlasmid->getDescription() . ".\n";
        }

        $sOutput .= "ACCESSION   " . ($oPlasmid->getExternalId() ?? $oPlasmid->getName()) . "\n";

        $sOutput .= "FEATURES             Location/Qualifiers\n";
        $sOutput .= sprintf("%-5s%-16s%s\n", "", "source", "1.." . $iLength);

        foreach ($oPlasmid->getFeatures() as $oFeature) {
            $sOutput .= $this->writeFeature($oFeature);
        }

        $sOutput .= $this->writeOriginBlock($oPlasmid->getSequence()->getValue());

        return $sOutput;
    }

    /**
     * @param   PlasmidFeature  $oFeature
     * @return  string
     */
    private function writeFeature(PlasmidFeature $oFeature): string
    {
        if ($oFeature->crossesOrigin()) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Feature "%s" crosses the origin (start %d > end %d) ; this GenBank writer has'
                    . ' no way to represent that.',
                    $oFeature->getName(),
                    $oFeature->getStart(),
                    $oFeature->getEnd()
                )
            );
        }

        $aMetadata = $oFeature->getMetadata();
        $sKey = $aMetadata["genbankKey"]
            ?? self::GENBANK_KEY_BY_FEATURE_TYPE[$oFeature->getType()]
            ?? "misc_feature";

        $sLocation = $oFeature->getStart() . ".." . $oFeature->getEnd();
        if ($oFeature->getStrand() === Strand::REVERSE) {
            $sLocation = "complement(" . $sLocation . ")";
        }

        $sOutput = sprintf("%-5s%-16s%s\n", "", $sKey, $sLocation);

        if (!empty($aMetadata["gene"])) {
            $sOutput .= $this->writeQualifier("gene", $aMetadata["gene"]);
        }
        if (!empty($aMetadata["label"])) {
            $sOutput .= $this->writeQualifier("label", $aMetadata["label"]);
        }
        if (!empty($aMetadata["product"])) {
            $sOutput .= $this->writeQualifier("product", $aMetadata["product"]);
        }
        if ($oFeature->getNote() !== null && $oFeature->getNote() !== "") {
            $sOutput .= $this->writeQualifier("note", $oFeature->getNote());
        }

        return $sOutput;
    }

    /**
     * @param   string      $sQualifier
     * @param   string      $sValue
     * @return  string
     */
    private function writeQualifier(string $sQualifier, string $sValue): string
    {
        return sprintf("%-21s/%s=\"%s\"\n", "", $sQualifier, str_replace('"', '""', $sValue));
    }

    /**
     * @param   string      $sSequence
     * @return  string
     */
    private function writeOriginBlock(string $sSequence): string
    {
        $sOutput = "ORIGIN\n";
        $sLower = strtolower($sSequence);
        $iLength = strlen($sLower);

        for ($iPos = 0; $iPos < $iLength; $iPos += self::BASES_PER_LINE) {
            $sChunk = substr($sLower, $iPos, self::BASES_PER_LINE);
            $sOutput .= sprintf(
                "%9d %s\n",
                $iPos + 1,
                implode(" ", str_split($sChunk, self::BASES_PER_GROUP))
            );
        }

        $sOutput .= "//\n";

        return $sOutput;
    }
}
