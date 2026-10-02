<?php
/**
 * Open reading frame search Interface
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Tools\Interfaces;

use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;
use Amelaye\BioPHP\Domain\Tools\ValueObject\OpenReadingFrame;

/**
 * Interface OrfFinderInterface - finds every open reading frame across all six reading frames (three
 * forward, three reverse) of a DNA sequence.
 * @package Amelaye\BioPHP\Domain\Tools\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface OrfFinderInterface
{
    /**
     * @param   DnaSequence     $oSequence
     * @param   int             $iMinimumProteinLength  In amino acids, stop codon excluded
     * @return  OpenReadingFrame[]
     */
    public function findOrfs(DnaSequence $oSequence, int $iMinimumProteinLength = 1): array;
}
