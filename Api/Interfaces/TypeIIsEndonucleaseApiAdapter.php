<?php
/**
 * Database of elements - Type IIs endonucleases
 * Inspired by BioPHP's project biophp.org
 * Created 21 December 2019
 * Last modified 21 December 2019
 */
namespace Amelaye\BioPHP\Api\Interfaces;

/**
 * Class TypeIIsEndonucleaseApi
 * @package Amelaye\BioPHP\Api\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface TypeIIsEndonucleaseApiAdapter
{
    /**
     * Gets the list of objects from Api
     * @return array
     */
    public function getTypeIIsEndonucleases() : array;

    /**
     * Gets a list as array
     * @param   array   $aType2sEndonucleases
     * @return  array
     */
    public static function GetTypeIIsEndonucleasesArray(array $aType2sEndonucleases) : array;
}