<?php
/**
 * Database of elements - Triplets species
 * Inspired by BioPHP's project biophp.org
 * Created 21 December 2019
 * Last modified 21 December 2019
 */
namespace App\Api;

use App\Api\DTO\TripletSpecieDTO;
use App\Api\Interfaces\TripletSpecieApiAdapter;

/**
 * Class TripletSpecieApi
 * @package App\Api
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class TripletSpecieApi extends Bioapi implements TripletSpecieApiAdapter
{
    /**
     * Gets the list of objects from Api
     * @return array
     */
    public function getTriplets() : array
    {
        $uri = '/triplet_species';
        $response = $this->bioapiClient->get($uri);
        $data = $this->serializer->deserialize($response->getBody()->getContents(), 'array', 'json');

        $aTriplets = array();
        foreach($data["hydra:member"] as $key => $elem) {
            $triplet = new TripletSpecieDTO();
            $triplet->setId($elem['id']);
            $triplet->setNature($elem['nature']);
            $triplet->setTripletsGroups($elem['tripletsGroups']);
            $triplet->setTriplets($elem['triplets']);
            $aTriplets[] = $triplet;
        }

        return $aTriplets;
    }

    /**
     * Gets a list of groups as array
     * @param   array   $aTriplets
     * @return  array
     */
    public static function GetTripletsGroups(array $aTriplets) : array
    {
        $newData = array();
        foreach($aTriplets as $key => $elem) {
            $newData[str_replace(" ", "_", $elem->getNature())] = $elem->getTripletsGroups();
        }
        return $newData;
    }

    /**
     * Gets a list as array
     * @param   array   $aTriplets
     * @return  array
     */
    public static function GetTripletsArray(array $aTriplets) : array
    {
        $newData = array();
        foreach($aTriplets as $key => $elem) {
            $newData[str_replace(" ", "_", $elem->getNature())] = $elem->getTriplets();
        }
        return $newData;
    }

    /**
     * Gets a list of triplets combinations
     * @param   array   $aTriplets
     * @return  array
     */
    public static function GetTripletsCombinations(array $aTriplets) : array
    {
        $newData = array();
        foreach($aTriplets as $key => $elem) {
            $newData = $newData + $elem->getTriplets();
        }
        return $newData;
    }

    /**
     * Gets a list of species as array
     * @param   array   $aTriplets
     * @return  array
     */
    public function GetSpeciesNames(array $aTriplets) : array
    {
        $newData = array();
        foreach($aTriplets as $key => $elem) {
            $newData[ucwords($elem->getNature())] = str_replace(" ", "_", $elem->getNature());
        }
        return $newData;
    }
}