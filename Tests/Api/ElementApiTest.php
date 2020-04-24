<?php
namespace Tests\Api;

use Amelaye\BioPHP\Api\ElementApi;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use GuzzleHttp;

class ElementApiTest extends WebTestCase
{
    public function setUp()
    {
        $aElementsObjects = [];

        require 'samples/Elements.php';

        $this->aElementsObjects = $aElementsObjects;
        $this->clientMock = new GuzzleHttp\Client(['base_uri' => 'http://api.amelayes-biophp.net']);

        $this->serializerMock = \JMS\Serializer\SerializerBuilder::create()
            ->build();
    }

    public function testGetElements()
    {
        $apiElements = new ElementApi($this->clientMock, $this->serializerMock);

        $this->assertEquals($this->aElementsObjects, $apiElements->getElements());
    }
}