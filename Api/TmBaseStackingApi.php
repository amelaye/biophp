<?php
/**
 * Database of elements - Tm Base stackings
 * Inspired by BioPHP's project biophp.org
 * Created 21 December 2019
 * Last modified 21 December 2019
 */
namespace Amelaye\BioPHP\Api;

use Amelaye\BioPHP\Api\DTO\TmBaseStackingDTO;
use Amelaye\BioPHP\Api\Interfaces\TmBaseStackingApiAdapter;

/**
 * Class TmBaseStackingApi
 * @package Amelaye\BioPHP\Api
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class TmBaseStackingApi extends Bioapi implements TmBaseStackingApiAdapter
{
    /**
     * Gets the list of objects from Api
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
     * Basic temperatures of nucleotids combinations - enthropy
     * @param   array   $aTmBaseStackings
     * @return  array
     */
    public static function GetEnthropyValues(array $aTmBaseStackings) : array
    {
        $newData = array();
        foreach($aTmBaseStackings as $key => $elem) {
            $newData[$elem->getId()] = $elem->getTemperatureEnthropy();
        }
        return $newData;
    }

    /**
     * TM Base Stacking
     * Basic temperatures of nucleotids combinations - enthalpy
     * @param   array   $aTmBaseStackings
     * @return  array
     */
    public static function getEnthalpyValues(array $aTmBaseStackings) : array
    {
        $newData = array();
        foreach($aTmBaseStackings as $key => $elem) {
            $newData[$elem->getId()] = $elem->getTemperatureEnthalpy();
        }
        return $newData;
    }
}