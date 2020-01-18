<?php
/**
 * Database of elements - Type II endonucleases
 * Inspired by BioPHP's project biophp.org
 * Created 21 December 2019
 * Last modified 21 December 2019
 */
namespace AppBundle\Api\Interfaces;

/**
 * Class TypeIIEndonucleaseApi
 * @package AppBundle\Api
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface TypeIIEndonucleaseApiAdapter
{
    /**
     * Gets the list of objects from Api
     * @return array
     */
    public function getTypeIIEndonucleases() : array;

    /**
     * Gets a list as array
     * @param   array   $aType2Endonucleases
     * @return  array
     */
    public static function GetTypeIIEndonucleasesArray(array $aType2Endonucleases) : array;

    /**
     * Gets a list of cleavages pos-upper
     * @param   array   $aEndonucleases
     * @return  array
     */
    public static function GetTypeIIbEndonucleasesCleavagePosUpper(array $aEndonucleases) : array;
}