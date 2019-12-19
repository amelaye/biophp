<?php
/**
 * Database of elements - Amino acids
 * Inspired by BioPHP's project biophp.org
 * Created 1st December 2019
 * Last modified 1st December 2019
 */
namespace AppBundle\Api;

use AppBundle\Api\DTO\AminoDTO;
use GuzzleHttp\Client;
use JMS\Serializer\Serializer;

/**
 * Database of elements - Amino acids
 * @package AppBundle\Api
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class AminoApi
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
     * Retrives Aminos informations
     * @return array
     */
    public function getAminos()
    {
        $uri = '/aminos';
        $response = $this->bioapiClient->get($uri);
        dump($response);

        $data = $this->serializer->deserialize($response->getBody()->getContents(), 'array', 'json');

        $aAminos = array();
        foreach($data["hydra:member"] as $key => $elem) {
            $oAminoDTO = new AminoDTO();
            $oAminoDTO->setId($elem["id"]);
            $oAminoDTO->setName($elem["name"]);
            $oAminoDTO->setName1Letter($elem["name1Letter"]);
            $oAminoDTO->setName3Letters($elem["name3Letters"]);
            $oAminoDTO->setWeight1($elem["weight1"]);
            $oAminoDTO->setWeight2($elem["weight2"]);
            $oAminoDTO->setResidueMolWeight(floatval($elem["residueMolWeight"]));
            $aAminos[] = $oAminoDTO;
        }

        return $aAminos;
    }

    public static function GetAminosOnlyLetters($aAminos)
    {
        $aFormattedAminos = array();
        foreach($aAminos as $key => $elem) {
            $aFormattedAminos[$elem->getName()] = [1 => $elem->getName1Letter(), 3 => $elem->getName3Letters()];
        }

        return $aFormattedAminos;
    }

    public static function GetAminoweights($aAminos)
    {
        $aFormattedAminos = array();
        foreach($aAminos as $key => $elem) {
            $aFormattedAminos[$elem->getId()] = [$elem->getWeight1(), $elem->getWeight2()];
        }

        return $aFormattedAminos;
    }
}