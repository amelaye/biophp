<?php
/**
 * Primer melting temperature and GC content calculation Interface
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Tools\Interfaces;

use Amelaye\BioPHP\Domain\Tools\Result\NearestNeighborTmResult;

/**
 * Interface MeltingTemperatureInterface
 * @package Amelaye\BioPHP\Domain\Tools\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface MeltingTemperatureInterface
{
    /**
     * @param   string      $sPrimer
     * @return  float       Percentage, 0.0 for an empty primer
     */
    public function calculateGcPercent(string $sPrimer): float;

    /**
     * The lowest melting temperature a degenerate primer could have, resolving every ambiguous
     * symbol to its weakest-pairing possibility. Equals calculateMaximumTm() when the primer has no
     * degenerate symbol.
     * @param   string      $sPrimer
     * @return  float       Degrees Celsius
     */
    public function calculateMinimumTm(string $sPrimer): float;

    /**
     * The highest melting temperature a degenerate primer could have, resolving every ambiguous
     * symbol to its strongest-pairing possibility. Equals calculateMinimumTm() when the primer has
     * no degenerate symbol.
     * @param   string      $sPrimer
     * @return  float       Degrees Celsius
     */
    public function calculateMaximumTm(string $sPrimer): float;

    /**
     * Nearest-neighbor thermodynamics (SantaLucia 1998), with the von Ahsen (1999) salt/Mg
     * correction.
     * @param   string      $sPrimer                    Must contain only A, C, G, T - no degenerate
     * symbol
     * @param   float         $iPrimerConcentration
     * @param   float         $iSaltConcentration
     * @param   float         $iMagnesiumConcentration
     * @return  NearestNeighborTmResult
     */
    public function calculateNearestNeighborTm(
        string $sPrimer,
        float $iPrimerConcentration,
        float $iSaltConcentration,
        float $iMagnesiumConcentration
    ): NearestNeighborTmResult;
}
