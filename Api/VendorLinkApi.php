<?php
/**
 * Database of elements - Vendors
 * Inspired by BioPHP's project biophp.org
 * Created 21 December 2019
 * Last modified 21 December 2019
 */
namespace Amelaye\BioPHP\Api;

use Amelaye\BioPHP\Api\DTO\VendorLinkDTO;
use Amelaye\BioPHP\Api\Interfaces\VendorLinkApiAdapter;

/**
 * Class VendorLinkApi
 * @package Amelaye\BioPHP\Api
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class VendorLinkApi extends Bioapi implements VendorLinkApiAdapter
{
    /**
     * Gets the list of objects from Api
     * @return array
     */
    public function getVendorLinks() : array
    {
        $uri = '/vendor_links';
        $response = $this->bioapiClient->get($uri);

        $data = $this->serializer->deserialize($response->getBody()->getContents(), 'array', 'json');

        $aVendorLinks = array();
        foreach($data["hydra:member"] as $key => $elem) {
            $oVendorLink = new VendorLinkDTO();
            $oVendorLink->setId($elem["id"]);
            $oVendorLink->setName($elem["name"]);
            $oVendorLink->setLink($elem["link"]);
            $aVendorLinks[] = $oVendorLink;
        }
        return($aVendorLinks);
    }

    /**
     * Gets a list as array
     * @return  array
     * @param   array   $aVendorLinks
     */
    public static function GetVendorLinksArray(array $aVendorLinks) : array
    {
        $newData = array();
        foreach($aVendorLinks as $key => $elem) {
            $newData[$elem->getId()] = ["name" => $elem->getName(), "url" => $elem->getUrl()];
        }
        return($newData);
    }
}