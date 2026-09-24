<?php
/**
 * Raised when a PlasmidFeature coordinate violates the 1-based inclusive coordinate convention
 * Freely inspired by BioPHP's project biophp.org
 * Created 24 September 2026
 * Last modified 24 September 2026
 */
namespace Amelaye\BioPHP\Domain\Cloning\Exception;

/**
 * Class InvalidFeatureCoordinatesException
 * @package Amelaye\BioPHP\Domain\Cloning\Exception
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class InvalidFeatureCoordinatesException extends \InvalidArgumentException
{
    /**
     * Builds the exception raised when a PlasmidFeature is given a start or end coordinate below 1,
     * the first valid position of the 1-based inclusive convention.
     * @param   string      $sBound         "start" or "end"
     * @param   int         $iValue         The rejected coordinate
     * @return  InvalidFeatureCoordinatesException
     */
    public static function belowOrigin(string $sBound, int $iValue): self
    {
        return new self(
            sprintf('Feature coordinate "%s" must be at least 1 (1-based inclusive), got %d.', $sBound, $iValue)
        );
    }

    /**
     * Builds the exception raised when a PlasmidFeature is attached to a Plasmid whose sequence is
     * shorter than one of the feature's coordinates.
     * @param   string      $sFeatureName       The offending feature's name
     * @param   string      $sBound             "start" or "end"
     * @param   int         $iValue             The rejected coordinate
     * @param   int         $iSequenceLength    The length of the plasmid sequence
     * @return  InvalidFeatureCoordinatesException
     */
    public static function beyondSequenceLength(
        string $sFeatureName,
        string $sBound,
        int $iValue,
        int $iSequenceLength
    ): self {
        return new self(
            sprintf(
                'Feature "%s" has %s coordinate %d beyond the plasmid length of %d.',
                $sFeatureName,
                $sBound,
                $iValue,
                $iSequenceLength
            )
        );
    }
}
