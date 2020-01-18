<?php
/**
 * Database of elements - Vendors
 * Inspired by BioPHP's project biophp.org
 * Created 21 December 2019
 * Last modified 21 December 2019
 */
namespace App\Api;

use App\Api\DTO\VendorDTO;
use App\Api\Interfaces\VendorApiAdapter;

/**
 * Class VendorApi
 * @package App\Api
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class VendorApi extends Bioapi implements VendorApiAdapter
{
    /**
     * Gets the list of objects from Api
     * @return array
     */
    public function getVendors() : array
    {
        $uri = '/vendors';
        $response = $this->bioapiClient->get($uri);
        $data = $this->serializer->deserialize($response->getBody()->getContents(), 'array', 'json');

        $aVendors = [];
        foreach($data["hydra:member"] as $key => $elem) {
            $oVendor = new VendorDTO();
            $oVendor->setId($elem["id"]);
            $oVendor->setVendor($elem["vendor"]);
            $aVendors[] = $oVendor;
        }
        return($aVendors);
    }

    /**
     * Gets a list as array
     * @return  array
     * @param   array   $aVendors
     */
    public static function GetVendorsArray(array $aVendors) : array
    {
        $newData = array();
        foreach($aVendors as $key => $elem) {
            $newData[$elem->getId()] = $elem->getVendor();
        }
        return($newData);
    }
}