<?php
/**
 * Traits for sequences formatting
 * Freely inspired by BioPHP's project biophp.org
 * Created 22 july 2019
 * Last modified 12 September 2026
 */
namespace Amelaye\BioPHP\Domain\Sequence\Traits;

/**
 * Trait SequenceTrait
 * @package Amelaye\BioPHP\Domain\Sequence\Traits
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
        $sSequence = strtoupper($sSequence);
        $original   = ["(A)","(T)","(G)","(C)","(Y)","(R)","(W)","(S)","(K)","(M)","(D)","(V)","(H)","(B)"];
        $complement = ["t","a","c","g","r","y","w","s","m","k","h","b","d","v"];
        $sSequence = preg_replace($original, $complement, $sSequence);
        $sSequence = strtoupper($sSequence);
        return $sSequence;
    }

    /**
     * Will yield the Reverse complement of a DNA sequence. Allows degenerated nucleotides
     * @param   string      $sSequence      is the sequence
     * @return  string
     * @throws \Exception
     */
    public function revCompDNA($sSequence)
    {
        $sSequence = strrev($sSequence);
        $sSequence = $this->compDNA($sSequence);
        return $sSequence;
    }

    /**
     * Checks that a sequence only holds symbols belonging to the alphabet of its molecule type.
     * The comparison is case insensitive, so a record read from a GenBank or EMBL file, where the
     * sequence is written in lower case, is not rejected for that reason alone.
     * @param   string      $sSequence      The sequence
     * @param   string      $sMolType       DNA, RNA or PROTEIN
     * @return  bool                        TRUE when every symbol is known, FALSE when one of them
     * is not, and FALSE as well when the molecule type itself cannot be checked
     */
    public function cleanSequence($sSequence, $sMolType)
    {
        $aAlphabets = [
            "DNA"     => "/[^ACGTMRWSYKVHDBXN]/",
            "RNA"     => "/[^ACGUMRWSYKVHDBXN]/",
            "PROTEIN" => "/[^ACDEFGHIKLMNPQRSTVWYX*]/"
        ];

        $sMolType = strtoupper((string) $sMolType);
        if (!isset($aAlphabets[$sMolType])) {
            return false;
        }

        preg_match_all($aAlphabets[$sMolType], strtoupper((string) $sSequence), $match);

        return count($match[0]) == 0;
    }
}
