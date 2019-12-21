<?php


namespace AppBundle\Api;


use AppBundle\Api\DTO\TripletSpecieDTO;

class TripletSpecieApi extends Bioapi
{
    /**
     * @return array
     */
    public function getTriplets()
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
     * @return array
     */
    public static function GetTripletsGroups($aTriplets)
    {
        $newData = array();
        foreach($aTriplets as $key => $elem) {
            $newData[str_replace(" ", "_", $elem->getNature())] = $elem->getTripletsGroups();
        }
        return $newData;
    }

    /**
     * @return array
     */
    public static function GetTripletsArray($aTriplets)
    {
        $newData = array();
        foreach($aTriplets as $key => $elem) {
            $newData[str_replace(" ", "_", $elem->getNature())] = $elem->getTriplets();
        }
        return $newData;
    }

    /**
     * @return array
     */
    public static function GetTripletsCombinations($aTriplets)
    {
        $newData = array();
        foreach($aTriplets as $key => $elem) {
            $newData = $newData + $elem->getTriplets();
        }
        return $newData;
    }

    /**
     * @return array
     */
    public function GetSpeciesNames($aTriplets)
    {
        $newData = array();
        foreach($aTriplets as $key => $elem) {
            $newData[ucwords($elem->getNature())] = str_replace(" ", "_", $elem->getNature());
        }
        return $newData;
    }
}