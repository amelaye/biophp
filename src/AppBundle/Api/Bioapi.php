<?php
/**
 * Bioapi requests
 * Created 3 november 2019
 * Last modified 21 december 2019
 */
namespace AppBundle\Api;

use GuzzleHttp\Client;
use JMS\Serializer\Serializer;

/**
 * This class makes requests on the Bio API api.amelayes-biophp.net
 * This is the sample database
 * Class Bioapi
 * @package AppBundle\Api
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
abstract class Bioapi
{
    /**
     * @var Client
     */
    protected $bioapiClient;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var string|null
     */
    protected $apiKey;

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
}