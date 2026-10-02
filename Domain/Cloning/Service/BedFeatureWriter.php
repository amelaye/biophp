<?php
/**
 * Serializes PlasmidFeature instances into BED text
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Cloning\Service;

use Amelaye\BioPHP\Domain\Cloning\Interfaces\BedFeatureWriterInterface;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\PlasmidFeature;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\Strand;

/**
 * The inverse of BedFeatureReader. PlasmidFeature's 1-based inclusive coordinates are converted back
 * to BED's zero-based, half-open ones the same way the reader converted them in : chromStart =
 * start - 1, chromEnd = end. An origin-crossing feature (start > end) has no BED representation -
 * BedFeatureReader already rejects one coming in with a warning, and this writer rejects one going
 * out by throwing, for the same reason GffFeatureWriter does.
 * Class BedFeatureWriter
 * @package Amelaye\BioPHP\Domain\Cloning\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class BedFeatureWriter implements BedFeatureWriterInterface
{
    /**
     * @param   string              $sChrom
     * @param   PlasmidFeature[]    $aFeatures
     * @return  string
     */
    public function write(string $sChrom, array $aFeatures): string
    {
        $sOutput = "";

        foreach ($aFeatures as $oFeature) {
            if (!$oFeature instanceof PlasmidFeature) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'BedFeatureWriter features must be PlasmidFeature instances, got %s.',
                        is_object($oFeature) ? get_class($oFeature) : gettype($oFeature)
                    )
                );
            }

            $sOutput .= $this->writeFeatureLine($sChrom, $oFeature);
        }

        return $sOutput;
    }

    /**
     * @param   string          $sChrom
     * @param   PlasmidFeature  $oFeature
     * @return  string
     */
    private function writeFeatureLine(string $sChrom, PlasmidFeature $oFeature): string
    {
        if ($oFeature->crossesOrigin()) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Feature "%s" crosses the origin (start %d > end %d) ; BED has no way to'
                    . ' represent that.',
                    $oFeature->getName(),
                    $oFeature->getStart(),
                    $oFeature->getEnd()
                )
            );
        }

        if ($oFeature->getStrand() === Strand::FORWARD) {
            $sStrand = "+";
        } elseif ($oFeature->getStrand() === Strand::REVERSE) {
            $sStrand = "-";
        } else {
            $sStrand = ".";
        }

        $sScore = $oFeature->getMetadata()["bedScore"] ?? "0";

        return implode("\t", [
            $sChrom,
            (string) ($oFeature->getStart() - 1),
            (string) $oFeature->getEnd(),
            $oFeature->getName(),
            (string) $sScore,
            $sStrand,
        ]) . "\n";
    }
}
