<?php
/**
 * SequenceMatchManager Testing
 * @author Amélie DUVERNET aka Amelaye
 * Freely inspired by BioPHP's project biophp.org
 * Created 14 november 2019
 * Last modified 14 november 2019
 */
namespace Tests\AppBundle\Entity;

use AppBundle\Api\DTO\AminoDTO;
use AppBundle\Entity\Sequencing\Sequence;
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

        $aAminosObjects = [];
        $amino = new AminoDTO();
        $amino->setId('A');
        $amino->setName("Alanine");
        $amino->setName1Letter('A');
        $amino->setName3Letters('Ala');
        $amino->setWeight1(89.09);
        $amino->setWeight2(89.09);
        $amino->setResidueMolWeight(71.07);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('B');
        $amino->setName("Aspartate or asparagine");
        $amino->setName1Letter('B');
        $amino->setName3Letters('N/A');
        $amino->setWeight1(132.12);
        $amino->setWeight2(132.1);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('C');
        $amino->setName("Cysteine");
        $amino->setName1Letter('C');
        $amino->setName3Letters('Cys');
        $amino->setWeight1(121.15);
        $amino->setWeight2(121.15);
        $amino->setResidueMolWeight(103.10);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('D');
        $amino->setName("Aspartic acid");
        $amino->setName1Letter('D');
        $amino->setName3Letters('Asp');
        $amino->setWeight1(133.1);
        $amino->setWeight2(133.1);
        $amino->setResidueMolWeight(115.08);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('E');
        $amino->setName("Glutamic acid");
        $amino->setName1Letter('E');
        $amino->setName3Letters('Glu');
        $amino->setWeight1(147.13);
        $amino->setWeight2(147.13);
        $amino->setResidueMolWeight(129.11);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('F');
        $amino->setName("Phenylalanine");
        $amino->setName1Letter('F');
        $amino->setName3Letters('Phe');
        $amino->setWeight1(165.19);
        $amino->setWeight2(165.19);
        $amino->setResidueMolWeight(147.17);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('G');
        $amino->setName("Glycine");
        $amino->setName1Letter('G');
        $amino->setName3Letters('Gly');
        $amino->setWeight1(75.07);
        $amino->setWeight2(75.07);
        $amino->setResidueMolWeight(57.05);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('H');
        $amino->setName("Histidine");
        $amino->setName1Letter('H');
        $amino->setName3Letters('His');
        $amino->setWeight1(155.16);
        $amino->setWeight2(155.16);
        $amino->setResidueMolWeight(137.14);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('I');
        $amino->setName("Isoleucine");
        $amino->setName1Letter('I');
        $amino->setName3Letters('Ile');
        $amino->setWeight1(131.18);
        $amino->setWeight2(131.18);
        $amino->setResidueMolWeight(113.15);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('K');
        $amino->setName("Lysine");
        $amino->setName1Letter('K');
        $amino->setName3Letters('Lys');
        $amino->setWeight1(146.19);
        $amino->setWeight2(146.19);
        $amino->setResidueMolWeight(128.17);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('L');
        $amino->setName("Leucine");
        $amino->setName1Letter('L');
        $amino->setName3Letters('Leu');
        $amino->setWeight1(131.18);
        $amino->setWeight2(131.18);
        $amino->setResidueMolWeight(113.15);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('M');
        $amino->setName("Methionine");
        $amino->setName1Letter('M');
        $amino->setName3Letters('Met');
        $amino->setWeight1(149.22);
        $amino->setWeight2(149.22);
        $amino->setResidueMolWeight(131.19);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('N');
        $amino->setName("Asparagine");
        $amino->setName1Letter('N');
        $amino->setName3Letters('Asn');
        $amino->setWeight1(132.12);
        $amino->setWeight2(132.12);
        $amino->setResidueMolWeight(114.08);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('O');
        $amino->setName("Pyrrolysine");
        $amino->setName1Letter('O');
        $amino->setName3Letters('Pyr');
        $amino->setWeight1(255.31);
        $amino->setWeight2(255.31);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('P');
        $amino->setName("Proline");
        $amino->setName1Letter('P');
        $amino->setName3Letters('Pro');
        $amino->setWeight1(115.13);
        $amino->setWeight2(115.13);
        $amino->setResidueMolWeight(97.11);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('Q');
        $amino->setName("Glutamine");
        $amino->setName1Letter('Q');
        $amino->setName3Letters('Gin');
        $amino->setWeight1(146.15);
        $amino->setWeight2(146.15);
        $amino->setResidueMolWeight(128.13);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('R');
        $amino->setName("Arginine");
        $amino->setName1Letter('R');
        $amino->setName3Letters('Arg');
        $amino->setWeight1(174.21);
        $amino->setWeight2(174.21);
        $amino->setResidueMolWeight(156.18);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('S');
        $amino->setName("Serine");
        $amino->setName1Letter('S');
        $amino->setName3Letters('Ser');
        $amino->setWeight1(105.09);
        $amino->setWeight2(105.09);
        $amino->setResidueMolWeight(87.07);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('T');
        $amino->setName("Threonine");
        $amino->setName1Letter('T');
        $amino->setName3Letters('Thr');
        $amino->setWeight1(119.12);
        $amino->setWeight2(119.12);
        $amino->setResidueMolWeight(101.10);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('U');
        $amino->setName("Selenocysteine");
        $amino->setName1Letter('U');
        $amino->setName3Letters('Sec');
        $amino->setWeight1(168.05);
        $amino->setWeight2(168.05);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('V');
        $amino->setName("Valine");
        $amino->setName1Letter('V');
        $amino->setName3Letters('Val');
        $amino->setWeight1(117.15);
        $amino->setWeight2(117.15);
        $amino->setResidueMolWeight(99.13);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('W');
        $amino->setName("Tryptophan");
        $amino->setName1Letter('W');
        $amino->setName3Letters('Trp');
        $amino->setWeight1(204.22);
        $amino->setWeight2(204.22);
        $amino->setResidueMolWeight(186.20);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('Y');
        $amino->setName("Tyrosine");
        $amino->setName1Letter('Y');
        $amino->setName3Letters('Tyr');
        $amino->setWeight1(181.19);
        $amino->setWeight2(181.19);
        $amino->setResidueMolWeight(163.17);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('Z');
        $amino->setName("Glutamate or glutamine");
        $amino->setName1Letter('Z');
        $amino->setName3Letters('N/A');
        $amino->setWeight1(75.07);
        $amino->setWeight2(204.22);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('X');
        $amino->setName("Any");
        $amino->setName1Letter('X');
        $amino->setName3Letters('XXX');
        $amino->setWeight1(146.15);
        $amino->setWeight2(146.15);
        $amino->setResidueMolWeight(114.822);
        $aAminosObjects[] = $amino;

        $amino = new AminoDTO();
        $amino->setId('*');
        $amino->setName("STOP");
        $amino->setName1Letter('*');
        $amino->setName3Letters('STP');
        $amino->setWeight1(0);
        $amino->setWeight2(0);
        $aAminosObjects[] = $amino;


        $this->apiAminoMock = $this->getMockBuilder('AppBundle\Api\AminoApi')
            ->setConstructorArgs([$clientMock, $serializerMock])
            ->setMethods(['getAminos'])
            ->getMock();
        $this->apiAminoMock->method("getAminos")->will($this->returnValue($aAminosObjects));
    }

    public function testCompareLetter()
    {
        $sequenceManager = new SequenceManager($this->apiMock, $this->apiAminoMock);
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
        $sequenceManager = new SequenceManager($this->apiMock, $this->apiAminoMock);
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
        $sequenceManager = new SequenceManager($this->apiMock, $this->apiAminoMock);
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
        $sequenceManager = new SequenceManager($this->apiMock, $this->apiAminoMock);
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