<?php
/**
 * Serializes PlasmidFeature instances into GFF3 text
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 6 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Cloning\Service\Writer;

use Amelaye\BioPHP\Domain\Cloning\Interfaces\GffFeatureWriterInterface;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\FeatureType;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\PlasmidFeature;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\Strand;

/**
 * The inverse of GffFeatureReader. Coordinates need no conversion : GFF3, like PlasmidFeature, is
 * 1-based inclusive. An origin-crossing feature (start > end) is written the GFF3 way, with end =
 * its real end + the molecule length, which therefore has to be given ; a landmark line spanning
 * 1..length and flagged Is_circular=true is then written too (or, when a feature read in as that
 * "region" landmark is among the features, that line is flagged instead), so GffFeatureReader can
 * fold the feature back. Without the length, such a feature is rejected by throwing.
 *
 * GFF3 requires a phase on every CDS. A CDS whose phase is unknown is written with phase 0, i.e. as
 * starting on a complete codon, the usual assumption for a complete CDS ; any other type gets ".".
 *
 * A feature read in FROM a GFF3 file carries its original "type" column in
 * `getMetadata()["gffType"]` ; that exact term is reused here so a read-then-write round trip is
 * lossless. A feature built some other way (hand-constructed, imported from GenBank, produced by
 * neighbor-joining-adjacent tooling...) has no such metadata, and FeatureType's own richer
 * vocabulary (INSERT, MARKER, REPORTER, TAG, RESTRICTION_SITE...) has no exact Sequence Ontology
 * counterpart for most of its members ; those fall back to the genuine generic SO term
 * "sequence_feature" rather than a plausible-looking but wrong guess.
 * Class GffFeatureWriter
 * @package Amelaye\BioPHP\Domain\Cloning\Service\Writer
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class GffFeatureWriter implements GffFeatureWriterInterface
{
    private const GENERIC_SO_TERM = "sequence_feature";

    /**
     * @var     array<string,string>
     */
    private const SO_TERM_BY_FEATURE_TYPE = [
        FeatureType::CDS => "CDS",
        FeatureType::PROMOTER => "promoter",
        FeatureType::TERMINATOR => "terminator",
        FeatureType::ORIGIN_OF_REPLICATION => "origin_of_replication",
    ];

    /**
     * @param   string              $sSeqId
     * @param   PlasmidFeature[]    $aFeatures
     * @return  string
     */
    public function write(string $sSeqId, array $aFeatures, ?int $iSequenceLength = null): string
    {
        if ($iSequenceLength !== null && $iSequenceLength < 1) {
            throw new \InvalidArgumentException(
                sprintf('Sequence length must be at least 1, got %d.', $iSequenceLength)
            );
        }

        foreach ($aFeatures as $oFeature) {
            if (!$oFeature instanceof PlasmidFeature) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'GffFeatureWriter features must be PlasmidFeature instances, got %s.',
                        is_object($oFeature) ? get_class($oFeature) : gettype($oFeature)
                    )
                );
            }
        }

        $oLandmark = null;
        if ($iSequenceLength !== null) {
            foreach ($aFeatures as $oFeature) {
                if ($this->isCircularLandmark($oFeature, $iSequenceLength)) {
                    $oLandmark = $oFeature;
                    break;
                }
            }
        }

        $sOutput = "##gff-version 3\n";

        if ($iSequenceLength !== null && $oLandmark === null) {
            $sOutput .= implode("\t", [
                $sSeqId, ".", "region", "1", (string) $iSequenceLength, ".", ".", ".",
                "ID=" . $this->escapeAttributeValue($sSeqId) . ";Is_circular=true",
            ]) . "\n";
        }

        foreach ($aFeatures as $oFeature) {
            $sOutput .= $this->writeFeatureLine($sSeqId, $oFeature, $iSequenceLength, $oFeature === $oLandmark);
        }

        return $sOutput;
    }

    /**
     * True for a feature read in from GFF3 as the "region" landmark spanning the whole molecule.
     * @param   PlasmidFeature  $oFeature
     * @param   int             $iSequenceLength
     * @return  bool
     */
    private function isCircularLandmark(PlasmidFeature $oFeature, int $iSequenceLength): bool
    {
        return ($oFeature->getMetadata()["gffType"] ?? null) === "region"
            && $oFeature->getStart() === 1
            && $oFeature->getEnd() === $iSequenceLength;
    }

    /**
     * @param   string          $sSeqId
     * @param   PlasmidFeature  $oFeature
     * @param   int|null        $iSequenceLength
     * @param   bool            $bIsLandmark        Flags the line as the circular landmark
     * @return  string
     */
    private function writeFeatureLine(
        string $sSeqId,
        PlasmidFeature $oFeature,
        ?int $iSequenceLength,
        bool $bIsLandmark
    ): string {
        $iEnd = $oFeature->getEnd();

        if ($iSequenceLength !== null && max($oFeature->getStart(), $iEnd) > $iSequenceLength) {
            // Written as is, such an end would be read back as an origin-crossing one.
            throw new \InvalidArgumentException(
                sprintf(
                    'Feature "%s" (%d..%d) lies beyond the sequence length %d.',
                    $oFeature->getName(),
                    $oFeature->getStart(),
                    $iEnd,
                    $iSequenceLength
                )
            );
        }

        if ($oFeature->crossesOrigin()) {
            if ($iSequenceLength === null) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Feature "%s" crosses the origin (start %d > end %d) ; the sequence length is'
                        . ' needed to write it as GFF3 end + length.',
                        $oFeature->getName(),
                        $oFeature->getStart(),
                        $oFeature->getEnd()
                    )
                );
            }
            $iEnd += $iSequenceLength;
        }

        $sType = $oFeature->getMetadata()["gffType"]
            ?? self::SO_TERM_BY_FEATURE_TYPE[$oFeature->getType()]
            ?? self::GENERIC_SO_TERM;

        if ($oFeature->getStrand() === Strand::FORWARD) {
            $sStrand = "+";
        } elseif ($oFeature->getStrand() === Strand::REVERSE) {
            $sStrand = "-";
        } else {
            $sStrand = ".";
        }

        $aAttributes = [];
        if ($bIsLandmark) {
            $aAttributes[] = "ID=" . $this->escapeAttributeValue($sSeqId);
            $aAttributes[] = "Is_circular=true";
        }
        $aAttributes[] = "Name=" . $this->escapeAttributeValue($oFeature->getName());
        if ($oFeature->getNote() !== null) {
            $aAttributes[] = "Note=" . $this->escapeAttributeValue($oFeature->getNote());
        }

        return implode("\t", [
            $sSeqId,
            ".",
            $sType,
            (string) $oFeature->getStart(),
            (string) $iEnd,
            ".",
            $sStrand,
            $this->formatPhase($oFeature),
            implode(";", $aAttributes),
        ]) . "\n";
    }

    /**
     * @param   PlasmidFeature  $oFeature
     * @return  string      "0", "1" or "2" on a CDS (0 when unknown), "." otherwise
     */
    private function formatPhase(PlasmidFeature $oFeature): string
    {
        if ($oFeature->getType() !== FeatureType::CDS) {
            return ".";
        }

        return (string) ($oFeature->getPhase() ?? 0);
    }

    /**
     * Percent-encodes the GFF3 column-9 reserved characters (and control characters that would
     * otherwise break the one-line-per-feature format).
     * @param   string      $sValue
     * @return  string
     */
    private function escapeAttributeValue(string $sValue): string
    {
        return strtr($sValue, [
            "%" => "%25",
            ";" => "%3B",
            "=" => "%3D",
            "," => "%2C",
            "\t" => "%09",
            "\n" => "%0A",
            "\r" => "%0D",
        ]);
    }
}
