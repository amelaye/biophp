<?php
/**
 * Database of elements - Vendors
 * Inspired by BioPHP's project biophp.org
 * Created 21 December 2019
 * Last modified 21 December 2019
 */
namespace AppBundle\Api\Interfaces;

/**
 * Class VendorLinkApi
 * @package AppBundle\Api
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface VendorLinkApiAdapter
{
    /**
     * Gets the list of objects from Api
     * @return array
     */
    public function getVendorLinks() : array;

    /**
     * Gets a list as array
     * @return  array
     * @param   array   $aVendorLinks
     */
    public static function GetVendorLinksArray(array $aVendorLinks) : array;
}