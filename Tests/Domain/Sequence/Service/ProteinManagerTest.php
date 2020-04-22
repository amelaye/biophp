<?php


namespace Tests\Domain\Sequence\Service;


use Amelaye\BioPHP\Api\AminoApi;
use Amelaye\BioPHP\Domain\Sequence\Entity\Protein;
use Amelaye\BioPHP\Domain\Sequence\Service\ProteinManager;
use PHPUnit\Framework\TestCase;

class ProteinManagerTest extends TestCase
{
    private $apiAminoMock;

    public function setUp()
    {
        require 'samples/Aminos.php';

        /**
         * Mock API
         */
        $clientMock = $this->getMockBuilder('GuzzleHttp\Client')->getMock();
        $serializerMock = \JMS\Serializer\SerializerBuilder::create()
            ->build();

        $aAminoWeights = [
          "*" =>  [
            0 => 0.0,
            1 => 0.0,
          ],
          "A" =>  [
            0 => 89.09,
            1 => 89.09,
          ],
          "B" =>  [
            0 => 132.12,
            1 => 132.1,
          ],
          "C" =>  [
            0 => 121.15,
            1 => 121.15,
          ],
          "D" =>  [
            0 => 133.1,
            1 => 133.1,
          ],
          "E" =>  [
            0 => 147.13,
            1 => 147.13,
          ],
          "F" =>  [
            0 => 165.19,
            1 => 165.19,
          ],
          "G" =>  [
            0 => 75.07,
            1 => 75.07,
          ],
          "H" =>  [
            0 => 155.16,
            1 => 155.16,
          ],
          "I" =>  [
            0 => 131.18,
            1 => 131.18,
          ],
          "K" =>  [
            0 => 146.19,
            1 => 146.19,
          ],
          "L" =>  [
            0 => 131.18,
            1 => 131.18,
          ],
          "M" =>  [
            0 => 149.22,
            1 => 149.22,
          ],
          "N" =>  [
            0 => 132.12,
            1 => 132.12,
          ],
          "O" =>  [
            0 => 255.31,
            1 => 255.31,
          ],
          "P" =>  [
            0 => 115.13,
            1 => 115.13,
          ],
          "Q" =>  [
            0 => 146.15,
            1 => 146.15,
          ],
          "R" =>  [
            0 => 174.21,
            1 => 174.21,
          ],
          "S" =>  [
            0 => 105.09,
            1 => 105.09,
          ],
          "T" =>  [
            0 => 119.12,
            1 => 119.12,
          ],
          "U" =>  [
            0 => 168.05,
            1 => 168.05,
          ],
          "V" =>  [
            0 => 117.15,
            1 => 117.15,
          ],
          "W" =>  [
            0 => 204.22,
            1 => 204.22,
          ],
          "X" =>  [
            0 => 146.15,
            1 => 146.15,
          ],
          "Y" =>  [
            0 => 181.19,
            1 => 181.19,
          ],
          "Z" =>  [
            0 => 75.07,
            1 => 204.22,
          ]
        ];

        $this->apiAminoMock = $this->getMockBuilder(AminoApi::class)
            ->setConstructorArgs([$clientMock, $serializerMock])
            ->setMethods(['getAminos'])
            ->getMock();
        $this->apiAminoMock->method("getAminos")->will($this->returnValue($aAminosObjects));
    }

    public function testSeqlen()
    {
        $proteinManager = new ProteinManager($this->apiAminoMock);

        $sProtein = "ARNDCEQGHARNDCEQGHILKMFPSTWYVXARNDKMFPSTWYVXARNDKMFPSTWYVXARNDCEQGHARNDCEQGHHARNDCEQGHILKMFPSTW";
        $sProtein .= "YVXARNDKMFPSTHARNDCEQGHILKMFPSTWYVXARNDKMFPSTHARNDCEQGHILKMFPSTWYVXARNDKMFPSTHARNDCEQGHILKMFPSTWY";
        $sProtein .= "VXARNDKMFPSTHARNDCEQGHILKMFPSTWYVXARNDKMFPST";

        $oProtein = new Protein();
        $oProtein->setName("toto");
        $oProtein->setSequence($sProtein);
        $proteinManager->setProtein($oProtein);

        $len = $proteinManager->seqlen();
        $sExpected = 236;

        $this->assertEquals($sExpected, $len);
    }

    public function testMolwt()
    {
        $proteinManager = new ProteinManager($this->apiAminoMock);

        $sProtein = "ARNDCEQGHARNDCEQGHILKMFPSTWYVXARNDKMFPSTWYVXARNDKMFPSTWYVXARNDCEQGHARNDCEQGHHARNDCEQGHILKMFPSTW";
        $sProtein .= "YVXARNDKMFPSTHARNDCEQGHILKMFPSTWYVXARNDKMFPSTHARNDCEQGHILKMFPSTWYVXARNDKMFPSTHARNDCEQGHILKMFPSTWY";
        $sProtein .= "VXARNDKMFPSTHARNDCEQGHILKMFPSTWYVXARNDKMFPST";

        $oProtein = new Protein();
        $oProtein->setName("toto");
        $oProtein->setSequence($sProtein);
        $proteinManager->setProtein($oProtein);

        $molwt = $proteinManager->molwt();
        $aExpected = [
            0 => 60160.715000000084,
            1 => -4233.525000000001
        ];

        $this->assertEquals($aExpected, $molwt);
    }
}