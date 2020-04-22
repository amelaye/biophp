<?php


namespace Tests\AppBundle\API;

use GuzzleHttp;
use Amelaye\BioPHP\Api\ProteinReductionApi;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProteinReductionApiTest extends WebTestCase
{
    public function setUp()
    {
        $aReductions = [];

        require 'samples/ProteinReductions.php';

        $this->aReductions = $aReductions;
        $this->clientMock = new GuzzleHttp\Client(['base_uri' => 'http://api.amelayes-biophp.net']);
        $this->serializerMock = \JMS\Serializer\SerializerBuilder::create()
            ->build();
    }

    public function testGetReductions()
    {
        $apiReduction = new ProteinReductionApi($this->clientMock, $this->serializerMock);

        $this->assertEquals($this->aReductions, $apiReduction->getReductions());
    }
}