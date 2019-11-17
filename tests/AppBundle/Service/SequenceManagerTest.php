<?php


namespace Tests\AppBundle\Service;


use AppBundle\Entity\Sequence;
use AppBundle\Service\SequenceManager;
use PHPUnit\Framework\TestCase;

class SequenceManagerTest extends TestCase
{
    private $apiMock;

    private $sequence;

    public function setUp()
    {
        $aElements = [
            "carbone" => 12.01,
            "oxygene" => 16,
            "nitrate" => 14.01,
            "hydrogene" => 1.01,
            "phosphore" => 30.97,
            "water" => 18.015
        ];

        $aDNAComplements = [
          "A" => "T",
          "T" => "A",
          "G" => "C",
          "C" => "G",
        ];

        $aRNAComplements = [
          "A" => "U",
          "U" => "A",
          "G" => "C",
          "C" => "G",
        ];

        $getDNAWeight = [
          "A" => 313.245,
          "T" => 304.225,
          "G" => 329.245,
          "C" => 289.215,
        ];

        $getRNAWeight = [
          "A" => 329.245,
          "U" => 306.195,
          "G" => 345.245,
          "C" => 305.215
        ];

        $aWater = [
          "id" => 6,
          "name" => "water",
          "weight" => 18.015
        ];

        $aAminos = [
          "STOP" => [
            1 => "*",
            3 => "STP"
          ],
          "Alanine" => [
            1 => "A",
            3 => "Ala"
          ],
          "Aspartate or asparagine" => [
            1 => "B",
            3 => "N/A"
          ],
          "Cysteine" => [
            1 => "C",
            3 => "Cys"
          ],
          "Aspartic acid" => [
            1 => "D",
            3 => "Asp"
          ],
          "Glutamic acid" => [
            1 => "E",
            3 => "Glu"
          ],
          "Phenylalanine" => [
            1 => "F",
            3 => "Phe"
          ],
          "Glycine" => [
            1 => "G",
            3 => "Gly"
          ],
          "Histidine" => [
            1 => "H",
            3 => "His"
          ],
          "Isoleucine" => [
            1 => "I",
            3 => "Ile"
          ],
          "Lysine" => [
            1 => "K",
            3 => "Lys"
          ],
          "Leucine" => [
            1 => "L",
            3 => "Leu"
          ],
          "Methionine" => [
            1 => "M",
            3 => "Met"
          ],
          "Asparagine" => [
            1 => "N",
            3 => "Asn"
          ],
          "Pyrrolysine" => [
            1 => "O",
            3 => "Pyr"
          ],
          "Proline" => [
            1 => "P",
            3 => "Pro"
          ],
          "Glutamine" => [
            1 => "Q",
            3 => "Gin"
          ],
          "Arginine" => [
            1 => "R",
            3 => "Arg"
          ],
          "Serine" => [
            1 => "S",
            3 => "Ser"
          ],
          "Threonine" => [
            1 => "T",
            3 => "Thr"
          ],
          "Selenocysteine" => [
            1 => "U",
            3 => "Sec"
          ],
          "Valine" => [
            1 => "V",
            3 => "Val"
          ],
          "Tryptophan" => [
            1 => "W",
            3 => "Trp"
          ],
          "Any" => [
            1 => "X",
            3 => "XXX"
          ],
          "Tyrosine" => [
            1 => "Y",
            3 => "Tyr"
          ],
          "Glutamate or glutamine" => [
            1 => "Z",
            3 => "N/A"
          ],
        ];

        /**
         * Mock API
         */
        $clientMock = $this->getMockBuilder('GuzzleHttp\Client')->getMock();
        $serializerMock = $this->getMockBuilder('JMS\Serializer\Serializer')
            ->disableOriginalConstructor()
            ->getMock();
        $this->apiMock = $this->getMockBuilder('AppBundle\Bioapi\Bioapi')
            ->setConstructorArgs([$clientMock, $serializerMock])
            ->setMethods(['getElements','getDNAComplement','getRNAComplement','getDNAWeight','getRNAWeight','getWater','getAminosOnlyLetters'])
            ->getMock();
        $this->apiMock->method("getElements")->will($this->returnValue($aElements));
        $this->apiMock->method("getDNAComplement")->will($this->returnValue($aDNAComplements));
        $this->apiMock->method("getRNAComplement")->will($this->returnValue($aRNAComplements));
        $this->apiMock->method("getDNAWeight")->will($this->returnValue($getDNAWeight));
        $this->apiMock->method("getRNAWeight")->will($this->returnValue($getRNAWeight));
        $this->apiMock->method("getWater")->will($this->returnValue($aWater));
        $this->apiMock->method("getAminosOnlyLetters")->will($this->returnValue($aAminos));

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
        $sequenceManager = new SequenceManager($this->apiMock);
        $sequenceManager->setSequence($this->sequence);

        $aComplement = $sequenceManager->complement("DNA");
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
        $sequenceManager = new SequenceManager($this->apiMock);
        $sequenceManager->setSequence($this->sequence);

        $sHalf = $sequenceManager->halfSequence("GATTAG", 0);
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
        $sequenceManager = new SequenceManager($this->apiMock);
        $sExpandNa = $sequenceManager->expandNa("GATTAGSW");

        $sExpected = "GATTAG[GC][AT]";

        $this->assertEquals($sExpandNa, $sExpected);
    }

    public function testMolWT()
    {
        $sequenceManager = new SequenceManager($this->apiMock);
        $sequenceManager->setSequence($this->sequence);
        $fMolWt = round($sequenceManager->molwt("upperlimit"),1);

        $fExpected = 379669.7;
        $this->assertEquals($fMolWt, $fExpected);
    }

    public function testSubseq()
    {
        $sequenceManager = new SequenceManager($this->apiMock);
        $sequenceManager->setSequence($this->sequence);

        $sExpected = "CAGATTCCCCCTAGACCCGCCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTGGAGGCTGGCGGGGC";

        $sCoupe = $sequenceManager->subSeq(2,100);
        $this->assertEquals($sCoupe, $sExpected);
    }

    public function testPatpos()
    {
        $sequenceManager = new SequenceManager($this->apiMock);
        $sequenceManager->setSequence($this->sequence);

        $test = $sequenceManager->patPos("TTT");
        $aExpected = [
          "TTT" =>  [
            0 => 193,
            1 => 296,
            2 => 312,
            3 => 348,
            4 => 459,
            5 => 464,
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
        $sequenceManager = new SequenceManager($this->apiMock);
        $sequenceManager->setSequence($this->sequence);

        $test = $sequenceManager->patPoso("TTT");
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
        $sequenceManager = new SequenceManager($this->apiMock);
        $sequenceManager->setSequence($this->sequence);

        $test = $sequenceManager->symFreq("A");
        $iExpected = 217;

        $this->assertEquals($test, $iExpected);
    }

    public function testGetCodon()
    {
        $sequenceManager = new SequenceManager($this->apiMock);
        $sequenceManager->setSequence($this->sequence);

        $codon = $sequenceManager->getCodon(3);
        $sExpected = "CCC";

        $this->assertEquals($codon, $sExpected);
    }

    public function testTranslate()
    {
        $sequenceManager = new SequenceManager($this->apiMock);
        $sequenceManager->setSequence($this->sequence);

        $sExpected = "GRFPLDPPAPWSGMPLLIAGHSPEGINSAGGWRGRPAES*AAAQRSHRDTMRALTLLALLALAALCIAGQAGECPHLPSGRIAVGAERRKH";
        $sExpected.= "HGPPLLTPLAGSPFAV*PPCCRLNPFAPALPLQRERREEQAARDAGEGG*GPWG*AGVNQAPFPLQVRSPAVQSPAKVQV*GWT*WVPGPS";
        $sExpected.= "PLTLVPQSHSPTPATSCLAIRKASLLPT*SSQTQSHLMPAPLLHSLCVQAGGQRGSEETQALPVSMAGVREKAELGQGPASPGWSVGELQQ";
        $sExpected.= "GVASLGCGGGTGSLPWWAPWSPMCRERRDGHFARGLMPPRRVSQSPSPLPGSPGAQEGGV*AQSGL*RVG*PHRLSGGLSALLRPGLGCRS";
        $sExpected.= "AGLAGNPSSAPLQAPFFPLPLALALTSQPYGCGVPIIPAAPK*TPEX";

        $translate = $sequenceManager->translate();

        $this->assertEquals($translate, $sExpected);
    }
}