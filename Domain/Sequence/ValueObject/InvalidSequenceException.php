<?php
/**
 * Raised when a string cannot be wrapped into a sequence value object
 * Freely inspired by BioPHP's project biophp.org
 * Created 25 August 2026
 * Last modified 24 September 2026
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

    /**
     * Builds the exception raised when a circular sequence is built from an empty string.
     * @return  InvalidSequenceException
     */
    public static function emptyCircularSequence() : self
    {
        return new self("A circular DNA sequence must not be empty.");
    }

    /**
     * Builds the exception raised when sliceCircular() is asked for a negative length.
     * @param   int         $iLength        The rejected, negative length
     * @return  InvalidSequenceException
     */
    public static function negativeCircularSliceLength(int $iLength) : self
    {
        return new self(
            sprintf('Circular slice length must not be negative, got %d.', $iLength)
        );
    }

    /**
     * Builds the exception raised when sliceCircular() is asked for a length longer than the
     * circular sequence itself, which would require an explicit, not yet supported, repeat.
     * @param   int         $iLength            The rejected length
     * @param   int         $iSequenceLength    The length of the circular sequence
     * @return  InvalidSequenceException
     */
    public static function circularSliceLengthExceedsSequence(int $iLength, int $iSequenceLength) : self
    {
        return new self(
            sprintf(
                'Circular slice length %d exceeds sequence length %d; wrapping repeats are not supported.',
                $iLength,
                $iSequenceLength
            )
        );
    }
}
