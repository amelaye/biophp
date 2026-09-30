<?php
/**
 * Global pairwise sequence alignment Interface
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 30 September 2026
 */
namespace Amelaye\BioPHP\Domain\Alignment\Interfaces;

use Amelaye\BioPHP\Domain\Alignment\Result\PairwiseAlignmentResult;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\AbstractMolecularSequence;

/**
 * Interface NeedlemanWunschAlignerInterface - computes the optimal end-to-end (global) alignment of
 * two sequences under a linear gap penalty.
 * @package Amelaye\BioPHP\Domain\Alignment\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface NeedlemanWunschAlignerInterface
{
    /**
     * @param   AbstractMolecularSequence       $oFirst
     * @param   AbstractMolecularSequence       $oSecond
     * @param   SubstitutionScoringInterface    $oScoring
     * @param   int                              $iGapPenalty    Strictly negative
     * @return  PairwiseAlignmentResult
     */
    public function align(
        AbstractMolecularSequence $oFirst,
        AbstractMolecularSequence $oSecond,
        SubstitutionScoringInterface $oScoring,
        int $iGapPenalty
    ): PairwiseAlignmentResult;
}
