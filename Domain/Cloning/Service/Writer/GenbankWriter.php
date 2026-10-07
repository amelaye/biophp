<?php
/**
 * Serializes a Plasmid into GenBank flat-file text
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 7 October 2026
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
 * looking values for them would misrepresent the record rather than describe it. The LOCUS line
 * follows NCBI's fixed columns (see writeLocusLine()), except for a locus name longer than 16
 * characters, written in full rather than truncated.
 *
 * A REVERSE-strand feature is written as "complement(start..end)" with start <= end, the standard
 * GenBank convention - a feature crossing the origin (start > end) has no such representation and is
 * rejected by throwing, same as GffFeatureWriter and BedFeatureWriter.
 *
 * A CDS phase is written as /codon_start = phase + 1, a bare number as INSDC specifies. A PROMOTER or
 * TERMINATOR with no GenBank key of its own is written as "regulatory" with the matching
 * /regulatory_class, the "promoter" and "terminator" keys being deprecated since 15-DEC-2014.
 * A feature's GenBank key and its /gene, /label, /product, /note qualifiers are read back from
 * `getMetadata()`/`getNote()` exactly as GenbankPlasmidMapper wrote them when importing, so a
 * read-then-write round trip of a feature that originated from GenBank is lossless. A feature with
 * no such metadata (hand-built, imported from GFF3/BED...) falls back to a FeatureType-derived key ;
 * FeatureType's richer vocabulary (INSERT, MARKER, REPORTER, TAG, RESTRICTION_SITE...) has no GenBank
 * feature-key equivalent for most of its members, so those honestly fall back to "misc_feature"
 * rather than a plausible-looking but wrong guess.
 * Class GenbankWriter
 * @package Amelaye\BioPHP\Domain\Cloning\Service\Writer
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
        FeatureType::PROMOTER => "regulatory",
        FeatureType::TERMINATOR => "regulatory",
        FeatureType::ORIGIN_OF_REPLICATION => "rep_origin",
    ];

    /**
     * The /regulatory_class a "regulatory" feature built from these types is written with, INSDC
     * having deprecated the "promoter" and "terminator" keys on 15-DEC-2014.
     * @var     array<string,string>
     */
    private const REGULATORY_CLASS_BY_FEATURE_TYPE = [
        FeatureType::PROMOTER => "promoter",
        FeatureType::TERMINATOR => "terminator",
    ];

    /**
     * @param   Plasmid     $oPlasmid
     * @return  string
     */
    public function write(Plasmid $oPlasmid): string
    {
        $iLength = $oPlasmid->getLength();

        $sOutput = $this->writeLocusLine($oPlasmid->getName(), $iLength);

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
     * NCBI's fixed LOCUS columns (1-based) : name 13-28, length right-justified 30-40, "bp" 42-43,
     * molecule type 48-53, topology 56-63 - the columns ParseGenbankManager::parseLocus() reads
     * back. A name longer than the 16 columns it is given is written in full, the remaining fields
     * separated by spaces, as NCBI itself does for long locus names ; a column-based reader such as
     * ParseGenbankManager then cannot read that line back.
     * @param   string      $sName
     * @param   int         $iLength
     * @return  string
     */
    private function writeLocusLine(string $sName, int $iLength): string
    {
        if (strlen($sName) > 16) {
            return sprintf("LOCUS       %s %d bp    DNA     circular\n", $sName, $iLength);
        }

        return sprintf("LOCUS       %-16s %11d bp    %-6s  circular\n", $sName, $iLength, "DNA");
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

        if ($sKey === "regulatory") {
            $sRegulatoryClass = $aMetadata["regulatoryClass"]
                ?? self::REGULATORY_CLASS_BY_FEATURE_TYPE[$oFeature->getType()]
                ?? null;
            if ($sRegulatoryClass !== null) {
                $sOutput .= $this->writeQualifier("regulatory_class", $sRegulatoryClass);
            }
        }

        if (!empty($aMetadata["gene"])) {
            $sOutput .= $this->writeQualifier("gene", $aMetadata["gene"]);
        }
        if (!empty($aMetadata["label"])) {
            $sOutput .= $this->writeQualifier("label", $aMetadata["label"]);
        }
        if (!empty($aMetadata["product"])) {
            $sOutput .= $this->writeQualifier("product", $aMetadata["product"]);
        }
        if ($oFeature->getPhase() !== null) {
            $sOutput .= sprintf("%-21s/codon_start=%d\n", "", $oFeature->getPhase() + 1);
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
