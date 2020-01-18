<?php
/**
 * Traits for sequences formatting
 * Freely inspired by BioPHP's project biophp.org
 * Created 22 july 2019
 * Last modified 18 january 2020
 */
namespace AppBundle\Domain\Sequence\Traits;

/**
 * Trait SequenceTrait
 * @package AppBundle\Domain\Sequence\Traits
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
trait SequenceTrait
{
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
     * Returns complement of RNA sequence
     * @param   string          $sSequence
     * @return  string
     * @throws  \Exception
     */
    public function compRNA($sSequence)
    {
        try {
            $sSequence = strtoupper($sSequence);
            $original   = ["(A)","(U)","(G)","(C)"];
            $complement = ["u","a","c","g"];
            $sSequence = preg_replace($original, $complement, $sSequence);
            $sSequence = strtoupper($sSequence);
            return $sSequence;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Will yield the Reverse complement of a DNA sequence. Allows degenerated nucleotides
     * @param   string      $sSequence      is the sequence
     * @return  string
     * @throws \Exception
     */
    public function revCompDNA($sSequence)
    {
        try {
            $sSequence = strrev($sSequence);
            $sSequence = $this->compDNA($sSequence);
            return $sSequence;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Will yield the Reverse complement of a RNA sequence. Allows degenerated nucleotides
     * @param   string      $sSequence      is the sequence
     * @return  string
     * @throws \Exception
     */
    public function revCompRNA($sSequence)
    {
        try {
            $sSequence = strrev($sSequence);
            $sSequence = $this->compRNA($sSequence);
            return $sSequence;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Check if characters outside our 20-letter amino alphabet is included in the sequence.
     * If there are unknown characters, then do not compute molwt and instead return FALSE.
     * @param $sSequence
     * @param $sMolType
     * @return bool
     */
    public function cleanSequence($sSequence, $sMolType)
    {
        if ($sMolType == "DNA") {
            preg_match_all("/[^ACGTMRWSYKVHDBXN]/", $sSequence, $match);
            if (count($match[0]) > 0) {
                return false;
            }
        } elseif ($sMolType == "RNA") {
            preg_match_all("/[^ACGUMRWSYKVHDBXN]/", $sSequence, $match);
            if (count($match[0]) > 0) {
                return false;
            }
        }
    }
}
