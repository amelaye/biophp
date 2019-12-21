<?php


namespace AppBundle\Api;


use AppBundle\Api\DTO\TypeIIbEndonucleaseDTO;

class TypeIIbEndonucleaseApi extends Bioapi
{
    public function getTypeIIbEndonucleases()
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
     * @return array
     */
    public static function GetTypeIIbEndonucleasesArray($aType2bEndonucleases)
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