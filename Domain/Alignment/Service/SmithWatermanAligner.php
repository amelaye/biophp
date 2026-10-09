<?php
/**
 * Local pairwise sequence alignment by dynamic programming
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Alignment\Service;

use Amelaye\BioPHP\Domain\Alignment\Exception\InvalidAlignmentInputException;
use Amelaye\BioPHP\Domain\Alignment\Interfaces\SmithWatermanAlignerInterface;
use Amelaye\BioPHP\Domain\Alignment\Interfaces\SubstitutionScoringInterface;
use Amelaye\BioPHP\Domain\Alignment\Result\PairwiseAlignmentResult;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\AbstractMolecularSequence;

/**
 * The classic Smith-Waterman algorithm : like NeedlemanWunschAligner's matrix, except every cell is
 * floored at zero (a bad-scoring region is simply abandoned instead of dragging the running total
 * negative), the borders start at zero rather than an accumulated gap cost, and the alignment is
 * recovered by walking back from the single highest-scoring cell in the whole matrix - not from the
 * bottom-right corner - stopping as soon as a cell of value zero is reached, rather than at (0, 0).
 * This is what makes the result a locally similar SUBSEQUENCE of each input rather than an end-to-end
 * alignment of the whole of both ; PairwiseAlignmentResult's first/secondStart and first/secondEnd
 * report exactly which subsequence was aligned. When the highest-scoring cell is itself zero - no
 * pair of substrings of the two sequences scores above zero under the given scheme - there is no
 * local alignment to report, and PairwiseAlignmentResult's own constructor rejects the resulting
 * empty aligned strings.
 * As in the global aligner, only a linear gap penalty is supported, and a tie between predecessor
 * cells prefers diagonal, then up, then left, so the same input always produces the same alignment.
 * Class SmithWatermanAligner
 * @package Amelaye\BioPHP\Domain\Alignment\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class SmithWatermanAligner implements SmithWatermanAlignerInterface
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

        $iBestScore = 0;
        $iBestI = 0;
        $iBestJ = 0;

        for ($i = 1; $i <= $iFirstLength; $i++) {
            for ($j = 1; $j <= $iSecondLength; $j++) {
                $iDiagonal = $aMatrix[$i - 1][$j - 1] + $oScoring->score($sFirst[$i - 1], $sSecond[$j - 1]);
                $iUp = $aMatrix[$i - 1][$j] + $iGapPenalty;
                $iLeft = $aMatrix[$i][$j - 1] + $iGapPenalty;

                $iCell = max(0, $iDiagonal, $iUp, $iLeft);
                $aMatrix[$i][$j] = $iCell;

                if ($iCell > $iBestScore) {
                    $iBestScore = $iCell;
                    $iBestI = $i;
                    $iBestJ = $j;
                }
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

        while ($i > 0 && $j > 0 && $aMatrix[$i][$j] > 0) {
            $iCurrent = $aMatrix[$i][$j];

            if ($iCurrent === $aMatrix[$i - 1][$j - 1] + $oScoring->score($sFirst[$i - 1], $sSecond[$j - 1])) {
                $sAlignedFirst = $sFirst[$i - 1] . $sAlignedFirst;
                $sAlignedSecond = $sSecond[$j - 1] . $sAlignedSecond;
                $i--;
                $j--;
            } elseif ($i > 0 && $iCurrent === $aMatrix[$i - 1][$j] + $iGapPenalty) {
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
