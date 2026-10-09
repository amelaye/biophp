<?php
/**
 * A substitution scoring that knows which molecules it makes sense for
 * Freely inspired by BioPHP's project biophp.org
 * Created 9 October 2026
 * Last modified 9 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Alignment\Interfaces;

/**
 * Interface MoleculeAwareScoringInterface - implemented by a scoring that only means something for
 * some molecules (PAM250 for proteins, the IUPAC nucleotide scoring for DNA and RNA). The aligners
 * refuse a sequence it does not support, instead of scoring A, C, G and T as alanine, cysteine,
 * glycine and threonine. A scoring that does not implement it is taken to suit any molecule. Kept
 * apart from SubstitutionScoringInterface, so that interface keeps its single method.
 * @package Amelaye\BioPHP\Domain\Alignment\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface MoleculeAwareScoringInterface
{
    /**
     * @param   string      $sMolType       "DNA", "RNA" or "PROTEIN", as AbstractMolecularSequence::getMolType()
     * @return  bool
     */
    public function supportsMolType(string $sMolType): bool;
}
