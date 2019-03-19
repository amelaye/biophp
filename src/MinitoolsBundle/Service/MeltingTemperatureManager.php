<?php
/**
 * MeltingTemperatureManager
 * @author Amélie DUVERNET akka Amelaye
 * Inspired by BioPHP's project biophp.org
 * Created 26 february 2019
 * Last modified 26 february 2019
 */
namespace MinitoolsBundle\Service;

class MeltingTemperatureManager
{
    private $dnaWeights;

    private $rnaWeights;

    private $tmBaseStacking;

    /**
     * MeltingTemperatureManager constructor.
     * @param array $dnaWeights
     * @param array $rnaWeights
     * @param array $tmBaseStacking
     */
    public function __construct(array $dnaWeights, array $rnaWeights, array $tmBaseStacking)
    {
        $this->dnaWeights       = $dnaWeights;
        $this->rnaWeights       = $rnaWeights;
        $this->tmBaseStacking   = $tmBaseStacking;
    }

    /**
     * @param $c
     * @param $conc_primer
     * @param $conc_salt
     * @param $conc_mg
     * @throws \Exception
     */
    public function tmBaseStacking($c, $conc_primer, $conc_salt, $conc_mg)
    {
        try {
            if (CountATCG($c) != strlen($c)) {
                print "The oligonucleotide is not valid";
                return;
            }
            $h = $s = 0;

            $array_h = $this->tmBaseStacking["enthalpy"]; // enthalpy values
            $array_s = $this->tmBaseStacking["entropy"]; // entropy values

            // effect on entropy by salt correction; von Ahsen et al 1999
            // Increase of stability due to presence of Mg;
            $salt_effect = ($conc_salt/1000)+(($conc_mg/1000) * 140);
            // effect on entropy
            $s+=0.368 * (strlen($c)-1)* log($salt_effect);

            // terminal corrections. Santalucia 1998
            $firstnucleotide = substr($c,0,1);
            if($firstnucleotide == "G" || $firstnucleotide == "C") {
                $h += 0.1;
                $s += -2.8;
            }
            if($firstnucleotide == "A" ||  $firstnucleotide == "T") {
                $h += 2.3;
                $s += 4.1;
            }

            $lastnucleotide=substr($c,strlen($c)-1,1);
            if ($lastnucleotide == "G" || $lastnucleotide == "C") {
                $h += 0.1;
                $s += -2.8;
            }
            if ($lastnucleotide == "A" || $lastnucleotide == "T"){
                $h += 2.3;
                $s += 4.1;
            }

            // compute new H and s based on sequence. Santalucia 1998
            for($i=0; $i<strlen($c)-1; $i++) {
                $subc = substr($c,$i,2);
                $h += $array_h[$subc];
                $s += $array_s[$subc];
            }
            $tm = ((1000*$h)/($s+(1.987*log($conc_primer/2000000000))))-273.15;
            print "Tm:                 <font color=880000><b>".round($tm,1)." &deg;C</b></font>";
            print  "\n<font color=008800>  Enthalpy: ".round($h,2)."\n  Entropy:  ".round($s,2)."</font>";
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }


    /**
     * @param $primer
     * @throws \Exception
     */
    public function mol_wt($primer)
    {
        try {
            $upper_mwt = $this->molwt($primer,"DNA","upperlimit");
            $lower_mwt = $this->molwt($primer,"DNA","lowerlimit");
            if ($upper_mwt == $lower_mwt) {
                print "Molecular weight:        $upper_mwt";
            } else {
                print "Upper Molecular weight:  $upper_mwt\nLower Molecular weight:  $lower_mwt";
            }
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }


    /**
     * @param $primer
     * @return float
     * @throws \Exception
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
     * @param $primer
     * @return float
     * @throws \Exception
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
     * @param $primer
     * @return string|string[]|null
     * @throws \Exception
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
     * @param $primer
     * @return string|string[]|null
     * @throws \Exception
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
     * @param $sequence
     * @param $moltype
     * @param $limit
     * @return float|int
     * @throws \Exception
     */
    function molwt($sequence, $moltype, $limit)
    {
        try {
            $water = 18.015;

            $dna_wts = [
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


            $rna_wts = [
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

            $all_na_wts = array('DNA' => $dna_wts, 'RNA' => $rna_wts);
            $na_wts = $all_na_wts[$moltype];

            $mwt = 0;
            $NA_len = strlen($sequence);

            if($limit == "lowerlimit"){
                $wlimit=1;
            }
            if($limit == "upperlimit"){
                $wlimit=0;
            }

            for ($i = 0; $i < $NA_len; $i++) {
                $NA_base = substr($sequence, $i, 1);
                $mwt += $na_wts[$NA_base][$wlimit];
            }
            $mwt += $water;

            return $mwt;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
}