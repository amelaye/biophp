<?php


namespace AppBundle\Api;

use AppBundle\Api\DTO\TmBaseStackingDTO;

class TmBaseStackingApi extends Bioapi
{
    /**
     * @return array
     */
    public function getTmBaseStackings() : array
    {
        $uri = '/tm_base_stackings';
        $response = $this->bioapiClient->get($uri);
        $data = $this->serializer->deserialize($response->getBody()->getContents(), 'array', 'json');

        $aTmBaseStackings = [];
        foreach($data["hydra:member"] as $key => $elem) {
            $oTmBaseStacking = new TmBaseStackingDTO();
            $oTmBaseStacking->setId($elem["id"]);
            $oTmBaseStacking->setTemperatureEnthalpy($elem["temperature_enthalpy"]);
            $oTmBaseStacking->setTemperatureEnthropy($elem["temperature_enthropy"]);
            $aTmBaseStackings[] = $oTmBaseStacking;
        }
        return $aTmBaseStackings;
    }

    /**
     * TM Base Stacking
     * Basic temperatures of nucleotids combinations
     * @return array
     */
    public static function GetEnthropyValues($aTmBaseStackings) : array
    {
        $newData = array();
        foreach($aTmBaseStackings as $key => $elem) {
            $newData[$elem->getId()] = $elem->getTemperatureEnthropy();
        }
        return $newData;
    }

    /**
     * @return array
     */
    public static function getEnthalpyValues($aTmBaseStackings) : array
    {
        $newData = array();
        foreach($aTmBaseStackings as $key => $elem) {
            $newData[$elem->getId()] = $elem->getTemperatureEnthalpy();
        }
        return $newData;
    }
}