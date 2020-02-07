<?php


namespace Tests\AppBundle\API;

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

        $aStream = [
          "@context" => "/contexts/PK",
          "@id" => "/p_ks/Solomon",
          "@type" => "PK",
          "id" => "Solomon",
          "NTerminus" => 9.6,
          "k" => 10.5,
          "r" => 125,
          "h" => 6,
          "CTerminus" => 2.4,
          "d" => 3.9,
          "e" => 4.3,
          "c" => 8.3,
          "y" => 10.1,
        ];

        $this->clientMock = new GuzzleHttp\Client(['base_uri' => 'http://api.amelayes-biophp.net']);

        $this->serializerMock = $this->getMockBuilder('JMS\Serializer\Serializer')
            ->setMethods(['deserialize'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializerMock->method('deserialize')->will($this->returnValue($aStream));
    }

    /*public function testGetElements()
    {
        $pkElements = new PKApi($this->clientMock, $this->serializerMock);

        $this->assertEquals((array)$this->aPKObjects[2], $pkElements->getPkValueById("Solomon"));
    }*/
}