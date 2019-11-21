<?php
/**
 * SequenceMatchManager Testing
 * @author Amélie DUVERNET aka Amelaye
 * Freely inspired by BioPHP's project biophp.org
 * Created 14 november 2019
 * Last modified 14 november 2019
 */
namespace Tests\AppBundle\Entity;

use AppBundle\Entity\Sequence;
use AppBundle\Entity\SubMatrix;
use AppBundle\Service\SequenceManager;
use AppBundle\Service\SequenceMatchManager;
use PHPUnit\Framework\TestCase;

class SequenceMatchManagerTest extends TestCase
{
    private $apiMock;

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

        $aElements = [
            "carbone" => 12.01,
            "oxygene" => 16,
            "nitrate" => 14.01,
            "hydrogene" => 1.01,
            "phosphore" => 30.97,
            "water" => 18.015
        ];

        /**
         * Mock API
         */
        $clientMock = $this->getMockBuilder('GuzzleHttp\Client')->getMock();
        $serializerMock = $this->getMockBuilder('JMS\Serializer\Serializer')
            ->disableOriginalConstructor()
            ->getMock();
        $this->apiMock = $this->getMockBuilder('AppBundle\Api\Bioapi')
            ->setConstructorArgs([$clientMock, $serializerMock])
            ->setMethods(['getElements'])
            ->getMock();
        $this->apiMock->method("getElements")->will($this->returnValue($aElements));
    }

    public function testCompareLetter()
    {
        $sequenceManager = new SequenceManager($this->apiMock);
        $sequenceManager->setSequence($this->sequence);

        $sSeq1 = $sequenceManager->subSeq(2,100);
        $sSeq2 = $sequenceManager->subSeq(100,100);

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
        $sequenceManager = new SequenceManager($this->apiMock);
        $sequenceManager->setSequence($this->sequence);
        $sSeq1 = $sequenceManager->subSeq(2,100);
        $sSeq2 = $sequenceManager->subSeq(100,100);

        $sequenceMatchManager = new SequenceMatchManager();
        $sequenceMatchManager->setSubMatrix($this->subMatrix);
        $iLevdist = $sequenceMatchManager->levdist($sSeq1, $sSeq2);

        $iExpected = 56;

        $this->assertEquals($iExpected, $iLevdist);
    }

    public function testXlevdist()
    {
        $sequenceManager = new SequenceManager($this->apiMock);
        $sequenceManager->setSequence($this->sequence);
        $sSeq1 = $sequenceManager->subSeq(2,100);
        $sSeq2 = $sequenceManager->subSeq(100,100);

        $sequenceMatchManager = new SequenceMatchManager();
        $sequenceMatchManager->setSubMatrix($this->subMatrix);
        $iLevdist = $sequenceMatchManager->xlevdist($sSeq1, $sSeq2);;

        $iExpected = 49;

        $this->assertEquals($iExpected, $iLevdist);
    }

    public function testMatch()
    {
        $sequenceManager = new SequenceManager($this->apiMock);
        $sequenceManager->setSequence($this->sequence);
        $sSeq1 = $sequenceManager->subSeq(2,100);
        $sSeq2 = $sequenceManager->subSeq(100,100);

        $sequenceMatchManager = new SequenceMatchManager();
        $sequenceMatchManager->setSubMatrix($this->subMatrix);
        $sMatch = $sequenceMatchManager->match($sSeq1, $sSeq2);;

        $sExpected = "......C..C..AG.CC.G..C..........C.......CC....C.C...G...G.C....C.C.....G....A...G..CTGG..GC.....G...";

        $this->assertEquals($sExpected, $sMatch);
    }
}