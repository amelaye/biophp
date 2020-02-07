<?php


namespace Tests\AppBundle\API\DTO;

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

        $aStream = [
          "@context" => "/contexts/Nucleotid",
          "@id" => "/nucleotids",
          "@type" => "hydra:Collection",
          "hydra:member" => [
            0 => [
              "@id" => "/nucleotids/1",
              "@type" => "Nucleotid",
              "id" => 1,
              "letter" => "A",
              "complement" => "T",
              "nature" => "DNA",
              "weight" => 313.245,
            ],
            1 => [
              "@id" => "/nucleotids/2",
              "@type" => "Nucleotid",
              "id" => 2,
              "letter" => "T",
              "complement" => "A",
              "nature" => "DNA",
              "weight" => 304.225,
            ],
            2 => [
              "@id" => "/nucleotids/3",
              "@type" => "Nucleotid",
              "id" => 3,
              "letter" => "G",
              "complement" => "C",
              "nature" => "DNA",
              "weight" => 329.245,
            ],
            3 => [
              "@id" => "/nucleotids/4",
              "@type" => "Nucleotid",
              "id" => 4,
              "letter" => "C",
              "complement" => "G",
              "nature" => "DNA",
              "weight" => 289.215,
            ],
            4 => [
              "@id" => "/nucleotids/5",
              "@type" => "Nucleotid",
              "id" => 5,
              "letter" => "A",
              "complement" => "U",
              "nature" => "RNA",
              "weight" => 329.245,
            ],
            5 => [
              "@id" => "/nucleotids/6",
              "@type" => "Nucleotid",
              "id" => 6,
              "letter" => "U",
              "complement" => "A",
              "nature" => "RNA",
              "weight" => 306.195,
            ],
            6 => [
              "@id" => "/nucleotids/7",
              "@type" => "Nucleotid",
              "id" => 7,
              "letter" => "G",
              "complement" => "C",
              "nature" => "RNA",
              "weight" => 345.245,
            ],
            7 => [
              "@id" => "/nucleotids/8",
              "@type" => "Nucleotid",
              "id" => 8,
              "letter" => "C",
              "complement" => "G",
              "nature" => "RNA",
              "weight" => 305.215,
            ]
          ],
          "hydra:totalItems" => 8
        ];

        $this->clientMock = new GuzzleHttp\Client(['base_uri' => 'http://api.amelayes-biophp.net']);

        $this->serializerMock = $this->getMockBuilder('JMS\Serializer\Serializer')
            ->setMethods(['deserialize'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializerMock->method('deserialize')->will($this->returnValue($aStream));
    }

    public function testGetNucleotids()
    {
        $apiNucleo = new NucleotidApi($this->clientMock, $this->serializerMock);

        $this->assertEquals($this->aNucleoObjects, $apiNucleo->getNucleotids());
    }
}