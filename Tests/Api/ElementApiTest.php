<?php


namespace Tests\AppBundle;

use AppBundle\Api\ElementApi;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use GuzzleHttp;

class ElementApiTest extends WebTestCase
{
    public function setUp()
    {
        $aElementsObjects = [];

        require 'samples/Elements.php';

        $this->aElementsObjects = $aElementsObjects;

        $aStream =   [
          "@context" => "/contexts/Element",
          "@id" => "/elements",
          "@type" => "hydra:Collection",
          "hydra:member" => [
            0 =>  [
              "@id" => "/elements/1",
              "@type" => "Element",
              "id" => 1,
              "name" => "carbone",
              "weight" => 12.01,
            ],
            1 =>  [
              "@id" => "/elements/2",
              "@type" => "Element",
              "id" => 2,
              "name" => "oxygene",
              "weight" => 16,
            ],
            2 =>  [
              "@id" => "/elements/3",
              "@type" => "Element",
              "id" => 3,
              "name" => "nitrate",
              "weight" => 14.01,
            ],
            3 =>  [
              "@id" => "/elements/4",
              "@type" => "Element",
              "id" => 4,
              "name" => "hydrogene",
              "weight" => 1.01,
            ],
            4 =>  [
              "@id" => "/elements/5",
              "@type" => "Element",
              "id" => 5,
              "name" => "phosphore",
              "weight" => 30.97,
            ],
            5 =>  [
              "@id" => "/elements/6",
              "@type" => "Element",
              "id" => 6,
              "name" => "water",
              "weight" => 18.015,
            ],
          ],
          "hydra:totalItems" => 6
        ];

        $this->clientMock = new GuzzleHttp\Client(['base_uri' => 'http://api.amelayes-biophp.net']);

        $this->serializerMock = $this->getMockBuilder('JMS\Serializer\Serializer')
            ->setMethods(['deserialize'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializerMock->method('deserialize')->will($this->returnValue($aStream));
    }

    public function testGetElements()
    {
        $apiElements = new ElementApi($this->clientMock, $this->serializerMock);

        $this->assertEquals($this->aElementsObjects, $apiElements->getElements());
    }
}