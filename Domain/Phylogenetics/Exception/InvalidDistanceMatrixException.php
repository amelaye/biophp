<?php
/**
 * Raised when a DistanceMatrix is built from inconsistent labels or distances
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Phylogenetics\Exception;

/**
 * Class InvalidDistanceMatrixException
 * @package Amelaye\BioPHP\Domain\Phylogenetics\Exception
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class InvalidDistanceMatrixException extends \InvalidArgumentException
{
    /**
     * Builds the exception raised when no taxon at all is given.
     * @return  InvalidDistanceMatrixException
     */
    public static function noTaxa(): self
    {
        return new self("A distance matrix must describe at least one taxon.");
    }

    /**
     * Builds the exception raised when a label is empty or not a string.
     * @param   int         $iPosition
     * @return  InvalidDistanceMatrixException
     */
    public static function emptyLabel(int $iPosition): self
    {
        return new self(
            sprintf('Label at position %d must be a non-empty string.', $iPosition)
        );
    }

    /**
     * Builds the exception raised when the same label is given twice, which would make it impossible
     * to tell the corresponding taxa apart in the resulting tree.
     * @param   string      $sLabel
     * @return  InvalidDistanceMatrixException
     */
    public static function duplicateLabel(string $sLabel): self
    {
        return new self(
            sprintf('Label "%s" is given more than once.', $sLabel)
        );
    }

    /**
     * Builds the exception raised when the distance matrix is not exactly N x N for N labels.
     * @param   int         $iExpected
     * @param   int         $iActual
     * @return  InvalidDistanceMatrixException
     */
    public static function mismatchedDimensions(int $iExpected, int $iActual): self
    {
        return new self(
            sprintf('Distance matrix must be %1$d x %1$d, got a dimension of %2$d.', $iExpected, $iActual)
        );
    }

    /**
     * Builds the exception raised when a taxon's distance to itself is not exactly zero.
     * @param   int         $iIndex
     * @param   float       $fValue
     * @return  InvalidDistanceMatrixException
     */
    public static function nonZeroDiagonal(int $iIndex, float $fValue): self
    {
        return new self(
            sprintf('Distance of taxon %d to itself must be 0, got %s.', $iIndex, $fValue)
        );
    }

    /**
     * Builds the exception raised when D[i][j] and D[j][i] disagree, which breaks the symmetry a
     * distance matrix requires.
     * @param   int         $iRow
     * @param   int         $iColumn
     * @param   float       $fForward
     * @param   float       $fBackward
     * @return  InvalidDistanceMatrixException
     */
    public static function asymmetricEntry(int $iRow, int $iColumn, float $fForward, float $fBackward): self
    {
        return new self(
            sprintf(
                'Distance matrix is not symmetric : D[%d][%d]=%s but D[%d][%d]=%s.',
                $iRow,
                $iColumn,
                $fForward,
                $iColumn,
                $iRow,
                $fBackward
            )
        );
    }

    /**
     * Builds the exception raised when an off-diagonal distance is negative, which is not a valid
     * input measurement regardless of the tree it might imply.
     * @param   int         $iRow
     * @param   int         $iColumn
     * @param   float       $fValue
     * @return  InvalidDistanceMatrixException
     */
    public static function negativeDistance(int $iRow, int $iColumn, float $fValue): self
    {
        return new self(
            sprintf('Distance D[%d][%d]=%s must not be negative.', $iRow, $iColumn, $fValue)
        );
    }
}
