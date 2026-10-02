<?php
/**
 * Immutable value object wrapping a validated symmetric distance matrix between taxa
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 2 October 2026
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

        $aDistances = array_values($aDistances);
        foreach ($aDistances as $aRow) {
            if (count($aRow) !== $iCount) {
                throw InvalidDistanceMatrixException::mismatchedDimensions($iCount, count($aRow));
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
