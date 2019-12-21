<?php
/**
 * Database of elements - Amino acids
 * Inspired by BioPHP's project biophp.org
 * Created 20 December 2019
 * Last modified 20 December 2019
 */
namespace AppBundle\Api\Interfaces;

/**
 * Database of aminos - Amino acids
 * @package AppBundle\Api
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
     * Creates a simple array juste with aminos weights
     * @param   array     $aAminos    Array of objects
     * @return  array
     */
    public static function GetAminoweights(array $aAminos) : array;
}