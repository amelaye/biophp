<?php
/**
 * Oligo-Nucleotids Functions
 * @author Amélie DUVERNET akka Amelaye
 * Inspired by BioPHP's project biophp.org
 * Created 9 march 2019
 * Last modified 2 november 2019
 * RIP Pasha, gone 27 february 2019 =^._.^= ?
 */
namespace AppBundle\Interfaces;


interface OligosInterface
{
    /**
     * For oligos 2 bases long
     * @param $oligos_1step
     * @return mixed
     */
    public static function FindOligos2BasesLong($oligos_1step);

    /**
     * For oligos 3 bases long
     * @param $oligos_1step
     * @return mixed
     */
    public static function FindOligos3BasesLong($oligos_1step);

    /**
     * For oligos 4 bases long
     * @param $oligos_1step
     * @return mixed
     */
    public static function FindOligos4BasesLong($oligos_1step);

    /**
     * For oligos 5 bases long
     * @param $oligos_1step
     * @return mixed
     */
    public static function FindOligos5BasesLong($oligos_1step);

    /**
     * For oligos 6 bases long
     * @param $oligos_1step
     * @return mixed
     */
    public static function FindOligos6BasesLong($oligos_1step);

    /**
     * For oligos 7 bases long
     * @param $oligos_1step
     * @return mixed
     */
    public static function FindOligos7BasesLong($oligos_1step);

    /**
     * For oligos 8 bases long
     * @param $oligos_1step
     * @return mixed
     */
    public static function FindOligos8BasesLong($oligos_1step);

    /**
     * Compute frequency of oligonucleotides with length $iOligoLen for sequence $sSequence
     * @param       string      $sSequence
     * @param       int         $iOligoLen
     * @return      array
     * @throws      \Exception
     */
    public static function FindOligos($sSequence, $iOligoLen);

    /**
     * COMPUTE Z-SCORES FOR TETRANUCLEOTIDES
     * @param $oligos2
     * @param $oligos3
     * @param $oligos4
     * @return array
     */
    public static function FindZScore($oligos2, $oligos3, $oligos4);
}