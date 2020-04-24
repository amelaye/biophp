<?php


namespace Tests\AppBundle\Service;


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