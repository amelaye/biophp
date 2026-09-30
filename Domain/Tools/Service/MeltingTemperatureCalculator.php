<?php
/**
 * Calculates a primer's GC content and melting temperature
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 30 September 2026
 */
namespace Amelaye\BioPHP\Domain\Tools\Service;

use Amelaye\BioPHP\Api\Interfaces\TmBaseStackingApiAdapter;
use Amelaye\BioPHP\Domain\Tools\Interfaces\MeltingTemperatureInterface;
use Amelaye\BioPHP\Domain\Tools\Result\NearestNeighborTmResult;

/**
 * Migrated from biotools' Service/MeltingTemperatureManager.php. calculateMinimumTm()/
 * calculateMaximumTm() resolve a degenerate primer into its two extreme single-symbol readings -
 * every ambiguous base collapsed to "A" (weak, A/T-like) or "G" (strong, C/G-like) depending on
 * whether it could possibly BE an A/T or is guaranteed C/G/S - then apply the classic Wallace-style
 * count formula (2 degrees per weak base + 4 per strong base under 14 bases, the empirical long-
 * primer formula from 14 bases up). calculateNearestNeighborTm() is SantaLucia (1998)'s unified
 * nearest-neighbor thermodynamics, with von Ahsen et al. (1999)'s salt/Mg correction ; unlike the
 * legacy biotools version, which returned a sentinel array of nulls plus a message for a degenerate
 * primer, this throws - the nearest-neighbor method has no defined thermodynamic parameters for an
 * ambiguous base pair, so there is no result to return.
 * Class MeltingTemperatureCalculator
 * @package Amelaye\BioPHP\Domain\Tools\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class MeltingTemperatureCalculator implements MeltingTemperatureInterface
{
    private const LONG_PRIMER_THRESHOLD = 14;

    /**
     * @var     array<string,float>
     */
    private $enthalpyValues;

    /**
     * @var     array<string,float>
     */
    private $entropyValues;

    /**
     * MeltingTemperatureCalculator constructor.
     * @param   TmBaseStackingApiAdapter    $oTmBaseStackingApi
     */
    public function __construct(TmBaseStackingApiAdapter $oTmBaseStackingApi)
    {
        $aTmBaseStackings = $oTmBaseStackingApi->getTmBaseStackings();
        $this->enthalpyValues = $oTmBaseStackingApi::GetEnthalpyValues($aTmBaseStackings);
        $this->entropyValues = $oTmBaseStackingApi::GetEnthropyValues($aTmBaseStackings);
    }

    /**
     * @param   string      $sPrimer
     * @return  float
     */
    public function calculateGcPercent(string $sPrimer): float
    {
        if ($sPrimer === "") {
            return 0.0;
        }

        return round(100 * GeneticsFunctions::CountCG($sPrimer) / strlen($sPrimer), 1);
    }

    /**
     * @param   string      $sPrimer
     * @return  float
     */
    public function calculateMinimumTm(string $sPrimer): float
    {
        return $this->calculateBasicTm($this->toWeakestReading($sPrimer));
    }

    /**
     * @param   string      $sPrimer
     * @return  float
     */
    public function calculateMaximumTm(string $sPrimer): float
    {
        return $this->calculateBasicTm($this->toStrongestReading($sPrimer));
    }

    /**
     * @param   string      $sPrimer
     * @param   int         $iPrimerConcentration
     * @param   int         $iSaltConcentration
     * @param   int         $iMagnesiumConcentration
     * @return  NearestNeighborTmResult
     */
    public function calculateNearestNeighborTm(
        string $sPrimer,
        int $iPrimerConcentration,
        int $iSaltConcentration,
        int $iMagnesiumConcentration
    ): NearestNeighborTmResult {
        if (GeneticsFunctions::CountACGT($sPrimer) !== strlen($sPrimer)) {
            throw new \InvalidArgumentException(
                "Nearest-neighbor Tm cannot be computed on a primer containing degenerate nucleotides."
            );
        }

        $fEnthalpy = 0.0;
        $fEntropy = 0.0;

        // Effect on entropy of salt concentration, and the added stabilization from Mg2+ ; von Ahsen et al. 1999
        $fSaltEffect = ($iSaltConcentration / 1000) + (($iMagnesiumConcentration / 1000) * 140);
        $fEntropy += 0.368 * (strlen($sPrimer) - 1) * log($fSaltEffect);

        // Terminal corrections ; SantaLucia 1998
        $sFirstBase = substr($sPrimer, 0, 1);
        if ($sFirstBase === "G" || $sFirstBase === "C") {
            $fEnthalpy += 0.1;
            $fEntropy += -2.8;
        }
        if ($sFirstBase === "A" || $sFirstBase === "T") {
            $fEnthalpy += 2.3;
            $fEntropy += 4.1;
        }

        $sLastBase = substr($sPrimer, -1);
        if ($sLastBase === "G" || $sLastBase === "C") {
            $fEnthalpy += 0.1;
            $fEntropy += -2.8;
        }
        if ($sLastBase === "A" || $sLastBase === "T") {
            $fEnthalpy += 2.3;
            $fEntropy += 4.1;
        }

        for ($i = 0; $i < strlen($sPrimer) - 1; $i++) {
            $sDinucleotide = substr($sPrimer, $i, 2);
            $fEnthalpy += $this->enthalpyValues[$sDinucleotide];
            $fEntropy += $this->entropyValues[$sDinucleotide];
        }

        $fTm = ((1000 * $fEnthalpy) / ($fEntropy + (1.987 * log($iPrimerConcentration / 2000000000)))) - 273.15;

        return new NearestNeighborTmResult(round($fTm, 1), round($fEnthalpy, 2), round($fEntropy, 2));
    }

    /**
     * @param   string      $sNormalizedPrimer      Every symbol already collapsed to "A" or "G"
     * @return  float
     */
    private function calculateBasicTm(string $sNormalizedPrimer): float
    {
        $iLength = strlen($sNormalizedPrimer);

        if ($iLength === 0) {
            return 0.0;
        }

        $iWeakCount = substr_count($sNormalizedPrimer, "A");
        $iStrongCount = substr_count($sNormalizedPrimer, "G");

        if ($iLength < self::LONG_PRIMER_THRESHOLD) {
            return (float) round(2 * $iWeakCount + 4 * $iStrongCount);
        }

        return round(64.9 + 41 * (($iStrongCount - 16.4) / $iLength), 1);
    }

    /**
     * Collapses every symbol that could possibly be A or T to "A", leaving only a symbol that can
     * only be C, G or S(=C/G) as "G" - the weakest-pairing reading a degenerate primer can have.
     * @param   string      $sPrimer
     * @return  string
     */
    private function toWeakestReading(string $sPrimer): string
    {
        $sPrimer = preg_replace("/A|T|Y|R|W|K|M|D|V|H|B|N/", "A", $sPrimer);

        return preg_replace("/C|G|S/", "G", $sPrimer);
    }

    /**
     * Collapses only a symbol that can only be A or T(=W) to "A", leaving every other symbol -
     * including one that could possibly be C or G - as "G" - the strongest-pairing reading a
     * degenerate primer can have.
     * @param   string      $sPrimer
     * @return  string
     */
    private function toStrongestReading(string $sPrimer): string
    {
        $sPrimer = preg_replace("/A|T|W/", "A", $sPrimer);

        return preg_replace("/C|G|Y|R|S|K|M|D|V|H|B|N/", "G", $sPrimer);
    }
}
