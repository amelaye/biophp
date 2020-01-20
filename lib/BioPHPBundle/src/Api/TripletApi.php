<?php
/**
 * Database of elements - Triplets
 * Inspired by BioPHP's project biophp.org
 * Created 21 December 2019
 * Last modified 21 December 2019
 */
namespace Amelaye\BioPHP\Api;

use Amelaye\BioPHP\Api\DTO\TripletDTO;
use Amelaye\BioPHP\Api\Interfaces\TripletApiAdapter;

/**
 * Class TripletApi
 * @package Amelaye\BioPHP\Api
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class TripletApi extends Bioapi implements TripletApiAdapter
{
    /**
     * Gets the list of objects from Api
     * @return array
     */
    public function getTriplets() : array
    {
        $uri = '/triplets';
        $response = $this->bioapiClient->get($uri);

        $data = $this->serializer->deserialize($response->getBody()->getContents(), 'array', 'json');

        $aTripletList = array();
        foreach($data["hydra:member"] as $key => $elem) {
            $oTriplet = new TripletDTO();
            $oTriplet->setId($elem["id"]);
            $oTriplet->setTriplet($elem["triplet"]);
            $aTripletList[] = $oTriplet;
        }

        return $aTripletList;
    }

    /**
     * Gets a list as array
     * @param   array   $aTripletList
     * @return  array
     */
    public static function GetTripletsArray(array $aTripletList) : array
    {
        $newData = array();
        foreach($aTripletList as $key => $elem) {
            $newData[] = $elem->getTriplet()." ";
        }
        return $newData;
    }
}