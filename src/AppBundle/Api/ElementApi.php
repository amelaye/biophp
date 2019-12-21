<?php
/**
 * Database of elements - Amino acids
 * Inspired by BioPHP's project biophp.org
 * Created 21 December 2019
 * Last modified 21 December 2019
 */
namespace AppBundle\Api;

use AppBundle\Api\DTO\ElementDTO;

/**
 * Database of elements
 * @package AppBundle\Api
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class ElementApi extends Bioapi
{
    /**
     * Gets the list of objects from Api
     * @return      array
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
     * Gets a single element from API
     * @param       int         $iElement
     * @return      ElementDTO
     */
    public function getElement(int $iElement) : ElementDTO
    {
        $uri = '/elements/'.$iElement;
        $response = $this->bioapiClient->get($uri);

        $oElement = $this->serializer->deserialize($response->getBody()->getContents(), ElementDTO::class, 'json');
        return $oElement;
    }

    /**
     * Gets the list of elements weights
     * @param   array   $aElements
     * @return  array
     */
    public static function GetElementsArray(array $aElements) : array
    {
        $newData = array();
        foreach($aElements as $key => $elem) {
            $newData[$elem->getName()] = $elem->getWeight();
        }
        return $newData;
    }
}