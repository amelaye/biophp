<?php
/**
 * Raised when a string cannot be wrapped into a sequence value object
 * Freely inspired by BioPHP's project biophp.org
 * Created 25 August 2026
 * Last modified 25 August 2026
 */
namespace Amelaye\BioPHP\Domain\Sequence\ValueObject;

/**
 * Class InvalidSequenceException
 * @package Amelaye\BioPHP\Domain\Sequence\ValueObject
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class InvalidSequenceException extends \InvalidArgumentException
{
    /**
     * Builds the exception raised when a symbol does not belong to the alphabet of the value object.
     * @param   string      $sMolType       The molecule type of the rejected value object (DNA, RNA, PROTEIN)
     * @param   string      $sSymbol        The offending symbol
     * @param   int         $iPosition      Zero-based position of the offending symbol
     * @return  InvalidSequenceException
     */
    public static function invalidSymbol(string $sMolType, string $sSymbol, int $iPosition) : self
    {
        return new self(
            sprintf(
                'Invalid %s symbol "%s" at position %d.',
                $sMolType,
                $sSymbol,
                $iPosition
            )
        );
    }

    /**
     * Builds the exception raised when a molecule type cannot be mapped to a value object.
     * @param   string      $sMolType       The unsupported molecule type
     * @return  InvalidSequenceException
     */
    public static function unsupportedMolType(string $sMolType) : self
    {
        return new self(sprintf('Unsupported molecule type "%s".', $sMolType));
    }
}
