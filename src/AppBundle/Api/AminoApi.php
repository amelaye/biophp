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
     * @return object
     */
    public function getAminos()
    {
        $uri = '/aminos';
        $response = $this->bioapiClient->get($uri);
        return $this->serializer->deserialize($response->getBody()->getContents(), AminoDTO::class, 'json');
    }
}