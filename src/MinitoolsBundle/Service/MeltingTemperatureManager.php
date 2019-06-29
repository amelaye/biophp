<?php
/**
 * MeltingTemperatureManager
 * Inspired by BioPHP's project biophp.org
 * Created 26 february 2019
 * Last modified 24 june 2019
 * RIP Pasha, gone 27 february 2019 =^._.^= ∫
 */
namespace MinitoolsBundle\Service;

use AppBundle\Service\NucleotidsManager;
use AppBundle\Bioapi\Bioapi;

/**
 * Class MeltingTemperatureManager
 * @package MinitoolsBundle\Service
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
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
     * @param $aTmBaseStacking
     * @param $primer
     * @param $cp
     * @param $cs
     * @param $cmg
     * @throws \Exception
     */
    public function neighborCalculations($bNearestNeighbor, &$aTmBaseStacking, $primer, $cp, $cs, $cmg)
    {
        if($bNearestNeighbor) {
            $aTmBaseStacking = $this->tmBaseStacking(
                $primer, $cp, $cs, $cmg
            );
        }
    }

    /**
     * Gets different informations when degenerated nucleotids are not allowed
     * @param   string      $primer         Primer string
     * @param   int         $concPrimer     Primer concentration
     * @param   int         $concSalt       Salt concentration
     * @param   int         $concMg         Mg2+ concentration
     * @return  array
     * @throws  \Exception
     */
    public function tmBaseStacking($primer, $concPrimer, $concSalt, $concMg)
    {
        try {
            $h = $s = 0;

            $array_h = $this->bioapi->getEnthalpyValues();
            $array_s = $this->bioapi->getEnthropyValues();

            // effect on entropy by salt correction; von Ahsen et al 1999
            // Increase of stability due to presence of Mg;
            $salt_effect = ($concSalt/1000) + (($concMg/1000) * 140);
            // effect on entropy
            $s += 0.368 * (strlen($primer)-1) * log($salt_effect);

            // terminal corrections. Santalucia 1998
            $firstnucleotide = substr($primer,0,1);
            if($firstnucleotide == "G" || $firstnucleotide == "C") {
                $h += 0.1;
                $s += -2.8;
            }
            if($firstnucleotide == "A" ||  $firstnucleotide == "T") {
                $h += 2.3;
                $s += 4.1;
            }

            $lastnucleotide = substr($primer,strlen($primer)-1,1);
            if ($lastnucleotide == "G" || $lastnucleotide == "C") {
                $h += 0.1;
                $s += -2.8;
            }
            if ($lastnucleotide == "A" || $lastnucleotide == "T"){
                $h += 2.3;
                $s += 4.1;
            }

            // compute new H and s based on sequence. Santalucia 1998
            for($i = 0; $i < strlen($primer)-1; $i++) {
                $subc = substr($primer,$i,2);
                $h += $array_h[$subc];
                $s += $array_s[$subc];
            }
            $tm = ((1000 * $h) / ($s + (1.987 * log($concPrimer / 2000000000)))) - 273.15;

            return [
                'tm'        => round($tm, 1),
                'enthalpy'  => round($h,2),
                'entropy'   => round($s,2)
            ];
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Gets temperature mini
     * @param       string      $primer     Sequence to analyze
     * @return      float
     * @throws      \Exception
     */
    public function tmMin($primer)
    {
        try {
            $primer_len = strlen($primer);
            $primer2 = preg_replace("/A|T|Y|R|W|K|M|D|V|H|B|N/","A",$primer);
            $n_AT = substr_count($primer2,"A");
            $primer2 = preg_replace("/C|G|S/","G",$primer);
            $n_CG = substr_count($primer2,"G");

            if($primer_len > 0) {
                if($primer_len < 14) {
                    return round(2 * ($n_AT) + 4 * ($n_CG));
                } else {
                    return round(64.9 + 41*(($n_CG-16.4)/$primer_len),1);
                }
            }
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }


    /**
     * Gets temperature maxi
     * @param       string      $primer     Sequence to analyze
     * @return      float
     * @throws      \Exception
     */
    public function tmMax($primer)
    {
        try {
            $primer_len = strlen($primer);
            $primer = $this->primerMax($primer);
            $n_AT = substr_count($primer,"A");
            $n_CG = substr_count($primer,"G");
            if($primer_len > 0) {
                if($primer_len < 14) {
                    return round(2 * ($n_AT) + 4 * ($n_CG));
                } else {
                    return round(64.9 + 41*(($n_CG-16.4)/$primer_len),1);
                }
            }
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
    public function primerMin($primer)
    {
        try {
            $primer = preg_replace("/A|T|Y|R|W|K|M|D|V|H|B|N/","A",$primer);
            $primer = preg_replace("/C|G|S/","G",$primer);
            return $primer;
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
    function primerMax($primer)
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
     * Gets the weight of the sequence
     * @param   string      $sSequence       Sequence to analyse
     * @param   string      $sMoltype        DNA or RNA
     * @param   string      $sLimit          "Upperlimit" or "lowerlimit" (string)
     * @return  float
     * @throws  \Exception
     */
    function molwt($sSequence, $sMoltype, $sLimit)
    {
        try {
            $water = $this->bioapi->getWater();
            $dnaWeightsTemp = $this->bioapi->getDNAWeight();
            $rnaWeightsTemp = $this->bioapi->getRNAWeight();

            $dnaWeights = [
                'A' => [$dnaWeightsTemp["A"], $dnaWeightsTemp["A"]],  // Adenine
                'C' => [$dnaWeightsTemp["C"], $dnaWeightsTemp["C"]],  // Cytosine
                'G' => [$dnaWeightsTemp["G"], $dnaWeightsTemp["G"]],  // Guanine
                'T' => [$dnaWeightsTemp["T"], $dnaWeightsTemp["T"]],  // Thymine
                'M' => [$dnaWeightsTemp["C"], $dnaWeightsTemp["A"]],  // A or C
                'R' => [$dnaWeightsTemp["A"], $dnaWeightsTemp["G"]],  // A or G
                'W' => [$dnaWeightsTemp["T"], $dnaWeightsTemp["A"]],  // A or T
                'S' => [$dnaWeightsTemp["C"], $dnaWeightsTemp["G"]],  // C or G
                'Y' => [$dnaWeightsTemp["C"], $dnaWeightsTemp["T"]],  // C or T
                'K' => [$dnaWeightsTemp["T"], $dnaWeightsTemp["G"]],  // G or T
                'V' => [$dnaWeightsTemp["C"], $dnaWeightsTemp["G"]],  // A or C or G
                'H' => [$dnaWeightsTemp["C"], $dnaWeightsTemp["A"]],  // A or C or T
                'D' => [$dnaWeightsTemp["T"], $dnaWeightsTemp["G"]],  // A or G or T
                'B' => [$dnaWeightsTemp["C"], $dnaWeightsTemp["G"]],  // C or G or T
                'X' => [$dnaWeightsTemp["C"], $dnaWeightsTemp["G"]],  // G, A, T or C
                'N' => [$dnaWeightsTemp["C"], $dnaWeightsTemp["G"]]   // G, A, T or C
            ];


            $rnaWeights = [
                'A' => [$rnaWeightsTemp["A"], $rnaWeightsTemp["A"]],  // Adenine
                'C' => [$rnaWeightsTemp["C"], $rnaWeightsTemp["C"]],  // Cytosine
                'G' => [$rnaWeightsTemp["G"], $rnaWeightsTemp["G"]],  // Guanine
                'U' => [$rnaWeightsTemp["U"], $rnaWeightsTemp["U"]],  // Uracil
                'M' => [$rnaWeightsTemp["C"], $rnaWeightsTemp["A"]],  // A or C
                'R' => [$rnaWeightsTemp["A"], $rnaWeightsTemp["G"]],  // A or G
                'W' => [$rnaWeightsTemp["U"], $rnaWeightsTemp["A"]],  // A or U
                'S' => [$rnaWeightsTemp["C"], $rnaWeightsTemp["G"]],  // C or G
                'Y' => [$rnaWeightsTemp["C"], $rnaWeightsTemp["U"]],  // C or U
                'K' => [$rnaWeightsTemp["U"], $rnaWeightsTemp["G"]],  // G or U
                'V' => [$rnaWeightsTemp["C"], $rnaWeightsTemp["G"]],  // A or C or G
                'H' => [$rnaWeightsTemp["C"], $rnaWeightsTemp["A"]],  // A or C or U
                'D' => [$rnaWeightsTemp["U"], $rnaWeightsTemp["G"]],  // A or G or U
                'B' => [$rnaWeightsTemp["C"], $rnaWeightsTemp["G"]],  // C or G or U
                'X' => [$rnaWeightsTemp["C"], $rnaWeightsTemp["G"]],  // G, A, U or C
                'N' => [$rnaWeightsTemp["C"], $rnaWeightsTemp["G"]]   // G, A, U or C
            ];

            $all_na_wts = ['DNA' => $dnaWeights, 'RNA' => $rnaWeights];
            $na_wts = $all_na_wts[$sMoltype];

            $mwt = 0;
            $NA_len = strlen($sSequence);

            if($sLimit == "lowerlimit") {
                $wlimit = 1;
            }
            else if($sLimit == "upperlimit") {
                $wlimit = 0;
            }

            for ($i = 0; $i < $NA_len; $i++) {
                $NA_base = substr($sSequence, $i, 1);
                $mwt += $na_wts[$NA_base][$wlimit];
            }
            $mwt += $water["weight"];

            return $mwt;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
}