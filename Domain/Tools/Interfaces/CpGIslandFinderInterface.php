<?php
/**
 * CpG island search Interface
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Tools\Interfaces;

use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;
use Amelaye\BioPHP\Domain\Tools\ValueObject\CpGIsland;

/**
 * Interface CpGIslandFinderInterface - finds CpG islands using the classic Gardiner-Garden & Frommer
 * (1987) criteria : GC content and observed/expected CpG ratio both above a threshold, over a
 * sliding window.
 * @package Amelaye\BioPHP\Domain\Tools\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface CpGIslandFinderInterface
{
    /**
     * @param   DnaSequence     $oSequence
     * @param   int             $iWindowSize
     * @param   int             $iStep
     * @param   float           $fMinGcContent              0.5 = 50%, the usual Gardiner-Garden &
     * Frommer threshold
     * @param   float           $fMinObservedToExpectedRatio    0.6, the usual threshold
     * @return  CpGIsland[]     In ascending order of start position, never overlapping
     */
    public function findIslands(
        DnaSequence $oSequence,
        int $iWindowSize = 200,
        int $iStep = 1,
        float $fMinGcContent = 0.5,
        float $fMinObservedToExpectedRatio = 0.6
    ): array;
}
