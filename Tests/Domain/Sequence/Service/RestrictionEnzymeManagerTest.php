<?php
/**
 * RestrictionEnzymeManager Testing
 * @author Amélie DUVERNET aka Amelaye
 * Freely inspired by BioPHP's project biophp.org
 * Created 14 november 2019
 * Last modified 7 October 2026
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
          62 => "BssSI",
          63 => "BssT1I",
          64 => "BstC8I",
          65 => "BstDSI",
          66 => "BstNSI",
          67 => "BstSNI",
          68 => "BstX2I",
          69 => "Cfr42I",
          70 => "Cfr9I",
          71 => "CfrI",
          72 => "DinI",
          73 => "DraI",
          74 => "Ecl136II",
          75 => "Eco32I",
          76 => "EcoRI",
          77 => "EcoT22I",
          78 => "EgeI",
          79 => "FauNDI",
          80 => "GsaI",
          81 => "HincII",
          82 => "HindIII",
          83 => "HpaI",
          84 => "Hpy166II",
          85 => "Hpy188III",
          86 => "KasI",
          87 => "KpnI",
          88 => "KroI",
          89 => "MfeI",
          90 => "MluI",
          91 => "Mly113I",
          92 => "MspA1I",
          93 => "NaeI",
          94 => "PaeR7I",
          95 => "PsiI",
          96 => "Psp124BI",
          97 => "PvuII",
          98 => "SalI",
          99 => "SmaI",
          100 => "SmlI",
          101 => "SspDI",
          102 => "SspI",
          103 => "TatI",
          104 => "XbaI",
          105 => "ZraI",
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

    /**
     * Builds a manager holding the named enzyme of the Type II samples, ready to cut $sSequence.
     */
    private function managerCutting(string $sSequence, string $sEnzyme) : RestrictionEnzymeManager
    {
        $oSequence = new Sequence();
        $oSequence->setMoltype("DNA");
        $oSequence->setSequence($sSequence);
        $oSequence->setSeqlength(strlen($sSequence));

        $sequenceBuilder = new SequenceBuilder(new SequenceManager($this->apiAminoMock, $this->apiNucleoMock, $this->apiElementsMock));
        $sequenceBuilder->setSequence($oSequence);

        $restrictionEnzymeManager = new RestrictionEnzymeManager($this->apiNucleolMock, new Enzyme());
        $restrictionEnzymeManager->setEnzyme();
        $restrictionEnzymeManager->setSequenceManager($sequenceBuilder);
        $restrictionEnzymeManager->parseEnzyme($sEnzyme, null, null, "inner");

        return $restrictionEnzymeManager;
    }

    public function testCutSeqCutsEachSequenceADegenerateSiteStandsForOnce()
    {
        // AvaII G'GWC_C : GGACC and GGTCC are both its site. Each used to be cut on its own, every
        // fragment of the first pass then repeated by the second.
        $sSequence = "AAAGGACCAAAAGGTCCAAA";
        $aFragments = $this->managerCutting($sSequence, "AvaII")->cutSeq();

        $this->assertEquals(["AAAG", "GACCAAAAG", "GTCCAAA"], $aFragments);
        $this->assertEquals($sSequence, implode("", $aFragments));
    }

    public function testCutSeqCutsEitherAlternativeOfAnOrPattern()
    {
        // AciI "C'CG_C or G'CG_G" : the literal " or " was searched for and nothing was ever cut.
        $this->assertEquals(["AAC", "CGCAAAG", "CGGAAA"], $this->managerCutting("AACCGCAAAGCGGAAA", "AciI")->cutSeq());
    }

    public function testCutSeqFindsANonPalindromicSiteOnTheOtherStrand()
    {
        // AccBSI CCG'CTC, blunt : GAGCGG is the same site read on the other strand, cut after GAG.
        $this->assertEquals(["AAAGAG", "CGGAAA"], $this->managerCutting("AAAGAGCGGAAA", "AccBSI")->cutSeq());
    }

    public function testCutSeqSearchesACustomEnzymeOnlyAsWritten()
    {
        // BsaI GGTCTC(1/5) described by its upper cut alone : read on the other strand (GAGACC at p),
        // its upper cut lies at p - 5, not p + 7, and nothing tells it. That site used to be cut at
        // the wrong place.
        $oManager = $this->managerCutting("AAAGAGACCAAAAAAAAA", "AvaII");
        $oManager->parseEnzyme("BsaI", "GGTCTC", "7", "custom");
        $this->assertEquals(["AAAGAGACCAAAAAAAAA"], $oManager->cutSeq());

        $oManager = $this->managerCutting("AAAGGTCTCAAAAAAAAA", "AvaII");
        $oManager->parseEnzyme("BsaI", "GGTCTC", "7", "custom");
        $this->assertEquals(["AAAGGTCTCA", "AAAAAAAA"], $oManager->cutSeq());
    }

    public function testCutSeqWithOverlappingSitesCutsEachOfThem()
    {
        // AspLEI (HhaI) G_CG'C : GCGCGC holds two overlapping GCGC sites.
        $this->assertEquals(["AAGCG", "CGCAA"], $this->managerCutting("AAGCGCGCAA", "AspLEI")->cutSeq("N"));
        $this->assertEquals(["AAGCG", "CG", "CAA"], $this->managerCutting("AAGCGCGCAA", "AspLEI")->cutSeq("O"));
    }

    public function testCutSeqOverlappingWithACutBeforeTheSiteTerminates()
    {
        // BfuCI (MboI, Sau3AI) 'GATC_ cuts before its site : option "O" used to resume the search on
        // the match it had just found, forever.
        $this->assertEquals(["AA", "GATCAA", "GATCA"], $this->managerCutting("AAGATCAAGATCA", "BfuCI")->cutSeq("O"));
    }

    public function testCutSeqReturnsTheWholeSequenceWhenItHoldsNoSite()
    {
        $this->assertEquals(["AAAAAAAAAA"], $this->managerCutting("AAAAAAAAAA", "AvaII")->cutSeq());
        $this->assertEquals(["AAAAAAAAAA"], $this->managerCutting("AAAAAAAAAA", "AvaII")->cutSeq("O"));
    }

    public function testCutSeqRejectsAnUnknownOption()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->managerCutting("AAGGACCAA", "AvaII")->cutSeq("X");
    }

    public function testFindRestEnMatchesAnAlternativeOrTheOtherStrandOfASite()
    {
        $restrictionEnzymeManager = $this->managerCutting("AAAA", "AciI");

        $this->assertContains("AciI", $restrictionEnzymeManager->findRestEn("GCGG"));
        $this->assertContains("AccBSI", $restrictionEnzymeManager->findRestEn("GAGCGG"));
        $this->assertEquals(4, $restrictionEnzymeManager->getLength("AciI"));
        $this->assertEquals(4, $restrictionEnzymeManager->getEnzyme()->getLength());
    }

    /**
     * An enzyme missing from the database raised a TypeError instead of the exception that says so.
     */
    public function testParseEnzymeOfAnUnknownNameThrowsAnException()
    {
        $restrictionEnzymeManager = new RestrictionEnzymeManager($this->apiNucleolMock, new Enzyme());
        $restrictionEnzymeManager->setEnzyme();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Cannot find entry in restriction endonuclease database.");
        $restrictionEnzymeManager->parseEnzyme("Foo", null, null, "inner");
    }
}
