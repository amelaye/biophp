<?php
/**
 * Immutable value object holding the outcome of a nearest-neighbor melting temperature calculation
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Tools\Result;

/**
 * Class NearestNeighborTmResult
 * @package Amelaye\BioPHP\Domain\Tools\Result
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class NearestNeighborTmResult
{
    /**
     * @var     float       Degrees Celsius
     */
    private float $tm;

    /**
     * @var     float       kcal/mol
     */
    private float $enthalpy;

    /**
     * @var     float       cal/(mol*K)
     */
    private float $entropy;

    /**
     * NearestNeighborTmResult constructor.
     * @param   float       $fTm
     * @param   float       $fEnthalpy
     * @param   float       $fEntropy
     */
    public function __construct(float $fTm, float $fEnthalpy, float $fEntropy)
    {
        $this->tm = $fTm;
        $this->enthalpy = $fEnthalpy;
        $this->entropy = $fEntropy;
    }

    /**
     * @return  float
     */
    public function getTm(): float
    {
        return $this->tm;
    }

    /**
     * @return  float
     */
    public function getEnthalpy(): float
    {
        return $this->enthalpy;
    }

    /**
     * @return  float
     */
    public function getEntropy(): float
    {
        return $this->entropy;
    }
}
