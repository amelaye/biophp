<?php
namespace Tests\Api;

use GuzzleHttp;
use Amelaye\BioPHP\Api\PKApi;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PKApiTest extends WebTestCase
{
    public function setUp()
    {
        $aPKObjects = [];

        require 'samples/PK.php';

        $this->aPKObjects = $aPKObjects;
        $this->clientMock = new GuzzleHttp\Client(['base_uri' => 'http://api.amelayes-biophp.net']);
        $this->serializerMock = \JMS\Serializer\SerializerBuilder::create()
            ->build();
    }

    /*public function testGetElements()
    {
        $pkElements = new PKApi($this->clientMock, $this->serializerMock);

        $this->assertEquals((array)$this->aPKObjects[2], $pkElements->getPkValueById("Solomon"));
    }*/
}