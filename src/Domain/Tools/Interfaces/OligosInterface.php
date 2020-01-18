<?php
/**
 * Oligo-Nucleotids Functions
 * Inspired by BioPHP's project biophp.org
 * Created 9 march 2019
 * RIP Pasha, gone 27 february 2019 =^._.^=
 * Last modified 18 january 2020
 */
namespace App\Domain\Tools\Interfaces;

/**
 * Interface OligosInterface
 * @package App\Domain\Tools\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface OligosInterface
{
    /**
     * For oligos 2 bases long
     * @param $oligos_1step
     * @return mixed
     */
    public function findOligos2BasesLong($oligos_1step);

    /**
     * For oligos 3 bases long
     * @param $oligos_1step
     * @return mixed
     */
    public function findOligos3BasesLong($oligos_1step);

    /**
     * For oligos 4 bases long
     * @param $oligos_1step
     * @return mixed
     */
    public function findOligos4BasesLong($oligos_1step);

    /**
     * For oligos 5 bases long
     * @param $oligos_1step
     * @return mixed
     */
    public function findOligos5BasesLong($oligos_1step);

    /**
     * For oligos 6 bases long
     * @param $oligos_1step
     * @return mixed
     */
    public function findOligos6BasesLong($oligos_1step);

    /**
     * For oligos 7 bases long
     * @param $oligos_1step
     * @return mixed
     */
    public function findOligos7BasesLong($oligos_1step);

    /**
     * For oligos 8 bases long
     * @param $oligos_1step
     * @return mixed
     */
    public function findOligos8BasesLong($oligos_1step);

    /**
     * Compute frequency of oligonucleotides with length $iOligoLen for sequence $sSequence
     * @param       string      $sSequence
     * @param       int         $iOligoLen
     * @return      array
     * @throws      \Exception
     */
    public function findOligos($sSequence, $iOligoLen);

    /**
     * COMPUTE Z-SCORES FOR TETRANUCLEOTIDES
     * @param $oligos2
     * @param $oligos3
     * @param $oligos4
     * @return array
     */
    public function findZScore($oligos2, $oligos3, $oligos4);
}