<?php
/**
 * Serializes PlasmidFeature instances into GFF3 text
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Cloning\Service\Writer;

use Amelaye\BioPHP\Domain\Cloning\Interfaces\GffFeatureWriterInterface;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\FeatureType;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\PlasmidFeature;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\Strand;

/**
 * The inverse of GffFeatureReader. Coordinates need no conversion : GFF3, like PlasmidFeature, is
 * 1-based inclusive. GFF3 does not support an origin-crossing feature (start > end) any more on the
 * way out than on the way in - GffFeatureReader rejects one with a warning, this writer rejects one
 * by throwing, since there is no line it could produce that would round-trip correctly.
 *
 * A feature read in FROM a GFF3 file carries its original "type" column in
 * `getMetadata()["gffType"]` ; that exact term is reused here so a read-then-write round trip is
 * lossless. A feature built some other way (hand-constructed, imported from GenBank, produced by
 * neighbor-joining-adjacent tooling...) has no such metadata, and FeatureType's own richer
 * vocabulary (INSERT, MARKER, REPORTER, TAG, RESTRICTION_SITE...) has no exact Sequence Ontology
 * counterpart for most of its members ; those fall back to the genuine generic SO term
 * "sequence_feature" rather than a plausible-looking but wrong guess.
 * Class GffFeatureWriter
 * @package Amelaye\BioPHP\Domain\Cloning\Service
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
    public function write(string $sSeqId, array $aFeatures): string
    {
        $sOutput = "##gff-version 3\n";

        foreach ($aFeatures as $oFeature) {
            if (!$oFeature instanceof PlasmidFeature) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'GffFeatureWriter features must be PlasmidFeature instances, got %s.',
                        is_object($oFeature) ? get_class($oFeature) : gettype($oFeature)
                    )
                );
            }

            $sOutput .= $this->writeFeatureLine($sSeqId, $oFeature);
        }

        return $sOutput;
    }

    /**
     * @param   string          $sSeqId
     * @param   PlasmidFeature  $oFeature
     * @return  string
     */
    private function writeFeatureLine(string $sSeqId, PlasmidFeature $oFeature): string
    {
        if ($oFeature->crossesOrigin()) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Feature "%s" crosses the origin (start %d > end %d) ; GFF3 has no way to'
                    . ' represent that.',
                    $oFeature->getName(),
                    $oFeature->getStart(),
                    $oFeature->getEnd()
                )
            );
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

        $aAttributes = ["Name=" . $this->escapeAttributeValue($oFeature->getName())];
        if ($oFeature->getNote() !== null) {
            $aAttributes[] = "Note=" . $this->escapeAttributeValue($oFeature->getNote());
        }

        return implode("\t", [
            $sSeqId,
            ".",
            $sType,
            (string) $oFeature->getStart(),
            (string) $oFeature->getEnd(),
            ".",
            $sStrand,
            ".",
            implode(";", $aAttributes),
        ]) . "\n";
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
