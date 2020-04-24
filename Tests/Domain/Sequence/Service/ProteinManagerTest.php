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