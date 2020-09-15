<?php
/**
 * Database of elements - Triplets species
 * Inspired by BioPHP's project biophp.org
 * Created 21 December 2019
 * Last modified 15 September 2020
 */
namespace Amelaye\BioPHP\Api\Interfaces;

/**
 * Class TripletSpecieApi
 * @package Amelaye\BioPHP\Api
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface TripletSpecieApiAdapter
{
    /**
     * Gets the list of objects from Api
     * @return array
     */
    public function getTriplets() : array;

    /**
     * Gets a list of groups as array
     * @param   array   $aTriplets
     * @return  array
     */
    public static function GetTripletsGroups(array $aTriplets) : array;

    /**
     * Gets a list as array
     * @param   array   $aTriplets
     * @return  array
     */
    public static function GetTripletsArray(array $aTriplets) : array;

    /**
     * Gets a list of triplets combinations
     * @param   array   $aTriplets
     * @return  array
     */
    public static function GetTripletsCombinations(array $aTriplets) : array;

    /**
     * Gets a list of species as array
     * @param   array   $aTriplets
     * @return  array
     */
    public static function GetSpeciesNames(array $aTriplets) : array;
}