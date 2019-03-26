<?php
/**
 * MeltingTemperatureManager
 * Inspired by BioPHP's project biophp.org
 * Created 26 february 2019
 * Last modified 26 march 2019
 * RIP Pasha, gone 27 february 2019 =^._.^= ∫
 */
namespace MinitoolsBundle\Service;

use AppBundle\Service\NucleotidsManager;

/**
 * Class MeltingTemperatureManager
 * @package MinitoolsBundle\Service
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class MeltingTemperatureManager
{
    /**
     * @var array
     */
    private $dnaWeights;

    /**
     * @var array
     */
    private $rnaWeights;

    /**
     * @var array
     */
    private $tmBaseStacking;

    /**
     * @var array
     */
    private $elements;

    /**
     * @var NucleotidsManager
     */
    private $oNucleotidsManager;

    /**
     * MeltingTemperatureManager constructor.
     * @param   NucleotidsManager   $oNucleotidsManager     Service counting nucleotids
     * @param   array               $dnaWeights             Array of A, T, G, C Weights
     * @param   array               $rnaWeights             Array of A, C, G, U Weights
     * @param   array               $tmBaseStacking         Basic temperatures of nucleotids combinations
     * @param   array               $elements               Weights of basic elements (for water)
     */
    public function __construct(
        NucleotidsManager $oNucleotidsManager,
        array $dnaWeights,
        array $rnaWeights,
        array $tmBaseStacking,
        array $elements
    )
    {
        $this->oNucleotidsManager   = $oNucleotidsManager;
        $this->dnaWeights           = $dnaWeights;
        $this->rnaWeights           = $rnaWeights;
        $this->tmBaseStacking       = $tmBaseStacking;
        $this->elements             = $elements;
    }

    /**
     * Gets different informations when degenerated nucleotids are not allowed
     * @param   string      $primer         Primer string
     * @param   int         $conc_primer    Primer concentration
     * @param   int         $conc_salt      Salt concentration:
     * @param   int         $conc_mg        Mg2+ concentration
     * @return  array
     * @throws  \Exception
     */
    public function tmBaseStacking($primer, $conc_primer, $conc_salt, $conc_mg)
    {
        try {
            if ($this->oNucleotidsManager->countACGT($primer) != strlen($primer)) {
                throw new \Exception("The oligonucleotide is not valid");
            }
            $h = $s = 0;

            $array_h = $this->tmBaseStacking["enthalpy"]; // enthalpy values
            $array_s = $this->tmBaseStacking["entropy"]; // entropy values

            // effect on entropy by salt correction; von Ahsen et al 1999
            // Increase of stability due to presence of Mg;
            $salt_effect = ($conc_salt/1000) + (($conc_mg/1000) * 140);
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
            $tm = ((1000 * $h) / ($s + (1.987 * log($conc_primer / 2000000000)))) - 273.15;

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
            $water = $this->elements["water"];

            $dnaWeights = [
                'A' => [$this->dnaWeights["A_wt"], $this->dnaWeights["A_wt"]],  // Adenine
                'C' => [$this->dnaWeights["C_wt"], $this->dnaWeights["C_wt"]],  // Cytosine
                'G' => [$this->dnaWeights["G_wt"], $this->dnaWeights["G_wt"]],  // Guanine
                'T' => [$this->dnaWeights["T_wt"], $this->dnaWeights["T_wt"]],  // Thymine
                'M' => [$this->dnaWeights["C_wt"], $this->dnaWeights["A_wt"]],  // A or C
                'R' => [$this->dnaWeights["A_wt"], $this->dnaWeights["G_wt"]],  // A or G
                'W' => [$this->dnaWeights["T_wt"], $this->dnaWeights["A_wt"]],  // A or T
                'S' => [$this->dnaWeights["C_wt"], $this->dnaWeights["G_wt"]],  // C or G
                'Y' => [$this->dnaWeights["C_wt"], $this->dnaWeights["T_wt"]],  // C or T
                'K' => [$this->dnaWeights["T_wt"], $this->dnaWeights["G_wt"]],  // G or T
                'V' => [$this->dnaWeights["C_wt"], $this->dnaWeights["G_wt"]],  // A or C or G
                'H' => [$this->dnaWeights["C_wt"], $this->dnaWeights["A_wt"]],  // A or C or T
                'D' => [$this->dnaWeights["T_wt"], $this->dnaWeights["G_wt"]],  // A or G or T
                'B' => [$this->dnaWeights["C_wt"], $this->dnaWeights["G_wt"]],  // C or G or T
                'X' => [$this->dnaWeights["C_wt"], $this->dnaWeights["G_wt"]],  // G, A, T or C
                'N' => [$this->dnaWeights["C_wt"], $this->dnaWeights["G_wt"]]   // G, A, T or C
            ];


            $rnaWeights = [
                'A' => [$this->rnaWeights["A_wt"], $this->rnaWeights["A_wt"]],  // Adenine
                'C' => [$this->rnaWeights["C_wt"], $this->rnaWeights["C_wt"]],  // Cytosine
                'G' => [$this->rnaWeights["G_wt"], $this->rnaWeights["G_wt"]],  // Guanine
                'U' => [$this->rnaWeights["U_wt"], $this->rnaWeights["U_wt"]],  // Uracil
                'M' => [$this->rnaWeights["C_wt"], $this->rnaWeights["A_wt"]],  // A or C
                'R' => [$this->rnaWeights["A_wt"], $this->rnaWeights["G_wt"]],  // A or G
                'W' => [$this->rnaWeights["U_wt"], $this->rnaWeights["A_wt"]],  // A or U
                'S' => [$this->rnaWeights["C_wt"], $this->rnaWeights["G_wt"]],  // C or G
                'Y' => [$this->rnaWeights["C_wt"], $this->rnaWeights["U_wt"]],  // C or U
                'K' => [$this->rnaWeights["U_wt"], $this->rnaWeights["G_wt"]],  // G or U
                'V' => [$this->rnaWeights["C_wt"], $this->rnaWeights["G_wt"]],  // A or C or G
                'H' => [$this->rnaWeights["C_wt"], $this->rnaWeights["A_wt"]],  // A or C or U
                'D' => [$this->rnaWeights["U_wt"], $this->rnaWeights["G_wt"]],  // A or G or U
                'B' => [$this->rnaWeights["C_wt"], $this->rnaWeights["G_wt"]],  // C or G or U
                'X' => [$this->rnaWeights["C_wt"], $this->rnaWeights["G_wt"]],  // G, A, U or C
                'N' => [$this->rnaWeights["C_wt"], $this->rnaWeights["G_wt"]]   // G, A, U or C
            ];

            $all_na_wts = ['DNA' => $dnaWeights, 'RNA' => $rnaWeights];
            $na_wts = $all_na_wts[$sMoltype];

            $mwt = 0;
            $NA_len = strlen($sSequence);

            if($sLimit == "lowerlimit") {
                $wlimit = 1;
            }
            if($sLimit == "upperlimit") {
                $wlimit = 0;
            }

            for ($i = 0; $i < $NA_len; $i++) {
                $NA_base = substr($sSequence, $i, 1);
                $mwt += $na_wts[$NA_base][$wlimit];
            }
            $mwt += $water;

            return $mwt;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
}