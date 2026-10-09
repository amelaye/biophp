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

        // Bio.SeqUtils.molecular_weight(sequence, "protein"), Biopython 1.88, with X read as Gly
        // for the lower limit and as Trp for the upper one
        $molwt = $proteinManager->molwt();

        $this->assertEqualsWithDelta(27394.4746, $molwt[0], 0.0001);
        $this->assertEqualsWithDelta(28427.7434, $molwt[1], 0.0001);
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

        $this->assertEqualsWithDelta([75.0666, 75.0666], $proteinManager->molwt(), 0.0001);
    }

    /**
     * U and O, which the weight table holds, lower case and the stop ending a translated ORF all
     * made molwt() return FALSE. An internal stop still does.
     */
    public function testMolwtOfSelenocysteineLowerCaseAndATerminalStop()
    {
        $proteinManager = new ProteinManager($this->apiAminoMock);
        $oProtein = new Protein();
        $oProtein->setName("translated");
        $oProtein->setSequence("gU*");
        $proteinManager->setProtein($oProtein);

        // Bio.SeqUtils.molecular_weight("GU", "protein"), Biopython 1.88
        $aMolwt = $proteinManager->molwt();
        $this->assertEqualsWithDelta(225.1045, $aMolwt[0], 0.0001);
        $this->assertEqualsWithDelta(225.1045, $aMolwt[1], 0.0001);

        $oProtein->setSequence("G*G");
        $this->assertFalse($proteinManager->molwt());
    }
}
