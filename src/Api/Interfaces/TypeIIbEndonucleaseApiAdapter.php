<?php
/**
 * Database of elements - Triplets species
 * Inspired by BioPHP's project biophp.org
 * Created 21 December 2019
 * Last modified 21 December 2019
 */
namespace AppBundle\Api\Interfaces;

/**
 * Class TypeIIbEndonucleaseApi
 * @package AppBundle\Api
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface TypeIIbEndonucleaseApiAdapter
{
    /**
     * Gets the list of objects from Api
     * @return array
     */
    public function getTypeIIbEndonucleases() : array;

    /**
     * Gets a list as array
     * @param   array   $aType2bEndonucleases
     * @return  array
     */
    public static function GetTypeIIbEndonucleasesArray(array $aType2bEndonucleases) : array;
}