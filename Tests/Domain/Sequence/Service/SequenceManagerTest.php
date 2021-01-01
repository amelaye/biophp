<?php
namespace Tests\Domain\Sequence\Service;

use Amelaye\BioPHP\Api\AminoApi;
use Amelaye\BioPHP\Api\ElementApi;
use Amelaye\BioPHP\Api\NucleotidApi;
use Amelaye\BioPHP\Domain\Sequence\Entity\Sequence;
use Amelaye\BioPHP\Domain\Sequence\Service\SequenceManager;
use Amelaye\BioPHP\Domain\Sequence\Builder\SequenceBuilder;
use PHPUnit\Framework\TestCase;

class SequenceManagerTest extends TestCase
{
    private $sequence;

    private $apiAminoMock;

    private $apiNucleoMock;

    private $apiElementsMock;

    public function setUp()
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

        $this->apiAminoMock = $this->getMockBuilder(AminoApi::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAminos'])
            ->getMock();
        $this->apiAminoMock->method("getAminos")->will($this->returnValue($aAminosObjects));

        $this->apiNucleoMock = $this->getMockBuilder(NucleotidApi::class)
            ->disableOriginalConstructor()
            ->setMethods(['getNucleotids'])
            ->getMock();
        $this->apiNucleoMock->method("getNucleotids")->will($this->returnValue($aNucleoObjects));

        $this->apiElementsMock = $this->getMockBuilder(ElementApi::class)
            ->disableOriginalConstructor()
            ->setMethods(['getElements', 'getElement'])
            ->getMock();
        $this->apiElementsMock->method("getElements")->will($this->returnValue($aElementsObjects));
        $this->apiElementsMock->method("getElement")->will($this->returnValue($aElementsObjects[5]));

        $sSequence = "GGCAGATTCCCCCTAGACCCGCCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTGGAGGCT";
        $sSequence.= "GGCGGGGCAGGCCAGCTGAGTCCTGAGCAGCAGCCCAGCGCAGCCACCGAGACACCATGAGAGCCCTCACACTCCTCGCCCTATTGGCCCTGGC";
        $sSequence.= "CGCACTTTGCATCGCTGGCCAGGCAGGTGAGTGCCCCCACCTCCCCTCAGGCCGCATTGCAGTGGGGGCTGAGAGGAGGAAGCACCATGGCCCA";
        $sSequence.= "CCTCTTCTCACCCCTTTGGCTGGCAGTCCCTTTGCAGTCTAACCACCTTGTTGCAGGCTCAATCCATTTGCCCCAGCTCTGCCCTTGCAGAGGG";
        $sSequence.= "AGAGGAGGGAAGAGCAAGCTGCCCGAGACGCAGGGGAAGGAGGATGAGGGCCCTGGGGATGAGCTGGGGTGAACCAGGCTCCCTTTCCTTTGCA";
        $sSequence.= "GGTGCGAAGCCCAGCGGTGCAGAGTCCAGCAAAGGTGCAGGTATGAGGATGGACCTGATGGGTTCCTGGACCCTCCCCTCTCACCCTGGTCCCT";
        $sSequence.= "CAGTCTCATTCCCCCACTCCTGCCACCTCCTGTCTGGCCATCAGGAAGGCCAGCCTGCTCCCCACCTGATCCTCCCAAACCCAGAGCCACCTGA";
        $sSequence.= "TGCCTGCCCCTCTGCTCCACAGCCTTTGTGTCCAAGCAGGAGGGCAGCGAGGTAGTGAAGAGACCCAGGCGCTACCTGTATCAATGGCTGGGGT";
        $sSequence.= "GAGAGAAAAGGCAGAGCTGGGCCAAGGCCCTGCCTCTCCGGGATGGTCTGTGGGGGAGCTGCAGCAGGGAGTGGCCTCTCTGGGTTGTGGTGGG";
        $sSequence.= "GGTACAGGCAGCCTGCCCTGGTGGGCACCCTGGAGCCCCATGTGTAGGGAGAGGAGGGATGGGCATTTTGCACGGGGGCTGATGCCACCACGTC";
        $sSequence.= "GGGTGTCTCAGAGCCCCAGTCCCCTACCCGGATCCCCTGGAGCCCAGGAGGGAGGTGTGTGAGCTCAATCCGGACTGTGACGAGTTGGCTGACC";
        $sSequence.= "ACATCGGCTTTCAGGAGGCCTATCGGCGCTTCTACGGCCCGGTCTAGGGTGTCGCTCTGCTGGCCTGGCCGGCAACCCCAGTTCTGCTCCTCTC";
        $sSequence.= "CAGGCACCCTTCTTTCCTCTTCCCCTTGCCCTTGCCCTGACCTCCCAGCCCTATGGATGTGGGGTCCCCATCATCCCAGCTGCTCCCAAATAAA";
        $sSequence.= "CTCCAGAAG";

        $oSequence = new Sequence();
        $oSequence->setMoltype("DNA");
        $oSequence->setSequence($sSequence);
        $oSequence->setSeqlength(1231);

        $this->sequence = $oSequence;
    }

    public function testComplement()
    {
        $sequenceManager = new SequenceManager($this->apiAminoMock, $this->apiNucleoMock, $this->apiElementsMock);
        $sequenceBuilder = new SequenceBuilder($sequenceManager);
        $sequenceBuilder->setSequence($this->sequence);

        $aComplement = $sequenceBuilder->complement("DNA");
        $aExpected = "CCGTCTAAGGGGGATCTGGGCGGGCGTGGTACCAGTCCGTACGGGGAGGAGTAGCGACCCGTGTCGGGTCTCCCATATTTGTCACGACCTCCGA";
        $aExpected.= "CCGCCCCGTCCGGTCGACTCAGGACTCGTCGTCGGGTCGCGTCGGTGGCTCTGTGGTACTCTCGGGAGTGTGAGGAGCGGGATAACCGGGACCG";
        $aExpected.= "GCGTGAAACGTAGCGACCGGTCCGTCCACTCACGGGGGTGGAGGGGAGTCCGGCGTAACGTCACCCCCGACTCTCCTCCTTCGTGGTACCGGGT";
        $aExpected.= "GGAGAAGAGTGGGGAAACCGACCGTCAGGGAAACGTCAGATTGGTGGAACAACGTCCGAGTTAGGTAAACGGGGTCGAGACGGGAACGTCTCCC";
        $aExpected.= "TCTCCTCCCTTCTCGTTCGACGGGCTCTGCGTCCCCTTCCTCCTACTCCCGGGACCCCTACTCGACCCCACTTGGTCCGAGGGAAAGGAAACGT";
        $aExpected.= "CCACGCTTCGGGTCGCCACGTCTCAGGTCGTTTCCACGTCCATACTCCTACCTGGACTACCCAAGGACCTGGGAGGGGAGAGTGGGACCAGGGA";
        $aExpected.= "GTCAGAGTAAGGGGGTGAGGACGGTGGAGGACAGACCGGTAGTCCTTCCGGTCGGACGAGGGGTGGACTAGGAGGGTTTGGGTCTCGGTGGACT";
        $aExpected.= "ACGGACGGGGAGACGAGGTGTCGGAAACACAGGTTCGTCCTCCCGTCGCTCCATCACTTCTCTGGGTCCGCGATGGACATAGTTACCGACCCCA";
        $aExpected.= "CTCTCTTTTCCGTCTCGACCCGGTTCCGGGACGGAGAGGCCCTACCAGACACCCCCTCGACGTCGTCCCTCACCGGAGAGACCCAACACCACCC";
        $aExpected.= "CCATGTCCGTCGGACGGGACCACCCGTGGGACCTCGGGGTACACATCCCTCTCCTCCCTACCCGTAAAACGTGCCCCCGACTACGGTGGTGCAG";
        $aExpected.= "CCCACAGAGTCTCGGGGTCAGGGGATGGGCCTAGGGGACCTCGGGTCCTCCCTCCACACACTCGAGTTAGGCCTGACACTGCTCAACCGACTGG";
        $aExpected.= "TGTAGCCGAAAGTCCTCCGGATAGCCGCGAAGATGCCGGGCCAGATCCCACAGCGAGACGACCGGACCGGCCGTTGGGGTCAAGACGAGGAGAG";
        $aExpected.= "GTCCGTGGGAAGAAAGGAGAAGGGGAACGGGAACGGGACTGGAGGGTCGGGATACCTACACCCCAGGGGTAGTAGGGTCGACGAGGGTTTATTT";
        $aExpected.= "GAGGTCTTC";

        $this->assertEquals($aExpected, $aComplement);
    }

    public function testHalfSequence()
    {
        $sequenceManager = new SequenceManager($this->apiAminoMock, $this->apiNucleoMock, $this->apiElementsMock);
        $sequenceBuilder = new SequenceBuilder($sequenceManager);
        $sequenceBuilder->setSequence($this->sequence);

        $sHalf = $sequenceBuilder->halfSequence(0);
        $sExpected = "GGCAGATTCCCCCTAGACCCGCCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTGGAGGCT";
        $sExpected.= "GGCGGGGCAGGCCAGCTGAGTCCTGAGCAGCAGCCCAGCGCAGCCACCGAGACACCATGAGAGCCCTCACACTCCTCGCCCTATTGGCCCTGGC";
        $sExpected.= "CGCACTTTGCATCGCTGGCCAGGCAGGTGAGTGCCCCCACCTCCCCTCAGGCCGCATTGCAGTGGGGGCTGAGAGGAGGAAGCACCATGGCCCA";
        $sExpected.= "CCTCTTCTCACCCCTTTGGCTGGCAGTCCCTTTGCAGTCTAACCACCTTGTTGCAGGCTCAATCCATTTGCCCCAGCTCTGCCCTTGCAGAGGG";
        $sExpected.= "AGAGGAGGGAAGAGCAAGCTGCCCGAGACGCAGGGGAAGGAGGATGAGGGCCCTGGGGATGAGCTGGGGTGAACCAGGCTCCCTTTCCTTTGCA";
        $sExpected.= "GGTGCGAAGCCCAGCGGTGCAGAGTCCAGCAAAGGTGCAGGTATGAGGATGGACCTGATGGGTTCCTGGACCCTCCCCTCTCACCCTGGTCCCTC";
        $sExpected.= "AGTCTCATTCCCCCACTCCTGCCACCTCCTGTCTGGCCATCAGGAAGGCC";

        $this->assertEquals($sExpected, $sHalf);
    }

    public function testExpandNA()
    {
        $sequenceManager = new SequenceManager($this->apiAminoMock, $this->apiNucleoMock, $this->apiElementsMock);
        $sequenceBuilder = new SequenceBuilder($sequenceManager);
        $sExpandNa = $sequenceBuilder->expandNa("GATTAGSW");

        $sExpected = "GATTAG[GC][AT]";

        $this->assertEquals($sExpandNa, $sExpected);
    }

    public function testMolWT()
    {
        $sequenceManager = new SequenceManager($this->apiAminoMock, $this->apiNucleoMock, $this->apiElementsMock);
        $sequenceBuilder = new SequenceBuilder($sequenceManager);
        $sequenceBuilder->setSequence($this->sequence);
        $fMolWt = round($sequenceBuilder->molwt("upperlimit"),1);

        $fExpected = 379669.7;
        $this->assertEquals($fExpected, $fMolWt);
    }

    public function testSubseq()
    {
        $sequenceManager = new SequenceManager($this->apiAminoMock, $this->apiNucleoMock, $this->apiElementsMock);
        $sequenceBuilder = new SequenceBuilder($sequenceManager);
        $sequenceBuilder->setSequence($this->sequence);

        $sExpected = "CAGATTCCCCCTAGACCCGCCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTGGAGGCTGGCGGGGC";

        $sCoupe = $sequenceBuilder->subSeq(2,100);
        $this->assertEquals($sCoupe, $sExpected);
    }

    public function testPatpos()
    {
        $sequenceManager = new SequenceManager($this->apiAminoMock, $this->apiNucleoMock, $this->apiElementsMock);
        $sequenceBuilder = new SequenceBuilder($sequenceManager);
        $sequenceBuilder->setSequence($this->sequence);

        $test = $sequenceBuilder->patPos("TTT");
        $aExpected = [
          "TTT" =>  [
            0 => 193,
            1 => 296,
            2 => 312,
            3 => 348,
            4 => 459,
            5 => 464,
            6 => 682,
            7 => 911,
            8 => 1042,
            9 => 1140
          ]
        ];

        $this->assertEquals($test, $aExpected);
    }

    public function testChemicalGroup()
    {
        $sequenceManager = new SequenceManager($this->apiAminoMock, $this->apiNucleoMock, $this->apiElementsMock);
        $sequenceBuilder = new SequenceBuilder($sequenceManager);

        $group = $sequenceBuilder->chemicalGroup("GAVLIFYWKRH");
        $sExpected = "LLLLLRRRCCC";

        $this->assertEquals($sExpected, $group);
    }

    public function testFindPattern()
    {
        $sequenceManager = new SequenceManager($this->apiAminoMock, $this->apiNucleoMock, $this->apiElementsMock);
        $sequenceBuilder = new SequenceBuilder($sequenceManager);
        $sequenceBuilder->setSequence($this->sequence);

        $aPattern = $sequenceBuilder->findPattern("AAA", null,"O");

        $aExpected = [
          0 => [
            0 => "AAA",
            1 => "AAA",
            2 => "AAA",
            3 => "AAA",
            4 => "AAA",
            5 => "AAA"
          ]
        ];

        $this->assertEquals($aExpected, $aPattern);
    }

    public function testPatposo()
    {
        $sequenceManager = new SequenceManager($this->apiAminoMock, $this->apiNucleoMock, $this->apiElementsMock);
        $sequenceBuilder = new SequenceBuilder($sequenceManager);
        $sequenceBuilder->setSequence($this->sequence);

        $test = $sequenceBuilder->patPoso("TTT");
        $aExpected = [
              0 => 193,
              1 => 296,
              2 => 312,
              3 => 348,
              4 => 459,
              5 => 464,
              6 => 682,
              7 => 911,
              8 => 912,
              9 => 1042,
              10 => 1140
        ];

        $this->assertEquals($test, $aExpected);
    }

    public function testSymfreq()
    {
        $sequenceManager = new SequenceManager($this->apiAminoMock, $this->apiNucleoMock, $this->apiElementsMock);
        $sequenceBuilder = new SequenceBuilder($sequenceManager);
        $sequenceBuilder->setSequence($this->sequence);

        $test = $sequenceBuilder->symFreq("A");
        $iExpected = 217;

        $this->assertEquals($test, $iExpected);
    }

    public function testGetCodon()
    {
        $sequenceManager = new SequenceManager($this->apiAminoMock, $this->apiNucleoMock, $this->apiElementsMock);
        $sequenceBuilder = new SequenceBuilder($sequenceManager);
        $sequenceBuilder->setSequence($this->sequence);

        $codon = $sequenceBuilder->getCodon(3);
        $sExpected = "CCC";

        $this->assertEquals($codon, $sExpected);
    }

    public function testTranslate()
    {
        $sequenceManager = new SequenceManager($this->apiAminoMock, $this->apiNucleoMock, $this->apiElementsMock);
        $sequenceBuilder = new SequenceBuilder($sequenceManager);
        $sequenceBuilder->setSequence($this->sequence);

        $sExpected = "GRFPLDPPAPWSGMPLLIAGHSPEGINSAGGWRGRPAES*AAAQRSHRDTMRALTLLALLALAALCIAGQAGECPHLPSGRIAVGAERRKH";
        $sExpected.= "HGPPLLTPLAGSPFAV*PPCCRLNPFAPALPLQRERREEQAARDAGEGG*GPWG*AGVNQAPFPLQVRSPAVQSPAKVQV*GWT*WVPGPS";
        $sExpected.= "PLTLVPQSHSPTPATSCLAIRKASLLPT*SSQTQSHLMPAPLLHSLCVQAGGQRGSEETQALPVSMAGVREKAELGQGPASPGWSVGELQQ";
        $sExpected.= "GVASLGCGGGTGSLPWWAPWSPMCRERRDGHFARGLMPPRRVSQSPSPLPGSPGAQEGGV*AQSGL*RVG*PHRLSGGLSALLRPGLGCRS";
        $sExpected.= "AGLAGNPSSAPLQAPFFPLPLALALTSQPYGCGVPIIPAAPK*TPEX";

        $translate = $sequenceBuilder->translate();

        $this->assertEquals($translate, $sExpected);
    }

    public function testCharge()
    {
        $sequenceManager = new SequenceManager($this->apiAminoMock, $this->apiNucleoMock, $this->apiElementsMock);
        $sequenceBuilder = new SequenceBuilder($sequenceManager);

        $charge = $sequenceBuilder->charge("GAVLIFYWKRH");
        $sExpected = "NNNNNNNNCCC";

        $this->assertEquals($charge, $sExpected);
    }

    public function testFindPalindrome()
    {
        $sequenceManager = new SequenceManager($this->apiAminoMock, $this->apiNucleoMock, $this->apiElementsMock);
        $sequenceBuilder = new SequenceBuilder($sequenceManager);
        $sequenceBuilder->setSequence($this->sequence);

        $sCoupe = $sequenceBuilder->subSeq(2,100);

        $testPalindrome = $sequenceBuilder->findPalindrome($sCoupe, 0,3);

        $aExpected = [
          0 => [
            0 => "CAGATTCCCCCTAGACCCGCCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTG",
            1 => 0
          ],
          1 => [
            0 => "CAGATTCCCCCTAGACCCGCCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTG",
            1 => 0
          ],
          2 => [
            0 => "CAGATTCCCCCTAGACCCGCCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTGGAGGCTG",
            1 => 0
          ],
          3 => [
            0 => "GATTCCCCCTAGACCCGCCCGCACCATGGTCAGGCATGCCCCTCCTCATC",
            1 => 2
          ],
          4 => [
            0 => "TCCCCCTAGACCCGCCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTGGA",
            1 => 5
          ],
          5 => [
            0 => "CCCCCTAGACCCGCCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGG",
            1 => 6
          ],
          6 => [
            0 => "CCCCCTAGACCCGCCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGG",
            1 => 6
          ],
          7 => [
            0 => "CCCCCTAGACCCGCCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTGGAGGCTGGCGGG",
            1 => 6
          ],
          8 => [
            0 => "CCCCCTAGACCCGCCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTGGAGGCTGGCGGGG",
            1 => 6
          ],
          9 => [
            0 => "CCCCTAGACCCGCCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGG",
            1 => 7
          ],
          10 => [
            0 => "CCCCTAGACCCGCCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGG",
            1 => 7
          ],
          11 => [
            0 => "CCCCTAGACCCGCCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTGGAGGCTGGCGGG",
            1 => 7
          ],
          12 => [
            0 => "CCCCTAGACCCGCCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTGGAGGCTGGCGGGG",
            1 => 7
          ],
          13 => [
            0 => "CCCTAGACCCGCCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGG",
            1 => 8
          ],
          14 => [
            0 => "CCCTAGACCCGCCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGG",
            1 => 8
          ],
          15 => [
            0 => "CCCTAGACCCGCCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTGGAGGCTGGCGGG",
            1 => 8
          ],
          16 => [
            0 => "CCCTAGACCCGCCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTGGAGGCTGGCGGGG",
            1 => 8
          ],
          17 => [
            0 => "CCTAGACCCGCCCGCACCATGGTCAGG",
            1 => 9
          ],
          18 => [
            0 => "CCTAGACCCGCCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGG",
            1 => 9
          ],
          19 => [
            0 => "CCTAGACCCGCCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTGGAGG",
            1 => 9
          ],
          20 => [
            0 => "GACCCGCCCGCACCATGGTC",
            1 => 13
          ],
          21 => [
            0 => "ACCCGCCCGCACCATGGT",
            1 => 14
          ],
          22 => [
            0 => "ACCCGCCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGGT",
            1 => 14
          ],
          23 => [
            0 => "CCCGCCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGG",
            1 => 15
          ],
          24 => [
            0 => "CCCGCCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGG",
            1 => 15
          ],
          25 => [
            0 => "CCCGCCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTGGAGGCTGGCGGG",
            1 => 15
          ],
          26 => [
            0 => "CCCGCCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTGGAGGCTGGCGGGG",
            1 => 15
          ],
          27 => [
            0 => "CCGCCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTGGAGGCTGGCGG",
            1 => 16
          ],
          28 => [
            0 => "CGCCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTGGAGGCTGGCG",
            1 => 17
          ],
          29 => [
            0 => "GCCCGCACCATGGTCAGGC",
            1 => 18
          ],
          30 => [
            0 => "GCCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGGC",
            1 => 18
          ],
          31 => [
            0 => "GCCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTGGAGGC",
            1 => 18
          ],
          32 => [
            0 => "GCCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTGGAGGCTGGC",
            1 => 18
          ],
          33 => [
            0 => "GCCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTGGAGGCTGGCGGGGC",
            1 => 18
          ],
          34 => [
            0 => "CCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGG",
            1 => 19
          ],
          35 => [
            0 => "CCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGG",
            1 => 19
          ],
          36 => [
            0 => "CCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTGGAGGCTGGCGGG",
            1 => 19
          ],
          37 => [
            0 => "CCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTGGAGGCTGGCGGGG",
            1 => 19
          ],
          38 => [
            0 => "CCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTGGAGGCTGGCGG",
            1 => 20
          ],
          39 => [
            0 => "CGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTGGAGGCTGGCG",
            1 => 21
          ],
          40 => [
            0 => "GCACCATGGTCAGGCATGC",
            1 => 22
          ],
          41 => [
            0 => "GCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGC",
            1 => 22
          ],
          42 => [
            0 => "CACCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTG",
            1 => 23
          ],
          43 => [
            0 => "ACCATGGT",
            1 => 24
          ],
          44 => [
            0 => "ACCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGGT",
            1 => 24
          ],
          45 => [
            0 => "CCATGG",
            1 => 25
          ],
          46 => [
            0 => "CCATGGTCAGGCATGCCCCTCCTCATCGCTGG",
            1 => 25
          ],
          47 => [
            0 => "CCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTGG",
            1 => 25
          ],
          48 => [
            0 => "CCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTGGAGGCTGG",
            1 => 25
          ],
          49 => [
            0 => "CATGGTCAGGCATG",
            1 => 26
          ],
          50 => [
            0 => "ATGGTCAGGCAT",
            1 => 27
          ],
          51 => [
            0 => "ATGGTCAGGCATGCCCCTCCTCAT",
            1 => 27
          ],
          52 => [
            0 => "TGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCA",
            1 => 28
          ],
          53 => [
            0 => "CAGGCATGCCCCTCCTCATCGCTG",
            1 => 32
          ],
          54 => [
            0 => "CAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTG",
            1 => 32
          ],
          55 => [
            0 => "CAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTGGAGGCTG",
            1 => 32
          ],
          56 => [
            0 => "AGGCATGCCCCT",
            1 => 33
          ],
          57 => [
            0 => "AGGCATGCCCCTCCT",
            1 => 33
          ],
          58 => [
            0 => "GGCATGCC",
            1 => 34
          ],
          59 => [
            0 => "GGCATGCCCCTCCTCATCGCTGGGCACAGCC",
            1 => 34
          ],
          60 => [
            0 => "GCATGC",
            1 => 35
          ],
          61 => [
            0 => "GCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGC",
            1 => 35
          ],
          62 => [
            0 => "ATGCCCCTCCTCAT",
            1 => 37
          ],
          63 => [
            0 => "TGCCCCTCCTCATCGCTGGGCA",
            1 => 38
          ],
          64 => [
            0 => "GCCCCTCCTCATCGCTGGGC",
            1 => 39
          ],
          65 => [
            0 => "GCCCCTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTGGAGGC",
            1 => 39
          ],
          66 => [
            0 => "GCCCCTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTGGAGGCTGGC",
            1 => 39
          ],
          67 => [
            0 => "GCCCCTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTGGAGGCTGGCGGGGC",
            1 => 39
          ],
          68 => [
            0 => "CCCCTCCTCATCGCTGGG",
            1 => 40
          ],
          69 => [
            0 => "CCCCTCCTCATCGCTGGGCACAGCCCAGAGGG",
            1 => 40
          ],
          70 => [
            0 => "CCCCTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTGGAGGCTGGCGGG",
            1 => 40
          ],
          71 => [
            0 => "CCCCTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTGGAGGCTGGCGGGG",
            1 => 40
          ],
          72 => [
            0 => "CCCTCCTCATCGCTGGG",
            1 => 41
          ],
          73 => [
            0 => "CCCTCCTCATCGCTGGGCACAGCCCAGAGGG",
            1 => 41
          ],
          74 => [
            0 => "CCCTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTGGAGGCTGGCGGG",
            1 => 41
          ],
          75 => [
            0 => "CCCTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTGGAGGCTGGCGGGG",
            1 => 41
          ],
          76 => [
            0 => "CCTCCTCATCGCTGGGCACAGCCCAGAGG",
            1 => 42
          ],
          77 => [
            0 => "CCTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTGGAGG",
            1 => 42
          ],
          78 => [
            0 => "CTCCTCATCGCTGGGCACAGCCCAGAG",
            1 => 43
          ],
          79 => [
            0 => "CTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTGGAG",
            1 => 43
          ],
          80 => [
            0 => "TCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTGGA",
            1 => 44
          ],
          81 => [
            0 => "CCTCATCGCTGGGCACAGCCCAGAGG",
            1 => 45
          ],
          82 => [
            0 => "CCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTGGAGG",
            1 => 45
          ],
          83 => [
            0 => "CTCATCGCTGGGCACAGCCCAGAG",
            1 => 46
          ],
          84 => [
            0 => "CTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTGGAG",
            1 => 46
          ],
          85 => [
            0 => "CGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTGGAGGCTGGCG",
            1 => 51
          ],
          86 => [
            0 => "GCTGGGCACAGC",
            1 => 52
          ],
          87 => [
            0 => "CTGGGCACAG",
            1 => 53
          ],
          88 => [
            0 => "CTGGGCACAGCCCAG",
            1 => 53
          ],
          89 => [
            0 => "CTGGGCACAGCCCAGAGGGTATAAACAG",
            1 => 53
          ],
          90 => [
            0 => "TGGGCACAGCCCA",
            1 => 54
          ],
          91 => [
            0 => "GGGCACAGCCC",
            1 => 55
          ],
          92 => [
            0 => "GGCACAGCC",
            1 => 56
          ],
          93 => [
            0 => "GCACAGCCCAGAGGGTATAAACAGTGC",
            1 => 57
          ],
          94 => [
            0 => "CACAGCCCAGAGGGTATAAACAGTG",
            1 => 58
          ],
          95 => [
            0 => "CAGCCCAGAGGGTATAAACAGTGCTG",
            1 => 60
          ],
          96 => [
            0 => "CAGCCCAGAGGGTATAAACAGTGCTGGAGGCTG",
            1 => 60
          ],
          97 => [
            0 => "AGCCCAGAGGGTATAAACAGTGCT",
            1 => 61
          ],
          98 => [
            0 => "AGCCCAGAGGGTATAAACAGTGCTGGAGGCT",
            1 => 61
          ],
          99 => [
            0 => "GCCCAGAGGGTATAAACAGTGCTGGAGGC",
            1 => 62
          ],
          100 => [
            0 => "GCCCAGAGGGTATAAACAGTGCTGGAGGCTGGC",
            1 => 62
          ],
          101 => [
            0 => "GCCCAGAGGGTATAAACAGTGCTGGAGGCTGGCGGGGC",
            1 => 62
          ],
          102 => [
            0 => "CCCAGAGGG",
            1 => 63
          ],
          103 => [
            0 => "CCCAGAGGGTATAAACAGTGCTGGAGGCTGGCGGG",
            1 => 63
          ],
          104 => [
            0 => "CCCAGAGGGTATAAACAGTGCTGGAGGCTGGCGGGG",
            1 => 63
          ],
          105 => [
            0 => "CCAGAGGGTATAAACAGTGCTGG",
            1 => 64
          ],
          106 => [
            0 => "CCAGAGGGTATAAACAGTGCTGGAGGCTGG",
            1 => 64
          ],
          107 => [
            0 => "CAGAGGGTATAAACAGTGCTG",
            1 => 65
          ],
          108 => [
            0 => "CAGAGGGTATAAACAGTGCTGGAGGCTG",
            1 => 65
          ],
          109 => [
            0 => "CAGTGCTG",
            1 => 78
          ],
          110 => [
            0 => "CAGTGCTGGAGGCTG",
            1 => 78
          ]
        ];

        $this->assertEquals($testPalindrome, $aExpected);
    }

    public function testFindPalindromeWithLen()
    {
        $sequenceManager = new SequenceManager($this->apiAminoMock, $this->apiNucleoMock, $this->apiElementsMock);
        $sequenceBuilder = new SequenceBuilder($sequenceManager);
        $sequenceBuilder->setSequence($this->sequence);

        $testPalindrome = $sequenceBuilder->findPalindrome(null, 100,0);

        $aExpected = [
            0 =>  [
                0 => "CTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTGGAGGCTGGCGGGGCAGGCCAGCTGAGTCCTGAGCAGCAGCCCAGCGCAGCCACCGAG",
                1 => 45
            ],
            1 =>  [
                0 => "AGGGTATAAACAGTGCTGGAGGCTGGCGGGGCAGGCCAGCTGAGTCCTGAGCAGCAGCCCAGCGCAGCCACCGAGACACCATGAGAGCCCTCACACTCCT",
                1 => 70
            ],
            2 =>  [
                0 => "CCTGAGCAGCAGCCCAGCGCAGCCACCGAGACACCATGAGAGCCCTCACACTCCTCGCCCTATTGGCCCTGGCCGCACTTTGCATCGCTGGCCAGGCAGG",
                1 => 115
            ],
            3 =>  [
                0 => "TCACACTCCTCGCCCTATTGGCCCTGGCCGCACTTTGCATCGCTGGCCAGGCAGGTGAGTGCCCCCACCTCCCCTCAGGCCGCATTGCAGTGGGGGCTGA",
                1 => 160
            ],
            4 =>  [
                0 => "CCTCGCCCTATTGGCCCTGGCCGCACTTTGCATCGCTGGCCAGGCAGGTGAGTGCCCCCACCTCCCCTCAGGCCGCATTGCAGTGGGGGCTGAGAGGAGG",
                1 => 167
            ],
            5 =>  [
                0 => "TTGCAGTGGGGGCTGAGAGGAGGAAGCACCATGGCCCACCTCTTCTCACCCCTTTGGCTGGCAGTCCCTTTGCAGTCTAACCACCTTGTTGCAGGCTCAA",
                1 => 244
            ],
            6 =>  [
                0 => "GGCTGAGAGGAGGAAGCACCATGGCCCACCTCTTCTCACCCCTTTGGCTGGCAGTCCCTTTGCAGTCTAACCACCTTGTTGCAGGCTCAATCCATTTGCC",
                1 => 254
            ],
            7 =>  [
                0 => "CTCTTCTCACCCCTTTGGCTGGCAGTCCCTTTGCAGTCTAACCACCTTGTTGCAGGCTCAATCCATTTGCCCCAGCTCTGCCCTTGCAGAGGGAGAGGAG",
                1 => 283
            ],
            8 =>  [
                0 => "GGCTGGCAGTCCCTTTGCAGTCTAACCACCTTGTTGCAGGCTCAATCCATTTGCCCCAGCTCTGCCCTTGCAGAGGGAGAGGAGGGAAGAGCAAGCTGCC",
                1 => 299
            ],
            9 =>  [
                0 => "CCTTTGCAGTCTAACCACCTTGTTGCAGGCTCAATCCATTTGCCCCAGCTCTGCCCTTGCAGAGGGAGAGGAGGGAAGAGCAAGCTGCCCGAGACGCAGG",
                1 => 310
            ],
            10 =>  [
                0 => "CTCAATCCATTTGCCCCAGCTCTGCCCTTGCAGAGGGAGAGGAGGGAAGAGCAAGCTGCCCGAGACGCAGGGGAAGGAGGATGAGGGCCCTGGGGATGAG",
                1 => 339
            ],
            11 =>  [
                0 => "AGCTCTGCCCTTGCAGAGGGAGAGGAGGGAAGAGCAAGCTGCCCGAGACGCAGGGGAAGGAGGATGAGGGCCCTGGGGATGAGCTGGGGTGAACCAGGCT",
                1 => 356
            ],
            12 =>  [
                0 => "GGGAAGAGCAAGCTGCCCGAGACGCAGGGGAAGGAGGATGAGGGCCCTGGGGATGAGCTGGGGTGAACCAGGCTCCCTTTCCTTTGCAGGTGCGAAGCCC",
                1 => 382
            ],
            13 =>  [
                0 => "GAAGCCCAGCGGTGCAGAGTCCAGCAAAGGTGCAGGTATGAGGATGGACCTGATGGGTTCCTGGACCCTCCCCTCTCACCCTGGTCCCTCAGTCTCATTC",
                1 => 475
            ],
            14 =>  [
                0 => "CAGCAAAGGTGCAGGTATGAGGATGGACCTGATGGGTTCCTGGACCCTCCCCTCTCACCCTGGTCCCTCAGTCTCATTCCCCCACTCCTGCCACCTCCTG",
                1 => 496
            ],
            15 =>  [
                0 => "TGGGTTCCTGGACCCTCCCCTCTCACCCTGGTCCCTCAGTCTCATTCCCCCACTCCTGCCACCTCCTGTCTGGCCATCAGGAAGGCCAGCCTGCTCCCCA",
                1 => 528
            ],
            16 =>  [
                0 => "GGTTCCTGGACCCTCCCCTCTCACCCTGGTCCCTCAGTCTCATTCCCCCACTCCTGCCACCTCCTGTCTGGCCATCAGGAAGGCCAGCCTGCTCCCCACC",
                1 => 530
            ],
            17 =>  [
                0 => "CAGTCTCATTCCCCCACTCCTGCCACCTCCTGTCTGGCCATCAGGAAGGCCAGCCTGCTCCCCACCTGATCCTCCCAAACCCAGAGCCACCTGATGCCTG",
                1 => 564
            ],
            18 =>  [
                0 => "CTGGCCATCAGGAAGGCCAGCCTGCTCCCCACCTGATCCTCCCAAACCCAGAGCCACCTGATGCCTGCCCCTCTGCTCCACAGCCTTTGTGTCCAAGCAG",
                1 => 597
            ],
            19 =>  [
                0 => "ACCTGATGCCTGCCCCTCTGCTCCACAGCCTTTGTGTCCAAGCAGGAGGGCAGCGAGGTAGTGAAGAGACCCAGGCGCTACCTGTATCAATGGCTGGGGT",
                1 => 652
            ],
            20 =>  [
                0 => "CAGCGAGGTAGTGAAGAGACCCAGGCGCTACCTGTATCAATGGCTGGGGTGAGAGAAAAGGCAGAGCTGGGCCAAGGCCCTGCCTCTCCGGGATGGTCTG",
                1 => 702
            ],
            21 =>  [
                0 => "CCCAGGCGCTACCTGTATCAATGGCTGGGGTGAGAGAAAAGGCAGAGCTGGGCCAAGGCCCTGCCTCTCCGGGATGGTCTGTGGGGGAGCTGCAGCAGGG",
                1 => 721
            ],
            22 =>  [
                0 => "CAATGGCTGGGGTGAGAGAAAAGGCAGAGCTGGGCCAAGGCCCTGCCTCTCCGGGATGGTCTGTGGGGGAGCTGCAGCAGGGAGTGGCCTCTCTGGGTTG",
                1 => 739
            ],
            23 =>  [
                0 => "AGGCAGAGCTGGGCCAAGGCCCTGCCTCTCCGGGATGGTCTGTGGGGGAGCTGCAGCAGGGAGTGGCCTCTCTGGGTTGTGGTGGGGGTACAGGCAGCCT",
                1 => 760
            ],
            24 =>  [
                0 => "GCAGAGCTGGGCCAAGGCCCTGCCTCTCCGGGATGGTCTGTGGGGGAGCTGCAGCAGGGAGTGGCCTCTCTGGGTTGTGGTGGGGGTACAGGCAGCCTGC",
                1 => 762
            ],
            25 =>  [
                0 => "GCCAAGGCCCTGCCTCTCCGGGATGGTCTGTGGGGGAGCTGCAGCAGGGAGTGGCCTCTCTGGGTTGTGGTGGGGGTACAGGCAGCCTGCCCTGGTGGGC",
                1 => 772
            ],
            26 =>  [
                0 => "TCTGTGGGGGAGCTGCAGCAGGGAGTGGCCTCTCTGGGTTGTGGTGGGGGTACAGGCAGCCTGCCCTGGTGGGCACCCTGGAGCCCCATGTGTAGGGAGA",
                1 => 798
            ],
            27 =>  [
                0 => "TGCAGCAGGGAGTGGCCTCTCTGGGTTGTGGTGGGGGTACAGGCAGCCTGCCCTGGTGGGCACCCTGGAGCCCCATGTGTAGGGAGAGGAGGGATGGGCA",
                1 => 811
            ],
            28 =>  [
                0 => "GCCTCTCTGGGTTGTGGTGGGGGTACAGGCAGCCTGCCCTGGTGGGCACCCTGGAGCCCCATGTGTAGGGAGAGGAGGGATGGGCATTTTGCACGGGGGC",
                1 => 825
            ],
            29 =>  [
                0 => "GCCACCACGTCGGGTGTCTCAGAGCCCCAGTCCCCTACCCGGATCCCCTGGAGCCCAGGAGGGAGGTGTGTGAGCTCAATCCGGACTGTGACGAGTTGGC",
                1 => 929
            ],
            30 =>  [
                0 => "GAGGTGTGTGAGCTCAATCCGGACTGTGACGAGTTGGCTGACCACATCGGCTTTCAGGAGGCCTATCGGCGCTTCTACGGCCCGGTCTAGGGTGTCGCTC",
                1 => 991
            ],
            31 =>  [
                0 => "AGGAGGCCTATCGGCGCTTCTACGGCCCGGTCTAGGGTGTCGCTCTGCTGGCCTGGCCGGCAACCCCAGTTCTGCTCCTCTCCAGGCACCCTTCTTTCCT",
                1 => 1046
            ],
            32 =>  [
                0 => "GGCGCTTCTACGGCCCGGTCTAGGGTGTCGCTCTGCTGGCCTGGCCGGCAACCCCAGTTCTGCTCCTCTCCAGGCACCCTTCTTTCCTCTTCCCCTTGCC",
                1 => 1058
            ],
        ];

        $this->assertEquals($aExpected, $testPalindrome);
    }

    public function testFindPalindromeWithNoPalenAndNoLen()
    {
        $sequenceManager = new SequenceManager($this->apiAminoMock, $this->apiNucleoMock, $this->apiElementsMock);
        $sequenceBuilder = new SequenceBuilder($sequenceManager);
        $sequenceBuilder->setSequence($this->sequence);

        $testPalindrome = $sequenceBuilder->findPalindrome(null, 0, 0);
        $this->assertFalse($testPalindrome);
    }

    public function testFindPalindromeWitPalenAndLen()
    {
        $sequenceManager = new SequenceManager($this->apiAminoMock, $this->apiNucleoMock, $this->apiElementsMock);
        $sequenceBuilder = new SequenceBuilder($sequenceManager);
        $sequenceBuilder->setSequence($this->sequence);

        $testPalindrome = $sequenceBuilder->findPalindrome(null, 2,2);

        $aExpected = [
            0 =>  [
                0 => "GC",
                1 => 1,
            ],
            1 =>  [
                0 => "AT",
                1 => 5,
            ],
            2 =>  [
                0 => "TA",
                1 => 13,
            ],
            3 =>  [
                0 => "CG",
                1 => 19,
            ],
            4 =>  [
                0 => "GC",
                1 => 20,
            ],
            5 =>  [
                0 => "CG",
                1 => 23,
            ],
            6 =>  [
                0 => "GC",
                1 => 24,
            ],
            7 =>  [
                0 => "AT",
                1 => 29,
            ],
            8 =>  [
                0 => "GC",
                1 => 37,
            ],
            9 =>  [
                0 => "AT",
                1 => 39,
            ],
            10 =>  [
                0 => "GC",
                1 => 41,
            ],
            11 =>  [
                0 => "AT",
                1 => 51,
            ],
            12 =>  [
                0 => "CG",
                1 => 53,
            ],
            13 =>  [
                0 => "GC",
                1 => 54,
            ],
            14 =>  [
                0 => "GC",
                1 => 59,
            ],
            15 =>  [
                0 => "GC",
                1 => 64,
            ],
            16 =>  [
                0 => "TA",
                1 => 74,
            ],
            17 =>  [
                0 => "AT",
                1 => 75,
            ],
            18 =>  [
                0 => "TA",
                1 => 76,
            ],
            19 =>  [
                0 => "GC",
                1 => 84,
            ],
            20 =>  [
                0 => "GC",
                1 => 91,
            ],
            21 =>  [
                0 => "GC",
                1 => 95,
            ],
            22 =>  [
                0 => "CG",
                1 => 96,
            ],
            23 =>  [
                0 => "GC",
                1 => 100,
            ],
            24 =>  [
                0 => "GC",
                1 => 104,
            ],
            25 =>  [
                0 => "GC",
                1 => 108,
            ],
            26 =>  [
                0 => "GC",
                1 => 120,
            ],
            27 =>  [
                0 => "GC",
                1 => 123,
            ],
            28 =>  [
                0 => "GC",
                1 => 126,
            ],
            29 =>  [
                0 => "GC",
                1 => 131,
            ],
            30 =>  [
                0 => "CG",
                1 => 132,
            ],
            31 =>  [
                0 => "GC",
                1 => 133,
            ],
            32 =>  [
                0 => "GC",
                1 => 136,
            ],
            33 =>  [
                0 => "CG",
                1 => 141,
            ],
            34 =>  [
                0 => "AT",
                1 => 150,
            ],
            35 =>  [
                0 => "GC",
                1 => 156
            ],
            36 =>  [
                0 => "CG",
                1 => 170
            ],
            37 =>  [
                0 => "GC",
                1 => 171
            ],
            38 =>  [
                0 => "TA",
                1 => 175
            ],
            39 =>  [
                0 => "AT",
                1 => 176
            ],
            40 =>  [
                0 => "GC",
                1 => 180
            ],
            41 =>  [
                0 => "GC",
                1 => 186
            ],
            42 =>  [
                0 => "CG",
                1 => 188
            ],
            43 =>  [
                0 => "GC",
                1 => 189
            ],
            44 =>  [
                0 => "GC",
                1 => 196
            ],
            45 =>  [
                0 => "AT",
                1 => 198
            ],
            46 =>  [
                0 => "CG",
                1 => 200
            ],
            47 =>  [
                0 => "GC",
                1 => 201
            ],
            48 =>  [
                0 => "GC",
                1 => 205
            ],
            49 =>  [
                0 => "GC",
                1 => 210
            ],
            50 =>  [
                0 => "GC",
                1 => 220
            ],
            51 =>  [
                0 => "GC",
                1 => 238
            ],
            52 =>  [
                0 => "CG",
                1 => 240
            ],
            53 =>  [
                0 => "GC",
                1 => 241
            ],
            54 =>  [
                0 => "AT",
                1 => 243
            ],
            55 =>  [
                0 => "GC",
                1 => 246
            ],
            56 =>  [
                0 => "GC",
                1 => 255
            ],
            57 =>  [
                0 => "GC",
                1 => 269
            ],
            58 =>  [
                0 => "AT",
                1 => 274
            ],
            59 =>  [
                0 => "GC",
                1 => 277
            ],
            60 =>  [
                0 => "GC",
                1 => 300
            ],
            61 =>  [
                0 => "GC",
                1 => 304
            ],
            62 =>  [
                0 => "GC",
                1 => 315
            ],
            63 =>  [
                0 => "TA",
                1 => 321
            ],
            64 =>  [
                0 => "GC",
                1 => 334
            ],
            65 =>  [
                0 => "GC",
                1 => 338
            ],
            66 =>  [
                0 => "AT",
                1 => 343
            ],
            67 =>  [
                0 => "AT",
                1 => 347
            ],
            68 =>  [
                0 => "GC",
                1 => 351
            ],
            69 =>  [
                0 => "GC",
                1 => 357
            ],
            70 =>  [
                0 => "GC",
                1 => 362
            ],
            71 =>  [
                0 => "GC",
                1 => 368
            ],
            72 =>  [
                0 => "GC",
                1 => 389
            ],
            73 =>  [
                0 => "GC",
                1 => 393
            ],
            74 =>  [
                0 => "GC",
                1 => 396
            ],
            75 =>  [
                0 => "CG",
                1 => 399
            ],
            76 =>  [
                0 => "CG",
                1 => 404
            ],
            77 =>  [
                0 => "GC",
                1 => 405
            ],
            78 =>  [
                0 => "AT",
                1 => 419
            ],
            79 =>  [
                0 => "GC",
                1 => 425
            ],
            80 =>  [
                0 => "AT",
                1 => 434
            ],
            81 =>  [
                0 => "GC",
                1 => 438
            ],
            82 =>  [
                0 => "GC",
                1 => 453
            ],
            83 =>  [
                0 => "GC",
                1 => 467
            ],
            84 =>  [
                0 => "GC",
                1 => 473
            ],
            85 =>  [
                0 => "CG",
                1 => 474
            ],
            86 =>  [
                0 => "GC",
                1 => 478
            ],
            87 =>  [
                0 => "GC",
                1 => 483
            ],
            88 =>  [
                0 => "CG",
                1 => 484
            ],
            89 =>  [
                0 => "GC",
                1 => 488
            ],
            90 =>  [
                0 => "GC",
                1 => 498
            ],
            91 =>  [
                0 => "GC",
                1 => 506
            ],
            92 =>  [
                0 => "TA",
                1 => 511
            ],
            93 =>  [
                0 => "AT",
                1 => 512
            ],
            94 =>  [
                0 => "AT",
                1 => 518
            ],
            95 =>  [
                0 => "AT",
                1 => 527
            ],
            96 =>  [
                0 => "AT",
                1 => 571
            ],
            97 =>  [
                0 => "GC",
                1 => 585
            ],
            98 =>  [
                0 => "GC",
                1 => 600
            ],
            99 =>  [
                0 => "AT",
                1 => 603
            ],
            100 =>  [
                0 => "GC",
                1 => 612
            ],
            101 =>  [
                0 => "GC",
                1 => 616
            ],
            102 =>  [
                0 => "GC",
                1 => 620
            ],
            103 =>  [
                0 => "AT",
                1 => 632
            ],
            104 =>  [
                0 => "GC",
                1 => 649
            ],
            105 =>  [
                0 => "AT",
                1 => 657
            ],
            106 =>  [
                0 => "GC",
                1 => 659
            ],
            107 =>  [
                0 => "GC",
                1 => 663
            ],
            108 =>  [
                0 => "GC",
                1 => 671
            ],
            109 =>  [
                0 => "GC",
                1 => 679
            ],
            110 =>  [
                0 => "GC",
                1 => 693
            ],
            111 =>  [
                0 => "GC",
                1 => 701
            ],
            112 =>  [
                0 => "GC",
                1 => 704
            ],
            113 =>  [
                0 => "CG",
                1 => 705
            ],
            114 =>  [
                0 => "TA",
                1 => 710
            ],
            115 =>  [
                0 => "GC",
                1 => 726
            ],
            116 =>  [
                0 => "CG",
                1 => 727
            ],
            117 =>  [
                0 => "GC",
                1 => 728
            ],
            118 =>  [
                0 => "TA",
                1 => 730
            ],
            119 =>  [
                0 => "TA",
                1 => 736
            ],
            120 =>  [
                0 => "AT",
                1 => 737
            ],
            121 =>  [
                0 => "AT",
                1 => 741
            ],
            122 =>  [
                0 => "GC",
                1 => 744
            ],
            123 =>  [
                0 => "GC",
                1 => 762
            ],
            124 =>  [
                0 => "GC",
                1 => 767
            ],
            125 =>  [
                0 => "GC",
                1 => 772
            ],
            126 =>  [
                0 => "GC",
                1 => 778
            ],
            127 =>  [
                0 => "GC",
                1 => 783
            ],
            128 =>  [
                0 => "CG",
                1 => 790
            ],
            129 =>  [
                0 => "AT",
                1 => 794
            ],
            130 =>  [
                0 => "GC",
                1 => 809
            ],
            131 =>  [
                0 => "GC",
                1 => 812
            ],
            132 =>  [
                0 => "GC",
                1 => 815
            ],
            133 =>  [
                0 => "GC",
                1 => 825
            ],
            134 =>  [
                0 => "TA",
                1 => 848
            ],
            135 =>  [
                0 => "GC",
                1 => 853
            ],
            136 =>  [
                0 => "GC",
                1 => 856
            ],
            137 =>  [
                0 => "GC",
                1 => 860
            ],
            138 =>  [
                0 => "GC",
                1 => 870
            ],
            139 =>  [
                0 => "GC",
                1 => 880
            ],
            140 =>  [
                0 => "AT",
                1 => 885
            ],
            141 =>  [
                0 => "TA",
                1 => 890
            ],
            142 =>  [
                0 => "AT",
                1 => 904
            ],
            143 =>  [
                0 => "GC",
                1 => 908
            ],
            144 =>  [
                0 => "AT",
                1 => 910
            ],
            145 =>  [
                0 => "GC",
                1 => 915
            ],
            146 =>  [
                0 => "CG",
                1 => 918
            ],
            147 =>  [
                0 => "GC",
                1 => 923
            ],
            148 =>  [
                0 => "AT",
                1 => 927
            ],
            149 =>  [
                0 => "GC",
                1 => 929
            ],
            150 =>  [
                0 => "CG",
                1 => 936
            ],
            151 =>  [
                0 => "CG",
                1 => 939
            ],
            152 =>  [
                0 => "GC",
                1 => 952
            ],
            153 =>  [
                0 => "TA",
                1 => 964
            ],
            154 =>  [
                0 => "CG",
                1 => 968
            ],
            155 =>  [
                0 => "AT",
                1 => 971
            ],
            156 =>  [
                0 => "GC",
                1 => 981
            ],
            157 =>  [
                0 => "GC",
                1 => 1002
            ],
            158 =>  [
                0 => "AT",
                1 => 1007
            ],
            159 =>  [
                0 => "CG",
                1 => 1010
            ],
            160 =>  [
                0 => "CG",
                1 => 1020
            ],
            161 =>  [
                0 => "GC",
                1 => 1027
            ],
            162 =>  [
                0 => "AT",
                1 => 1036
            ],
            163 =>  [
                0 => "CG",
                1 => 1038
            ],
            164 =>  [
                0 => "GC",
                1 => 1040
            ],
            165 =>  [
                0 => "GC",
                1 => 1051
            ],
            166 =>  [
                0 => "TA",
                1 => 1054
            ],
            167 =>  [
                0 => "AT",
                1 => 1055
            ],
            168 =>  [
                0 => "CG",
                1 => 1057
            ],
            169 =>  [
                0 => "GC",
                1 => 1059
            ],
            170 =>  [
                0 => "CG",
                1 => 1060
            ],
            171 =>  [
                0 => "GC",
                1 => 1061
            ],
            172 =>  [
                0 => "TA",
                1 => 1066
            ],
            173 =>  [
                0 => "CG",
                1 => 1068
            ],
            174 =>  [
                0 => "GC",
                1 => 1070
            ],
            175 =>  [
                0 => "CG",
                1 => 1073
            ],
            176 =>  [
                0 => "TA",
                1 => 1078
            ],
            177 =>  [
                0 => "CG",
                1 => 1086
            ],
            178 =>  [
                0 => "GC",
                1 => 1087
            ],
            179 =>  [
                0 => "GC",
                1 => 1092
            ],
            180 =>  [
                0 => "GC",
                1 => 1096
            ],
            181 =>  [
                0 => "GC",
                1 => 1101
            ],
            182 =>  [
                0 => "CG",
                1 => 1103
            ],
            183 =>  [
                0 => "GC",
                1 => 1105
            ],
            184 =>  [
                0 => "GC",
                1 => 1119
            ],
            185 =>  [
                0 => "GC",
                1 => 1131
            ],
            186 =>  [
                0 => "GC",
                1 => 1155
            ],
            187 =>  [
                0 => "GC",
                1 => 1161
            ],
            188 =>  [
                0 => "GC",
                1 => 1175
            ],
            189 =>  [
                0 => "TA",
                1 => 1179
            ],
            190 =>  [
                0 => "AT",
                1 => 1180
            ],
            191 =>  [
                0 => "AT",
                1 => 1184
            ],
            192 =>  [
                0 => "AT",
                1 => 1197
            ],
            193 =>  [
                0 => "AT",
                1 => 1200
            ],
            194 =>  [
                0 => "GC",
                1 => 1206
            ],
            195 =>  [
                0 => "GC",
                1 => 1209
            ],
            196 =>  [
                0 => "AT",
                1 => 1217
            ],
            197 =>  [
                0 => "TA",
                1 => 1218
            ]
        ];

        $this->assertEquals($testPalindrome, $aExpected);
    }

    public function testIsMirror()
    {
        $sequenceManager = new SequenceManager($this->apiAminoMock, $this->apiNucleoMock, $this->apiElementsMock);
        $sequenceBuilder = new SequenceBuilder($sequenceManager);

        $isPalindrome = $sequenceBuilder->isPalindrome("TTTAAAGCTTTAAA");
        $this->assertTrue($isPalindrome);
    }

    public function testFindMirror()
    {
        $oSequence = new Sequence();
        $oSequence->setSequence("AGGGAATTAAGTAAATGGTAGTGG");

        $sequenceManager = new SequenceManager($this->apiAminoMock, $this->apiNucleoMock, $this->apiElementsMock);
        $sequenceBuilder = new SequenceBuilder($sequenceManager);
        $sequenceBuilder->setSequence($this->sequence);

        $aMirrors = $sequenceBuilder->findMirror($oSequence->getSequence(), 6, 8, "E");

        $aExpected = [
          6 => [
            0 => [
              0 => "AATTAA",
              1 => 4
            ],
            1 => [
              0 => "ATGGTA",
              1 => 14
            ]
          ],
          8 => [
            0 => [
              0 => "GAATTAAG",
              1 => 3
            ]
          ]
        ];

        $this->assertEquals($aMirrors, $aExpected);
    }
}