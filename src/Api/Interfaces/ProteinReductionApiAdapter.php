<?php
/**
 * Database of elements - Protein reductions
 * Inspired by BioPHP's project biophp.org
 * Created 21 December 2019
 * Last modified 21 December 2019
 */
namespace AppBundle\Api\Interfaces;

/**
 * Class ProteinReductionApi
 * @package AppBundle\Api
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface ProteinReductionApiAdapter
{
    /**
     * Gets the list of objects from Api
     * @return array
     */
    public function getReductions() : array;

    /**
     * Gets a list as array
     * @param   array   $aReductions
     * @return  array
     */
    public static function GetReductionsArray(array $aReductions) : array;

    /**
     * @param   array   $aReductions
     * @param   string  $type
     * @return  array
     */
    public static function GetAlphabetInfos(array $aReductions, string $type) : array;
}