<?php
/**
 * Database of elements - Nucleotids
 * Inspired by BioPHP's project biophp.org
 * Created 21 December 2019
 * Last modified 21 December 2019
 */
namespace Amelaye\BioPHP\Api\Interfaces;

/**
 * Database of elements - Nucleotids
 * @package Amelaye\BioPHP\Api
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface NucleotidApiAdapter
{
    /**
     * List of DNA/RNA nucleotids
     * @return      array
     */
    public function getNucleotids() : array;

    /**
     * List of DNA nucleotids
     * @param   array   $aNucleotids
     * @return  array
     */
    public static function GetNucleotidsDNA(array $aNucleotids) : array;

    /**
     * List of RNA nucleotids
     * @param   array   $aNucleotids
     * @return  array
     */
    public static function GetNucleotidsRNA(array $aNucleotids) : array;

    /**
     * List of DNA nucleotids complements
     * @param   array   $aNucleotids
     * @return  array
     */
    public static function GetDNAComplement(array $aNucleotids) : array;

    /**
     * List of RNA nucleotids complements
     * @param   array   $aNucleotids
     * @return  array
     */
    public static function GetRNAComplement(array $aNucleotids) : array;

    /**
     * @param   array   $aNucleotids
     * @return  array
     */
    public static function GetDNAWeight(array $aNucleotids) : array;

    /**
     * @param   array   $aNucleotids
     * @return  array
     */
    public static function GetRNAWeight(array $aNucleotids) : array;
}