<?php
/**
 * Database of elements - Tm Base stackings
 * Inspired by BioPHP's project biophp.org
 * Created 21 December 2019
 * Last modified 21 December 2019
 */
namespace Amelaye\BioPHP\Api\Interfaces;

/**
 * Class TmBaseStackingApi
 * @package Amelaye\BioPHP\Api
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface TmBaseStackingApiAdapter
{
    /**
     * Gets the list of objects from Api
     * @return array
     */
    public function getTmBaseStackings() : array;

    /**
     * TM Base Stacking
     * Basic temperatures of nucleotids combinations - enthropy
     * @param   array   $aTmBaseStackings
     * @return  array
     */
    public static function GetEnthropyValues(array $aTmBaseStackings) : array;

    /**
     * TM Base Stacking
     * Basic temperatures of nucleotids combinations - enthalpy
     * @param   array   $aTmBaseStackings
     * @return  array
     */
    public static function getEnthalpyValues(array $aTmBaseStackings) : array;
}