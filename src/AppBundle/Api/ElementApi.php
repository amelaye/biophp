<?php


namespace AppBundle\Api;


use AppBundle\Api\DTO\ElementDTO;

class ElementApi extends Bioapi
{
    /**
     * @return array
     */
    public function getElements() : array
    {
        $uri = '/elements';
        $response = $this->bioapiClient->get($uri);

        $data = $this->serializer->deserialize($response->getBody()->getContents(), 'array', 'json');
        $aElements = array();
        foreach($data["hydra:member"] as $key => $elem) {
            $oElement = new ElementDTO();
            $oElement->setName($elem['name']);
            $oElement->setWeight($elem['weight']);
            $aElements[] = $oElement;
        }
        return $aElements;
    }

    /**
     * @param $iElement
     * @return ElementDTO
     */
    public function getElement($iElement) : ElementDTO
    {
        $uri = '/elements/'.$iElement;
        $response = $this->bioapiClient->get($uri);

        $oElement = $this->serializer->deserialize($response->getBody()->getContents(), ElementDTO::class, 'json');
        return $oElement;
    }

    /**
     * @return array
     */
    public static function GetElementsArray($aElements) : array
    {
        $newData = array();
        foreach($aElements as $key => $elem) {
            $newData[$elem->getName()] = $elem->getWeight();
        }
        return $newData;
    }
}