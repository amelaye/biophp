<?php
/**
 * Database of elements - Type II endonucleases
 * Inspired by BioPHP's project biophp.org
 * Created 21 December 2019
 * Last modified 21 December 2019
 */
namespace AppBundle\Api;

use AppBundle\Api\DTO\TypeIIEndonucleaseDTO;

/**
 * Class TypeIIEndonucleaseApi
 * @package AppBundle\Api
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class TypeIIEndonucleaseApi extends Bioapi
{
    /**
     * Gets the list of objects from Api
     * @return array
     */
    public function getTypeIIEndonucleases() : array
    {
        $uri = '/type_i_i_endonucleases';
        $response = $this->bioapiClient->get($uri);

        $data = $this->serializer->deserialize($response->getBody()->getContents(), 'array', 'json');

        $aType2Endonucleases = array();
        foreach($data["hydra:member"] as $key => $elem) {
            $oType2Endonuclease = new TypeIIEndonucleaseDTO();
            $oType2Endonuclease->setId($elem["id"]);
            $oType2Endonuclease->setSamePattern($elem["samePattern"]);
            $oType2Endonuclease->setRecognitionPattern($elem["recognitionPattern"]);
            $oType2Endonuclease->setComputingPattern($elem["computingPattern"]);
            $oType2Endonuclease->setLengthRecognitionPattern($elem["lengthRecognitionPattern"]);
            $oType2Endonuclease->setCleavagePosUpper($elem["cleavagePosUpper"]);
            $oType2Endonuclease->setCleavagePosLower($elem["cleavagePosLower"]);
            $oType2Endonuclease->setNbNonNBases($elem["nbNonNBases"]);
            $aType2Endonucleases[] = $oType2Endonuclease;
        }

        return $aType2Endonucleases;
    }

    /**
     * Gets a list as array
     * @param   array   $aType2Endonucleases
     * @return  array
     */
    public static function GetTypeIIEndonucleasesArray(array $aType2Endonucleases) : array
    {
        $newData = array();
        foreach($aType2Endonucleases as $key => $elem) {
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

    /**
     * Gets a list of cleavages pos-upper
     * @param   array   $aEndonucleases
     * @return  array
     */
    public static function GetTypeIIbEndonucleasesCleavagePosUpper(array $aEndonucleases) : array
    {
        $newData = array();
        foreach($aEndonucleases as $key => $elem) {
            $sPattern = $MaVariable = str_replace("'", "", $elem->getRecognitionPattern());
            $sPattern = $MaVariable = str_replace("_", "", $sPattern);
            $newData[$elem->getId()] = [
                $sPattern,
                $elem->getCleavagePosUpper(),
            ];
        }
        return $newData;
    }
}