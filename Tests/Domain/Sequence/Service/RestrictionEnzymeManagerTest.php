<?php
/**
 * RestrictionEnzymeManager Testing
 * @author Amélie DUVERNET aka Amelaye
 * Freely inspired by BioPHP's project biophp.org
 * Created 14 november 2019
 * Last modified 14 november 2019
 */
namespace Tests\Domain\Sequence\Service;

use Amelaye\BioPHP\Api\AminoApi;
use Amelaye\BioPHP\Api\ElementApi;
use Amelaye\BioPHP\Api\NucleotidApi;
use Amelaye\BioPHP\Api\TypeIIEndonucleaseApi;
use Amelaye\BioPHP\Domain\Sequence\Builder\SequenceBuilder;
use Amelaye\BioPHP\Domain\Sequence\Entity\Enzyme;
use Amelaye\BioPHP\Domain\Sequence\Entity\Sequence;
use Amelaye\BioPHP\Domain\Sequence\Service\RestrictionEnzymeManager;
use Amelaye\BioPHP\Domain\Sequence\Service\SequenceManager;
use PHPUnit\Framework\TestCase;

class RestrictionEnzymeManagerTest extends TestCase
{
    private $sequence;

    private $apiAminoMock;

    private $apiNucleoMock;

    private $apiElementsMock;

    private $apiNucleolMock;

    public function setUp(): void
    {
        /**
         * Mock API
         */
        $clientMock = $this->getMockBuilder('GuzzleHttp\Client')->getMock();
        $serializerMock = \JMS\Serializer\SerializerBuilder::create()
            ->build();

        require 'samples/Aminos.php';

        require 'samples/Nucleotids.php';

        require 'samples/Elements.php';

        require 'samples/TypeIIEndonucleases.php';


        $this->apiAminoMock = $this->getMockBuilder(AminoApi::class)
            ->setConstructorArgs([$clientMock, $serializerMock])
            ->onlyMethods(['getAminos'])
            ->getMock();
        $this->apiAminoMock->method("getAminos")->willReturn($aAminosObjects);

        $this->apiNucleoMock = $this->getMockBuilder(NucleotidApi::class)
            ->setConstructorArgs([$clientMock, $serializerMock])
            ->onlyMethods(['getNucleotids'])
            ->getMock();
        $this->apiNucleoMock->method("getNucleotids")->willReturn($aNucleoObjects);

        $this->apiElementsMock = $this->getMockBuilder(ElementApi::class)
            ->setConstructorArgs([$clientMock, $serializerMock])
            ->onlyMethods(['getElements', 'getElement'])
            ->getMock();
        $this->apiElementsMock->method("getElements")->willReturn($aElementsObjects);
        $this->apiElementsMock->method("getElement")->willReturn($aElementsObjects[5]);

        $this->apiNucleolMock = $this->getMockBuilder(TypeIIEndonucleaseApi::class)
            ->setConstructorArgs([$clientMock, $serializerMock])
            ->onlyMethods(['getTypeIIEndonucleases'])
            ->getMock();
        $this->apiNucleolMock->method("getTypeIIEndonucleases")->willReturn($aTypeIIEndonucleases);

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
        $sequenceBuilder = new SequenceBuilder($sequenceManager);
        $sequenceBuilder->setSequence($this->sequence);

        $restrictionEnzymeManager = new RestrictionEnzymeManager($this->apiNucleolMock, new Enzyme());
        $restrictionEnzymeManager->setEnzyme();

        $restrictionEnzymeManager->setSequenceManager($sequenceBuilder);
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
        $sequenceBuilder = new SequenceBuilder($sequenceManager);
        $sequenceBuilder->setSequence($this->sequence);

        $restrictionEnzymeManager = new RestrictionEnzymeManager($this->apiNucleolMock, new Enzyme());
        $restrictionEnzymeManager->setEnzyme();

        $restrictionEnzymeManager->setSequenceManager($sequenceBuilder);
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
        $sequenceBuilder = new SequenceBuilder($sequenceManager);
        $sequenceBuilder->setSequence($this->sequence);

        $restrictionEnzymeManager = new RestrictionEnzymeManager($this->apiNucleolMock, new Enzyme());
        $restrictionEnzymeManager->setEnzyme();

        $restrictionEnzymeManager->setSequenceManager($sequenceBuilder);
        $restrictionEnzymeManager->parseEnzyme('AatI', 'AGGCCT', 0, "inner");

        $list = $restrictionEnzymeManager->findRestEn("AGGCCT");
        $aExpected = ["AatI"];

        $this->assertEquals($aExpected, $list);
    }

    public function testFindRestEnFetchCutposAndPlen()
    {
        $sequenceManager = new SequenceManager($this->apiAminoMock, $this->apiNucleoMock, $this->apiElementsMock);
        $sequenceBuilder = new SequenceBuilder($sequenceManager);
        $sequenceBuilder->setSequence($this->sequence);

        $restrictionEnzymeManager = new RestrictionEnzymeManager($this->apiNucleolMock, new Enzyme());
        $restrictionEnzymeManager->setEnzyme();

        $restrictionEnzymeManager->setSequenceManager($sequenceBuilder);
        $list5 = $restrictionEnzymeManager->findRestEn(null,3, 6);

        $aExpected = [
          0 => "AatI",
          1 => "Acc16I",
          2 => "AccBSI",
          3 => "AcvI",
          4 => "AfeI",
          5 => "AjiI",
          6 => "AssI",
          7 => "BalI",
          8 => "BmiI",
          9 => "BsaAI",
          10 => "Bsp68I",
          11 => "BssNAI",
          12 => "BstC8I",
          13 => "BstSNI",
          14 => "DinI",
          15 => "DraI",
          16 => "Ecl136II",
          17 => "Eco32I",
          18 => "EgeI",
          19 => "HincII",
          20 => "HpaI",
          21 => "Hpy166II",
          22 => "MspA1I",
          23 => "NaeI",
          24 => "PsiI",
          25 => "PvuII",
          26 => "SmaI",
          27 => "SspI",
          28 => "ZraI",
        ];
        $this->assertEquals($aExpected, $list5);
    }

    public function testFindRestEnFetchLength()
    {
        $sequenceManager = new SequenceManager($this->apiAminoMock, $this->apiNucleoMock, $this->apiElementsMock);
        $sequenceBuilder = new SequenceBuilder($sequenceManager);
        $sequenceBuilder->setSequence($this->sequence);

        $restrictionEnzymeManager = new RestrictionEnzymeManager($this->apiNucleolMock, new Enzyme());
        $restrictionEnzymeManager->setEnzyme();

        $restrictionEnzymeManager->setSequenceManager($sequenceBuilder);
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
          60 => "BspOI",
          61 => "BssNAI",
          62 => "BssT1I",
          63 => "BstC8I",
          64 => "BstDSI",
          65 => "BstNSI",
          66 => "BstSNI",
          67 => "BstX2I",
          68 => "Cfr42I",
          69 => "Cfr9I",
          70 => "CfrI",
          71 => "DinI",
          72 => "DraI",
          73 => "Ecl136II",
          74 => "Eco32I",
          75 => "EcoRI",
          76 => "EcoT22I",
          77 => "EgeI",
          78 => "FauNDI",
          79 => "GsaI",
          80 => "HincII",
          81 => "HindIII",
          82 => "HpaI",
          83 => "Hpy166II",
          84 => "Hpy188III",
          85 => "KasI",
          86 => "KpnI",
          87 => "KroI",
          88 => "MfeI",
          89 => "MluI",
          90 => "Mly113I",
          91 => "MspA1I",
          92 => "NaeI",
          93 => "PaeR7I",
          94 => "PsiI",
          95 => "Psp124BI",
          96 => "PvuII",
          97 => "SalI",
          98 => "SmaI",
          99 => "SmlI",
          100 => "SspDI",
          101 => "SspI",
          102 => "TatI",
          103 => "XbaI",
          104 => "ZraI",
        ];
        $this->assertEquals($aExpected, $list4);
    }

    public function testFindRestEnFetchCutpos()
    {
        $sequenceManager = new SequenceManager($this->apiAminoMock, $this->apiNucleoMock, $this->apiElementsMock);
        $sequenceBuilder = new SequenceBuilder($sequenceManager);
        $sequenceBuilder->setSequence($this->sequence);

        $restrictionEnzymeManager = new RestrictionEnzymeManager($this->apiNucleolMock, new Enzyme());
        $restrictionEnzymeManager->setEnzyme();

        $restrictionEnzymeManager->setSequenceManager($sequenceBuilder);
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
          10 => "BlsI",
          11 => "BmiI",
          12 => "BsaAI",
          13 => "Bsp68I",
          14 => "BssNAI",
          15 => "Bst4CI",
          16 => "BstC8I",
          17 => "BstKTI",
          18 => "BstSNI",
          19 => "DinI",
          20 => "DraI",
          21 => "Ecl136II",
          22 => "Eco32I",
          23 => "EgeI",
          24 => "HincII",
          25 => "HpaI",
          26 => "Hpy166II",
          27 => "Hpy188I",
          28 => "MspA1I",
          29 => "NaeI",
          30 => "PsiI",
          31 => "PvuII",
          32 => "SmaI",
          33 => "SspI",
          34 => "ZraI",
        ];
        $this->assertEquals($aExpected, $list3);
    }

    public function testFindRestEnFetchPatternAndCutpos()
    {
        $sequenceManager = new SequenceManager($this->apiAminoMock, $this->apiNucleoMock, $this->apiElementsMock);
        $sequenceBuilder = new SequenceBuilder($sequenceManager);
        $sequenceBuilder->setSequence($this->sequence);

        $restrictionEnzymeManager = new RestrictionEnzymeManager($this->apiNucleolMock, new Enzyme());
        $restrictionEnzymeManager->setEnzyme();

        $restrictionEnzymeManager->setSequenceManager($sequenceBuilder);
        $list2 = $restrictionEnzymeManager->findRestEn("AGGCCT",3);

        $aExpected = [
          0 => "AatI"
        ];
        $this->assertEquals($aExpected, $list2);
    }

    public function testFindRestEnFetchPatternOnly()
    {
        $sequenceManager = new SequenceManager($this->apiAminoMock, $this->apiNucleoMock, $this->apiElementsMock);
        $sequenceBuilder = new SequenceBuilder($sequenceManager);
        $sequenceBuilder->setSequence($this->sequence);

        $restrictionEnzymeManager = new RestrictionEnzymeManager($this->apiNucleolMock, new Enzyme());
        $restrictionEnzymeManager->setEnzyme();

        $restrictionEnzymeManager->setSequenceManager($sequenceBuilder);
        $list = $restrictionEnzymeManager->findRestEn("AGGCCT");

        $aExpected = [
            0 => "AatI"
        ];
        $this->assertEquals($aExpected, $list);
    }
}