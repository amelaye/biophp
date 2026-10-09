<?php
/**
 * Checks the sequences given to a pairwise aligner and the scoring that goes with them
 * Freely inspired by BioPHP's project biophp.org
 * Created 9 October 2026
 * Last modified 9 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Alignment\Service;

use Amelaye\BioPHP\Domain\Alignment\Exception\InvalidAlignmentInputException;
use Amelaye\BioPHP\Domain\Alignment\Interfaces\MoleculeAwareScoringInterface;
use Amelaye\BioPHP\Domain\Alignment\Interfaces\SubstitutionScoringInterface;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\AbstractMolecularSequence;

/**
 * Two sequences of different molecules (a DNA against an RNA, whose T and U would score as a
 * mismatch, or a nucleic acid against a protein) and a scoring made for another molecule (PAM250 on
 * DNA) both produced a score with no error. They are refused here, for the three aligners alike.
 * Class AlignmentInputGuard
 * @package Amelaye\BioPHP\Domain\Alignment\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class AlignmentInputGuard
{
    /**
     * @param   AbstractMolecularSequence       $oFirst
     * @param   AbstractMolecularSequence       $oSecond
     * @param   SubstitutionScoringInterface    $oScoring
     * @throws  InvalidAlignmentInputException  When the molecules differ, or the scoring is not made for them
     */
    public static function assertCompatible(
        AbstractMolecularSequence $oFirst,
        AbstractMolecularSequence $oSecond,
        SubstitutionScoringInterface $oScoring
    ): void {
        if ($oFirst->getMolType() !== $oSecond->getMolType()) {
            throw InvalidAlignmentInputException::mismatchedMoleculeTypes($oFirst->getMolType(), $oSecond->getMolType());
        }

        if ($oScoring instanceof MoleculeAwareScoringInterface && !$oScoring->supportsMolType($oFirst->getMolType())) {
            throw InvalidAlignmentInputException::scoringNotMadeForMolecule(get_class($oScoring), $oFirst->getMolType());
        }
    }
}
