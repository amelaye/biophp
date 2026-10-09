<?php
/**
 * Serializes a Plasmid into GenBank flat-file text
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 9 October 2026
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
 * GenBank convention. A feature crossing the origin (start > end) is written as INSDC does on a
 * circular molecule, "join(start..length,1..end)", wrapped in complement() on the REVERSE strand -
 * the form GenbankPlasmidMapper reads back as the same origin-crossing feature.
 *
 * DEFINITION and qualifier lines wrap at 79 characters, as NCBI writes them. The source feature
 * carries /organism and /mol_type when the plasmid's metadata gives them (GenbankPlasmidMapper keeps
 * those of the record it read).
 *
 * A feature with no /gene, /label or /product metadata is written with its name as /label.
 *
 * INSDC has no notation for an unstranded feature : a location without complement() lies on the
 * direct strand. A Strand::NONE feature is therefore written as a plain range followed by
 * /biophp_strand="none" (UNSTRANDED_QUALIFIER), a qualifier of this library's own, which
 * GenbankPlasmidMapper reads back as Strand::NONE. Any other reader ignores it and, as INSDC says,
 * reads the feature on the direct strand.
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

    private const MAX_LINE_LENGTH = 79;

    /**
     * The qualifier marking a feature on no particular strand, which INSDC cannot write
     */
    public const UNSTRANDED_QUALIFIER = "biophp_strand";

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
            // A DEFINITION ends with a period ; ParseGenbankManager keeps it in the description it
            // reads, which must not gain a second one at each round trip.
            $sOutput .= $this->wrap("DEFINITION  ", rtrim($oPlasmid->getDescription(), ".") . ".");
        }

        $sOutput .= "ACCESSION   " . ($oPlasmid->getExternalId() ?? $oPlasmid->getName()) . "\n";

        $sOutput .= "FEATURES             Location/Qualifiers\n";
        $sOutput .= sprintf("%-5s%-16s%s\n", "", "source", "1.." . $iLength);
        // INSDC wants both on every source feature, but a Plasmid has them only when it was read
        // from a record giving them : they are not made up.
        $aPlasmidMetadata = $oPlasmid->getMetadata();
        if (!empty($aPlasmidMetadata["organism"]) && is_string($aPlasmidMetadata["organism"])) {
            $sOutput .= $this->writeQualifier("organism", $aPlasmidMetadata["organism"]);
        }
        if (!empty($aPlasmidMetadata["molType"]) && is_string($aPlasmidMetadata["molType"])) {
            $sOutput .= $this->writeQualifier("mol_type", $aPlasmidMetadata["molType"]);
        }

        // Two features of one key at one location follow each other as one run of qualifiers when read
        // back : each of them then gets a /label, which the reader splits them on
        $aFeatures = array_values($oPlasmid->getFeatures());
        foreach ($aFeatures as $iIndex => $oFeature) {
            $sSignature = $this->locationSignature($oFeature);
            $bSharesItsLocation = ($iIndex > 0 && $this->locationSignature($aFeatures[$iIndex - 1]) === $sSignature)
                || (isset($aFeatures[$iIndex + 1]) && $this->locationSignature($aFeatures[$iIndex + 1]) === $sSignature);
            $sOutput .= $this->writeFeature($oFeature, $iLength, $bSharesItsLocation);
        }

        $sOutput .= $this->writeOriginBlock($oPlasmid->getSequence()->getValue());

        return $sOutput;
    }

    /**
     * NCBI's fixed LOCUS columns (1-based) : name 13-28, length right-justified 30-40, "bp" 42-43,
     * molecule type 48-53, topology 56-63. A name longer than the 16 columns it is given is written
     * in full, the remaining fields separated by spaces, as NCBI itself does for long locus names ;
     * ParseGenbankManager::parseLocus() reads both back, word by word.
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
     * @param   int             $iLength    The plasmid length, where an origin-crossing feature wraps
     * @param   bool            $bSharesItsLocation     Another feature of the same key lies next to it, at the same place
     * @return  string
     */
    private function writeFeature(PlasmidFeature $oFeature, int $iLength, bool $bSharesItsLocation = false): string
    {
        $aMetadata = $oFeature->getMetadata();
        $sKey = $aMetadata["genbankKey"]
            ?? self::GENBANK_KEY_BY_FEATURE_TYPE[$oFeature->getType()]
            ?? "misc_feature";

        $sLocation = $oFeature->getStart() . ".." . $oFeature->getEnd();
        if ($oFeature->crossesOrigin()) {
            $sLocation = "join(" . $oFeature->getStart() . ".." . $iLength . ",1.." . $oFeature->getEnd() . ")";
        }
        if ($oFeature->getStrand() === Strand::REVERSE) {
            $sLocation = "complement(" . $sLocation . ")";
        }

        $sOutput = sprintf("%-5s%-16s%s\n", "", $sKey, $sLocation);

        if ($oFeature->getStrand() === Strand::NONE) {
            $sOutput .= $this->writeQualifier(self::UNSTRANDED_QUALIFIER, "none");
        }

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
        // A feature built by hand or read from GFF3/BED carries its name alone : without a
        // /label it would come back named after its key.
        if (empty($aMetadata["gene"]) && empty($aMetadata["label"]) && empty($aMetadata["product"])
            && ($oFeature->getName() !== $sKey || $bSharesItsLocation)) {
            $sOutput .= $this->writeQualifier("label", $oFeature->getName());
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
     * @param   PlasmidFeature  $oFeature
     * @return  string          What a feature shares with another one that the reader cannot tell it from
     */
    private function locationSignature(PlasmidFeature $oFeature): string
    {
        $aMetadata = $oFeature->getMetadata();

        return implode("|", [
            $aMetadata["genbankKey"] ?? self::GENBANK_KEY_BY_FEATURE_TYPE[$oFeature->getType()] ?? "misc_feature",
            $oFeature->getStart(),
            $oFeature->getEnd(),
            $oFeature->getStrand(),
        ]);
    }

    /**
     * @param   string      $sQualifier
     * @param   string      $sValue
     * @return  string
     */
    private function writeQualifier(string $sQualifier, string $sValue): string
    {
        return $this->wrap(str_repeat(" ", 21), "/" . $sQualifier . "=\"" . str_replace('"', '""', $sValue) . "\"");
    }

    /**
     * Writes a text over as many lines of at most 79 characters as it needs, the first opening on
     * $sPrefix, the next ones indented as deep. Lines break at blanks ; a word longer than a whole
     * line is cut.
     * @param   string      $sPrefix    The label or indentation opening the first line
     * @param   string      $sText
     * @return  string
     */
    private function wrap(string $sPrefix, string $sText): string
    {
        $iWidth = self::MAX_LINE_LENGTH - strlen($sPrefix);
        $sIndent = str_repeat(" ", strlen($sPrefix));

        $aLines = explode("\n", wordwrap($sText, $iWidth, "\n", true));

        return $sPrefix . implode("\n" . $sIndent, $aLines) . "\n";
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
