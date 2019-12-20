<?php


namespace AppBundle\Api;

use AppBundle\Api\DTO\TmBaseStackingDTO;
use GuzzleHttp\Client;
use JMS\Serializer\Serializer;

class TmBaseStackingApi
{
    /**
     * @var Client
     */
    private $bioapiClient;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var string|null
     */
    private $apiKey;

    /**
     * Bioapi constructor.
     * @param Client        $bioapiClient
     * @param Serializer    $serializer
     * @param string        $apiKey
     */
    public function __construct(Client $bioapiClient, Serializer $serializer, $apiKey = null)
    {
        $this->bioapiClient = $bioapiClient;
        $this->serializer   = $serializer;
        $this->apiKey       = $apiKey;
    }

    public function getTmBaseStackings()
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
    public static function GetEnthropyValues($aTmBaseStackings)
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
    public static function getEnthalpyValues($aTmBaseStackings)
    {
        $newData = array();
        foreach($aTmBaseStackings as $key => $elem) {
            $newData[$elem->getId()] = $elem->getTemperatureEnthalpy();
        }
        return $newData;
    }
}