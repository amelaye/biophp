<?php
/**
 * Semi-global (overlap) pairwise sequence alignment Interface
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 30 September 2026
 */
namespace Amelaye\BioPHP\Domain\Alignment\Interfaces;

use Amelaye\BioPHP\Domain\Alignment\Result\PairwiseAlignmentResult;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\AbstractMolecularSequence;

/**
 * Interface SemiGlobalAlignerInterface - aligns two sequences without penalizing gaps at either end
 * of either sequence, so a short sequence fully contained in a longer one (a primer inside a vector,
 * an overlap between two fragments to assemble) scores as if the longer sequence's untouched flanks
 * were never there.
 * @package Amelaye\BioPHP\Domain\Alignment\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface SemiGlobalAlignerInterface
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
