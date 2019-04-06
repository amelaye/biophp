<?php
/**
 * Formulas Functions
 * Inspired by BioPHP's project biophp.org
 * Created 3 march  2019
 * Last modified 6 april 2019
 * RIP Pasha, gone 27 february 2019 =^._.^= ∫
 */
namespace MinitoolsBundle\Service;

/**
 * Class RandomSequencesManager
 * @package MinitoolsBundle\Service
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class RandomSequencesManager
{
    private $randomSequence;

    /**
     * @param $randomSequence
     */
    public function setRandomSequence($randomSequence)
    {
        $this->randomSequence = $randomSequence;
    }


    /**
     * Generate a random protein or DNA sequence
     * $a, $c, $g and $t are the number of nucleotides A, C, G or T
     * @param  array $aElements
     * @return string
     */
    public function randomize($aElements)
    {
        $sElements = "";
        foreach($aElements as $key => $element) {
            $sElements .= str_repeat($key, $element);
        }
        return str_shuffle($sElements);
    }

    /**
     * Creates a random sequence
     * @todo : refacto cette methode
     * @param       int         $iLength        Length of the sequence
     * @param       string      $sSequence      Sequence
     * @return string
     * @throws \Exception
     */
    public function createFromSeq($iLength, $sSequence)
    {
        try {
            if($iLength != "") {
                // remove from sequence characters different to ACGT.
                $seqACGT = preg_replace("/[^ACGT]/","", $sSequence);
                // The sequence is DNA if A+C+G+T>70% (so, if $seqACGT is long enough)
                if(strlen($seqACGT) > strlen($sSequence) * 0.7) {
                    $aDNA = [];
                    // The sequence is DNA
                    // get the frequencies for each nucleotide
                    $a = substr_count($sSequence,"A");
                    $c = substr_count($sSequence,"C");
                    $g = substr_count($sSequence,"G");
                    $t = substr_count($sSequence,"T");
                    $acgt = $a + $c + $g + $t;
                    // Get number of ocurrences per each nucleotide for a seq with length=$length1
                    $aDNA["A"] = round($a * $iLength / $acgt);
                    $aDNA["C"] = round($c * $iLength / $acgt);
                    $aDNA["G"] = round($g * $iLength / $acgt);
                    $aDNA["T"] = round($t * $iLength / $acgt);
                    // get randomized sequence
                    $result = $this->randomize($aDNA);
                } else {
                    // The sequence is protein
                    $aProteins = [];
                    // get the frequencies for each aminoacid
                    $A = substr_count($sSequence,"A");
                    $C = substr_count($sSequence,"C");
                    $D = substr_count($sSequence,"D");
                    $E = substr_count($sSequence,"E");
                    $F = substr_count($sSequence,"F");
                    $G = substr_count($sSequence,"G");
                    $H = substr_count($sSequence,"H");
                    $I = substr_count($sSequence,"I");
                    $K = substr_count($sSequence,"K");
                    $L = substr_count($sSequence,"L");
                    $M = substr_count($sSequence,"M");
                    $N = substr_count($sSequence,"N");
                    $P = substr_count($sSequence,"P");
                    $Q = substr_count($sSequence,"Q");
                    $R = substr_count($sSequence,"R");
                    $S = substr_count($sSequence,"S");
                    $T = substr_count($sSequence,"T");
                    $V = substr_count($sSequence,"V");
                    $W = substr_count($sSequence,"W");
                    $Y = substr_count($sSequence,"Y");
                    $ACDEFGHIKLMNPGRSTVWY = $A + $C + $D + $E + $F + $G + $H + $I + $K + $L + $M + $N + $P
                        + $Q + $R + $S + $T + $V + $W + $Y;
                    // Get number of ocurrences per each aminoacid for a seq with length=$length1
                    $aProteins["A"] = round($A * $iLength / $ACDEFGHIKLMNPGRSTVWY);
                    $aProteins["C"] = round($C * $iLength / $ACDEFGHIKLMNPGRSTVWY);
                    $aProteins["D"] = round($D * $iLength / $ACDEFGHIKLMNPGRSTVWY);
                    $aProteins["E"] = round($E * $iLength / $ACDEFGHIKLMNPGRSTVWY);
                    $aProteins["F"] = round($F * $iLength / $ACDEFGHIKLMNPGRSTVWY);
                    $aProteins["G"] = round($G * $iLength / $ACDEFGHIKLMNPGRSTVWY);
                    $aProteins["H"] = round($H * $iLength / $ACDEFGHIKLMNPGRSTVWY);
                    $aProteins["I"] = round($I * $iLength / $ACDEFGHIKLMNPGRSTVWY);
                    $aProteins["K"] = round($K * $iLength / $ACDEFGHIKLMNPGRSTVWY);
                    $aProteins["L"] = round($L * $iLength / $ACDEFGHIKLMNPGRSTVWY);
                    $aProteins["M"] = round($M * $iLength / $ACDEFGHIKLMNPGRSTVWY);
                    $aProteins["N"] = round($N * $iLength / $ACDEFGHIKLMNPGRSTVWY);
                    $aProteins["P"] = round($P * $iLength / $ACDEFGHIKLMNPGRSTVWY);
                    $aProteins["Q"] = round($Q * $iLength / $ACDEFGHIKLMNPGRSTVWY);
                    $aProteins["R"] = round($R * $iLength / $ACDEFGHIKLMNPGRSTVWY);
                    $aProteins["S"] = round($S * $iLength / $ACDEFGHIKLMNPGRSTVWY);
                    $aProteins["T"] = round($T * $iLength / $ACDEFGHIKLMNPGRSTVWY);
                    $aProteins["V"] = round($V * $iLength / $ACDEFGHIKLMNPGRSTVWY);
                    $aProteins["W"] = round($W * $iLength / $ACDEFGHIKLMNPGRSTVWY);
                    $aProteins["Y"] = round($Y * $iLength / $ACDEFGHIKLMNPGRSTVWY);
                    // get randomized sequence
                    $result = $this->randomize($aProteins);
                }
            } else {
                // just shuffle the sequence when length is not provided
                $result = str_shuffle($sSequence);
            }
            return $result;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Creates a random sequence from ATGC combination
     * @param       array       $aAminoAcids    Array containing combinations of nucleotids
     * @param       int         $iLength        Length of the sequence
     * @return      string
     * @throws      \Exception
     */
    public function createFromACGT($aAminoAcids, $iLength)
    {
        try {
            $aDNA = [];
            $acgt = 0;
            if ($iLength != "") {
                // in case length is specified
                foreach($aAminoAcids as $amino) {
                    $acgt += $amino;
                }
                foreach($aAminoAcids as $key => $data) {
                    $aDNA[$key] = round($data * $iLength / $acgt);
                }
            } else {
                // in case length is not specified
                foreach($aAminoAcids as $key => $data) {
                    $aDNA[$key] = round($data);
                }
            }

            $result = $this->randomize($aDNA); // get randomized sequence
            return $result;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Creates a random sequence from ATGC combination
     * @param       array       $aAminoAcids    Array containing combinations of nucleotids
     * @param       int         $iLength        Length of the sequence
     * @return      string
     * @throws      \Exception
     */
    public function createFromAA($aAminoAcids, $iLength)
    {
        try {
            $aProteins = [];
            $ACDEFGHIKLMNPGRSTVWY = 0;

            // Get number of ocurrences per each aminoacid
            if ($iLength != "") {
                // in case length is specified
                foreach($aAminoAcids as $amino) {
                    $ACDEFGHIKLMNPGRSTVWY += $amino;
                }

                foreach($aAminoAcids as $key => $data) {
                    $aProteins[$key] = round($data * $iLength / $ACDEFGHIKLMNPGRSTVWY);
                }

            } else {
                // in case length is not specified
                foreach($aAminoAcids as $key => $data) {
                    $aProteins[$key] = round($data);
                }
            }
            // get randomized sequence
            $result = $this->randomize($aProteins);

            return $result;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
}