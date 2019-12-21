<?php
/**
 * RestrictionEnzymeManager Testing
 * @author Amélie DUVERNET aka Amelaye
 * Freely inspired by BioPHP's project biophp.org
 * Created 14 november 2019
 * Last modified 14 november 2019
 */
namespace Tests\AppBundle\Service;

use AppBundle\Entity\Enzyme;
use AppBundle\Entity\Sequencing\Sequence;
use AppBundle\Service\RestrictionEnzymeManager;
use AppBundle\Service\SequenceManager;
use PHPUnit\Framework\TestCase;

class RestrictionEnzymeManagerTest extends TestCase
{
    private $sequence;

    private $apiAminoMock;

    private $apiNucleoMock;

    private $apiElementsMock;

    private $apiNucleolMock;

    public function setUp()
    {
        /**
         * Mock API
         */
        $clientMock = $this->getMockBuilder('GuzzleHttp\Client')->getMock();
        $serializerMock = $this->getMockBuilder('JMS\Serializer\Serializer')
            ->disableOriginalConstructor()
            ->getMock();

        require 'samples/Aminos.php';

        require 'samples/Nucleotids.php';

        require 'samples/Elements.php';

        require 'samples/TypeIIEndonucleases.php';


        $this->apiAminoMock = $this->getMockBuilder('AppBundle\Api\AminoApi')
            ->setConstructorArgs([$clientMock, $serializerMock])
            ->setMethods(['getAminos'])
            ->getMock();
        $this->apiAminoMock->method("getAminos")->will($this->returnValue($aAminosObjects));

        $this->apiNucleoMock = $this->getMockBuilder('AppBundle\Api\NucleotidApi')
            ->setConstructorArgs([$clientMock, $serializerMock])
            ->setMethods(['getNucleotids'])
            ->getMock();
        $this->apiNucleoMock->method("getNucleotids")->will($this->returnValue($aNucleoObjects));

        $this->apiElementsMock = $this->getMockBuilder('AppBundle\Api\ElementApi')
            ->setConstructorArgs([$clientMock, $serializerMock])
            ->setMethods(['getElements', 'getElement'])
            ->getMock();
        $this->apiElementsMock->method("getElements")->will($this->returnValue($aElementsObjects));
        $this->apiElementsMock->method("getElement")->will($this->returnValue($aElementsObjects[5]));

        $this->apiNucleolMock = $this->getMockBuilder('AppBundle\Api\TypeIIEndonucleaseApi')
            ->setConstructorArgs([$clientMock, $serializerMock])
            ->setMethods(['getTypeIIEndonucleases'])
            ->getMock();
        $this->apiNucleolMock->method("getTypeIIEndonucleases")->will($this->returnValue($aTypeIIEndonucleases));

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
    }

    public function testParseEnzymeInner()
    {
        $restrictionEnzymeManager = new RestrictionEnzymeManager($this->apiNucleolMock, new Enzyme());
        $restrictionEnzymeManager->setEnzyme();

        $restrictionEnzymeManager->parseEnzyme('AatI', 'AGGCCT', 0, "inner");
        $oEnzyme = $restrictionEnzymeManager->getEnzyme();

        $oExpected = new Enzyme();
        $oExpected->setName("AatI");
        $oExpected->setPattern("AGGCCT");
        $oExpected->setCutpos(3);
        $oExpected->setLength(6);

        $this->assertEquals($oExpected, $oEnzyme);
    }

    public function testParseEnzymeCustom()
    {
        $restrictionEnzymeManager = new RestrictionEnzymeManager($this->apiNucleolMock, new Enzyme());
        $restrictionEnzymeManager->setEnzyme();

        $restrictionEnzymeManager->parseEnzyme('AatI', 'AGGCCT', 0, "custom");
        $oEnzyme = $restrictionEnzymeManager->getEnzyme();

        $oExpected = new Enzyme();
        $oExpected->setName("AatI");
        $oExpected->setPattern("AGGCCT");
        $oExpected->setCutpos(0);
        $oExpected->setLength(6);

        $this->assertEquals($oExpected, $oEnzyme);
    }

    public function testCutSeqPatposo()
    {
        $sequenceManager = new SequenceManager($this->apiAminoMock, $this->apiNucleoMock, $this->apiElementsMock);
        $sequenceManager->setSequence($this->sequence);

        $restrictionEnzymeManager = new RestrictionEnzymeManager($this->apiNucleolMock, new Enzyme());
        $restrictionEnzymeManager->setEnzyme();

        $restrictionEnzymeManager->setSequenceManager($sequenceManager);
        $restrictionEnzymeManager->parseEnzyme('AatI', 'AGGCCT', 0, "inner");

        $cutseq = $restrictionEnzymeManager->cutSeq();

        $seq1 = "GGCAGATTCCCCCTAGACCCGCCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTGGAGGCTGGCGG";
        $seq1.= "GGCAGGCCAGCTGAGTCCTGAGCAGCAGCCCAGCGCAGCCACCGAGACACCATGAGAGCCCTCACACTCCTCGCCCTATTGGCCCTGGCCGCACTTTGCA";
        $seq1.= "TCGCTGGCCAGGCAGGTGAGTGCCCCCACCTCCCCTCAGGCCGCATTGCAGTGGGGGCTGAGAGGAGGAAGCACCATGGCCCACCTCTTCTCACCCCTTT";
        $seq1.= "GGCTGGCAGTCCCTTTGCAGTCTAACCACCTTGTTGCAGGCTCAATCCATTTGCCCCAGCTCTGCCCTTGCAGAGGGAGAGGAGGGAAGAGCAAGCTGCC";
        $seq1.= "CGAGACGCAGGGGAAGGAGGATGAGGGCCCTGGGGATGAGCTGGGGTGAACCAGGCTCCCTTTCCTTTGCAGGTGCGAAGCCCAGCGGTGCAGAGTCCAG";
        $seq1.= "CAAAGGTGCAGGTATGAGGATGGACCTGATGGGTTCCTGGACCCTCCCCTCTCACCCTGGTCCCTCAGTCTCATTCCCCCACTCCTGCCACCTCCTGTCT";
        $seq1.= "GGCCATCAGGAAGGCCAGCCTGCTCCCCACCTGATCCTCCCAAACCCAGAGCCACCTGATGCCTGCCCCTCTGCTCCACAGCCTTTGTGTCCAAGCAGGA";
        $seq1.= "GGGCAGCGAGGTAGTGAAGAGACCCAGGCGCTACCTGTATCAATGGCTGGGGTGAGAGAAAAGGCAGAGCTGGGCCAAGGCCCTGCCTCTCCGGGATGGT";
        $seq1.= "CTGTGGGGGAGCTGCAGCAGGGAGTGGCCTCTCTGGGTTGTGGTGGGGGTACAGGCAGCCTGCCCTGGTGGGCACCCTGGAGCCCCATGTGTAGGGAGAG";
        $seq1.= "GAGGGATGGGCATTTTGCACGGGGGCTGATGCCACCACGTCGGGTGTCTCAGAGCCCCAGTCCCCTACCCGGATCCCCTGGAGCCCAGGAGGGAGGTGTG";
        $seq1.= "TGAGCTCAATCCGGACTGTGACGAGTTGGCTGACCACATCGGCTTTCAGGAGG";

        $seq2 =  "CCTATCGGCGCTTCTACGGCCCGGTCTAGGGTGTCGCTCTGCTGGCCTGGCCGGCAACCCCAGTTCTGCTCCTCTCCAGGCACCCTTCTTTCCTCTTC";
        $seq2.= "CCCTTGCCCTTGCCCTGACCTCCCAGCCCTATGGATGTGGGGTCCCCATCATCCCAGCTGCTCCCAAATAAACTCCAGAAG";

        $aExpected = array($seq1, $seq2);

        $this->assertEquals($aExpected, $cutseq);
    }

    public function testCutSeqPatpos()
    {
        $sequenceManager = new SequenceManager($this->apiAminoMock, $this->apiNucleoMock, $this->apiElementsMock);
        $sequenceManager->setSequence($this->sequence);

        $restrictionEnzymeManager = new RestrictionEnzymeManager($this->apiNucleolMock, new Enzyme());
        $restrictionEnzymeManager->setEnzyme();

        $restrictionEnzymeManager->setSequenceManager($sequenceManager);
        $restrictionEnzymeManager->parseEnzyme('AatI', 'AGGCCT', 0, "inner");

        $cutseq = $restrictionEnzymeManager->cutSeq("O");

        $seq1 = "GGCAGATTCCCCCTAGACCCGCCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTGGAGGCTGGCGG";
        $seq1.= "GGCAGGCCAGCTGAGTCCTGAGCAGCAGCCCAGCGCAGCCACCGAGACACCATGAGAGCCCTCACACTCCTCGCCCTATTGGCCCTGGCCGCACTTTGCA";
        $seq1.= "TCGCTGGCCAGGCAGGTGAGTGCCCCCACCTCCCCTCAGGCCGCATTGCAGTGGGGGCTGAGAGGAGGAAGCACCATGGCCCACCTCTTCTCACCCCTTT";
        $seq1.= "GGCTGGCAGTCCCTTTGCAGTCTAACCACCTTGTTGCAGGCTCAATCCATTTGCCCCAGCTCTGCCCTTGCAGAGGGAGAGGAGGGAAGAGCAAGCTGCC";
        $seq1.= "CGAGACGCAGGGGAAGGAGGATGAGGGCCCTGGGGATGAGCTGGGGTGAACCAGGCTCCCTTTCCTTTGCAGGTGCGAAGCCCAGCGGTGCAGAGTCCAG";
        $seq1.= "CAAAGGTGCAGGTATGAGGATGGACCTGATGGGTTCCTGGACCCTCCCCTCTCACCCTGGTCCCTCAGTCTCATTCCCCCACTCCTGCCACCTCCTGTCT";
        $seq1.= "GGCCATCAGGAAGGCCAGCCTGCTCCCCACCTGATCCTCCCAAACCCAGAGCCACCTGATGCCTGCCCCTCTGCTCCACAGCCTTTGTGTCCAAGCAGGA";
        $seq1.= "GGGCAGCGAGGTAGTGAAGAGACCCAGGCGCTACCTGTATCAATGGCTGGGGTGAGAGAAAAGGCAGAGCTGGGCCAAGGCCCTGCCTCTCCGGGATGGT";
        $seq1.= "CTGTGGGGGAGCTGCAGCAGGGAGTGGCCTCTCTGGGTTGTGGTGGGGGTACAGGCAGCCTGCCCTGGTGGGCACCCTGGAGCCCCATGTGTAGGGAGAG";
        $seq1.= "GAGGGATGGGCATTTTGCACGGGGGCTGATGCCACCACGTCGGGTGTCTCAGAGCCCCAGTCCCCTACCCGGATCCCCTGGAGCCCAGGAGGGAGGTGTG";
        $seq1.= "TGAGCTCAATCCGGACTGTGACGAGTTGGCTGACCACATCGGCTTTCAGGAGG";

        $seq2 =  "CCTATCGGCGCTTCTACGGCCCGGTCTAGGGTGTCGCTCTGCTGGCCTGGCCGGCAACCCCAGTTCTGCTCCTCTCCAGGCACCCTTCTTTCCTCTTC";
        $seq2.= "CCCTTGCCCTTGCCCTGACCTCCCAGCCCTATGGATGTGGGGTCCCCATCATCCCAGCTGCTCCCAAATAAACTCCAGAAG";

        $aExpected = array($seq1, $seq2);

        $this->assertEquals($aExpected, $cutseq);
    }

    public function testFindRestEn()
    {
        $sequenceManager = new SequenceManager($this->apiAminoMock, $this->apiNucleoMock, $this->apiElementsMock);
        $sequenceManager->setSequence($this->sequence);

        $restrictionEnzymeManager = new RestrictionEnzymeManager($this->apiNucleolMock, new Enzyme());
        $restrictionEnzymeManager->setEnzyme();

        $restrictionEnzymeManager->setSequenceManager($sequenceManager);
        $restrictionEnzymeManager->parseEnzyme('AatI', 'AGGCCT', 0, "inner");

        $list = $restrictionEnzymeManager->findRestEn("AGGCCT");
        $aExpected = ["AatI"];

        $this->assertEquals($aExpected, $list);
    }

    public function testFindRestEnFetchCutposAndPlen()
    {
        $sequenceManager = new SequenceManager($this->apiAminoMock, $this->apiNucleoMock, $this->apiElementsMock);
        $sequenceManager->setSequence($this->sequence);

        $restrictionEnzymeManager = new RestrictionEnzymeManager($this->apiNucleolMock, new Enzyme());
        $restrictionEnzymeManager->setEnzyme();

        $restrictionEnzymeManager->setSequenceManager($sequenceManager);
        $list5 = $restrictionEnzymeManager->findRestEn(null,3, 6);

        $aExpected = [];
        $this->assertEquals($aExpected, $list5);
    }

    public function testFindRestEnFetchLength()
    {
        $sequenceManager = new SequenceManager($this->apiAminoMock, $this->apiNucleoMock, $this->apiElementsMock);
        $sequenceManager->setSequence($this->sequence);

        $restrictionEnzymeManager = new RestrictionEnzymeManager($this->apiNucleolMock, new Enzyme());
        $restrictionEnzymeManager->setEnzyme();

        $restrictionEnzymeManager->setSequenceManager($sequenceManager);
        $list4 = $restrictionEnzymeManager->findRestEn(null,null, 6); // fetchLength

        $aExpected = [
          0 => "AatI",
          1 => "AatII",
          2 => "Acc16I",
          3 => "Acc65I",
          4 => "AccB1I",
          5 => "AccBSI",
          6 => "AccI",
          7 => "AccIII",
          8 => "AclI",
          9 => "AcoI",
          10 => "AcsI",
          11 => "AcvI",
          12 => "AcyI",
          13 => "AfeI",
          14 => "AflII",
          15 => "AflIII",
          16 => "AgeI",
          17 => "AhlI",
          18 => "AjiI",
          19 => "Alw21I",
          20 => "Alw44I",
          21 => "Ama87I",
          22 => "ApaI",
          23 => "AseI",
          24 => "AspA2I",
          25 => "AssI",
          26 => "AsuII",
          27 => "AsuNHI",
          28 => "BaeGI",
          29 => "BalI",
          30 => "BamHI",
          31 => "BanII",
          32 => "BanIII",
          33 => "BauI",
          34 => "BbeI",
          35 => "BbuI",
          36 => "BclI",
          37 => "BfmI",
          38 => "BfoI",
          39 => "BglII",
          40 => "BmiI",
          41 => "BmtI",
          42 => "BpvUI",
          43 => "BsaAI",
          44 => "BsaJI",
          45 => "BsaWI",
          46 => "Bse118I",
          47 => "BsePI",
          48 => "BseX3I",
          49 => "BseYI",
          50 => "Bsh1285I",
          51 => "BsiWI",
          52 => "Bsp120I",
          53 => "Bsp1286I",
          54 => "Bsp1407I",
          55 => "Bsp19I",
          56 => "Bsp68I",
          57 => "BspHI",
          58 => "BspLU11I",
          59 => "BspMAI",
          60 => "BssNAI",
          61 => "BssT1I",
          62 => "BstC8I",
          63 => "BstDSI",
          64 => "BstNSI",
          65 => "BstSNI",
          66 => "BstX2I",
          67 => "Cfr42I",
          68 => "Cfr9I",
          69 => "CfrI",
          70 => "DinI",
          71 => "DraI",
          72 => "Ecl136II",
          73 => "Eco32I",
          74 => "EcoRI",
          75 => "EcoT22I",
          76 => "EgeI",
          77 => "FauNDI",
          78 => "GsaI",
          79 => "HincII",
          80 => "HindIII",
          81 => "HpaI",
          82 => "Hpy166II",
          83 => "Hpy188III",
          84 => "KasI",
          85 => "KpnI",
          86 => "KroI",
          87 => "MfeI",
          88 => "MluI",
          89 => "MspA1I",
          90 => "NaeI",
          91 => "PaeR7I",
          92 => "PsiI",
          93 => "Psp124BI",
          94 => "PvuII",
          95 => "SalI",
          96 => "SmaI",
          97 => "SmlI",
          98 => "SspI",
          99 => "TatI",
          100 => "XbaI",
          101 => "ZraI",
        ];
        $this->assertEquals($aExpected, $list4);
    }

    public function testFindRestEnFetchCutpos()
    {
        $sequenceManager = new SequenceManager($this->apiAminoMock, $this->apiNucleoMock, $this->apiElementsMock);
        $sequenceManager->setSequence($this->sequence);

        $restrictionEnzymeManager = new RestrictionEnzymeManager($this->apiNucleolMock, new Enzyme());
        $restrictionEnzymeManager->setEnzyme();

        $restrictionEnzymeManager->setSequenceManager($sequenceManager);
        $list3 = $restrictionEnzymeManager->findRestEn(null,3); // fetchCutpos

        $aExpected = [
          0 => "AatI",
          1 => "Acc16I",
          2 => "AccBSI",
          3 => "AcvI",
          4 => "AfeI",
          5 => "AgSI",
          6 => "AjiI",
          7 => "AspLEI",
          8 => "AssI",
          9 => "BalI",
          10 => "BmiI",
          11 => "BsaAI",
          12 => "Bsp68I",
          13 => "BssNAI",
          14 => "Bst4CI",
          15 => "BstC8I",
          16 => "BstKTI",
          17 => "BstSNI",
          18 => "DraI",
          19 => "Ecl136II",
          20 => "Eco32I",
          21 => "EgeI",
          22 => "HincII",
          23 => "HpaI",
          24 => "Hpy166II",
          25 => "Hpy188I",
          26 => "MspA1I",
          27 => "NaeI",
          28 => "PsiI",
          29 => "PvuII",
          30 => "SmaI",
          31 => "SspI",
          32 => "ZraI",
        ];
        $this->assertEquals($aExpected, $list3);
    }

    public function testFindRestEnFetchPatternAndCutpos()
    {
        $sequenceManager = new SequenceManager($this->apiAminoMock, $this->apiNucleoMock, $this->apiElementsMock);
        $sequenceManager->setSequence($this->sequence);

        $restrictionEnzymeManager = new RestrictionEnzymeManager($this->apiNucleolMock, new Enzyme());
        $restrictionEnzymeManager->setEnzyme();

        $restrictionEnzymeManager->setSequenceManager($sequenceManager);
        $list2 = $restrictionEnzymeManager->findRestEn("AGGCCT",3);

        $aExpected = [
          0 => "AatI"
        ];
        $this->assertEquals($aExpected, $list2);
    }

    public function testFindRestEnFetchPatternOnly()
    {
        $sequenceManager = new SequenceManager($this->apiAminoMock, $this->apiNucleoMock, $this->apiElementsMock);
        $sequenceManager->setSequence($this->sequence);

        $restrictionEnzymeManager = new RestrictionEnzymeManager($this->apiNucleolMock, new Enzyme());
        $restrictionEnzymeManager->setEnzyme();

        $restrictionEnzymeManager->setSequenceManager($sequenceManager);
        $list = $restrictionEnzymeManager->findRestEn("AGGCCT");

        $aExpected = [
            0 => "AatI"
        ];
        $this->assertEquals($aExpected, $list);
    }
}