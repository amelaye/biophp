<?php
/**
 * Database of Temperatures
 * Inspired by BioPHP's project biophp.org
 * Created 20 december 2019
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Api\DTO;

/**
 * Database of temperatures of di-nucleotids
 * From table at http://www.ncbi.nlm.nih.gov/pmc/articles/PMC19045/table/T2/ (SantaLucia, 1998)
 * @package Amelaye\BioPHP\Api\DTO
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class TmBaseStackingDTO
{
    /**
     * @var     string         Id of the nucleotid (auto-increment)
     */
    private ?string $id = null;

    /**
     * @var     float      Enthalpy Temperature
     */
    private ?float $temperatureEnthalpy = null;

    /**
     * @var     float      Enthropy Temperature
     */
    private ?float $temperatureEnthropy = null;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return float
     */
    public function getTemperatureEnthalpy(): float
    {
        return $this->temperatureEnthalpy;
    }

    /**
     * @param float $temperatureEnthalpy
     */
    public function setTemperatureEnthalpy(float $temperatureEnthalpy): void
    {
        $this->temperatureEnthalpy = $temperatureEnthalpy;
    }

    /**
     * @return float
     */
    public function getTemperatureEnthropy(): float
    {
        return $this->temperatureEnthropy;
    }

    /**
     * @param float $temperatureEnthropy
     */
    public function setTemperatureEnthropy(float $temperatureEnthropy): void
    {
        $this->temperatureEnthropy = $temperatureEnthropy;
    }
}