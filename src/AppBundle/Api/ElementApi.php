<?php


namespace AppBundle\Api;


use AppBundle\Api\DTO\ElementDTO;
use GuzzleHttp\Client;
use JMS\Serializer\Serializer;

class ElementApi
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