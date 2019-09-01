<?php
/**
 * Traits for sequences formatting
 * Freely inspired by BioPHP's project biophp.org
 * Created 22 july 2019
 * Last modified 1st september 2019
 */
namespace AppBundle\Traits;

/**
 * Trait SequenceTrait
 * @package AppBundle\Traits
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
trait SequenceTrait
{
    /**
     * Returns complement of sequence $sSequence
     * @param   string    $sSequence
     * @return  string
     */
    public function comp($sSequence)
    {
        $sSequence = str_replace("A", "t", $sSequence);
        $sSequence = str_replace("T", "a", $sSequence);
        $sSequence = str_replace("G", "c", $sSequence);
        $sSequence = str_replace("C", "g", $sSequence);
        return strtoupper($sSequence);
    }

    /**
     * Computes the reverse complement of a sequence (only ACGT nucleotides are used)
     * @param   string          $sSequence
     * @return  mixed|string
     */
    public function revComp($sSequence) {
        $sSequence = strrev($sSequence);
        $sSequence = $this->comp($sSequence);
        return $sSequence;
    }

    /**
     * Returns complement of sequence $sSequence
     * @param   string          $sSequence
     * @return  string
     * @throws  \Exception
     */
    public function compDNA($sSequence)
    {
        try {
            $sSequence = strtoupper($sSequence);
            $original   = ["(A)","(T)","(G)","(C)","(Y)","(R)","(W)","(S)","(K)","(M)","(D)","(V)","(H)","(B)"];
            $complement = ["t","a","c","g","r","y","w","s","m","k","h","b","d","v"];
            $sSequence = preg_replace($original, $complement, $sSequence);
            $sSequence = strtoupper($sSequence);
            return $sSequence;
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
            $sSequence = strrev($sSequence);
            $sSequence = $this->compDNA($sSequence);
            return $sSequence;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
}
