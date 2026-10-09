<?php
/**
 * Bioapi requests
 * Created 3 november 2019
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Api;

use GuzzleHttp\Client;
use JMS\Serializer\Serializer;

/**
 * This class makes requests on the Bio API api.amelayes-biophp.net
 * This is the sample database
 * Class Bioapi
 * @package Amelaye\BioPHP\Api
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
abstract class Bioapi
{
    /**
     * @var Client
     */
    protected Client $bioapiClient;

    /**
     * @var Serializer
     */
    protected ?Serializer $serializer = null;

    /**
     * @var string|null
     */
    protected ?string $apiKey = null;

    /**
     * Bioapi constructor.
     * @param Client        $bioapiClient
     * @param Serializer    $serializer
     * @param string        $apiKey
     */
    public function __construct(Client $bioapiClient, Serializer $serializer, ?string $apiKey = null) {
        $this->bioapiClient = $bioapiClient;
        $this->serializer   = $serializer;
        $this->apiKey       = $apiKey;
    }
}