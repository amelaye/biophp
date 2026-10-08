<?php
/**
 * Calculates a primer's GC content and melting temperature
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 8 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Tools\Service;

use Amelaye\BioPHP\Api\Interfaces\TmBaseStackingApiAdapter;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\AbstractNucleicSequence;
use Amelaye\BioPHP\Domain\Tools\Interfaces\MeltingTemperatureInterface;
use Amelaye\BioPHP\Domain\Tools\Result\NearestNeighborTmResult;

/**
 * Migrated from biotools' Service/MeltingTemperatureManager.php. calculateMinimumTm()/
 * calculateMaximumTm() resolve a degenerate primer into its two extreme single-symbol readings -
 * every ambiguous base collapsed to "A" (weak, A/T-like) or "G" (strong, C/G-like) depending on
 * whether it could possibly BE an A/T or is guaranteed C/G/S - then apply the classic Wallace-style
 * count formula (2 degrees per weak base + 4 per strong base under 14 bases, the empirical long-
 * primer formula from 14 bases up). calculateNearestNeighborTm() is SantaLucia (1998)'s unified
 * nearest-neighbor thermodynamics : Tm = dH / (dS + R ln(Ct/x)), x = 4 for two complementary strands
 * at equal concentration, x = 1 (plus a -1.4 e.u. symmetry term) for a self-complementary primer,
 * whose two strands are the same molecule. Mg2+ is converted into its sodium equivalent,
 * [Na+] + 120 x sqrt([Mg2+]) in mM (von Ahsen et al., Clin Chem 2001), then corrects the entropy by
 * 0.368 x (N - 1) x ln[Na+] (SantaLucia 1998) ; the results agree with Biopython's Tm_NN (DNA_NN3,
 * saltcorr=5). The legacy biotools version used Ct/2 and 140 x [Mg2+]. Unlike it, which returned a
 * sentinel array of nulls plus a message for a degenerate primer, this throws - the nearest-neighbor
 * method has no defined thermodynamic parameters for an ambiguous base pair, so there is no result
 * to return. A primer may be written in lower case.
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
    private array $enthalpyValues;

    /**
     * @var     array<string,float>
     */
    private array $entropyValues;

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
        return round(100 * AbstractNucleicSequence::gcFraction($sPrimer), 1);
    }

    /**
     * @param   string      $sPrimer
     * @return  float
     */
    public function calculateMinimumTm(string $sPrimer): float
    {
        return $this->calculateBasicTm($this->toWeakestReading(strtoupper($sPrimer)));
    }

    /**
     * @param   string      $sPrimer
     * @return  float
     */
    public function calculateMaximumTm(string $sPrimer): float
    {
        return $this->calculateBasicTm($this->toStrongestReading(strtoupper($sPrimer)));
    }

    /**
     * @param   string      $sPrimer
     * @param   float       $iPrimerConcentration       Total strand concentration, nM
     * @param   float       $iSaltConcentration         Monovalent cations (Na+, K+), mM
     * @param   float       $iMagnesiumConcentration    Free Mg2+, mM
     * @return  NearestNeighborTmResult
     */
    public function calculateNearestNeighborTm(
        string $sPrimer,
        float $iPrimerConcentration,
        float $iSaltConcentration,
        float $iMagnesiumConcentration
    ): NearestNeighborTmResult {
        $sPrimer = strtoupper($sPrimer);
        if (GeneticsFunctions::CountACGT($sPrimer) !== strlen($sPrimer)) {
            throw new \InvalidArgumentException(
                "Nearest-neighbor Tm cannot be computed on a primer containing degenerate nucleotides."
            );
        }
        // The model sums nearest-neighbour stacks : it needs one at least, so two bases.
        if (strlen($sPrimer) < 2) {
            throw new \InvalidArgumentException("Nearest-neighbor Tm needs a primer of two bases at least.");
        }
        if ($iPrimerConcentration <= 0 || $iSaltConcentration < 0 || $iMagnesiumConcentration < 0) {
            throw new \InvalidArgumentException(
                "Primer concentration must be positive, salt and magnesium concentrations not negative."
            );
        }

        // Sodium equivalent of the monovalent cations and Mg2+, in mM ; von Ahsen et al. 2001
        $fSodiumEquivalent = $iSaltConcentration + 120 * sqrt($iMagnesiumConcentration);
        if ($fSodiumEquivalent <= 0) {
            throw new \InvalidArgumentException("Nearest-neighbor Tm needs some salt or magnesium.");
        }

        $fEnthalpy = 0.0;
        $fEntropy = 0.0;

        // Effect on entropy of the salt concentration ; SantaLucia 1998
        $fEntropy += 0.368 * (strlen($sPrimer) - 1) * log($fSodiumEquivalent / 1000);

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

        // A self-complementary primer pairs with itself : its strand concentration is not shared
        // between two species (x = 1 instead of 4) and its duplex has a twofold symmetry.
        $bSelfComplementary = $sPrimer === strrev(strtr($sPrimer, "ACGT", "TGCA"));
        $fConcentrationFactor = 4;
        if ($bSelfComplementary) {
            $fEntropy += -1.4;
            $fConcentrationFactor = 1;
        }

        $fTm = ((1000 * $fEnthalpy) / ($fEntropy + (1.987 * log($iPrimerConcentration / ($fConcentrationFactor * 1000000000))))) - 273.15;

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
