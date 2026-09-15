<?php
namespace Tests\Domain\Sequence\Service;

use Amelaye\BioPHP\Api\AminoApi;
use Amelaye\BioPHP\Domain\Sequence\Entity\Protein;
use Amelaye\BioPHP\Domain\Sequence\Service\ProteinManager;
use PHPUnit\Framework\TestCase;

class ProteinManagerTest extends TestCase
{
    private $apiAminoMock;

    public function setUp(): void
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
            ->onlyMethods(['getAminos'])
            ->getMock();
        $this->apiAminoMock->method("getAminos")->willReturn($aAminosObjects);
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
            0 => 27394.954999999976,
            1 => 28428.15499999998
        ];

        $this->assertEquals($aExpected, $molwt);
    }

    /**
     * Regression test: an empty protein sequence used to return [18.015, 18.015] (one spurious
     * water molecule gained) instead of [0, 0], because "(seqlen() - 1) * water" is negative
     * when seqlen() is 0.
     */
    public function testMolwtOfEmptySequence()
    {
        $proteinManager = new ProteinManager($this->apiAminoMock);

        $oProtein = new Protein();
        $oProtein->setName("empty");
        $oProtein->setSequence("");
        $proteinManager->setProtein($oProtein);

        $this->assertEquals([0, 0], $proteinManager->molwt());
    }

    /**
     * A single-residue protein loses no water at all (there is no peptide bond to form), so its
     * molecular weight is simply the free amino acid's own weight.
     */
    public function testMolwtOfSingleResidueSequence()
    {
        $proteinManager = new ProteinManager($this->apiAminoMock);

        $oProtein = new Protein();
        $oProtein->setName("single");
        $oProtein->setSequence("G");
        $proteinManager->setProtein($oProtein);

        $this->assertEquals([75.07, 75.07], $proteinManager->molwt());
    }
}