<?php
/**
 * Formulas Functions
 * Inspired by BioPHP's project biophp.org
 * Created 3 march  2019
 * Last modified 6 april 2019
 * RIP Pasha, gone 27 february 2019 =^._.^= ∫
 */
namespace MinitoolsBundle\Service;

use MinitoolsBundle\Entity\RandomSequences;

/**
 * Class RandomSequencesManager
 * @package MinitoolsBundle\Service
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class RandomSequencesManager
{
    /**
     * @var array
     */
    private $aAminos;

    /**
     * @var array
     */
    private $aProteins;

    /**
     * RandomSequencesManager constructor.
     * @param   array   $aAminos
     * @param   array   $aProteins
     */
    public function __construct($aAminos, $aProteins)
    {
        $this->aAminos = $aAminos;
        /* we don't need X and STOP */
        array_pop($aProteins);
        array_pop($aProteins);

        foreach($aProteins as $protein) {
            if(isset($protein[3])) {
                $this->aProteins[] = $protein[1];
            }
        }
    }

    /**
     * Generate a random protein or DNA sequence
     * $a, $c, $g and $t are the number of nucleotides A, C, G or T
     * @param   array   $aElements
     * @return  string
     * @throws  \Exception
     */
    public function randomize($aElements)
    {
        try {
            $sElements = "";
            foreach($aElements as $key => $element) {
                $sElements .= str_repeat($key, $element);
            }
            return str_shuffle($sElements);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Creates a random sequence
     * @param       int         $iLength        Length of the sequence
     * @param       string      $sSequence      Sequence
     * @return string
     * @throws \Exception
     */
    public function createFromSeq($iLength, $sSequence)
    {
        try {
            if($iLength != null) {
                // remove from sequence characters different to ACGT.
                $seqACGT = preg_replace("/[^ACGT]/","", $sSequence);
                // The sequence is DNA if A+C+G+T>70% (so, if $seqACGT is long enough)
                if(strlen($seqACGT) > strlen($sSequence) * 0.7) {
                    $acgt = 0;
                    $aDNA = [];

                    // The sequence is DNA
                    // get the frequencies for each nucleotide
                    foreach($this->aAminos as $amino) {
                        $$amino = substr_count($sSequence,$amino);
                        $acgt += $$amino;
                    }

                    // Get number of ocurrences per each nucleotide for a seq with length=$length1
                    foreach($this->aAminos as $amino) {
                        $aDNA[$amino] = round($$amino * $iLength / $acgt);
                    }

                    // get randomized sequence
                    $result = $this->randomize($aDNA);
                } else {
                    // The sequence is protein
                    $aProteins = [];
                    $ACDEFGHIKLMNPGRSTVWY = 0;
                    $aListProteins = ["A", "C", "D", "E", "F", "G", "H", "I", "K", "L", "M", "N", "P", "Q", "R", "S", "T", "V", "W", "Y"];

                    // get the frequencies for each aminoacid
                    foreach($aListProteins as $protein) {
                        $$protein = substr_count($sSequence, $protein);
                        $ACDEFGHIKLMNPGRSTVWY += $$protein;
                    }

                    // Get number of ocurrences per each nucleotide for a seq with length=$length1
                    foreach($aListProteins as $protein) {
                        $aProteins[$protein] = round($$protein * $iLength / $ACDEFGHIKLMNPGRSTVWY);
                    }

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