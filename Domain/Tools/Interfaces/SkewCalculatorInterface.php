<?php
/**
 * Base-composition skew calculation Interface
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 30 September 2026
 */
namespace Amelaye\BioPHP\Domain\Tools\Interfaces;

use Amelaye\BioPHP\Domain\Tools\ValueObject\SkewResult;

/**
 * Interface SkewCalculatorInterface
 * @package Amelaye\BioPHP\Domain\Tools\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface SkewCalculatorInterface
{
    /**
     * @param   string      $sSequence
     * @return  SkewResult
     */
    public function calculate(string $sSequence): SkewResult;

    /**
     * @param   string      $sSequence
     * @param   int         $iWindowSize
     * @param   int         $iStep
     * @return  array<int,SkewResult>   Keyed by each window's 0-based start position
     */
    public function calculateSlidingWindow(string $sSequence, int $iWindowSize, int $iStep): array;
}
