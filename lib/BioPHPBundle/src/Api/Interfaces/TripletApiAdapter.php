<?php
/**
 * Database of elements - Triplets
 * Inspired by BioPHP's project biophp.org
 * Created 21 December 2019
 * Last modified 21 December 2019
 */
namespace Amelaye\BioPHP\Api\Interfaces;

/**
 * Class TripletApi
 * @package Amelaye\BioPHP\Api
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface TripletApiAdapter
{
    /**
     * Gets the list of objects from Api
     * @return array
     */
    public function getTriplets() : array;

    /**
     * Gets a list as array
     * @param   array   $aTripletList
     * @return  array
     */
    public static function GetTripletsArray(array $aTripletList) : array;
}