<?php
/**
 * Finds CpG islands in a DNA sequence
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 9 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Tools\Service;

use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;
use Amelaye\BioPHP\Domain\Tools\Interfaces\CpGIslandFinderInterface;
use Amelaye\BioPHP\Domain\Tools\Interfaces\SkewCalculatorInterface;
use Amelaye\BioPHP\Domain\Tools\ValueObject\CpGIsland;

/**
 * Net-new (present in neither BioPHP nor biotools). Implements the classic Gardiner-Garden & Frommer
 * (1987) criteria : a window qualifies when its GC content and its observed/expected CpG ratio -
 * Obs/Exp = (CpG count * window length) / (C count * G count), the standard formula - both clear
 * their threshold. Every scanned window is independent ; consecutive or overlapping qualifying
 * windows are then merged into a single island, whose own gcContent/observedToExpectedRatio are
 * recomputed over its full merged span rather than inherited from whichever window first qualified,
 * so they describe the island actually reported. Reuses SkewCalculator for the GC content half of
 * the criteria rather than re-counting bases a second time.
 * Class CpGIslandFinder
 * @package Amelaye\BioPHP\Domain\Tools\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class CpGIslandFinder implements CpGIslandFinderInterface
{
    /**
     * @var     SkewCalculatorInterface
     */
    private SkewCalculatorInterface $skewCalculator;

    /**
     * CpGIslandFinder constructor.
     * @param   SkewCalculatorInterface     $oSkewCalculator
     */
    public function __construct(SkewCalculatorInterface $oSkewCalculator)
    {
        $this->skewCalculator = $oSkewCalculator;
    }

    /**
     * @param   DnaSequence     $oSequence
     * @param   int             $iWindowSize
     * @param   int             $iStep
     * @param   float           $fMinGcContent
     * @param   float           $fMinObservedToExpectedRatio
     * @return  CpGIsland[]
     */
    public function findIslands(
        DnaSequence $oSequence,
        int $iWindowSize = 200,
        int $iStep = 1,
        float $fMinGcContent = 0.5,
        float $fMinObservedToExpectedRatio = 0.6
    ): array {
        if ($iWindowSize < 1) {
            throw new \InvalidArgumentException(sprintf('Window size must be at least 1, got %d.', $iWindowSize));
        }

        if ($iStep < 1) {
            throw new \InvalidArgumentException(sprintf('Step must be at least 1, got %d.', $iStep));
        }

        $sValue = $oSequence->getValue();
        $iLength = strlen($sValue);

        $aMergedSpans = [];
        $iCurrentStart = null;
        $iCurrentEnd = null;

        for ($iPos = 0; $iPos + $iWindowSize <= $iLength; $iPos += $iStep) {
            $iWindowEnd = $iPos + $iWindowSize;

            if (!$this->qualifies(substr($sValue, $iPos, $iWindowSize), $fMinGcContent, $fMinObservedToExpectedRatio)) {
                continue;
            }

            if ($iCurrentStart === null) {
                $iCurrentStart = $iPos;
                $iCurrentEnd = $iWindowEnd;
            } elseif ($iPos <= $iCurrentEnd) {
                $iCurrentEnd = max($iCurrentEnd, $iWindowEnd);
            } else {
                $aMergedSpans[] = [$iCurrentStart, $iCurrentEnd];
                $iCurrentStart = $iPos;
                $iCurrentEnd = $iWindowEnd;
            }
        }

        if ($iCurrentStart !== null) {
            $aMergedSpans[] = [$iCurrentStart, $iCurrentEnd];
        }

        $aIslands = [];
        foreach ($aMergedSpans as $aSpan) {
            [$iSpanStart, $iSpanEnd] = $aSpan;
            $sIslandSequence = substr($sValue, $iSpanStart, $iSpanEnd - $iSpanStart);

            $aIslands[] = new CpGIsland(
                $iSpanStart + 1,
                $iSpanEnd,
                $this->skewCalculator->calculate($sIslandSequence)->getGcContent(),
                $this->observedToExpectedRatio($sIslandSequence)
            );
        }

        return $aIslands;
    }

    /**
     * @param   string      $sWindow
     * @param   float       $fMinGcContent
     * @param   float       $fMinObservedToExpectedRatio
     * @return  bool
     */
    private function qualifies(string $sWindow, float $fMinGcContent, float $fMinObservedToExpectedRatio): bool
    {
        // A window mostly made of N (an assembly gap) says nothing about CpG : the GC content and the
        // ratio are over the few bases called, and a lone CG between gaps would pass for an island
        $iCalled = strlen((string) preg_replace('/[^ACGT]/i', "", $sWindow));
        if ($iCalled * 2 < strlen($sWindow)) {
            return false;
        }

        $fGcContent = $this->skewCalculator->calculate($sWindow)->getGcContent();

        if ($fGcContent < $fMinGcContent) {
            return false;
        }

        return $this->observedToExpectedRatio($sWindow) >= $fMinObservedToExpectedRatio;
    }

    /**
     * @param   string      $sSequence
     * @return  float       0.0 when the sequence has no C or no G at all
     */
    private function observedToExpectedRatio(string $sSequence): float
    {
        $sUpper = strtoupper($sSequence);
        // The expectation is over the bases, the N of an assembly gap excluded as the GC content does
        $iLength = strlen((string) preg_replace('/[^ACGT]/', "", $sUpper));

        $iC = substr_count($sUpper, "C");
        $iG = substr_count($sUpper, "G");

        if ($iC === 0 || $iG === 0) {
            return 0.0;
        }

        $iCpG = substr_count($sUpper, "CG");

        return ($iCpG * $iLength) / ($iC * $iG);
    }
}
