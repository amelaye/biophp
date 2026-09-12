<?php
/**
 * Nucleotids Functions
 * Inspired by BioPHP's project biophp.org
 * Created 19 march  2019
 * RIP Pasha, gone 27 february 2019 =^._.^= ∫
 * Last modified 12 September 2026
 */
namespace Amelaye\BioPHP\Domain\Tools\Service;

/**
 * Class NucleotidsManager
 * @package Amelaye\BioPHP\Domain\Tools\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class GeneticsFunctions
{
    /**
     * Will count number of A, C, G and T bases in the sequence
     * @param   string  $sSequence  is the sequence
     * @return  int
     * @throws \Exception
     */
    public static function CountACGT($sSequence)
    {
        $cg = substr_count($sSequence,"A")
            + substr_count($sSequence,"T")
            + substr_count($sSequence,"G")
            + substr_count($sSequence,"C");
        return $cg;
    }

    /**
     * Will count number of degenerate nucleotides (Y, R, W, S, K, MD, V, H and B) in the sequence
     * @param   string $c
     * @return  int
     * @throws \Exception
     */
    public function CountYRWSKMDVHB($c)
    {
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
    }

    /**
     * @param $c
     * @return int
     * @throws \Exception
     */
    public static function CountCG($c)
    {
        $cg = substr_count($c,"G")
            + substr_count($c,"C");
        return $cg;
    }

    /**
     * Place sequence and reverse complement of sequence in one line
     * @param $sSequence
     * @param $dnaComplements
     * @return string
     */
    public static function CreateInversion($sSequence, $dnaComplements): string
    {
        $seqRevert = strrev($sSequence);
        $sNewSequence = "";
        foreach ($dnaComplements as $nucleotide => $complement) {
            $seqRevert = str_replace($nucleotide, strtolower($complement), $seqRevert);
        }
        $sNewSequence .= strtoupper($seqRevert);
        return $sNewSequence;
    }

    /**
     * Removes non-coding characters
     * @param       string      $sSequence
     * @return      string
     * @throws      \Exception
     */
    public static function RemoveNonCodingProt($sSequence)
    {
        $sSequence = strtoupper($sSequence);
        // remove non-coding characters([^ARNDCEQGHILKMFPSTWYVX\*])
        $sSequence = preg_replace("([^ARNDCEQGHILKMFPSTWYVX\*])", "", $sSequence);
        return $sSequence;
    }
}