<?php
/**
 * FindPalindromeManager
 * @author Amélie DUVERNET akka Amelaye
 * Inspired by BioPHP's project biophp.org
 * Created 26 february 2019
 * Last modified 26 february 2019
 */
namespace MinitoolsBundle\Service;

class FindPalindromeManager
{
    /**
     * Searches sequence for palindromic substrings
     * @param   string $seq is the sequence to be searched
     * @param   int $min the minimum length of palindromic sequence to be searched
     * @param   int $max the maximum length of palindromic sequence to be searched
     * @return  array   keys are positions in genome, and values are length of palindromic sequences
     * @throws \Exception
     */
    public function findPalindromicSeqs($seq, $min, $max)
    {
        try {
            $result = "";
            $seq_len = strlen($seq);
            for($i = 0; $i < $seq_len-$min+1; $i++) {
                $j = $min;
                while($j < $max+1 && ($i+$j) <= $seq_len) {
                    $sub_seq = substr($seq,$i,$j);
                    if ($this->dnaIsPalindrome($sub_seq)==1) {
                        $results [$i] = $sub_seq;
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
     * Checks whether a DNA sequeence is palindromic.
     * When degenerate nucleotides are included in the sequence to be searched,
     * sequences as "AANTT" will be considered palindromic.
     * @param   string $seq is the sequence to be searched
     * @return  bool
     * @throws \Exception
     */
    public function dnaIsPalindrome($seq)
    {
        try {
            if ($seq == $this->revCompDNA2($seq)) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }


    /**
     * Will yield the Reverse comlement of a NA sequence. Allows degenerated nucleotides
     * @param   string $seq is the sequence
     * @return  string
     * @throws \Exception
     */
    public function revCompDNA2($seq)
    {
        try {
            $seq = strtoupper($seq);
            $seq = strrev($seq);
            $seq = str_replace("A", "t", $seq);
            $seq = str_replace("T", "a", $seq);
            $seq = str_replace("G", "c", $seq);
            $seq = str_replace("C", "g", $seq);
            $seq = str_replace("Y", "r", $seq);
            $seq = str_replace("R", "y", $seq);
            $seq = str_replace("W", "w", $seq);
            $seq = str_replace("S", "s", $seq);
            $seq = str_replace("K", "m", $seq);
            $seq = str_replace("M", "k", $seq);
            $seq = str_replace("D", "h", $seq);
            $seq = str_replace("V", "b", $seq);
            $seq = str_replace("H", "d", $seq);
            $seq = str_replace("B", "v", $seq);
            $seq = strtoupper ($seq);
            return $seq;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }


    /**
     * Will remove non coding characters from a DNA sequence
     * @param   string $seq is the sequence
     * @return  string
     * @throws \Exception
     */
    public function removeUselessFromDNA($seq)
    {
        try {
            $seq = strtoupper($seq);
            $seq = preg_replace("/\\W|\\d/","",$seq);
            $seq = preg_replace("/X/","N",$seq);
            $len_seq = strlen($seq);
            $number_ATGC = $this->countACGT($seq);
            $number_YRWSKMDVHB = $this->countYRWSKMDVHB($seq);
            $number = $number_ATGC + $number_YRWSKMDVHB + substr_count($seq,"N");
            if ($number != $len_seq) {
                throw new \Exception("Sequence is not valid. At least one letter in the sequence is unknown (not a NC-UIBMB valid code)");
            }
            return ($seq);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }


    /**
     * Will count number of A, C, G and T bases in the sequence
     * @param   string $seq is the sequence
     * @return  int
     * @throws \Exception
     */
    public function countACGT($seq)
    {
        try {
            $cg = substr_count($seq,"A")
                + substr_count($seq,"T")
                + substr_count($seq,"G")
                + substr_count($seq,"C");
            return $cg;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }


    /**
     * Will count number of degenerate nucleotides (Y, R, W, S, K, MD, V, H and B) in the sequence
     * @param   string $c
     * @return  int
     * @throws \Exception
     */
    public function countYRWSKMDVHB($c){
        try {
            $cg = substr_count($c,"Y")
                + substr_count($c,"R")
                + substr_count($c,"W")
                + substr_count($c,"S")
                + substr_count($c,"K")
                + substr_count($c,"M")
                + substr_count($c,"D")
                + substr_count($c,"V")
                + substr_count($c,"H")
                + substr_count($c,"B");
            return $cg;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
}