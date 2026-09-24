<?php
/**
 * Immutable value object wrapping a circular DNA sequence and its origin-crossing operations
 * Freely inspired by BioPHP's project biophp.org
 * Created 24 September 2026
 * Last modified 24 September 2026
 */
namespace Amelaye\BioPHP\Domain\Sequence\ValueObject;

/**
 * A DNA sequence with no first or last symbol : position 0 follows the last symbol, so rotating and
 * slicing must be able to cross that origin. All three methods below are zero-based, consistently
 * with AbstractMolecularSequence::subSequence(). Extracting a piece with sliceCircular() yields a
 * plain, linear DnaSequence : a fragment cut out of a circular molecule is no longer circular.
 * Class CircularDnaSequence
 * @package Amelaye\BioPHP\Domain\Sequence\ValueObject
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class CircularDnaSequence extends DnaSequence
{
    /**
     * CircularDnaSequence constructor.
     * @param   string      $sSequence      The raw sequence string, must not be empty once normalized
     * @throws  InvalidSequenceException    When a symbol does not belong to the alphabet, or the
     * sequence is empty
     */
    public function __construct(string $sSequence)
    {
        parent::__construct($sSequence);

        if ($this->isEmpty()) {
            throw InvalidSequenceException::emptyCircularSequence();
        }
    }

    /**
     * Brings a zero-based position back within [0, length[, wrapping around the origin as many
     * times as needed. A position equal to the length, or a negative one, is therefore always valid.
     * @param   int         $iPosition      Any zero-based position, may be negative or oversized
     * @return  int                         The equivalent position within [0, length[
     */
    public function positionModulo(int $iPosition) : int
    {
        $iModulo = $iPosition % $this->getLength();

        if ($iModulo < 0) {
            $iModulo += $this->getLength();
        }

        return $iModulo;
    }

    /**
     * Returns the same circular molecule read starting from a different zero-based origin. Rotating
     * by zero or by a multiple of the sequence length yields a sequence with the same symbols.
     * @param   int         $iPosition      Zero-based position of the new origin
     * @return  self
     * @throws  InvalidSequenceException
     */
    public function rotateTo(int $iPosition) : self
    {
        $iStart = $this->positionModulo($iPosition);
        $sValue = $this->getValue();

        return new self(substr($sValue, $iStart) . substr($sValue, 0, $iStart));
    }

    /**
     * Extracts a linear fragment of the circular molecule, wrapping past the origin when the slice
     * reaches the last symbol before covering its whole requested length.
     * @param   int         $iStart         Zero-based position of the first symbol, may be negative
     * or oversized, normalized with positionModulo()
     * @param   int         $iLength        Number of symbols to extract, at most the sequence length
     * @return  DnaSequence                 A linear fragment, not a circular one
     * @throws  InvalidSequenceException    When $iLength is negative or exceeds the sequence length
     */
    public function sliceCircular(int $iStart, int $iLength) : DnaSequence
    {
        if ($iLength < 0) {
            throw InvalidSequenceException::negativeCircularSliceLength($iLength);
        }

        $iSequenceLength = $this->getLength();

        if ($iLength > $iSequenceLength) {
            throw InvalidSequenceException::circularSliceLengthExceedsSequence($iLength, $iSequenceLength);
        }

        if ($iLength === 0) {
            return new DnaSequence("");
        }

        $iStart = $this->positionModulo($iStart);
        $sValue = $this->getValue();
        $iTailLength = $iSequenceLength - $iStart;

        if ($iLength <= $iTailLength) {
            $sSlice = substr($sValue, $iStart, $iLength);
        } else {
            $sSlice = substr($sValue, $iStart, $iTailLength) . substr($sValue, 0, $iLength - $iTailLength);
        }

        return new DnaSequence($sSlice);
    }
}
