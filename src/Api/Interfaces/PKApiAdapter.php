<?php
/**
 * Database of elements - PK
 * Inspired by BioPHP's project biophp.org
 * Created 20 December 2019
 * Last modified 20 December 2019
 */
namespace App\Api\Interfaces;

/**
 * Database of elements - Nucleotids
 * @package App\Api
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface PKApiAdapter
{
    /**
     * @param $id
     * @return array
     */
    public function getPkValueById($id) : array;
}