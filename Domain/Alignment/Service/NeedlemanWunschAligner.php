<?php
/**
 * Global pairwise sequence alignment by dynamic programming
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 30 September 2026
 */
namespace Amelaye\BioPHP\Domain\Alignment\Service;

use Amelaye\BioPHP\Domain\Alignment\Exception\InvalidAlignmentInputException;
use Amelaye\BioPHP\Domain\Alignment\Interfaces\NeedlemanWunschAlignerInterface;
use Amelaye\BioPHP\Domain\Alignment\Interfaces\SubstitutionScoringInterface;
use Amelaye\BioPHP\Domain\Alignment\ValueObject\PairwiseAlignmentResult;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\AbstractMolecularSequence;

/**
 * The classic Needleman-Wunsch algorithm : a (firstLength+1) x (secondLength+1) matrix is filled so
 * that cell (i, j) holds the best score of aligning the first i symbols of the first sequence against
 * the first j symbols of the second one, then the alignment itself is recovered by walking that matrix
 * back from its bottom-right corner to (0, 0). Only a linear (per-gap-symbol) gap penalty is supported
 * ; an affine, opening-versus-extension penalty would need a second matrix and is not implemented here.
 * When two or three predecessor cells tie for the best score, the diagonal (substitution) move is
 * preferred over an "up" move (a gap in the second sequence), itself preferred over a "left" move (a
 * gap in the first sequence) ; this is an arbitrary but deterministic choice, made so the same input
 * always produces the same alignment.
 * Class NeedlemanWunschAligner
 * @package Amelaye\BioPHP\Domain\Alignment\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class NeedlemanWunschAligner implements NeedlemanWunschAlignerInterface
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

        $sFirst = $oFirst->getValue();
        $sSecond = $oSecond->getValue();
        $iFirstLength = strlen($sFirst);
        $iSecondLength = strlen($sSecond);

        $aMatrix = $this->buildMatrix($sFirst, $sSecond, $iFirstLength, $iSecondLength, $oScoring, $iGapPenalty);

        [$sAlignedFirst, $sAlignedSecond] = $this->traceback(
            $aMatrix,
            $sFirst,
            $sSecond,
            $iFirstLength,
            $iSecondLength,
            $oScoring,
            $iGapPenalty
        );

        return new PairwiseAlignmentResult(
            $sAlignedFirst,
            $sAlignedSecond,
            $aMatrix[$iFirstLength][$iSecondLength],
            0,
            $iFirstLength - 1,
            0,
            $iSecondLength - 1
        );
    }

    /**
     * @param   string                          $sFirst
     * @param   string                          $sSecond
     * @param   int                              $iFirstLength
     * @param   int                              $iSecondLength
     * @param   SubstitutionScoringInterface    $oScoring
     * @param   int                              $iGapPenalty
     * @return  int[][]
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
            $aMatrix[$i][0] = $i * $iGapPenalty;
        }
        for ($j = 0; $j <= $iSecondLength; $j++) {
            $aMatrix[0][$j] = $j * $iGapPenalty;
        }

        for ($i = 1; $i <= $iFirstLength; $i++) {
            for ($j = 1; $j <= $iSecondLength; $j++) {
                $iDiagonal = $aMatrix[$i - 1][$j - 1] + $oScoring->score($sFirst[$i - 1], $sSecond[$j - 1]);
                $iUp = $aMatrix[$i - 1][$j] + $iGapPenalty;
                $iLeft = $aMatrix[$i][$j - 1] + $iGapPenalty;

                $aMatrix[$i][$j] = max($iDiagonal, $iUp, $iLeft);
            }
        }

        return $aMatrix;
    }

    /**
     * @param   int[][]                         $aMatrix
     * @param   string                          $sFirst
     * @param   string                          $sSecond
     * @param   int                              $iFirstLength
     * @param   int                              $iSecondLength
     * @param   SubstitutionScoringInterface    $oScoring
     * @param   int                              $iGapPenalty
     * @return  string[]                        [alignedFirst, alignedSecond]
     */
    private function traceback(
        array $aMatrix,
        string $sFirst,
        string $sSecond,
        int $iFirstLength,
        int $iSecondLength,
        SubstitutionScoringInterface $oScoring,
        int $iGapPenalty
    ): array {
        $sAlignedFirst = "";
        $sAlignedSecond = "";

        $i = $iFirstLength;
        $j = $iSecondLength;

        while ($i > 0 || $j > 0) {
            $iCurrent = $aMatrix[$i][$j];

            if ($i > 0 && $j > 0 && $iCurrent === $aMatrix[$i - 1][$j - 1] + $oScoring->score($sFirst[$i - 1], $sSecond[$j - 1])) {
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

        return [$sAlignedFirst, $sAlignedSecond];
    }
}
