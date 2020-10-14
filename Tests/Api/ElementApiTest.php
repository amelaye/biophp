<?php
namespace Tests\Api;

use Amelaye\BioPHP\Api\DTO\ElementDTO;
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

        static::assertEquals($this->aElementsObjects, $apiElements->getElements());
    }
/*
    public function testgetElement()
    {
        $elementExpected = new ElementDTO();
        $elementExpected->setId(6);
        $elementExpected->setName("water");
        $elementExpected->setWeight(18.015);

        $elementApi = new ElementApi($this->clientMock, $this->serializerMock);
        $element = $elementApi->getElement(6);

        static::assertEquals($elementExpected, $element);
    }
*/
}