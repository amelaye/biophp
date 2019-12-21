<?php


namespace AppBundle\Api;


use AppBundle\Api\DTO\TripletDTO;

class TripletApi extends Bioapi
{
    /**
     * @return array
     */
    public function getTriplets()
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
     * @return array
     */
    public static function GetTripletsList($aTripletList)
    {
        $newData = array();
        foreach($aTripletList as $key => $elem) {
            $newData[] = $elem->getTriplet()." ";
        }
        return $newData;
    }
}