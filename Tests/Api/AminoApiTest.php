<?php
namespace Tests\Api;

use Amelaye\BioPHP\Api\AminoApi;
use Amelaye\BioPHP\Api\DTO\ElementDTO;
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

        static::assertEquals($this->aAminosObjects, $apiAminos->getAminos());
    }

    public function testGetAminosOnlyLetters()
    {
        $apiAminos = new AminoApi($this->clientMock, $this->serializerMock);
        $aminosOnlyLetters = AminoApi::GetAminosOnlyLetters($apiAminos->getAminos());

        $aminosOnlyLettersExpected = [
          "STOP" =>  [
            1 => "*",
            3 => "STP",
          ],
          "Alanine" =>  [
            1 => "A",
            3 => "Ala",
          ],
          "Aspartate or asparagine" =>  [
            1 => "B",
            3 => "N/A",
          ],
          "Cysteine" =>  [
            1 => "C",
            3 => "Cys",
          ],
          "Aspartic acid" =>  [
            1 => "D",
            3 => "Asp",
          ],
          "Glutamic acid" =>  [
            1 => "E",
            3 => "Glu",
          ],
          "Phenylalanine" =>  [
            1 => "F",
            3 => "Phe",
          ],
          "Glycine" =>  [
            1 => "G",
            3 => "Gly",
          ],
          "Histidine" =>  [
            1 => "H",
            3 => "His",
          ],
          "Isoleucine" =>  [
            1 => "I",
            3 => "Ile",
          ],
          "Lysine" =>  [
            1 => "K",
            3 => "Lys",
          ],
          "Leucine" =>  [
            1 => "L",
            3 => "Leu",
          ],
          "Methionine" =>  [
            1 => "M",
            3 => "Met",
          ],
          "Asparagine" =>  [
            1 => "N",
            3 => "Asn",
          ],
          "Pyrrolysine" =>  [
            1 => "O",
            3 => "Pyr",
          ],
          "Proline" =>  [
            1 => "P",
            3 => "Pro",
          ],
          "Glutamine" =>  [
            1 => "Q",
            3 => "Gin",
          ],
          "Arginine" =>  [
            1 => "R",
            3 => "Arg",
          ],
          "Serine" =>  [
            1 => "S",
            3 => "Ser",
          ],
          "Threonine" =>  [
            1 => "T",
            3 => "Thr",
          ],
          "Selenocysteine" =>  [
            1 => "U",
            3 => "Sec",
          ],
          "Valine" =>  [
            1 => "V",
            3 => "Val",
          ],
          "Tryptophan" =>  [
            1 => "W",
            3 => "Trp",
          ],
          "Any" =>  [
            1 => "X",
            3 => "XXX",
          ],
          "Tyrosine" =>  [
            1 => "Y",
            3 => "Tyr",
          ],
          "Glutamate or glutamine" =>  [
            1 => "Z",
            3 => "N/A",
          ],
        ];

        static::assertEquals($aminosOnlyLettersExpected, $aminosOnlyLetters);
    }

    public function testGetAminosOneToThreeLetters()
    {
        $apiAminos = new AminoApi($this->clientMock, $this->serializerMock);
        $aminosAminosOneToThreeLetters = AminoApi::GetAminosOneToThreeLetters($apiAminos->getAminos());

        $aminosAminosOneToThreeLettersExpected = [
          "*" => "STP",
          "A" => "Ala",
          "B" => "N/A",
          "C" => "Cys",
          "D" => "Asp",
          "E" => "Glu",
          "F" => "Phe",
          "G" => "Gly",
          "H" => "His",
          "I" => "Ile",
          "K" => "Lys",
          "L" => "Leu",
          "M" => "Met",
          "N" => "Asn",
          "O" => "Pyr",
          "P" => "Pro",
          "Q" => "Gin",
          "R" => "Arg",
          "S" => "Ser",
          "T" => "Thr",
          "U" => "Sec",
          "V" => "Val",
          "W" => "Trp",
          "X" => "XXX",
          "Y" => "Tyr",
          "Z" => "N/A"
        ];

        static::assertEquals($aminosAminosOneToThreeLettersExpected, $aminosAminosOneToThreeLetters);
    }

    public function testGetAminoResidueWeights()
    {
        $apiAminos = new AminoApi($this->clientMock, $this->serializerMock);
        $aAminosResidueMolWeights = AminoApi::GetAminoResidueWeights($apiAminos->getAminos());

        $aAminosResidueMolWeightsExpected = [
          "*" => 0.0,
          "A" => 71.07,
          "B" => 0.0,
          "C" => 103.1,
          "D" => 115.08,
          "E" => 129.11,
          "F" => 147.17,
          "G" => 57.05,
          "H" => 137.14,
          "I" => 113.15,
          "K" => 128.17,
          "L" => 113.15,
          "M" => 131.19,
          "N" => 114.08,
          "O" => 0.0,
          "P" => 97.11,
          "Q" => 128.13,
          "R" => 156.18,
          "S" => 87.07,
          "T" => 101.1,
          "U" => 0.0,
          "V" => 99.13,
          "W" => 186.2,
          "X" => 114.82,
          "Y" => 163.17,
          "Z" => 0.0
        ];

        static::assertEquals($aAminosResidueMolWeightsExpected, $aAminosResidueMolWeights);
    }
}