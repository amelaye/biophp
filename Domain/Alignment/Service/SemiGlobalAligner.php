<?php
/**
 * Semi-global (overlap) pairwise sequence alignment by dynamic programming
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 7 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Alignment\Service;

use Amelaye\BioPHP\Domain\Alignment\Exception\InvalidAlignmentInputException;
use Amelaye\BioPHP\Domain\Alignment\Interfaces\SemiGlobalAlignerInterface;
use Amelaye\BioPHP\Domain\Alignment\Interfaces\SubstitutionScoringInterface;
use Amelaye\BioPHP\Domain\Alignment\Result\PairwiseAlignmentResult;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\AbstractMolecularSequence;

/**
 * The third classic pairwise alignment shape, between NeedlemanWunschAligner (global : every gap,
 * including leading and trailing ones, is penalized) and SmithWatermanAligner (local : a whole
 * sub-region can be abandoned mid-alignment). Here, the matrix borders start at zero exactly like
 * Smith-Waterman's, so leading gaps are free, but unlike Smith-Waterman the recurrence itself is never
 * floored at zero : once the alignment leaves the border it is never allowed to bail out mid-way, only
 * to finish at either the last row or the last column, which is what makes trailing gaps free too
 * without ever discarding an interior stretch the way local alignment would. The alignment is
 * recovered by walking back from the best-scoring cell among the last row and the last column (never
 * from an interior cell, since only those two represent "one of the two sequences has been fully
 * consumed"), until reaching either i = 0 or j = 0 - whichever comes first - leaving the other
 * sequence's unconsumed prefix as a free leading gap that contributes nothing to the score and is not
 * part of the reported alignment.
 * As in the other two aligners, only a linear gap penalty is supported, and a tie between predecessor
 * or candidate-endpoint cells prefers diagonal over up over left, and the last row over the last
 * column, so the same input always produces the same alignment.
 * Class SemiGlobalAligner
 * @package Amelaye\BioPHP\Domain\Alignment\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class SemiGlobalAligner implements SemiGlobalAlignerInterface
{
    /**
     * @param   AbstractMolecularSequence       $oFirst
     * @param   AbstractMolecularSequence       $oSecond
     * @param   SubstitutionScoringInterface    $oScoring
     * @param   int                              $iGapPenalty
     * @return  PairwiseAlignmentResult
     */
    public function align(
        AbstractMolecularSequence $oFirst,
        AbstractMolecularSequence $oSecond,
        SubstitutionScoringInterface $oScoring,
        int $iGapPenalty
    ): PairwiseAlignmentResult {
        if ($iGapPenalty >= 0) {
            throw InvalidAlignmentInputException::nonNegativeGapPenalty($iGapPenalty);
        }
        AlignmentInputGuard::assertCompatible($oFirst, $oSecond, $oScoring);

        $sFirst = $oFirst->getValue();
        $sSecond = $oSecond->getValue();
        $iFirstLength = strlen($sFirst);
        $iSecondLength = strlen($sSecond);
        if ($iFirstLength === 0 || $iSecondLength === 0) {
            throw InvalidAlignmentInputException::emptyAlignedSequence();
        }

        [$aMatrix, $iBestI, $iBestJ, $iBestScore] = $this->buildMatrix(
            $sFirst,
            $sSecond,
            $iFirstLength,
            $iSecondLength,
            $oScoring,
            $iGapPenalty
        );

        [$sAlignedFirst, $sAlignedSecond, $iFirstStart, $iSecondStart] = $this->traceback(
            $aMatrix,
            $sFirst,
            $sSecond,
            $iBestI,
            $iBestJ,
            $oScoring,
            $iGapPenalty
        );

        return new PairwiseAlignmentResult(
            $sAlignedFirst,
            $sAlignedSecond,
            $iBestScore,
            $iFirstStart,
            $iBestI - 1,
            $iSecondStart,
            $iBestJ - 1
        );
    }

    /**
     * @param   string                          $sFirst
     * @param   string                          $sSecond
     * @param   int                              $iFirstLength
     * @param   int                              $iSecondLength
     * @param   SubstitutionScoringInterface    $oScoring
     * @param   int                              $iGapPenalty
     * @return  array                           [int[][] matrix, int bestI, int bestJ, int bestScore]
     */
    private function buildMatrix(
        string $sFirst,
        string $sSecond,
        int $iFirstLength,
        int $iSecondLength,
        SubstitutionScoringInterface $oScoring,
        int $iGapPenalty
    ): array {
        $aMatrix = [];

        for ($i = 0; $i <= $iFirstLength; $i++) {
            $aMatrix[$i][0] = 0;
        }
        for ($j = 0; $j <= $iSecondLength; $j++) {
            $aMatrix[0][$j] = 0;
        }

        for ($i = 1; $i <= $iFirstLength; $i++) {
            for ($j = 1; $j <= $iSecondLength; $j++) {
                $iDiagonal = $aMatrix[$i - 1][$j - 1] + $oScoring->score($sFirst[$i - 1], $sSecond[$j - 1]);
                $iUp = $aMatrix[$i - 1][$j] + $iGapPenalty;
                $iLeft = $aMatrix[$i][$j - 1] + $iGapPenalty;

                $aMatrix[$i][$j] = max($iDiagonal, $iUp, $iLeft);
            }
        }

        // The best endpoint is the highest score among the last row and the last column - the only
        // cells where at least one of the two sequences has been fully consumed. The two corners
        // (firstLength, 0) and (0, secondLength) are left out : they align no column at all, and
        // always score zero. Leaving them in made two sequences whose best overlap scores zero or
        // less give an empty alignment, which no result can hold ; such an overlap is now reported
        // with its own score, negative or not.
        $iBestScore = null;
        $iBestI = $iFirstLength;
        $iBestJ = 1;

        for ($j = 1; $j <= $iSecondLength; $j++) {
            if ($iBestScore === null || $aMatrix[$iFirstLength][$j] > $iBestScore) {
                $iBestScore = $aMatrix[$iFirstLength][$j];
                $iBestI = $iFirstLength;
                $iBestJ = $j;
            }
        }

        for ($i = 1; $i <= $iFirstLength; $i++) {
            if ($aMatrix[$i][$iSecondLength] > $iBestScore) {
                $iBestScore = $aMatrix[$i][$iSecondLength];
                $iBestI = $i;
                $iBestJ = $iSecondLength;
            }
        }

        return [$aMatrix, $iBestI, $iBestJ, $iBestScore];
    }

    /**
     * @param   int[][]                         $aMatrix
     * @param   string                          $sFirst
     * @param   string                          $sSecond
     * @param   int                              $iBestI
     * @param   int                              $iBestJ
     * @param   SubstitutionScoringInterface    $oScoring
     * @param   int                              $iGapPenalty
     * @return  array                           [string alignedFirst, string alignedSecond, int firstStart, int secondStart]
     */
    private function traceback(
        array $aMatrix,
        string $sFirst,
        string $sSecond,
        int $iBestI,
        int $iBestJ,
        SubstitutionScoringInterface $oScoring,
        int $iGapPenalty
    ): array {
        $sAlignedFirst = "";
        $sAlignedSecond = "";

        $i = $iBestI;
        $j = $iBestJ;

        while ($i > 0 && $j > 0) {
            $iCurrent = $aMatrix[$i][$j];

            if ($iCurrent === $aMatrix[$i - 1][$j - 1] + $oScoring->score($sFirst[$i - 1], $sSecond[$j - 1])) {
                $sAlignedFirst = $sFirst[$i - 1] . $sAlignedFirst;
                $sAlignedSecond = $sSecond[$j - 1] . $sAlignedSecond;
                $i--;
                $j--;
            } elseif ($iCurrent === $aMatrix[$i - 1][$j] + $iGapPenalty) {
                $sAlignedFirst = $sFirst[$i - 1] . $sAlignedFirst;
                $sAlignedSecond = "-" . $sAlignedSecond;
                $i--;
            } else {
                $sAlignedFirst = "-" . $sAlignedFirst;
                $sAlignedSecond = $sSecond[$j - 1] . $sAlignedSecond;
                $j--;
            }
        }

        return [$sAlignedFirst, $sAlignedSecond, $i, $j];
    }
}
