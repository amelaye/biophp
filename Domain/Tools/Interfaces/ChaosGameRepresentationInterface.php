<?php
/**
 * Chaos Game Representation of a DNA sequence Interface
 * Freely inspired by BioPHP's project biophp.org
 * Created 9 October 2026
 * Last modified 9 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Tools\Interfaces;

use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;

/**
 * Interface ChaosGameRepresentationInterface - the computation behind a CGR and an FCGR image, with no
 * drawing : the points of the chaos game, and the counts of the oligonucleotides on their grid.
 * @package Amelaye\BioPHP\Domain\Tools\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface ChaosGameRepresentationInterface
{
    /**
     * @param   DnaSequence     $oSequence
     * @return  array<int,array{0:float,1:float}>   One [x, y] per A, C, G or T, in [0, 1[
     */
    public function points(DnaSequence $oSequence): array;

    /**
     * @param   DnaSequence     $oSequence
     * @param   int             $iOligoLength       1 to 8
     * @param   bool            $bBothStrands       Count the reverse complement too
     * @return  array<string,int>   Every oligonucleotide of that length, 0 included, to its count
     */
    public function oligoCounts(DnaSequence $oSequence, int $iOligoLength, bool $bBothStrands = false): array;

    /**
     * @param   string  $sOligo     A, C, G and T only, 1 to 8 of them
     * @return  array{0:int,1:int}  [column, row] of the oligonucleotide on the 2^k x 2^k grid
     */
    public function cell(string $sOligo): array;

    /**
     * @param   DnaSequence     $oSequence
     * @param   int             $iOligoLength       1 to 8
     * @param   bool            $bBothStrands
     * @return  int[][]         Row by row, the count of the oligonucleotide in each cell of the grid
     */
    public function frequencyMatrix(DnaSequence $oSequence, int $iOligoLength, bool $bBothStrands = false): array;
}
