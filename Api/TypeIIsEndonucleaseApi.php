<?php
/**
 * Database of elements - Type IIs endonucleases
 * Inspired by BioPHP's project biophp.org
 * Created 21 December 2019
 * Last modified 15 September 2020
 */
namespace Amelaye\BioPHP\Api;

use Amelaye\BioPHP\Api\DTO\TypeIIsEndonucleaseDTO;
use Amelaye\BioPHP\Api\Interfaces\TypeIIsEndonucleaseApiAdapter;

/**
 * Class TypeIIsEndonucleaseApi
 * @package Amelaye\BioPHP\Api
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class TypeIIsEndonucleaseApi extends Bioapi implements TypeIIsEndonucleaseApiAdapter
{
    /**
     * Gets the list of objects from Api
     * @return array
     */
    public function getTypeIIsEndonucleases() : array
    {
        $uri = '/type_i_is_endonucleases';
        $response = $this->bioapiClient->get($uri);
        $data = $this->serializer->deserialize($response->getBody()->getContents(), 'array', 'json');

        $aType2sEndonucleases = [];
        foreach($data["hydra:member"] as $key => $elem) {
            $oType2sEndonuclease = new TypeIIsEndonucleaseDTO();
            $oType2sEndonuclease->setId($elem["id"]);
            $oType2sEndonuclease->setSamePattern($elem["samePattern"]);
            $oType2sEndonuclease->setRecognitionPattern($elem["recognitionPattern"]);
            $oType2sEndonuclease->setComputingPattern($elem["computingPattern"]);
            $oType2sEndonuclease->setLengthRecognitionPattern($elem["lengthRecognitionPattern"]);
            $oType2sEndonuclease->setCleavagePosUpper($elem["cleavagePosUpper"]);
            $oType2sEndonuclease->setCleavagePosLower($elem["cleavagePosLower"]);
            $oType2sEndonuclease->setNbNonNBases($elem["nbNonNBases"]);
            $aType2sEndonucleases[] = $oType2sEndonuclease;
        }

        return $aType2sEndonucleases;
    }

    /**
     * Gets a list as array
     * @param   array   $aType2sEndonucleases
     * @return  array
     */
    public static function GetTypeIIsEndonucleasesArray(array $aType2sEndonucleases) : array
    {
        $newData = array();
        foreach($aType2sEndonucleases as $key => $elem) {
            $newData[$elem->getId()] = [
                $elem->getSamePattern()[0],
                $elem->getRecognitionPattern(),
                $elem->getComputingPattern(),
                $elem->getLengthRecognitionPattern(),
                $elem->getCleavagePosUpper(),
                $elem->getCleavagePosLower(),
                $elem->getNbNonNBases(),
            ];
        }
        return $newData;
    }
}