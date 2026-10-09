<?php
/**
 * Immutable value object holding the outcome of reading a BED file
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Cloning\Result;

use Amelaye\BioPHP\Domain\Cloning\ValueObject\PlasmidFeature;

/**
 * Unlike GenbankImportResult, this never wraps a whole Plasmid : a BED file annotates a sequence it
 * does not itself carry, exactly like GffImportResult ; the caller attaches getFeatures() onto a
 * Plasmid it already has.
 * Class BedImportResult
 * @package Amelaye\BioPHP\Domain\Cloning\Result
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class BedImportResult
{
    /**
     * @var     PlasmidFeature[]
     */
    private array $features;

    /**
     * @var     string[]
     */
    private array $warnings;

    /**
     * BedImportResult constructor.
     * @param   PlasmidFeature[]    $aFeatures
     * @param   string[]            $aWarnings
     */
    public function __construct(array $aFeatures, array $aWarnings = [])
    {
        foreach ($aFeatures as $oFeature) {
            if (!$oFeature instanceof PlasmidFeature) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'BedImportResult features must be PlasmidFeature instances, got %s.',
                        is_object($oFeature) ? get_class($oFeature) : gettype($oFeature)
                    )
                );
            }
        }

        $this->features = array_values($aFeatures);
        $this->warnings = array_values($aWarnings);
    }

    /**
     * @return  PlasmidFeature[]
     */
    public function getFeatures(): array
    {
        return $this->features;
    }

    /**
     * @return  string[]
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }
}
