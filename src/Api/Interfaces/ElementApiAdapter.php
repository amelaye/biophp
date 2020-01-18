<?php
/**
 * Database of elements - Amino acids
 * Inspired by BioPHP's project biophp.org
 * Created 21 December 2019
 * Last modified 21 December 2019
 */
namespace App\Api\Interfaces;

use App\Api\DTO\ElementDTO;

/**
 * Database of elements
 * @package App\Api
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface ElementApiAdapter
{
    /**
     * Gets the list of objects from Api
     * @return      array
     */
    public function getElements() : array;

    /**
     * Gets a single element from API
     * @param       int         $iElement
     * @return      ElementDTO
     */
    public function getElement(int $iElement) : ElementDTO;

    /**
     * Gets the list of elements weights
     * @param   array   $aElements
     * @return  array
     */
    public static function GetElementsArray(array $aElements) : array;
}