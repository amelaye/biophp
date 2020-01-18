<?php
/**
 * Database of elements - Pam250 Matrix
 * Inspired by BioPHP's project biophp.org
 * Created 21 December 2019
 * Last modified 21 December 2019
 */
namespace App\Api\Interfaces;

/**
 * Class Pam250MatrixDigitApi
 * @package App\Api
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface Pam250MatrixDigitApiAdapter
{
    /**
     * Gets the list of objects from Api
     * @return array
     */
    public function getPam250Matrix() : array;

    /**
     * Gets a list as array
     * @param   array   $aPam250
     * @return  array
     */
    public static function GetPam250MatrixArray(array $aPam250) : array;
}