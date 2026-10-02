<?php
/**
 * Scores one pair of aligned symbols
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Alignment\Interfaces;

/**
 * Interface SubstitutionScoringInterface - lets a pairwise aligner stay ignorant of how two symbols
 * are scored against each other, whether by a flat match/mismatch rule or a substitution matrix such
 * as PAM250.
 * @package Amelaye\BioPHP\Domain\Alignment\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface SubstitutionScoringInterface
{
    /**
     * @param   string      $sFirstSymbol       A single symbol, already validated by its owning
     * AbstractMolecularSequence
     * @param   string      $sSecondSymbol      A single symbol, already validated by its owning
     * AbstractMolecularSequence
     * @return  int
     */
    public function score(string $sFirstSymbol, string $sSecondSymbol): int;
}
