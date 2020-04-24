<?php
namespace Tests\Api;

use Amelaye\BioPHP\Api\NucleotidApi;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use GuzzleHttp;

class NucleotidApiTest extends WebTestCase
{
    public function setUp()
    {
        $aNucleoObjects = [];

        require 'samples/Nucleotids.php';

        $this->aNucleoObjects = $aNucleoObjects;
        $this->clientMock = new GuzzleHttp\Client(['base_uri' => 'http://api.amelayes-biophp.net']);
        $this->serializerMock = \JMS\Serializer\SerializerBuilder::create()
            ->build();
    }

    public function testGetNucleotids()
    {
        $apiNucleo = new NucleotidApi($this->clientMock, $this->serializerMock);
        $this->assertEquals($this->aNucleoObjects, $apiNucleo->getNucleotids());
    }
}