<?php
/**
 * MeltingTemperatureManager
 * Inspired by BioPHP's project biophp.org
 * Created 26 february 2019
 * Last modified 15 august 2019
 * RIP Pasha, gone 27 february 2019 =^._.^= ∫
 */
namespace MinitoolsBundle\Service;

use AppBundle\Service\NucleotidsManager;
use AppBundle\Bioapi\Bioapi;

/**
 * Class MeltingTemperatureManager
 * @package MinitoolsBundle\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class MeltingTemperatureManager
{
    /**
     * @var NucleotidsManager
     */
    private $nucleotidsManager;

    /**
     * @var Bioapi
     */
    private $bioapi;

    /**
     * MeltingTemperatureManager constructor.
     * @param   NucleotidsManager   $nucleotidsManager      Service counting nucleotids
     * @param   Bioapi              $bioapi
     */
    public function __construct(
        NucleotidsManager $nucleotidsManager,
        Bioapi $bioapi
    )
    {
        $this->nucleotidsManager    = $nucleotidsManager;
        $this->bioapi               = $bioapi;
    }

    /**
     * Calculates CG
     * @param       int     $primer
     * @return      float
     * @throws      \Exception
     */
    public function calculateCG($primer)
    {
        $cg = round(100 * $this->nucleotidsManager->countCG($primer) / strlen($primer),1);
        return $cg;
    }

    /**
     * Gets both the upper and lower MWT
     * @param $upperMwt
     * @param $lowerMwt
     * @param $primer
     * @throws \Exception
     */
    public function calculateMWT(&$upperMwt, &$lowerMwt, $primer)
    {
        $upperMwt = $this->molwt($primer,"DNA","upperlimit");
        $lowerMwt = $this->molwt($primer,"DNA","lowerlimit");
    }

    /**
     * @param $bBasic
     * @param $primer
     * @param $countATGC
     * @param $tmMin
     * @param $tmMax
     * @throws \Exception
     */
    public function basicCalculations($bBasic, $primer, &$countATGC, &$tmMin, &$tmMax)
    {
        if($bBasic) {
            $countATGC = $this->nucleotidsManager->countACGT($primer);
            $tmMin = $this->tmMin($primer);
            $tmMax = $this->tmMax($primer);
        }
    }

    /**
     * @param $bNearestNeighbor
     * @param   array   $aTmBaseStacking
     * @param   string  $sPrimer
     * @param   int     $iConcPrimer
     * @param   int     $iConcSalt
     * @param   int     $iConcMg
     * @throws \Exception
     */
    public function neighborCalculations($bNearestNeighbor, &$aTmBaseStacking, $sPrimer, $iConcPrimer, $iConcSalt, $iConcMg)
    {
        if($bNearestNeighbor) {
            $aTmBaseStacking = $this->tmBaseStacking(
                $sPrimer, $iConcPrimer, $iConcSalt, $iConcMg
            );
        }
    }

    /**
     * Gets different informations when degenerated nucleotids are not allowed
     * @param   string      $sPrimer         Primer string
     * @param   int         $iConcPrimer     Primer concentration
     * @param   int         $iConcSalt       Salt concentration
     * @param   int         $iConcMg         Mg2+ concentration
     * @return  array
     * @throws  \Exception
     */
    public function tmBaseStacking($sPrimer, $iConcPrimer, $iConcSalt, $iConcMg)
    {
        try {
            $h = $s = 0;

            $aEnthalpyValues = $this->bioapi->getEnthalpyValues();
            $aEnthropyValues = $this->bioapi->getEnthropyValues();

            // effect on entropy by salt correction; von Ahsen et al 1999
            // Increase of stability due to presence of Mg;
            $fSaltEffect = ($iConcSalt/1000) + (($iConcMg/1000) * 140);
            // effect on entropy
            $s += 0.368 * (strlen($sPrimer)-1) * log($fSaltEffect);

            // terminal corrections. Santalucia 1998
            $sFirstNucleotid = substr($sPrimer,0,1);
            if($sFirstNucleotid == "G" || $sFirstNucleotid == "C") {
                $h += 0.1;
                $s += -2.8;
            }
            if($sFirstNucleotid == "A" ||  $sFirstNucleotid == "T") {
                $h += 2.3;
                $s += 4.1;
            }

            $sLastNucleotid = substr($sPrimer,strlen($sPrimer)-1,1);
            if ($sLastNucleotid == "G" || $sLastNucleotid == "C") {
                $h += 0.1;
                $s += -2.8;
            }
            if ($sLastNucleotid == "A" || $sLastNucleotid == "T"){
                $h += 2.3;
                $s += 4.1;
            }

            // compute new H and s based on sequence. Santalucia 1998
            for($i = 0; $i < strlen($sPrimer)-1; $i++) {
                $subc = substr($sPrimer, $i,2);
                $h += $aEnthalpyValues[$subc];
                $s += $aEnthropyValues[$subc];
            }
            $tm = ((1000 * $h) / ($s + (1.987 * log($iConcPrimer / 2000000000)))) - 273.15;

            $aBaseStacking = [
                'tm'        => round($tm, 1),
                'enthalpy'  => round($h,2),
                'entropy'   => round($s,2)
            ];

            return $aBaseStacking;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Gets temperature mini
     * @param       string      $sPrimer     Sequence to analyze
     * @return      float
     * @throws      \Exception
     */
    public function tmMin($sPrimer)
    {
        try {
            $fTemperature = 0;

            $iPrimerLen = strlen($sPrimer);
            $sPrimer2 = $this->primerMin($sPrimer);
            $fTemperature = $this->calculateTemperature($sPrimer2, $iPrimerLen);

            return $fTemperature;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }


    /**
     * Gets temperature maxi
     * @param       string      $sPrimer     Sequence to analyze
     * @return      float
     * @throws      \Exception
     */
    public function tmMax($sPrimer)
    {
        try {
            $iPrimerLen = strlen($sPrimer);
            $sPrimer2 = $this->primerMax($sPrimer);
            $fTemperature = $this->calculateTemperature($sPrimer2, $iPrimerLen);
            return $fTemperature;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Gets the weight of the sequence
     * @param   string      $sSequence       Sequence to analyse
     * @param   string      $sMoltype        DNA or RNA
     * @param   string      $sLimit          "Upperlimit" or "lowerlimit" (string)
     * @return  float
     * @throws  \Exception
     */
    public function molwt($sSequence, $sMoltype, $sLimit)
    {
        try {
            $iWlimit = null;
            $aWater = $this->bioapi->getWater();

            $aDnaWeightsTemp = $this->bioapi->getDNAWeight();
            $aRnaWeightsTemp = $this->bioapi->getRNAWeight();

            $aDnaWeights = [
                'A' => [$aDnaWeightsTemp["A"], $aDnaWeightsTemp["A"]],  // Adenine
                'C' => [$aDnaWeightsTemp["C"], $aDnaWeightsTemp["C"]],  // Cytosine
                'G' => [$aDnaWeightsTemp["G"], $aDnaWeightsTemp["G"]],  // Guanine
                'T' => [$aDnaWeightsTemp["T"], $aDnaWeightsTemp["T"]],  // Thymine
                'M' => [$aDnaWeightsTemp["C"], $aDnaWeightsTemp["A"]],  // A or C
                'R' => [$aDnaWeightsTemp["A"], $aDnaWeightsTemp["G"]],  // A or G
                'W' => [$aDnaWeightsTemp["T"], $aDnaWeightsTemp["A"]],  // A or T
                'S' => [$aDnaWeightsTemp["C"], $aDnaWeightsTemp["G"]],  // C or G
                'Y' => [$aDnaWeightsTemp["C"], $aDnaWeightsTemp["T"]],  // C or T
                'K' => [$aDnaWeightsTemp["T"], $aDnaWeightsTemp["G"]],  // G or T
                'V' => [$aDnaWeightsTemp["C"], $aDnaWeightsTemp["G"]],  // A or C or G
                'H' => [$aDnaWeightsTemp["C"], $aDnaWeightsTemp["A"]],  // A or C or T
                'D' => [$aDnaWeightsTemp["T"], $aDnaWeightsTemp["G"]],  // A or G or T
                'B' => [$aDnaWeightsTemp["C"], $aDnaWeightsTemp["G"]],  // C or G or T
                'X' => [$aDnaWeightsTemp["C"], $aDnaWeightsTemp["G"]],  // G, A, T or C
                'N' => [$aDnaWeightsTemp["C"], $aDnaWeightsTemp["G"]]   // G, A, T or C
            ];


            $aRnaWeights = [
                'A' => [$aRnaWeightsTemp["A"], $aRnaWeightsTemp["A"]],  // Adenine
                'C' => [$aRnaWeightsTemp["C"], $aRnaWeightsTemp["C"]],  // Cytosine
                'G' => [$aRnaWeightsTemp["G"], $aRnaWeightsTemp["G"]],  // Guanine
                'U' => [$aRnaWeightsTemp["U"], $aRnaWeightsTemp["U"]],  // Uracil
                'M' => [$aRnaWeightsTemp["C"], $aRnaWeightsTemp["A"]],  // A or C
                'R' => [$aRnaWeightsTemp["A"], $aRnaWeightsTemp["G"]],  // A or G
                'W' => [$aRnaWeightsTemp["U"], $aRnaWeightsTemp["A"]],  // A or U
                'S' => [$aRnaWeightsTemp["C"], $aRnaWeightsTemp["G"]],  // C or G
                'Y' => [$aRnaWeightsTemp["C"], $aRnaWeightsTemp["U"]],  // C or U
                'K' => [$aRnaWeightsTemp["U"], $aRnaWeightsTemp["G"]],  // G or U
                'V' => [$aRnaWeightsTemp["C"], $aRnaWeightsTemp["G"]],  // A or C or G
                'H' => [$aRnaWeightsTemp["C"], $aRnaWeightsTemp["A"]],  // A or C or U
                'D' => [$aRnaWeightsTemp["U"], $aRnaWeightsTemp["G"]],  // A or G or U
                'B' => [$aRnaWeightsTemp["C"], $aRnaWeightsTemp["G"]],  // C or G or U
                'X' => [$aRnaWeightsTemp["C"], $aRnaWeightsTemp["G"]],  // G, A, U or C
                'N' => [$aRnaWeightsTemp["C"], $aRnaWeightsTemp["G"]]   // G, A, U or C
            ];

            $aAllNaWts = ['DNA' => $aDnaWeights, 'RNA' => $aRnaWeights];
            $aNaWts = $aAllNaWts[$sMoltype];

            $fMwt = 0;
            $iNALen = strlen($sSequence);

            if($sLimit == "lowerlimit") {
                $iWlimit = 1;
            }
            else if($sLimit == "upperlimit") {
                $iWlimit = 0;
            }

            for ($i = 0; $i < $iNALen; $i++) {
                $sNAbase = substr($sSequence, $i, 1);
                $fMwt += $aNaWts[$sNAbase][$iWlimit];
            }
            $fMwt += $aWater["weight"];
            return $fMwt;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Gets Temperature
     * @param   string      $sPrimer
     * @param   int         $iPrimerLen
     * @return  float
     * @throws  \Exception
     */
    private function calculateTemperature($sPrimer, $iPrimerLen)
    {
        try {
            $fTemperature = 0;

            $iNbAT = substr_count($sPrimer,"A");
            $iNbCG = substr_count($sPrimer,"G");

            if($iPrimerLen > 0) {
                if($iPrimerLen < 14) {
                    $fTemperature = round(2 * ($iNbAT) + 4 * ($iNbCG));
                } else {
                    $fTemperature = round(64.9 + 41 * (($iNbCG - 16.4) / $iPrimerLen),1);
                }
            }

            return $fTemperature;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Unreduces the primer
     * @param       string      $primer     Sequence to analyze
     * @return      string
     * @throws      \Exception
     */
    private function primerMax($primer)
    {
        try {
            $primer = preg_replace("/A|T|W/","A",$primer);
            $primer = preg_replace("/C|G|Y|R|S|K|M|D|V|H|B|N/","G",$primer);
            return $primer;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Reduces the primer
     * @param       string      $primer     Sequence to analyze
     * @return      string
     * @throws      \Exception
     */
    private function primerMin($primer)
    {
        try {
            $primer = preg_replace("/A|T|Y|R|W|K|M|D|V|H|B|N/","A",$primer);
            $primer = preg_replace("/C|G|S/","G",$primer);
            return $primer;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
}