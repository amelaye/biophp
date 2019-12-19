<?php


namespace Tests\AppBundle\Service;


use AppBundle\Entity\Sequencing\Sequence;
use AppBundle\Service\SequenceManager;
use PHPUnit\Framework\TestCase;
use AppBundle\Api\DTO\AminoDTO;

class SequenceManagerTest extends TestCase
{
    private $apiMock;

    private $sequence;

    private $apiAminoMock;

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
        $this->apiMock = $this->getMockBuilder('AppBundle\Api\Bioapi')
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
        $sequenceManager = new SequenceManager($this->apiMock, $this->apiAminoMock);
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
        $sequenceManager = new SequenceManager($this->apiMock, $this->apiAminoMock);
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
        $sequenceManager = new SequenceManager($this->apiMock, $this->apiAminoMock);
        $sExpandNa = $sequenceManager->expandNa("GATTAGSW");

        $sExpected = "GATTAG[GC][AT]";

        $this->assertEquals($sExpandNa, $sExpected);
    }

    public function testMolWT()
    {
        $sequenceManager = new SequenceManager($this->apiMock, $this->apiAminoMock);
        $sequenceManager->setSequence($this->sequence);
        $fMolWt = round($sequenceManager->molwt("upperlimit"),1);

        $fExpected = 379669.7;
        $this->assertEquals($fMolWt, $fExpected);
    }

    public function testSubseq()
    {
        $sequenceManager = new SequenceManager($this->apiMock, $this->apiAminoMock);
        $sequenceManager->setSequence($this->sequence);

        $sExpected = "CAGATTCCCCCTAGACCCGCCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTGGAGGCTGGCGGGGC";

        $sCoupe = $sequenceManager->subSeq(2,100);
        $this->assertEquals($sCoupe, $sExpected);
    }

    public function testPatpos()
    {
        $sequenceManager = new SequenceManager($this->apiMock, $this->apiAminoMock);
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
        $sequenceManager = new SequenceManager($this->apiMock, $this->apiAminoMock);
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
        $sequenceManager = new SequenceManager($this->apiMock, $this->apiAminoMock);
        $sequenceManager->setSequence($this->sequence);

        $test = $sequenceManager->symFreq("A");
        $iExpected = 217;

        $this->assertEquals($test, $iExpected);
    }

    public function testGetCodon()
    {
        $sequenceManager = new SequenceManager($this->apiMock, $this->apiAminoMock);
        $sequenceManager->setSequence($this->sequence);

        $codon = $sequenceManager->getCodon(3);
        $sExpected = "CCC";

        $this->assertEquals($codon, $sExpected);
    }

    public function testTranslate()
    {
        $sequenceManager = new SequenceManager($this->apiMock, $this->apiAminoMock);
        $sequenceManager->setSequence($this->sequence);

        $sExpected = "GRFPLDPPAPWSGMPLLIAGHSPEGINSAGGWRGRPAES*AAAQRSHRDTMRALTLLALLALAALCIAGQAGECPHLPSGRIAVGAERRKH";
        $sExpected.= "HGPPLLTPLAGSPFAV*PPCCRLNPFAPALPLQRERREEQAARDAGEGG*GPWG*AGVNQAPFPLQVRSPAVQSPAKVQV*GWT*WVPGPS";
        $sExpected.= "PLTLVPQSHSPTPATSCLAIRKASLLPT*SSQTQSHLMPAPLLHSLCVQAGGQRGSEETQALPVSMAGVREKAELGQGPASPGWSVGELQQ";
        $sExpected.= "GVASLGCGGGTGSLPWWAPWSPMCRERRDGHFARGLMPPRRVSQSPSPLPGSPGAQEGGV*AQSGL*RVG*PHRLSGGLSALLRPGLGCRS";
        $sExpected.= "AGLAGNPSSAPLQAPFFPLPLALALTSQPYGCGVPIIPAAPK*TPEX";

        $translate = $sequenceManager->translate();

        $this->assertEquals($translate, $sExpected);
    }

    public function testCharge()
    {
        $sequenceManager = new SequenceManager($this->apiMock, $this->apiAminoMock);
        $charge = $sequenceManager->charge("GAVLIFYWKRH");
        $sExpected = "NNNNNNNNCCC";

        $this->assertEquals($charge, $sExpected);
    }

    public function testFindPalindrome()
    {
        $sequenceManager = new SequenceManager($this->apiMock, $this->apiAminoMock);
        $sequenceManager->setSequence($this->sequence);
        $sCoupe = $sequenceManager->subSeq(2,100);

        $testPalindrome = $sequenceManager->findPalindrome($sCoupe, null,3);

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

        $sequenceManager = new SequenceManager($this->apiMock, $this->apiAminoMock);
        $sequenceManager->setSequence($oSequence);

        $aMirrors = $sequenceManager->findMirror($oSequence->getSequence(), 6, 8, "E");

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