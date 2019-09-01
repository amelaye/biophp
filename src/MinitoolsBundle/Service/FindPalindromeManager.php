<?php
/**
 * FindPalindromeManager
 * Inspired by BioPHP's project biophp.org
 * Created 26 february 2019
 * Last modified 1st september 2019
 */
namespace MinitoolsBundle\Service;

use AppBundle\Traits\SequenceTrait;

/**
 * Class FindPalindromeManager
 * @package MinitoolsBundle\Service
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class FindPalindromeManager
{
    use SequenceTrait;

    /**
     * Searches sequence for palindromic substrings
     * @param   string  $sSequence      is the sequence to be searched
     * @param   int     $iMin           the minimum length of palindromic sequence to be searched
     * @param   int     $iMax           the maximum length of palindromic sequence to be searched
     * @return  array                   keys are positions in genome, and values are length of palindromic sequences
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
            if ($sSequence == $this->revCompDNA($sSequence)) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
}