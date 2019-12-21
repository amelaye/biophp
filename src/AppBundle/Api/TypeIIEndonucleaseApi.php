<?php


namespace AppBundle\Api;

class TypeIIEndonucleaseApi extends Bioapi
{
    /**
     * @return array
     */
    public function getTypeIIEndonucleases()
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
     * @return array
     */
    public static function GetTypeIIEndonucleasesArray($aType2Endonucleases)
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
}