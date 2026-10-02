<?php
/**
 * Immutable value object holding the outcome of reading a GFF3 file
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Cloning\Result;

use Amelaye\BioPHP\Domain\Cloning\ValueObject\PlasmidFeature;

/**
 * Unlike GenbankImportResult, this never wraps a whole Plasmid : a GFF3 file annotates a sequence it
 * does not itself carry, so the caller is expected to attach getFeatures() onto a Plasmid it already
 * has, via repeated withFeature() calls, which is also where an out-of-range coordinate for that
 * particular plasmid would surface.
 * Class GffImportResult
 * @package Amelaye\BioPHP\Domain\Cloning\Result
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class GffImportResult
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
     * GffImportResult constructor.
     * @param   PlasmidFeature[]    $aFeatures
     * @param   string[]            $aWarnings
     */
    public function __construct(array $aFeatures, array $aWarnings = [])
    {
        foreach ($aFeatures as $oFeature) {
            if (!$oFeature instanceof PlasmidFeature) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'GffImportResult features must be PlasmidFeature instances, got %s.',
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
