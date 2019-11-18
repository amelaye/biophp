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
use AppBundle\Entity\Sequence;
use AppBundle\Service\RestrictionEnzymeManager;
use AppBundle\Service\SequenceManager;
use PHPUnit\Framework\TestCase;

class RestrictionEnzymeManagerTest extends TestCase
{
    private $sequence;

    private $apiMock;

    public function setUp()
    {
        $aEnzymes = [
          "AasI" => [
            0 => "GACNNNNNNGTC",
            1 => 7,
          ],
          "AatI" => [
            0 => "AGGCCT",
            1 => 3,
          ],
          "AatII" => [
            0 => "GACGTC",
            1 => 5,
          ],
          "AbsI" => [
            0 => "CCTCGAGG",
            1 => 6,
          ],
          "Acc16I" => [
            0 => "TGCGCA",
            1 => 3,
          ],
          "Acc65I" => [
            0 => "GGTACC",
            1 => 1,
          ],
          "AccB1I" => [
            0 => "GGYRCC",
            1 => 1,
          ],
          "AccB7I" => [
            0 => "CCANNNNNTGG",
            1 => 7
          ],
          "AccBSI" => [
            0 => "CCGCTC",
            1 => 3
          ],
          "AccI" => [
            0 => "GTMKAC",
            1 => 2
          ],
          "AccII" => [
            0 => "CGCG",
            1 => 2
          ],
          "AccIII" => [
            0 => "TCCGGA",
            1 => 1
          ],
          "AciI" => [
            0 => "CCGC or GCGG",
            1 => 1
          ],
          "AclI" => [
            0 => "AACGTT",
            1 => 2
          ],
          "AcoI" => [
            0 => "YCCGGR",
            1 => 1
          ],
          "AcsI" => [
            0 => "RAATTY",
            1 => 1
          ],
          "AcvI" => [
            0 => "CACGTG",
            1 => 3
          ],
          "AcyI" => [
            0 => "GRCGYC",
            1 => 2
          ],
          "AdeI" => [
            0 => "CACNNNGTG",
            1 => 6
          ],
          "AfaI" => [
            0 => "GTAC",
            1 => 2
          ],
          "AfeI" => [
            0 => "AGCGCT",
            1 => 3
          ],
          "AfiI" => [
            0 => "CCNNNNNNNGG",
            1 => 7
          ],
          "AflII" => [
            0 => "CTTAAG",
            1 => 1
          ],
          "AflIII" => [
            0 => "ACRYGT",
            1 => 1
          ],
          "AgeI" => [
            0 => "ACCGGT",
            1 => 1
          ],
          "AgSI" => [
            0 => "TTSAA",
            1 => 3
          ],
          "AhdI" => [
            0 => "GACNNNNNGTC",
            1 => 6
          ],
          "AhlI" => [
            0 => "ACTAGT",
            1 => 1
          ],
          "AjiI" => [
            0 => "CACGTC",
            1 => 3
          ],
          "AjnI" => [
            0 => "CCWGG",
            1 => 0
          ],
          "AleI" => [
            0 => "CACNNNNGTG",
            1 => 5
          ],
          "AluI" => [
            0 => "AGCT",
            1 => 2
          ],
          "Alw21I" => [
            0 => "GWGCWC",
            1 => 5
          ],
          "Alw44I" => [
            0 => "GTGCAC",
            1 => 1
          ],
          "AlwNI" => [
            0 => "CAGNNNCTG",
            1 => 6
          ],
          "Ama87I" => [
            0 => "CYCGRG",
            1 => 1
          ],
          "ApaI" => [
            0 => "GGGCCC",
            1 => 5
          ],
          "ApeKI" => [
            0 => "GCWGC",
            1 => 1
          ],
          "AscI" => [
            0 => "GGCGCGCC",
            1 => 2
          ],
          "AseI" => [
            0 => "ATTAAT",
            1 => 2
          ],
          "AsiSI" => [
            0 => "GCGATCGC",
            1 => 5
          ],
          "Asp700I" => [
            0 => "GAANNNNTTC",
            1 => 5
          ],
          "AspA2I" => [
            0 => "CCTAGG",
            1 => 1
          ],
          "AspI" => [
            0 => "GACNNNGTC",
            1 => 4
          ],
          "AspLEI" => [
            0 => "GCGC",
            1 => 3
          ],
          "AspS9I" => [
            0 => "GGNCC",
            1 => 1
          ],
          "AssI" => [
            0 => "AGTACT",
            1 => 3
          ],
          "AsuC2I" => [
            0 => "CCSGG",
            1 => 2
          ],
          "AsuII" => [
            0 => "TTCGAA",
            1 => 2
          ],
          "AsuNHI" => [
            0 => "GCTAGC",
            1 => 1
          ],
          "AvaII" => [
            0 => "GGWCC",
            1 => 1
          ],
          "AxyI" => [
            0 => "CCTNAGG",
            1 => 2
          ],
          "BaeGI" => [
            0 => "GKGCMC",
            1 => 5
          ],
          "BalI" => [
            0 => "TGGCCA",
            1 => 3
          ],
          "BamHI" => [
            0 => "GGATCC",
            1 => 1
          ],
          "BanII" => [
            0 => "GRGCYC",
            1 => 5
          ],
          "BanIII" => [
            0 => "ATCGAT",
            1 => 2
          ],
          "BauI" => [
            0 => "CACGAG",
            1 => 1
          ],
          "BbeI" => [
            0 => "GGCGCC",
            1 => 5
          ],
          "BbuI" => [
            0 => "GCATGC",
            1 => 5
          ],
          "BbvCI" => [
            0 => "CCTCAGC or  GCTGAGG",
            1 => 2
          ],
          "BciT130I" => [
            0 => "CCWGG",
            1 => 2
          ],
          "BclI" => [
            0 => "TGATCA",
            1 => 1
          ],
          "BfaI" => [
            0 => "CTAG",
            1 => 1
          ],
          "BfmI" => [
            0 => "CTRYAG",
            1 => 1
          ],
          "BfoI" => [
            0 => "RGCGCY",
            1 => 5
          ],
          "BfuCI" => [
            0 => "GATC",
            1 => 0
          ],
          "BglI" => [
            0 => "GCCNNNNNGGC",
            1 => 7
          ],
          "BglII" => [
            0 => "AGATCT",
            1 => 1
          ],
          "BisI" => [
            0 => "GCNGC",
            1 => 2
          ],
          "BlpI" => [
            0 => "GCTNAGC",
            1 => 2
          ],
          "Bme1390I" => [
            0 => "CCNGG",
            1 => 2
          ],
          "BmiI" => [
            0 => "GGNNCC",
            1 => 3
          ],
          "BmrFI" => [
            0 => "CCNGG",
            1 => 0
          ],
          "BmtI" => [
            0 => "GCTAGC",
            1 => 5
          ],
          "BoxI" => [
            0 => "GACNNNNGTC",
            1 => 5
          ],
          "BptI" => [
            0 => "CCWGG",
            1 => 2
          ],
          "Bpu10I" => [
            0 => "CCTNAGC",
            1 => 2
          ],
          "BpvUI" => [
            0 => "CGATCG",
            1 => 4
          ],
          "BsaAI" => [
            0 => "YACGTR",
            1 => 3
          ],
          "BsaBI" => [
            0 => "GATNNNNATC",
            1 => 5
          ],
          "BsaJI" => [
            0 => "CCNNGG",
            1 => 1
          ],
          "BsaWI" => [
            0 => "WCCGGW",
            1 => 1
          ],
          "Bse118I" => [
            0 => "RCCGGY",
            1 => 1
          ],
          "BsePI" => [
            0 => "GCGCGC",
            1 => 1
          ],
          "BseX3I" => [
            0 => "CGGCCG",
            1 => 1
          ],
          "BseYI" => [
            0 => "CCCAGC",
            1 => 1
          ],
          "Bsh1285I" => [
            0 => "CGRYCG",
            1 => 4
          ],
          "BshFI" => [
            0 => "GGCC",
            1 => 2
          ],
          "BsiSI" => [
            0 => "CCGG",
            1 => 1
          ],
          "BsiWI" => [
            0 => "CGTACG",
            1 => 1
          ],
          "Bsp120I" => [
            0 => "GGGCCC",
            1 => 1
          ],
          "Bsp1286I" => [
            0 => "GDGCHC",
            1 => 5
          ],
          "Bsp1407I" => [
            0 => "TGTACA",
            1 => 1
          ],
          "Bsp19I" => [
            0 => "CCATGG",
            1 => 1
          ],
          "Bsp68I" => [
            0 => "TCGCGA",
            1 => 3
          ],
          "BspHI" => [
            0 => "TCATGA",
            1 => 1
          ],
          "BspLU11I" => [
            0 => "ACATGT",
            1 => 1
          ],
          "BspMAI" => [
            0 => "CTGCAG",
            1 => 5
          ],
          "BssNAI" => [
            0 => "GTATAC",
            1 => 3
          ],
          "BssSI" => [
            0 => "CACGAG or CTCGTG",
            1 => 1
          ],
          "BssT1I" => [
            0 => "CCWWGG",
            1 => 1
          ],
          "Bst4CI" => [
            0 => "ACNGT",
            1 => 3
          ],
          "BstAPI" => [
            0 => "GCANNNNNTGC",
            1 => 7
          ],
          "BstC8I" => [
            0 => "GCNNGC",
            1 => 3
          ],
          "BstDEI" => [
            0 => "CTNAG",
            1 => 1
          ],
          "BstDSI" => [
            0 => "CCRYGG",
            1 => 1
          ],
          "BstEII" => [
            0 => "GGTNACC",
            1 => 1
          ],
          "BstENI" => [
            0 => "CCTNNNNNAGG",
            1 => 5
          ],
          "BstKTI" => [
            0 => "GATC",
            1 => 3
          ],
          "BstMWI" => [
            0 => "GCNNNNNNNGC",
            1 => 7
          ],
          "BstNSI" => [
            0 => "RCATGY",
            1 => 5
          ],
          "BstSNI" => [
            0 => "TACGTA",
            1 => 3
          ],
          "BstX2I" => [
            0 => "RGATCY",
            1 => 1
          ],
          "BstXI" => [
            0 => "CCANNNNNNTGG",
            1 => 8
          ],
          "CciNI" => [
            0 => "GCGGCCGC",
            1 => 2
          ],
          "Cfr42I" => [
            0 => "CCGCGG",
            1 => 4
          ],
          "Cfr9I" => [
            0 => "CCCGGG",
            1 => 1
          ],
          "CfrI" => [
            0 => "YGGCCR",
            1 => 1
          ],
          "CpoI" => [
            0 => "CGGWCCG",
            1 => 2
          ],
          "CsiI" => [
            0 => "ACCWGGT",
            1 => 1
          ],
          "Csp6I" => [
            0 => "GTAC",
            1 => 1
          ],
          "CviAII" => [
            0 => "CATG",
            1 => 4
          ],
          "CviJI" => [
            0 => "RGCY",
            1 => 2
          ],
          "DinI" => [
            0 => "GGCGCC",
            1 => 2
          ],
          "DpnI" => [
            0 => "GATC",
            1 => 2
          ],
          "DraI" => [
            0 => "TTTAAA",
            1 => 3
          ],
          "Ecl136II" => [
            0 => "GAGCTC",
            1 => 3
          ],
          "Eco32I" => [
            0 => "GATATC",
            1 => 3
          ],
          "EcoO109I" => [
            0 => "RGGNCCY",
            1 => 2
          ],
          "EcoRI" => [
            0 => "GAATTC",
            1 => 1
          ],
          "EcoT22I" => [
            0 => "ATGCAT",
            1 => 5
          ],
          "EgeI" => [
            0 => "GGCGCC",
            1 => 3
          ],
          "FaiI" => [
            0 => "YATR",
            1 => 2
          ],
          "FatI" => [
            0 => "CATG",
            1 => 0
          ],
          "FauNDI" => [
            0 => "CATATG",
            1 => 2
          ],
          "FseI" => [
            0 => "GGCCGGCC",
            1 => 6
          ],
          "FspAI" => [
            0 => "RTGCGCAY",
            1 => 4
          ],
          "GlaI" => [
            0 => "GCGC",
            1 => 2
          ],
          "GsaI" => [
            0 => "CCCAGC",
            1 => 5
          ],
          "Hin6I" => [
            0 => "GCGC",
            1 => 1
          ],
          "HincII" => [
            0 => "GTYRAC",
            1 => 3
          ],
          "HindIII" => [
            0 => "AAGCTT",
            1 => 1
          ],
          "HinfI" => [
            0 => "GANTC",
            1 => 1
          ],
          "HpaI" => [
            0 => "GTTAAC",
            1 => 3
          ],
          "Hpy166II" => [
            0 => "GTNNAC",
            1 => 3
          ],
          "Hpy188I" => [
            0 => "TCNGA",
            1 => 3
          ],
          "Hpy188III" => [
            0 => "TCNNGA",
            1 => 2
          ],
          "Hpy99I" => [
            0 => "CGWCG",
            1 => 5
          ],
          "HpyCH4IV" => [
            0 => "ACGT",
            1 => 1
          ],
          "HpyCH4V" => [
            0 => "TGCA",
            1 => 2
          ],
          "HpyF10VI" => [
            0 => "GCNNNNNNNGC",
            1 => 7
          ],
          "KasI" => [
            0 => "GGCGCC",
            1 => 1
          ],
          "KflI" => [
            0 => "GGGWCCC",
            1 => 2
          ],
          "KpnI" => [
            0 => "GGTACC",
            1 => 5
          ],
          "KroI" => [
            0 => "GCCGGC",
            1 => 1
          ],
          "MaeIII" => [
            0 => "GTNAC",
            1 => 0
          ],
          "MauBI" => [
            0 => "CGCGCGCG",
            1 => 2
          ],
          "MfeI" => [
            0 => "CAATTG",
            1 => 1
          ],
          "MluCI" => [
            0 => "AATT",
            1 => 0
          ],
          "MluI" => [
            0 => "ACGCGT",
            1 => 1
          ],
          "MreI" => [
            0 => "CGCCGGCG",
            1 => 2
          ],
          "MseI" => [
            0 => "TTAA",
            1 => 1
          ],
          "MslI" => [
            0 => "CAYNNNNRTG",
            1 => 5
          ],
          "MspA1I" => [
            0 => "CMGCKG",
            1 => 3
          ],
          "MssI" => [
            0 => "GTTTAAAC",
            1 => 4
          ],
          "NaeI" => [
            0 => "GCCGGC",
            1 => 3
          ],
          "NmuCI" => [
            0 => "GTSAC",
            1 => 0
          ],
          "PacI" => [
            0 => "TTAATTAA",
            1 => 5
          ],
          "PaeR7I" => [
            0 => "CTCGAG",
            1 => 1
          ],
          "PasI" => [
            0 => "CCCWGGG",
            1 => 2
          ],
          "PcsI" => [
            0 => "WCGNNNNNNNCGW",
            1 => 7
          ],
          "PfeI" => [
            0 => "GAWTC",
            1 => 1
          ],
          "PfoI" => [
            0 => "TCCNGGA",
            1 => 1
          ],
          "PpuMI" => [
            0 => "RGGWCCY",
            1 => 2
          ],
          "PsiI" => [
            0 => "TTATAA",
            1 => 3
          ],
          "Psp124BI" => [
            0 => "GAGCTC",
            1 => 5
          ],
          "PspXI" => [
            0 => "VCTCGAGB",
            1 => 2
          ],
          "PvuII" => [
            0 => "CAGCTG",
            1 => 3
          ],
          "SalI" => [
            0 => "GTCGAC",
            1 => 1
          ],
          "SbfI" => [
            0 => "CCTGCAGG",
            1 => 6
          ],
          "SetI" => [
            0 => "ASST",
            1 => 4
          ],
          "SfiI" => [
            0 => "GGCCNNNNNGGCC",
            1 => 8
          ],
          "SgrAI" => [
            0 => "CRCCGGYG",
            1 => 2
          ],
          "SgrDI" => [
            0 => "CGTCGACG",
            1 => 2
          ],
          "SmaI" => [
            0 => "CCCGGG",
            1 => 3
          ],
          "SmiI" => [
            0 => "ATTTAAAT",
            1 => 4
          ],
          "SmlI" => [
            0 => "CTYRAG",
            1 => 1
          ],
          "SrfI" => [
            0 => "GCCCGGGC",
            1 => 4
          ],
          "SspI" => [
            0 => "AATATT",
            1 => 3
          ],
          "TaiI" => [
            0 => "ACGT",
            1 => 4
          ],
          "TaqI" => [
            0 => "TCGA",
            1 => 1
          ],
          "TatI" => [
            0 => "WGTACW",
            1 => 1
          ],
          "TauI" => [
            0 => "GCSGC",
            1 => 4
          ],
          "TscAI" => [
            0 => "NNCASTGNN",
            1 => 9
          ],
          "XbaI" => [
            0 => "TCTAGA",
            1 => 1
          ],
          "XcmI" => [
            0 => "CCANNNNNNNNNTGG",
            1 => 8
          ],
          "ZraI" => [
            0 => "GACGTC",
            1 => 3
          ],
        ];

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
            ->setMethods(['getTypeIIEndonucleasesForRest','getElements'])
            ->getMock();
        $this->apiMock->method("getTypeIIEndonucleasesForRest")->will($this->returnValue($aEnzymes));
        $this->apiMock->method("getElements")->will($this->returnValue($aElements));

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
        $restrictionEnzymeManager = new RestrictionEnzymeManager($this->apiMock);
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
        $restrictionEnzymeManager = new RestrictionEnzymeManager($this->apiMock);
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
        $sequenceManager = new SequenceManager($this->apiMock);
        $sequenceManager->setSequence($this->sequence);

        $restrictionEnzymeManager = new RestrictionEnzymeManager($this->apiMock);
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
        $sequenceManager = new SequenceManager($this->apiMock);
        $sequenceManager->setSequence($this->sequence);

        $restrictionEnzymeManager = new RestrictionEnzymeManager($this->apiMock);
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
        $sequenceManager = new SequenceManager($this->apiMock);
        $sequenceManager->setSequence($this->sequence);

        $restrictionEnzymeManager = new RestrictionEnzymeManager($this->apiMock);
        $restrictionEnzymeManager->setEnzyme();

        $restrictionEnzymeManager->setSequenceManager($sequenceManager);
        $restrictionEnzymeManager->parseEnzyme('AatI', 'AGGCCT', 0, "inner");

        $list = $restrictionEnzymeManager->findRestEn("AGGCCT");
        $aExpected = ["AatI"];

        $this->assertEquals($aExpected, $list);
    }

    public function testFindRestEnFetchCutposAndPlen()
    {
        $sequenceManager = new SequenceManager($this->apiMock);
        $sequenceManager->setSequence($this->sequence);

        $restrictionEnzymeManager = new RestrictionEnzymeManager($this->apiMock);
        $restrictionEnzymeManager->setEnzyme();

        $restrictionEnzymeManager->setSequenceManager($sequenceManager);
        $list5 = $restrictionEnzymeManager->findRestEn(null,3, 6);

        $aExpected = [];
        $this->assertEquals($aExpected, $list5);
    }

    public function testFindRestEnFetchLength()
    {
        $sequenceManager = new SequenceManager($this->apiMock);
        $sequenceManager->setSequence($this->sequence);

        $restrictionEnzymeManager = new RestrictionEnzymeManager($this->apiMock);
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
        $sequenceManager = new SequenceManager($this->apiMock);
        $sequenceManager->setSequence($this->sequence);

        $restrictionEnzymeManager = new RestrictionEnzymeManager($this->apiMock);
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
        $sequenceManager = new SequenceManager($this->apiMock);
        $sequenceManager->setSequence($this->sequence);

        $restrictionEnzymeManager = new RestrictionEnzymeManager($this->apiMock);
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
        $sequenceManager = new SequenceManager($this->apiMock);
        $sequenceManager->setSequence($this->sequence);

        $restrictionEnzymeManager = new RestrictionEnzymeManager($this->apiMock);
        $restrictionEnzymeManager->setEnzyme();

        $restrictionEnzymeManager->setSequenceManager($sequenceManager);
        $list = $restrictionEnzymeManager->findRestEn("AGGCCT");

        $aExpected = [
            0 => "AatI"
        ];
        $this->assertEquals($aExpected, $list);
    }
}