<?php
/**
 * Database of elements - Vendors
 * Inspired by BioPHP's project biophp.org
 * Created 21 December 2019
 * Last modified 21 December 2019
 */
namespace AppBundle\Api\Interfaces;

/**
 * Class VendorApi
 * @package AppBundle\Api
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface VendorApiAdapter
{
    /**
     * Gets the list of objects from Api
     * @return array
     */
    public function getVendors() : array;

    /**
     * Gets a list as array
     * @return  array
     * @param   array   $aVendors
     */
    public static function GetVendorsArray(array $aVendors) : array;
}