<?php
/**
 * Local pairwise sequence alignment Interface
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Alignment\Interfaces;

use Amelaye\BioPHP\Domain\Alignment\Result\PairwiseAlignmentResult;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\AbstractMolecularSequence;

/**
 * Interface SmithWatermanAlignerInterface - finds the highest-scoring locally similar subsequence of
 * two sequences under a linear gap penalty, rather than aligning them end to end.
 * @package Amelaye\BioPHP\Domain\Alignment\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface SmithWatermanAlignerInterface
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
