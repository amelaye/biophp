<?php
/**
 * Calculates GC-skew, AT-skew, KETO-skew and GC content of a sequence window
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 7 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Tools\Service;

use Amelaye\BioPHP\Domain\Sequence\ValueObject\AbstractNucleicSequence;
use Amelaye\BioPHP\Domain\Tools\Interfaces\SkewCalculatorInterface;
use Amelaye\BioPHP\Domain\Tools\ValueObject\SkewResult;

/**
 * Migrated from biotools' Service/SkewsManager.php::computeImage() : the four per-window base-
 * composition metrics it fed into its SVG plot, extracted as pure math with no rendering attached -
 * that SVG plotting stays a biotools/UI concern. Unlike the legacy version, a window with an
 * undefined denominator (e.g. no G or C at all, for GC-skew) returns 0.0 rather than dividing by
 * zero - under PHP 8, int/int division by a zero denominator throws a DivisionByZeroError, which the
 * legacy code would actually have hit for such a window ; this is a real, not hypothetical, edge
 * case a sliding window can land on at a sequence's A/T-only stretch.
 * The keto skew opposes the keto bases (G, T : IUPAC K) to the amino ones (A, C : IUPAC M), as
 * (G+T-A-C)/(A+C+G+T). The legacy formula, (G+C-A-T)/(A+C+G+T), opposed strong bases to weak ones
 * instead : that is only 2 x GC content - 1, and no keto skew at all.
 * Class SkewCalculator
 * @package Amelaye\BioPHP\Domain\Tools\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class SkewCalculator implements SkewCalculatorInterface
{
    /**
     * @param   string      $sSequence
     * @return  SkewResult
     */
    public function calculate(string $sSequence): SkewResult
    {
        $sUpper = strtoupper($sSequence);

        $iA = substr_count($sUpper, "A");
        $iC = substr_count($sUpper, "C");
        $iG = substr_count($sUpper, "G");
        $iT = substr_count($sUpper, "T");

        $iGcSum = $iG + $iC;
        $iAtSum = $iA + $iT;
        $iTotal = $iA + $iC + $iG + $iT;

        return new SkewResult(
            $iGcSum === 0 ? 0.0 : ($iG - $iC) / $iGcSum,
            $iAtSum === 0 ? 0.0 : ($iA - $iT) / $iAtSum,
            $iTotal === 0 ? 0.0 : ($iG + $iT - $iA - $iC) / $iTotal,
            AbstractNucleicSequence::gcFraction($sUpper)
        );
    }

    /**
     * @param   string      $sSequence
     * @param   int         $iWindowSize
     * @param   int         $iStep
     * @return  array<int,SkewResult>
     */
    public function calculateSlidingWindow(string $sSequence, int $iWindowSize, int $iStep): array
    {
        if ($iWindowSize < 1) {
            throw new \InvalidArgumentException(sprintf('Window size must be at least 1, got %d.', $iWindowSize));
        }

        if ($iStep < 1) {
            throw new \InvalidArgumentException(sprintf('Step must be at least 1, got %d.', $iStep));
        }

        $aResults = [];
        $iLength = strlen($sSequence);

        for ($iPos = 0; $iPos + $iWindowSize <= $iLength; $iPos += $iStep) {
            $aResults[$iPos] = $this->calculate(substr($sSequence, $iPos, $iWindowSize));
        }

        return $aResults;
    }
}
