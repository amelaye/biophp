<?php


namespace Tests\AppBundle\Service;


use AppBundle\Entity\Protein;
use AppBundle\Service\ProteinManager;
use PHPUnit\Framework\TestCase;

class ProteinManagerTest extends TestCase
{
    private $apiMock;

    public function setUp()
    {
        $aWTS = [
          "*" =>  [
            0 => 0,
            1 => 0,
          ],
          "A" =>  [
            0 => 89,
            1 => 89,
          ],
          "B" =>  [
            0 => 132,
            1 => 132,
          ],
          "C" =>  [
            0 => 121,
            1 => 121,
          ],
          "D" =>  [
            0 => 133,
            1 => 133,
          ],
          "E" =>  [
            0 => 147,
            1 => 147,
          ],
          "F" =>  [
            0 => 165,
            1 => 165,
          ],
          "G" =>  [
            0 => 75,
            1 => 75,
          ],
          "H" =>  [
            0 => 155,
            1 => 155,
          ],
          "I" =>  [
            0 => 131,
            1 => 131,
          ],
          "K" =>  [
            0 => 146,
            1 => 146,
          ],
          "L" =>  [
            0 => 131,
            1 => 131,
          ],
          "M" =>  [
            0 => 149,
            1 => 149,
          ],
          "N" =>  [
            0 => 132,
            1 => 132,
          ],
          "O" =>  [
            0 => 255,
            1 => 255,
          ],
          "P" =>  [
            0 => 115,
            1 => 115,
          ],
          "Q" =>  [
            0 => 146,
            1 => 146,
          ],
          "R" =>  [
            0 => 174,
            1 => 174,
          ],
          "S" =>  [
            0 => 105,
            1 => 105,
          ],
          "T" =>  [
            0 => 119,
            1 => 119,
          ],
          "U" =>  [
            0 => 168,
            1 => 168,
          ],
          "V" =>  [
            0 => 117,
            1 => 117,
          ],
          "W" =>  [
            0 => 204,
            1 => 204,
          ],
          "X" =>  [
            0 => 146,
            1 => 146,
          ],
          "Y" =>  [
            0 => 181,
            1 => 181,
          ],
          "Z" =>  [
            0 => 75,
            1 => 204,
          ]
        ];

        
        /**
         * Mock API
         */
        $clientMock = $this->getMockBuilder('GuzzleHttp\Client')->getMock();
        $serializerMock = $this->getMockBuilder('JMS\Serializer\Serializer')
            ->disableOriginalConstructor()
            ->getMock();
        $this->apiMock = $this->getMockBuilder('AppBundle\Bioapi\Bioapi')
            ->setConstructorArgs([$clientMock, $serializerMock])
            ->setMethods(['getAminoweights'])
            ->getMock();
        $this->apiMock->method("getAminoweights")->will($this->returnValue($aWTS));
    }

    public function testSeqlen()
    {
        $proteinManager = new ProteinManager($this->apiMock);

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
        $proteinManager = new ProteinManager($this->apiMock);

        $sProtein = "ARNDCEQGHARNDCEQGHILKMFPSTWYVXARNDKMFPSTWYVXARNDKMFPSTWYVXARNDCEQGHARNDCEQGHHARNDCEQGHILKMFPSTW";
        $sProtein .= "YVXARNDKMFPSTHARNDCEQGHILKMFPSTWYVXARNDKMFPSTHARNDCEQGHILKMFPSTWYVXARNDKMFPSTHARNDCEQGHILKMFPSTWY";
        $sProtein .= "VXARNDKMFPSTHARNDCEQGHILKMFPSTWYVXARNDKMFPST";

        $oProtein = new Protein();
        $oProtein->setName("toto");
        $oProtein->setSequence($sProtein);
        $proteinManager->setProtein($oProtein);

        $molwt = $proteinManager->molwt();
        $aExpected = [
          0 => 27928.475,
          1 => 27928.475
        ];

        $this->assertEquals($aExpected, $molwt);
    }
}