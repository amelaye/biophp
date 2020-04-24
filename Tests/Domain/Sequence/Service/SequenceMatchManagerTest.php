<?php
/**
 * SequenceMatchManager Testing
 * @author Amélie DUVERNET aka Amelaye
 * Freely inspired by BioPHP's project biophp.org
 * Created 14 november 2019
 * Last modified 14 november 2019
 */
namespace Tests\AppBundle\Entity;

use Amelaye\BioPHP\Api\AminoApi;
use Amelaye\BioPHP\Api\ElementApi;
use Amelaye\BioPHP\Api\NucleotidApi;
use Amelaye\BioPHP\Domain\Sequence\Entity\Sequence;
use Amelaye\BioPHP\Domain\Sequence\Entity\SubMatrix;
use Amelaye\BioPHP\Domain\Sequence\Service\SequenceManager;
use Amelaye\BioPHP\Domain\Sequence\Service\SequenceMatchManager;
use Amelaye\BioPHP\Domain\Sequence\Builder\SequenceBuilder;
use PHPUnit\Framework\TestCase;

class SequenceMatchManagerTest extends TestCase
{
    private $apiAminoMock;

    private $apiNucleoMock;

    private $apiElementsMock;

    private $sequence;

    private $subMatrix;

    public function setUp()
    {
        $oSubMatrix = new SubMatrix();
        $oSubMatrix->addrule('D', 'E');
        $oSubMatrix->addrule('K', 'R', 'H');
        $oSubMatrix->addrule('X');

        $this->subMatrix = $oSubMatrix;

        $oSequence = new Sequence();
        $oSequence->setMoltype("DNA");

        $sSeqTest = "GGCAGATTCCCCCTAGACCCGCCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGG";
        $sSeqTest.= "GTATAAACAGTGCTGGAGGCTGGCGGGGCAGGCCAGCTGAGTCCTGAGCAGCAGCCCAGCGCAGCCACCGAGACACCATGAGAGCCCTCACACTCCTCGCCCTATTGG";
        $sSeqTest.= "CCCTGGCCGCACTTTGCATCGCTGGCCAGGCAGGTGAGTGCCCCCACCTCCCCTCAGGCCGCATTGCAGTGGGGGCTGAGAGGAGGAAGCACCATGGCCCACCTCTTC";
        $sSeqTest.= "TCACCCCTTTGGCTGGCAGTCCCTTTGCAGTCTAACCACCTTGTTGCAGGCTCAATCCATTTGCCCCAGCTCTGCCCTTGCAGAGGGAGAGGAGGGAAGAGCAAGCTG";
        $sSeqTest.= "CCCGAGACGCAGGGGAAGGAGGATGAGGGCCCTGGGGATGAGCTGGGGTGAACCAGGCTCCCTTTCCTTTGCAGGTGCGAAGCCCAGCGGTGCAGAGTCCAGCAAAGG";
        $sSeqTest.= "TGCAGGTATGAGGATGGACCTGATGGGTTCCTGGACCCTCCCCTCTCACCCTGGTCCCTCAGTCTCATTCCCCCACTCCTGCCACCTCCTGTCTGGCCATCAGGAAGG";
        $sSeqTest.= "CCAGCCTGCTCCCCACCTGATCCTCCCAAACCCAGAGCCACCTGATGCCTGCCCCTCTGCTCCACAGCCTTTGTGTCCAAGCAGGAGGGCAGCGAGGTAGTGAAGAGA";
        $sSeqTest.= "CCCAGGCGCTACCTGTATCAATGGCTGGGGTGAGAGAAAAGGCAGAGCTGGGCCAAGGCCCTGCCTCTCCGGGATGGTCTGTGGGGGAGCTGCAGCAGGGAGTGGCCT";
        $sSeqTest.= "CTCTGGGTTGTGGTGGGGGTACAGGCAGCCTGCCCTGGTGGGCACCCTGGAGCCCCATGTGTAGGGAGAGGAGGGATGGGCATTTTGCACGGGGGCTGATGCCACCAC";
        $sSeqTest.= "GTCGGGTGTCTCAGAGCCCCAGTCCCCTACCCGGATCCCCTGGAGCCCAGGAGGGAGGTGTGTGAGCTCAATCCGGACTGTGACGAGTTGGCTGACCACATCGGCTTT";
        $sSeqTest.= "CAGGAGGCCTATCGGCGCTTCTACGGCCCGGTCTAGGGTGTCGCTCTGCTGGCCTGGCCGGCAACCCCAGTTCTGCTCCTCTCCAGGCACCCTTCTTTCCTCTTCCCC";
        $sSeqTest.= "TTGCCCTTGCCCTGACCTCCCAGCCCTATGGATGTGGGGTCCCCATCATCCCAGCTGCTCCCAAATAAACTCCAGAAG";

        $oSequence->setSequence($sSeqTest);
        $oSequence->setSeqlength(1231);

        $this->sequence = $oSequence;

        /**
         * Mock API
         */
        $clientMock = $this->getMockBuilder('GuzzleHttp\Client')->getMock();
        $serializerMock = \JMS\Serializer\SerializerBuilder::create()
            ->build();

        require 'samples/Aminos.php';

        require 'samples/Nucleotids.php';

        require 'samples/Elements.php';


        $this->apiAminoMock = $this->getMockBuilder(AminoApi::class)
            ->setConstructorArgs([$clientMock, $serializerMock])
            ->setMethods(['getAminos'])
            ->getMock();
        $this->apiAminoMock->method("getAminos")->will($this->returnValue($aAminosObjects));

        $this->apiNucleoMock = $this->getMockBuilder(NucleotidApi::class)
            ->setConstructorArgs([$clientMock, $serializerMock])
            ->setMethods(['getNucleotids'])
            ->getMock();
        $this->apiNucleoMock->method("getNucleotids")->will($this->returnValue($aNucleoObjects));

        $this->apiElementsMock = $this->getMockBuilder(ElementApi::class)
            ->setConstructorArgs([$clientMock, $serializerMock])
            ->setMethods(['getElements', 'getElement'])
            ->getMock();
        $this->apiElementsMock->method("getElements")->will($this->returnValue($aElementsObjects));
        $this->apiElementsMock->method("getElement")->will($this->returnValue($aElementsObjects[5]));
    }

    public function testCompareLetter()
    {
        $sequenceManager = new SequenceManager($this->apiAminoMock, $this->apiNucleoMock, $this->apiElementsMock);
        $sequenceBuilder = new SequenceBuilder($sequenceManager);
        $sequenceBuilder->setSequence($this->sequence);

        $sSeq1 = $sequenceBuilder->subSeq(2,100);
        $sSeq2 = $sequenceBuilder->subSeq(100,100);

        $sequenceMatchManager = new SequenceMatchManager();
        $sequenceMatchManager->setSubMatrix($this->subMatrix);
        $iDistance = $sequenceMatchManager->hamdist($sSeq1, $sSeq2);

        $iExpected = 72;

        $this->assertEquals($iExpected, $iDistance);
    }

    public function testCompareLetterDifferent()
    {
        $sequenceMatchManager = new SequenceMatchManager();
        $sequenceMatchManager->setSubMatrix($this->subMatrix);
        $sCompare1 = $sequenceMatchManager->compareLetter('A', 'T');

        $sExpected = ".";

        $this->assertEquals($sExpected, $sCompare1);
    }

    public function testCompareLetterSame()
    {
        $sequenceMatchManager = new SequenceMatchManager();
        $sequenceMatchManager->setSubMatrix($this->subMatrix);
        $sCompare1 = $sequenceMatchManager->compareLetter('A', 'A');

        $sExpected = "A";

        $this->assertEquals($sExpected, $sCompare1);
    }

    public function testLevdist()
    {
        $sequenceManager = new SequenceManager($this->apiAminoMock, $this->apiNucleoMock, $this->apiElementsMock);
        $sequenceBuilder = new SequenceBuilder($sequenceManager);
        $sequenceBuilder->setSequence($this->sequence);

        $sSeq1 = $sequenceBuilder->subSeq(2,100);
        $sSeq2 = $sequenceBuilder->subSeq(100,100);

        $sequenceMatchManager = new SequenceMatchManager();
        $sequenceMatchManager->setSubMatrix($this->subMatrix);
        $iLevdist = $sequenceMatchManager->levdist($sSeq1, $sSeq2);

        $iExpected = 56;

        $this->assertEquals($iExpected, $iLevdist);
    }

    public function testXlevdist()
    {
        $sequenceManager = new SequenceManager($this->apiAminoMock, $this->apiNucleoMock, $this->apiElementsMock);
        $sequenceBuilder = new SequenceBuilder($sequenceManager);
        $sequenceBuilder->setSequence($this->sequence);

        $sSeq1 = $sequenceBuilder->subSeq(2,100);
        $sSeq2 = $sequenceBuilder->subSeq(100,100);

        $sequenceMatchManager = new SequenceMatchManager();
        $sequenceMatchManager->setSubMatrix($this->subMatrix);
        $iLevdist = $sequenceMatchManager->xlevdist($sSeq1, $sSeq2);;

        $iExpected = 49;

        $this->assertEquals($iExpected, $iLevdist);
    }

    public function testMatch()
    {
        $sequenceManager = new SequenceManager($this->apiAminoMock, $this->apiNucleoMock, $this->apiElementsMock);
        $sequenceBuilder = new SequenceBuilder($sequenceManager);
        $sequenceBuilder->setSequence($this->sequence);

        $sSeq1 = $sequenceBuilder->subSeq(2,100);
        $sSeq2 = $sequenceBuilder->subSeq(100,100);

        $sequenceMatchManager = new SequenceMatchManager();
        $sequenceMatchManager->setSubMatrix($this->subMatrix);
        $sMatch = $sequenceMatchManager->match($sSeq1, $sSeq2);;

        $sExpected = "......C..C..AG.CC.G..C..........C.......CC....C.C...G...G.C....C.C.....G....A...G..CTGG..GC.....G...";

        $this->assertEquals($sExpected, $sMatch);
    }
}