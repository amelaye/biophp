<?php
/**
 * Nucleotids Functions
 * Inspired by BioPHP's project biophp.org
 * Created 19 march  2019
 * RIP Pasha, gone 27 february 2019 =^._.^= ∫
 * Last modified 9 October 2026
 */
declare(strict_types=1);

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
    public static function CountACGT(string $sSequence) : int {
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
    public function CountYRWSKMDVHB(string $c) : int {
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
    public static function CountCG($c) : int {
        $cg = substr_count($c,"G")
            + substr_count($c,"C");
        return $cg;
    }

    /**
     * Returns the reverse complement of a sequence, in upper case
     * @param $sSequence
     * @param $dnaComplements
     * @return string
     */
    public static function CreateInversion($sSequence, $dnaComplements): string
    {
        // The IUPAC ambiguity codes pair up as well (R/Y, K/M, B/V, D/H), the table given taking
        // precedence. strtr() replaces in one pass, so a complement is never complemented again.
        $aComplements = ["R" => "Y", "Y" => "R", "K" => "M", "M" => "K", "B" => "V", "V" => "B",
            "D" => "H", "H" => "D", "S" => "S", "W" => "W", "N" => "N"];
        foreach ($dnaComplements as $nucleotide => $complement) {
            $aComplements[strtoupper((string) $nucleotide)] = strtoupper((string) $complement);
        }

        return strtr(strrev(strtoupper($sSequence)), $aComplements);
    }

    /**
     * Removes non-coding characters
     * @param       string      $sSequence
     * @return      string
     * @throws      \Exception
     */
    public static function RemoveNonCodingProt(string $sSequence) : string {
        $sSequence = strtoupper($sSequence);
        // remove non-coding characters : what is neither one of the 20 amino acids, an ambiguity
        // (B, Z, J, X), selenocysteine (U), pyrrolysine (O) nor a stop (*)
        $sSequence = preg_replace("([^ARNDCEQGHILKMFPSTWYVXBZJUO\*])", "", $sSequence);
        return $sSequence;
    }
}