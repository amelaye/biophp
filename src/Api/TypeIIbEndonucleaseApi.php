<?php
/**
 * Database of elements - Triplets species
 * Inspired by BioPHP's project biophp.org
 * Created 21 December 2019
 * Last modified 21 December 2019
 */
namespace App\Api;

use App\Api\DTO\TypeIIbEndonucleaseDTO;
use App\Api\Interfaces\TypeIIbEndonucleaseApiAdapter;

/**
 * Class TypeIIbEndonucleaseApi
 * @package App\Api
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class TypeIIbEndonucleaseApi extends Bioapi implements TypeIIbEndonucleaseApiAdapter
{
    /**
     * Gets the list of objects from Api
     * @return array
     */
    public function getTypeIIbEndonucleases() : array
    {
        $uri = '/type_i_ib_endonucleases';
        $response = $this->bioapiClient->get($uri);
        $data = $this->serializer->deserialize($response->getBody()->getContents(), 'array', 'json');

        $aType2bEndonucleases = [];
        foreach($data["hydra:member"] as $key => $elem) {
            $oType2bEndonuclease = new TypeIIbEndonucleaseDTO();
            $oType2bEndonuclease->setId($elem["id"]);
            $oType2bEndonuclease->setSamePattern($elem["samePattern"]);
            $oType2bEndonuclease->setRecognitionPattern($elem["recognitionPattern"]);
            $oType2bEndonuclease->setComputingPattern($elem["computingPattern"]);
            $oType2bEndonuclease->setLengthRecognitionPattern($elem["lengthRecognitionPattern"]);
            $oType2bEndonuclease->setCleavagePosUpper($elem["cleavagePosUpper"]);
            $oType2bEndonuclease->setCleavagePosLower($elem["cleavagePosLower"]);
            $oType2bEndonuclease->setNbNonNBases($elem["nbNonNBases"]);
            $aType2bEndonucleases[] = $oType2bEndonuclease;
        }
        return $aType2bEndonucleases;
    }

    /**
     * Gets a list as array
     * @param   array   $aType2bEndonucleases
     * @return  array
     */
    public static function GetTypeIIbEndonucleasesArray(array $aType2bEndonucleases) : array
    {
        $newData = array();
        foreach($aType2bEndonucleases as $key => $elem) {
            $newData[$elem->getId()] = [
                $elem->getSamePattern()[0],
                $elem->getRecognitionPattern(),
                $elem->setComputingPattern(),
                $elem->setLengthRecognitionPattern(),
                $elem->setCleavagePosUpper(),
                $elem->setCleavagePosLower(),
                $elem->setNbNonNBases(),
            ];
        }
        return $newData;
    }
}