<?php
namespace Tests\Api;

use Amelaye\BioPHP\Api\AminoApi;
use Amelaye\BioPHP\Api\DTO\ElementDTO;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use GuzzleHttp;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class AminoApiTest extends WebTestCase
{
    private $aAminosObjects;
    private $clientMock;
    private $serializerMock;

    public function setUp(): void
    {
        $aAminosObjects = [];

        require 'samples/Aminos.php';

        $this->aAminosObjects = $aAminosObjects;

        $aMembers = [];
        foreach ($aAminosObjects as $amino) {
            $aMember = [
                'id' => $amino->getId(),
                'name' => $amino->getName(),
                'name1Letter' => $amino->getName1Letter(),
                'name3Letters' => $amino->getName3Letters(),
                'weight1' => $amino->getWeight1(),
                'weight2' => $amino->getWeight2(),
            ];
            if ($amino->getResidueMolWeight() !== null) {
                $aMember['residueMolWeight'] = $amino->getResidueMolWeight();
            }
            $aMembers[] = $aMember;
        }

        $oMockHandler = new MockHandler([
            new Response(200, [], json_encode(['hydra:member' => $aMembers])),
        ]);
        $this->clientMock = new GuzzleHttp\Client([
            'base_uri' => 'https://api.amelayes-biophp.net',
            'handler' => HandlerStack::create($oMockHandler),
        ]);
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
            3 => "Pyl",
          ],
          "Proline" =>  [
            1 => "P",
            3 => "Pro",
          ],
          "Glutamine" =>  [
            1 => "Q",
            3 => "Gln",
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
          "O" => "Pyl",
          "P" => "Pro",
          "Q" => "Gln",
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
          "A" => 71.0779,
          "B" => 114.1026,
          "C" => 103.1429,
          "D" => 115.0874,
          "E" => 129.114,
          "F" => 147.1738,
          "G" => 57.0513,
          "H" => 137.1393,
          "I" => 113.1576,
          "K" => 128.1723,
          "L" => 113.1576,
          "M" => 131.196,
          "N" => 114.1026,
          "O" => 237.2981,
          "P" => 97.1152,
          "Q" => 128.1292,
          "R" => 156.1857,
          "S" => 87.0773,
          "T" => 101.1039,
          "U" => 150.0379,
          "V" => 99.131,
          "W" => 186.2099,
          "X" => 57.0513,
          "Y" => 163.1732,
          "Z" => 128.1292
        ];

        static::assertEquals($aAminosResidueMolWeightsExpected, $aAminosResidueMolWeights);
    }
}