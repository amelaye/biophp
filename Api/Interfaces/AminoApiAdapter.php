<?php
/**
 * Database of elements - Amino acids
 * Inspired by BioPHP's project biophp.org
 * Created 20 December 2019
 * Last modified 15 September 2020
 */
namespace Amelaye\BioPHP\Api\Interfaces;

/**
 * Database of aminos - Amino acids
 * @package Amelaye\BioPHP\Api
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface AminoApiAdapter
{
    /**
     * Retrives Aminos informations : gets a full array of objects
     * @return array
     */
    public function getAminos() : array;

    /**
     * Creates a simple array of aminos
     * @example $aFormattedAminos["name"] = [$aFormattedAminos["name1Letter"], $aFormattedAminos["name3Letters"]]
     * @param   array   $aAminos    Array of objects
     * @return  array
     */
    public static function GetAminosOnlyLetters(array $aAminos) : array;

    /**
     * Creates the simpliest array of aminos
     * @example $aFormattedAminos["name1Letter"] = "name3Letters"
     * @param   array $aAminos
     * @return  array
     */
    public static function GetAminosOneToThreeLetters(array $aAminos) : array;

    /**
     * Creates a simple array juste with aminos weights
     * @param   array     $aAminos    Array of objects
     * @return  array
     */
    public static function GetAminoweights(array $aAminos) : array;

    /**
     * Creates a simple array juste with aminos residues molweights
     * @param   array     $aAminos    Array of objects
     * @return  array
     */
    public static function GetAminoResidueWeights(array $aAminos) : array;
}