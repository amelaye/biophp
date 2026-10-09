<?php
/**
 * Immutable value object wrapping a validated symmetric distance matrix between taxa
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 9 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Phylogenetics\ValueObject;

use Amelaye\BioPHP\Domain\Phylogenetics\Exception\InvalidDistanceMatrixException;

/**
 * Validates, once and for all at construction, every invariant NeighborJoiningTreeBuilder relies on :
 * square, zero diagonal, symmetric, non-negative, unique non-empty labels. A distance is itself
 * always non-negative by definition (it is a measurement, unlike the branch lengths the neighbor-
 * joining algorithm later derives from it, which can legitimately go negative).
 * Class DistanceMatrix
 * @package Amelaye\BioPHP\Domain\Phylogenetics\ValueObject
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class DistanceMatrix
{
    /**
     * @var     string[]
     */
    private array $labels;

    /**
     * @var     float[][]
     */
    private array $distances;

    /**
     * DistanceMatrix constructor.
     * @param   string[]    $aLabels        One name per taxon, unique and non-empty
     * @param   array       $aDistances     An N x N matrix, N = count($aLabels) ; zero diagonal,
     * symmetric, non-negative
     * @throws  InvalidDistanceMatrixException
     */
    public function __construct(array $aLabels, array $aDistances)
    {
        $iCount = count($aLabels);

        if ($iCount === 0) {
            throw InvalidDistanceMatrixException::noTaxa();
        }

        $aSeenLabels = [];
        foreach (array_values($aLabels) as $iIndex => $sLabel) {
            if (!is_string($sLabel) || $sLabel === "") {
                throw InvalidDistanceMatrixException::emptyLabel($iIndex);
            }
            if (isset($aSeenLabels[$sLabel])) {
                throw InvalidDistanceMatrixException::duplicateLabel($sLabel);
            }
            $aSeenLabels[$sLabel] = true;
        }

        if (count($aDistances) !== $iCount) {
            throw InvalidDistanceMatrixException::mismatchedDimensions($iCount, count($aDistances));
        }

        // A matrix keyed by label (rows and columns) is read by label, in the order of the labels ; any
        // other is read in order, whatever its keys. Reading a label-keyed one in its own order built
        // a wrong tree when it was not listed in the order of the labels.
        $aLabelList = array_values($aLabels);
        $aRows = self::inLabelOrder($aDistances, $aLabelList) ?? array_values($aDistances);
        $aDistances = [];
        foreach ($aRows as $iRow => $aRow) {
            if (!is_array($aRow)) {
                throw InvalidDistanceMatrixException::mismatchedDimensions($iCount, 1);
            }
            $aDistances[] = self::inLabelOrder($aRow, $aLabelList) ?? array_values($aRow);
        }
        foreach ($aDistances as $iRow => $aRow) {
            if (count($aRow) !== $iCount) {
                throw InvalidDistanceMatrixException::mismatchedDimensions($iCount, count($aRow));
            }
            foreach ($aRow as $iColumn => $mValue) {
                if (!is_int($mValue) && !is_float($mValue) && !(is_string($mValue) && is_numeric($mValue))) {
                    throw InvalidDistanceMatrixException::nonNumericDistance($iRow, $iColumn, $mValue);
                }
            }
        }

        for ($i = 0; $i < $iCount; $i++) {
            $fDiagonal = (float) $aDistances[$i][$i];
            if ($fDiagonal !== 0.0) {
                throw InvalidDistanceMatrixException::nonZeroDiagonal($i, $fDiagonal);
            }

            for ($j = $i + 1; $j < $iCount; $j++) {
                $fForward = (float) $aDistances[$i][$j];
                $fBackward = (float) $aDistances[$j][$i];

                if (!is_finite($fForward)) {
                    throw InvalidDistanceMatrixException::nonFiniteDistance($i, $j, $fForward);
                }

                if ($fForward !== $fBackward) {
                    throw InvalidDistanceMatrixException::asymmetricEntry($i, $j, $fForward, $fBackward);
                }

                if ($fForward < 0.0) {
                    throw InvalidDistanceMatrixException::negativeDistance($i, $j, $fForward);
                }
            }
        }

        $this->labels = array_values($aLabels);
        $this->distances = array_map(
            static fn(array $aRow) => array_map(static fn($mValue) => (float) $mValue, $aRow),
            $aDistances
        );
    }

    /**
     * @param   array       $aMap           Values keyed by label, or a plain list
     * @param   string[]    $aLabels
     * @return  array|null                  The values in the order of the labels, null when the keys
     * are not exactly the labels
     */
    private static function inLabelOrder(array $aMap, array $aLabels): ?array
    {
        // A list is read in order, even when numeric labels happen to be a permutation of its indexes
        if (array_is_list($aMap)) {
            return null;
        }

        $aKeys = array_map('strval', array_keys($aMap));
        if (count($aKeys) !== count($aLabels) || array_diff($aLabels, $aKeys) !== []) {
            return null;
        }

        return array_map(static fn(string $sLabel) => $aMap[$sLabel], $aLabels);
    }

    /**
     * @return  string[]
     */
    public function getLabels(): array
    {
        return $this->labels;
    }

    /**
     * @return  int
     */
    public function getSize(): int
    {
        return count($this->labels);
    }

    /**
     * @return  float[][]
     */
    public function getMatrix(): array
    {
        return $this->distances;
    }

    /**
     * @param   int         $iRow
     * @param   int         $iColumn
     * @return  float
     */
    public function getDistance(int $iRow, int $iColumn): float
    {
        return $this->distances[$iRow][$iColumn];
    }
}
