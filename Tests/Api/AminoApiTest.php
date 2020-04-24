<?php
namespace Tests\Api;

use Amelaye\BioPHP\Api\AminoApi;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use GuzzleHttp;

class AminoApiTest extends WebTestCase
{
    public function setUp()
    {
        $aAminosObjects = [];

        require 'samples/Aminos.php';

        $this->aAminosObjects = $aAminosObjects;
        $this->clientMock = new GuzzleHttp\Client(['base_uri' => 'http://api.amelayes-biophp.net']);
        $this->serializerMock = \JMS\Serializer\SerializerBuilder::create()
            ->build();
    }

    public function testGetAminos()
    {
        $apiAminos = new AminoApi($this->clientMock, $this->serializerMock);

        $this->assertEquals($this->aAminosObjects, $apiAminos->getAminos());
    }
}