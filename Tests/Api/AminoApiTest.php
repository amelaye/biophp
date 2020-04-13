<?php


namespace Tests\API;


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

        $aStream =  [
          "@context" => "/contexts/Amino",
          "@id" => "/aminos",
          "@type" => "hydra:Collection",
          "hydra:member" =>  [
            0 =>  [
              "@id" => "/aminos/%252A",
              "@type" => "Amino",
              "id" => "*",
              "name" => "STOP",
              "name1Letter" => "*",
              "name3Letters" => "STP",
              "weight1" => 0,
              "weight2" => 0,
              "residueMolWeight" => null,
            ],
            1 =>  [
              "@id" => "/aminos/A",
              "@type" => "Amino",
              "id" => "A",
              "name" => "Alanine",
              "name1Letter" => "A",
              "name3Letters" => "Ala",
              "weight1" => 89,
              "weight2" => 89,
              "residueMolWeight" => 71.07,
            ],
            2 =>  [
              "@id" => "/aminos/B",
              "@type" => "Amino",
              "id" => "B",
              "name" => "Aspartate or asparagine",
              "name1Letter" => "B",
              "name3Letters" => "N/A",
              "weight1" => 132,
              "weight2" => 132,
              "residueMolWeight" => null,
            ],
            3 =>  [
              "@id" => "/aminos/C",
              "@type" => "Amino",
              "id" => "C",
              "name" => "Cysteine",
              "name1Letter" => "C",
              "name3Letters" => "Cys",
              "weight1" => 121,
              "weight2" => 121,
              "residueMolWeight" => 103.1,
            ],
            4 =>  [
              "@id" => "/aminos/D",
              "@type" => "Amino",
              "id" => "D",
              "name" => "Aspartic acid",
              "name1Letter" => "D",
              "name3Letters" => "Asp",
              "weight1" => 133,
              "weight2" => 133,
              "residueMolWeight" => 115.08,
            ],
            5 =>  [
              "@id" => "/aminos/E",
              "@type" => "Amino",
              "id" => "E",
              "name" => "Glutamic acid",
              "name1Letter" => "E",
              "name3Letters" => "Glu",
              "weight1" => 147,
              "weight2" => 147,
              "residueMolWeight" => 129.11,
            ],
            6 =>  [
              "@id" => "/aminos/F",
              "@type" => "Amino",
              "id" => "F",
              "name" => "Phenylalanine",
              "name1Letter" => "F",
              "name3Letters" => "Phe",
              "weight1" => 165,
              "weight2" => 165,
              "residueMolWeight" => 147.17,
            ],
            7 =>  [
              "@id" => "/aminos/G",
              "@type" => "Amino",
              "id" => "G",
              "name" => "Glycine",
              "name1Letter" => "G",
              "name3Letters" => "Gly",
              "weight1" => 75,
              "weight2" => 75,
              "residueMolWeight" => 57.05,
            ],
            8 =>  [
              "@id" => "/aminos/H",
              "@type" => "Amino",
              "id" => "H",
              "name" => "Histidine",
              "name1Letter" => "H",
              "name3Letters" => "His",
              "weight1" => 155,
              "weight2" => 155,
              "residueMolWeight" => 137.14,
            ],
            9 =>  [
              "@id" => "/aminos/I",
              "@type" => "Amino",
              "id" => "I",
              "name" => "Isoleucine",
              "name1Letter" => "I",
              "name3Letters" => "Ile",
              "weight1" => 131,
              "weight2" => 131,
              "residueMolWeight" => 113.15,
            ],
            10 =>  [
              "@id" => "/aminos/K",
              "@type" => "Amino",
              "id" => "K",
              "name" => "Lysine",
              "name1Letter" => "K",
              "name3Letters" => "Lys",
              "weight1" => 146,
              "weight2" => 146,
              "residueMolWeight" => 128.17,
            ],
            11 =>  [
              "@id" => "/aminos/L",
              "@type" => "Amino",
              "id" => "L",
              "name" => "Leucine",
              "name1Letter" => "L",
              "name3Letters" => "Leu",
              "weight1" => 131,
              "weight2" => 131,
              "residueMolWeight" => 113.15,
            ],
            12 =>  [
              "@id" => "/aminos/M",
              "@type" => "Amino",
              "id" => "M",
              "name" => "Methionine",
              "name1Letter" => "M",
              "name3Letters" => "Met",
              "weight1" => 149,
              "weight2" => 149,
              "residueMolWeight" => 131.19,
            ],
            13 =>  [
              "@id" => "/aminos/N",
              "@type" => "Amino",
              "id" => "N",
              "name" => "Asparagine",
              "name1Letter" => "N",
              "name3Letters" => "Asn",
              "weight1" => 132,
              "weight2" => 132,
              "residueMolWeight" => 114.08,
            ],
            14 =>  [
              "@id" => "/aminos/O",
              "@type" => "Amino",
              "id" => "O",
              "name" => "Pyrrolysine",
              "name1Letter" => "O",
              "name3Letters" => "Pyr",
              "weight1" => 255,
              "weight2" => 255,
              "residueMolWeight" => null,
            ],
            15 =>  [
              "@id" => "/aminos/P",
              "@type" => "Amino",
              "id" => "P",
              "name" => "Proline",
              "name1Letter" => "P",
              "name3Letters" => "Pro",
              "weight1" => 115,
              "weight2" => 115,
              "residueMolWeight" => 97.11,
            ],
            16 =>  [
              "@id" => "/aminos/Q",
              "@type" => "Amino",
              "id" => "Q",
              "name" => "Glutamine",
              "name1Letter" => "Q",
              "name3Letters" => "Gin",
              "weight1" => 146,
              "weight2" => 146,
              "residueMolWeight" => 128.13,
            ],
            17 =>  [
              "@id" => "/aminos/R",
              "@type" => "Amino",
              "id" => "R",
              "name" => "Arginine",
              "name1Letter" => "R",
              "name3Letters" => "Arg",
              "weight1" => 174,
              "weight2" => 174,
              "residueMolWeight" => 156.18,
            ],
            18 =>  [
              "@id" => "/aminos/S",
              "@type" => "Amino",
              "id" => "S",
              "name" => "Serine",
              "name1Letter" => "S",
              "name3Letters" => "Ser",
              "weight1" => 105,
              "weight2" => 105,
              "residueMolWeight" => 87.07,
            ],
            19 =>  [
              "@id" => "/aminos/T",
              "@type" => "Amino",
              "id" => "T",
              "name" => "Threonine",
              "name1Letter" => "T",
              "name3Letters" => "Thr",
              "weight1" => 119,
              "weight2" => 119,
              "residueMolWeight" => 101.1,
            ],
            20 =>  [
              "@id" => "/aminos/U",
              "@type" => "Amino",
              "id" => "U",
              "name" => "Selenocysteine",
              "name1Letter" => "U",
              "name3Letters" => "Sec",
              "weight1" => 168,
              "weight2" => 168,
              "residueMolWeight" => null,
            ],
            21 =>  [
              "@id" => "/aminos/V",
              "@type" => "Amino",
              "id" => "V",
              "name" => "Valine",
              "name1Letter" => "V",
              "name3Letters" => "Val",
              "weight1" => 117,
              "weight2" => 117,
              "residueMolWeight" => 99.13,
            ],
            22 =>  [
              "@id" => "/aminos/W",
              "@type" => "Amino",
              "id" => "W",
              "name" => "Tryptophan",
              "name1Letter" => "W",
              "name3Letters" => "Trp",
              "weight1" => 204,
              "weight2" => 204,
              "residueMolWeight" => 186.2,
            ],
            23 =>  [
              "@id" => "/aminos/X",
              "@type" => "Amino",
              "id" => "X",
              "name" => "Any",
              "name1Letter" => "X",
              "name3Letters" => "XXX",
              "weight1" => 146,
              "weight2" => 146,
              "residueMolWeight" => 114.82,
            ],
            24 =>  [
              "@id" => "/aminos/Y",
              "@type" => "Amino",
              "id" => "Y",
              "name" => "Tyrosine",
              "name1Letter" => "Y",
              "name3Letters" => "Tyr",
              "weight1" => 181,
              "weight2" => 181,
              "residueMolWeight" => 163.17,
            ],
            25 =>  [
              "@id" => "/aminos/Z",
              "@type" => "Amino",
              "id" => "Z",
              "name" => "Glutamate or glutamine",
              "name1Letter" => "Z",
              "name3Letters" => "N/A",
              "weight1" => 75,
              "weight2" => 204,
              "residueMolWeight" => null,
            ]
          ],
          "hydra:totalItems" => 26
        ];

        $this->clientMock = new GuzzleHttp\Client(['base_uri' => 'http://api.amelayes-biophp.net']);

        $this->serializerMock = $this->getMockBuilder('JMS\Serializer\Serializer')
            ->setMethods(['deserialize'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializerMock->method('deserialize')->will($this->returnValue($aStream));

    }

    public function testGetAminos()
    {
        $apiAminos = new AminoApi($this->clientMock, $this->serializerMock);

        $this->assertEquals($this->aAminosObjects, $apiAminos->getAminos());
    }
}