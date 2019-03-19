<?php
/**
 * FindPalindromeManager
 * @author Amélie DUVERNET akka Amelaye
 * Inspired by BioPHP's project biophp.org
 * Created 26 february 2019
 * Last modified 18 march 2019
 */
namespace MinitoolsBundle\Service;

use AppBundle\Service\NucleotidsManager;

class FindPalindromeManager
{
    private $oNucleotidsManager;

    public function __construct(NucleotidsManager $oNucleotidsManager)
    {
        $this->oNucleotidsManager = $oNucleotidsManager;
    }

    /**
     * Searches sequence for palindromic substrings
     * @param   string  $sSequence      is the sequence to be searched
     * @param   int     $iMin           the minimum length of palindromic sequence to be searched
     * @param   int     $iMax           the maximum length of palindromic sequence to be searched
     * @return  array   keys are positions in genome, and values are length of palindromic sequences
     * @throws \Exception
     */
    public function findPalindromicSeqs($sSequence, $iMin, $iMax)
    {
        try {
            $results = [];
            $seqLen = strlen($sSequence);
            for($i = 0; $i < $seqLen-$iMin+1; $i++) {
                $j = $iMin;
                while($j < $iMax+1 && ($i+$j) <= $seqLen) {
                    $subSeq = substr($sSequence, $i, $j);
                    if ($this->dnaIsPalindrome($subSeq) == 1) {
                        $results[$i] = $subSeq;
                    }
                    $j++;
                }

            }
            return $results;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }


    /**
     * Checks whether a DNA sequence is palindromic.
     * When degenerate nucleotides are included in the sequence to be searched,
     * sequences as "AANTT" will be considered palindromic.
     * @param   string      $sSequence      is the sequence to be searched
     * @return  bool
     * @throws \Exception
     */
    public function dnaIsPalindrome($sSequence)
    {
        try {
            if ($sSequence == $this->revCompDNA2($sSequence)) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }


    /**
     * Will yield the Reverse complement of a NA sequence. Allows degenerated nucleotides
     * @param   string      $sSequence      is the sequence
     * @return  string
     * @throws \Exception
     */
    public function revCompDNA2($sSequence)
    {
        try {
            $sSequence = strtoupper($sSequence);
            $sSequence = strrev($sSequence);
            $aPattern = ["A", "T", "G", "C", "Y", "R", "W", "S",  "K", "M",  "D", "V",  "H",  "B"];
            $aReplace = ["t", "a",  "c", "g", "r", "y", "w", "s", "m", "k", "h", "b", "d", "v"];
            $sSequence = str_replace($aPattern, $aReplace, $sSequence);
            $sSequence = strtoupper ($sSequence);
            return $sSequence;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }


    /**
     * Will remove non coding characters from a DNA sequence
     * @param   string      $sSequence      is the sequence
     * @return  string
     * @throws \Exception
     */
    public function removeUselessFromDNA($sSequence)
    {
        try {
            $sSequence = strtoupper($sSequence);
            $sSequence = preg_replace("/\\W|\\d/","", $sSequence);
            $sSequence = preg_replace("/X/","N", $sSequence);
            $len_seq = strlen($sSequence);
            $number_ATGC = $this->oNucleotidsManager->countACGT($sSequence);
            $number_YRWSKMDVHB = $this->oNucleotidsManager->countYRWSKMDVHB($sSequence);
            $number = $number_ATGC + $number_YRWSKMDVHB + substr_count($sSequence,"N");
            if ($number != $len_seq) {
                throw new \Exception("Sequence is not valid. At least one letter in the sequence is unknown (not a NC-UIBMB valid code)");
            }
            return ($sSequence);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
}