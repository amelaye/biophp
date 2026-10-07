<?php
/**
 * Immutable value object holding the base-composition skew metrics of a sequence window
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 7 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Tools\ValueObject;

/**
 * A skew with an undefined denominator (e.g. GC-skew over a window with no G or C at all) is 0.0
 * rather than thrown or NAN - a deliberate, documented choice (SkewCalculator's own docblock) so a
 * sliding window never has to special-case an all-A/T or otherwise degenerate window.
 * Class SkewResult
 * @package Amelaye\BioPHP\Domain\Tools\ValueObject
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class SkewResult
{
    /**
     * @var     float       (G-C)/(G+C) ; 0.0 when G+C=0
     */
    private float $gcSkew;

    /**
     * @var     float       (A-T)/(A+T) ; 0.0 when A+T=0
     */
    private float $atSkew;

    /**
     * @var     float       (G+T-A-C)/(A+C+G+T), keto (K) minus amino (M) bases ; 0.0 for an empty window
     */
    private float $ketoSkew;

    /**
     * @var     float       AbstractNucleicSequence::gcFraction() : (G+C+S)/(A+C+G+T+S+W) ; 0.0 for
     * a window with none of these bases
     */
    private float $gcContent;

    /**
     * SkewResult constructor.
     * @param   float       $fGcSkew
     * @param   float       $fAtSkew
     * @param   float       $fKetoSkew
     * @param   float       $fGcContent
     */
    public function __construct(float $fGcSkew, float $fAtSkew, float $fKetoSkew, float $fGcContent)
    {
        $this->gcSkew = $fGcSkew;
        $this->atSkew = $fAtSkew;
        $this->ketoSkew = $fKetoSkew;
        $this->gcContent = $fGcContent;
    }

    /**
     * @return  float
     */
    public function getGcSkew(): float
    {
        return $this->gcSkew;
    }

    /**
     * @return  float
     */
    public function getAtSkew(): float
    {
        return $this->atSkew;
    }

    /**
     * @return  float
     */
    public function getKetoSkew(): float
    {
        return $this->ketoSkew;
    }

    /**
     * @return  float
     */
    public function getGcContent(): float
    {
        return $this->gcContent;
    }
}
