<?php
/**
 * Flat match/mismatch substitution scoring
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Alignment\Service;

use Amelaye\BioPHP\Domain\Alignment\Interfaces\SubstitutionScoringInterface;

/**
 * Awards a fixed score for identical symbols and another fixed score for anything else, case
 * insensitive. Suitable for DNA and RNA, and usable as a crude default for amino acids when no
 * substitution matrix is available.
 * Class SimpleMatchMismatchScoring
 * @package Amelaye\BioPHP\Domain\Alignment\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class SimpleMatchMismatchScoring implements SubstitutionScoringInterface
{
    /**
     * @var     int
     */
    private int $matchScore;

    /**
     * @var     int
     */
    private int $mismatchScore;

    /**
     * SimpleMatchMismatchScoring constructor.
     * @param   int         $iMatchScore        Awarded when both symbols are identical
     * @param   int         $iMismatchScore     Awarded when they differ
     */
    public function __construct(int $iMatchScore = 1, int $iMismatchScore = -1)
    {
        $this->matchScore = $iMatchScore;
        $this->mismatchScore = $iMismatchScore;
    }

    /**
     * @param   string      $sFirstSymbol
     * @param   string      $sSecondSymbol
     * @return  int
     */
    public function score(string $sFirstSymbol, string $sSecondSymbol): int
    {
        return strtoupper($sFirstSymbol) === strtoupper($sSecondSymbol) ? $this->matchScore : $this->mismatchScore;
    }
}
