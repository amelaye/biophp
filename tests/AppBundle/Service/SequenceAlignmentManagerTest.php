<?php


namespace Tests\AppBundle\Service;


use AppBundle\Entity\Sequence;
use AppBundle\Service\SequenceAlignmentManager;
use AppBundle\Service\SequenceManager;
use PHPUnit\Framework\TestCase;

class SequenceAlignmentManagerTest extends TestCase
{
    private $apiMock;

    private $sequenceManager;

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

        $sequenceManager = new SequenceManager($this->apiMock);
        $this->sequenceManager = $sequenceManager;
/*
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
*/
    }

    public function testSortAlpha()
    {
        $sequenceAlignmentManager = new SequenceAlignmentManager($this->sequenceManager);
        $sequenceAlignmentManager->setFilename("data/clustal.txt");
        $sequenceAlignmentManager->setFormat("CLUSTAL");
        $sequenceAlignmentManager->parseFile();
        $sequenceAlignmentManager->sortAlpha("ASC");

        $sequences = $sequenceAlignmentManager->getSeqSet()->getArrayCopy();

        // 0
        $oExpected1 = new Sequence();
        $oExpected1->setId("sp|O09185|P53_CRIGR");
        $oExpected1->setSequence("MEEPQSDLSIEL-PLSQETFSDLWKLLPPNNVLSTLPS-SDSIEE-LFLSENVTGWLEDSGGALQGVAAAA---ASTAEDPVTET-------PAPVASAPATPWPLSSSVPSYKTYQGDYGFRLGFLHSGTAKSVTCTYSPSLNKLFCQLAKTCPVQLWVNSTPPPGTRVRAMAIYKKLQYMTEVVRRCPHHERSSE-GDSLAPPQHLIRVEGNLHAEYLDDKQTFRHSVVVPYEPPEVGSDCTTIHYNYMCNSSCMGGMNRRPILTIITLEDPSGNLLGRNSFEVRICACPGRDRRTEEKNFQKKGEPCPELP---PKSAKRALPT--NTS--SSPP-PK-----KKTLDGEYFTLKIRGHERFKMFQELNEALELKDAQASKGSEDNGAHSSYL-----KSKKGQSASRLKKLMIKREGPDSD-");
        $oExpected1->setStart(0);
        $oExpected1->setEnd(425);
        $oExpected1->setSeqlength(426);
        $this->assertEquals($sequences[0], $oExpected1);

        // 10
        $oExpected2 = new Sequence();
        $oExpected2->setId("sp|P13481|P53_CHLAE");
        $oExpected2->setSequence("MEEPQSDPSIEP-PLSQETFSDLWKLLPENNVLSPLPS-QA-VDDLMLSPDDLAQWLTEDPGPDEAP---RMSEAAPHMAPTPAA-------PTPAAPAPAPSWPLSSSVPSQKTYHGSYGFRLGFLHSGTAKSVTCTYSPDLNKMFCQLAKTCPVQLWVDSTPPPGSRVRAMAIYKQSQHMTEVVRRCPHHERCSD-SDGLAPPQHLIRVEGNLRVEYSDDRNTFRHSVVVPYEPPEVGSDCTTIHYNYMCNSSCMGGMNRRPILTIITLEDSSGNLLGRNSFEVRVCACPGRDRRTEEENFRKKGEPCHELP---PGSTKRALPN--NTS--SSPQ-PK-----KKPLDGEYFTLQIRGRERFEMFRELNEALELKDAQAGKEPAGSRAHSSHL-----KSKKGQSTSRHKKFMFKTEGPDSD-");
        $oExpected2->setStart(0);
        $oExpected2->setEnd(425);
        $oExpected2->setSeqlength(426);
        $this->assertEquals($sequences[10], $oExpected2);

        // 20
        $oExpected3 = new Sequence();
        $oExpected3->setId("sp|P79820|P53_ORYLA");
        $oExpected3->setSequence("------MDPVPDLPESQGSFQELWETVSYPPLETL---------SLPTVNEPTGSWVATGDMF---L---LDQDLSGTFDDKIFD-------IPIEPVPTNEVNPPPTTVPVTTDYPGSYELELRFQKSGTAKSVTSTYSETLNKLYCQLAKTSPIEVRVSKEPPKGAILRATAVYKKTEHVADVVRRCPHHQN----EDSVEHRSHLIRVEGSQLAQYFEDPYTKRQSVTVPYEPPQPGSEMTTILLSYMCNSSCMGGMNRRPILTILTLET-EGLVLGRRCFEVRICACPGRDRKTEEESRQKTQP-----K-------KRKVTPNTS----S-SKRKKSHSSGEEEDNREVFHFEVYGRERYEFLKKINDGLELLEKESKSKN----------------KDSGMVPSSGKKLKSN--------");
        $oExpected3->setStart(0);
        $oExpected3->setEnd(425);
        $oExpected3->setSeqlength(426);
        $this->assertEquals($sequences[20], $oExpected3);

        // 33
        $oExpected4 = new Sequence();
        $oExpected4->setId("sp|Q9WUR6|P53_CAVPO");
        $oExpected4->setSequence("MEEPHSDLSIEP-PLSQETFSDLWKLLPENNVLSDSLS-PPM-DHLLLSPEEVASWLGENP--DGDG---HVSAAPVSEAPTSAG-------PALVAPAPATSWPLSSSVPSHKPYRGSYGFEVHFLKSGTAKSVTCTYSPGLNKLFCQLAKTCPVQVWVESPPPPGTRVRALAIYKKSQHMTEVVRRCPHHERCSD-SDGLAPPQHLIRVEGNLHAEYVDDRTTFRHSVVVPYEPPEVGSDCTTIHYNYMCNSSCMGGMNRRPILTIITLEDSSGKLLGRDSFEVRVCACPGRDRRTEEENFRKKGGLCPEPT---PGNIKRALPT--STS--SSPQ-PK-----KKPLDAEYFTLKIRGRKNFEILREINEALEFKDAQTEKEPGESRPHSSYP-----KSKKGQSTSCHKKLMFKREGLDSD-");
        $oExpected4->setStart(0);
        $oExpected4->setEnd(425);
        $oExpected4->setSeqlength(426);
        $this->assertEquals($sequences[33], $oExpected4);
    }

    public function testFetchClustal()
    {
        $sequenceAlignmentManager = new SequenceAlignmentManager($this->sequenceManager);
        $sequenceAlignmentManager->setFilename("data/clustal.txt");
        $sequenceAlignmentManager->setFormat("CLUSTAL");
        $sequenceAlignmentManager->parseFile();
        $sequenceAlignmentManager->sortAlpha("ASC");

        $oMySuperSeq = $sequenceAlignmentManager->getSeqSet()->offsetGet(13);

        $oExpected = new Sequence();
        $oExpected->setId("sp|P51664|P53_SHEEP");
        $oExpected->setSequence("MEESQAELGVEP-PLSQETFSDLWNLLPENNLLSSELS-AP-VDDLLPYSEDVVTWLDE--CPNEAP---QMPEPPAQA-----------------ALAPATSWPLSSFVPSQKTYPGNYGFRLGFLHSGTAKSVTCTYSPSLNKLFCQLAKTCPVQLWVDSPPPPGTRVRAMAIYKKLEHMTEVVRRSPHHERSSDYSDGLAPPQHLIRVEGNLRAEYFDDRNTFRHSVVVPYESPEIESECTTIHYNFMCNSSCMGGMNRRPILTIITLEDSRGNLLGRSSFEVRVCACPGRDRRTEEENFRKKGQSCPEPP---PGSTKRALPS--STS--SSPQ-QK-----KKPLDGEYFTLQIRGRKRFEMFRELNEALELMDAQAGREPGESRAHSSHL-----KSKKGPSPSCHKKPMLKREGPDSD-");
        $oExpected->setStart(0);
        $oExpected->setEnd(425);
        $oExpected->setSeqlength(426);

        $this->assertEquals($oMySuperSeq, $oExpected);
    }

    public function testFetchFasta()
    {
        $sequenceAlignmentManager = new SequenceAlignmentManager($this->sequenceManager);
        $sequenceAlignmentManager->setFilename("data/fasta-2.txt");
        $sequenceAlignmentManager->setFormat("FASTA");
        $sequenceAlignmentManager->parseFile();

        $oMySuperSeq1 = $sequenceAlignmentManager->getSeqSet()->offsetGet(0);
        $oMySuperSeq2 = $sequenceAlignmentManager->getSeqSet()->offsetGet(1);

        $sSeq1 = "GGCAGATTCCCCCTAGACCCGCCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTGGAGGCTGGCGG";
        $sSeq1.= "GGCAGGCCAGCTGAGTCCTGAGCAGCAGCCCAGCGCAGCCACCGAGACACCATGAGAGCCCTCACACTCCTCGCCCTATTGGCCCTGGCCGCACTTTGC";
        $sSeq1.= "ATCGCTGGCCAGGCAGGTGAGTGCCCCCACCTCCCCTCAGGCCGCATTGCAGTGGGGGCTGAGAGGAGGAAGCACCATGGCCCACCTCTTCTCACCCCT";
        $sSeq1.= "TTGGCTGGCAGTCCCTTTGCAGTCTAACCACCTTGTTGCAGGCTCAATCCATTTGCCCCAGCTCTGCCCTTGCAGAGGGAGAGGAGGGAAGAGCAAGCT";
        $sSeq1.= "GCCCGAGACGCAGGGGAAGGAGGATGAGGGCCCTGGGGATGAGCTGGGGTGAACCAGGCTCCCTTTCCTTTGCAGGTGCGAAGCCCAGCGGTGCAGAGT";
        $sSeq1.= "CCAGCAAAGGTGCAGGTATGAGGATGGACCTGATGGGTTCCTGGACCCTCCCCTCTCACCCTGGTCCCTCAGTCTCATTCCCCCACTCCTGCCACCTCC";
        $sSeq1.= "TGTCTGGCCATCAGGAAGGCCAGCCTGCTCCCCACCTGATCCTCCCAAACCCAGAGCCACCTGATGCCTGCCCCTCTGCTCCACAGCCTTTGTGTCCAA";
        $sSeq1.= "GCAGGAGGGCAGCGAGGTAGTGAAGAGACCCAGGCGCTACCTGTATCAATGGCTGGGGTGAGAGAAAAGGCAGAGCTGGGCCAAGGCCCTGCCTCTCCG";
        $sSeq1.= "GGATGGTCTGTGGGGGAGCTGCAGCAGGGAGTGGCCTCTCTGGGTTGTGGTGGGGGTACAGGCAGCCTGCCCTGGTGGGCACCCTGGAGCCCCATGTGT";
        $sSeq1.= "AGGGAGAGGAGGGATGGGCATTTTGCACGGGGGCTGATGCCACCACGTCGGGTGTCTCAGAGCCCCAGTCCCCTACCCGGATCCCCTGGAGCCCAGGAG";
        $sSeq1.= "GGAGGTGTGTGAGCTCAATCCGGACTGTGACGAGTTGGCTGACCACATCGGCTTTCAGGAGGCCTATCGGCGCTTCTACGGCCCGGTCTAGGGTGTCGC";
        $sSeq1.= "TCTGCTGGCCTGGCCGGCAACCCCAGTTCTGCTCCTCTCCAGGCACCCTTCTTTCCTCTTCCCCTTGCCCTTGCCCTGACCTCCCAGCCCTATGGATGT";
        $sSeq1.= "GGGGTCCCCATCATCCCAGCTGCTCCCAAATAAACTCCAGAAG";

        $oExpected1 = new Sequence();
        $oExpected1->setId(0);
        $oExpected1->setSequence($sSeq1);
        $oExpected1->setSeqlength(1231);

        $this->assertEquals($oMySuperSeq1, $oExpected1);

        $sSeq2 = "GGCAGATTCCCCCTAGACCCGCCCGCACCATGGTCAGGCATGCCCCTCCTCATCGCTGGGCACAGCCCAGAGGGTATAAACAGTGCTGGAGGCTGGCGG";
        $sSeq2.= "GGCAGGCCAGCTGAGTCCTGAGCAGCAGCCCAGCGCAGCCACCGAGACACCATGAGAGCCCTCACACTCCTCGCCCTATTGGCCCTGGCCGCACTTTGC";
        $sSeq2.= "ATCGCTGGCCAGGCAGGTGAGTGCCCCCACCTCCCCTCAGGCCGCATTGCAGTGGGGGCTGAGAGGAGGAAGCACCATGGCCCACCTCTTCTCACCCCT";
        $sSeq2.= "TTGGCTGGCAGTCCCTTTGCAGTCTAACCACCTTGTTGCAGGCTCAATCCATTTGCCCCAGCTCTGCCCTTGCAGAGGGAGAGGAGGGAAGAGCAAGCT";
        $sSeq2.= "GCCCGAGACGCAGGGGAAGGAGGATGAGGGCCCTGGGGATGAGCTGGGGTGAACCAGGCTCCCTTTCCTTTGCAGGTGCGAAGCCCAGCGGTGCAGAGT";
        $sSeq2.= "CCAGCAAAGGTGCAGGTATGAGGATGGACCTGATGGGTTCCTGGACCCTCCCCTCTCACCCTGGTCCCTCAGTCTCATTCCCCCACTCCTGCCACCTCC";
        $sSeq2.= "TGTCTGGCCATCAGGAAGGCCAGCCTGCTCCCCACCTGATCCTCCCAAACCCAGAGCCACCTGATGCCTGCCCCTCTGCTCCACAGCCTTTGTGTCCAA";
        $sSeq2.= "GCAGGAGGGCAGCGAGGTAGTGAAGAGACCCAGGCGCTACCTGTATCAATGGCTGGGGTGAGAGAAAAGGCAGAGCTGGGCCAAGGCCCTGCCTCTCCG";
        $sSeq2.= "GGATGGTCTGTGGGGGAGCTGCAGCAGGGAGTGGCCTCTCTGGGTTGTGGTGGGGGTACAGGCAGCCTGCCCTGGTGGGCACCCTGGAGCCCCATGTGT";
        $sSeq2.= "AGGGAGAGGAGGGATGGGCATTTTGCACGGGGGCTGATGCCACCACGTCGGGTGTCTCAGAGCCCCAGTCCCCTACCCGGATCCCCTGGAGCCCAGGAG";
        $sSeq2.= "GGAGGTGTGTGAGCTCAATCCGGACTGTGACGAGTTGGCTGACCACATCGGCTTTCAGGAGGCCTATCGGCGCTTCTACGGCCCGGTCTAGGGTGTCGC";
        $sSeq2.= "TCTGCTGGCCTGGCCGGCAACCCCAGTTCTGCTCCTCTCCAGGCACCCTTCTTTCCTCTTCCCCTTGCCCTTGCCCTGACCTCCCAGCCCTATGGATGT";
        $sSeq2.= "GGGGTCCCCATCATCCCAGCTGCTCCCAAATAAACTCCAGAAGCCACTGCACTCACCGCACCCGGCCAATTTTTGTGTTTTTAGTAGAGACTAAATACC";
        $sSeq2.= "ATATAGTGAACACCTAAGACGGGGGGCCTTGGATCCAGGGCGATTCAGAGGGCCCCGGTCGGAGCTGTCGGAGATTGAGCGCGCGCGGTCCCGGGATCT";
        $sSeq2.= "CCGACGAGGCCCTGGACCCCCGGGCGGCGAAGCTGCGGCGCGGCGCCCCCTGGAGGCCGCGGGACCCCTGGCCGGTCCGCGCAGGCGCAGCGGGGTCGC";
        $sSeq2.= "AGGGCGCGGCGGGTTCCAGCGCGGGGATGGCGCTGTCCGCGGAGGACCGGGCGCTGGTGCGCGCCCTGTGGAAGAAGCTGGGCAGCAACGTCGGCGTCT";
        $sSeq2.= "ACACGACAGAGGCCCTGGAAAGGTGCGGCAGGCTGGGCGCCCCCGCCCCCAGGGGCCCTCCCTCCCCAAGCCCCCCGGACGCGCCTCACCCACGTTCCT";
        $sSeq2.= "CTCGCAGGACCTTCCTGGCTTTCCCCGCCACGAAGACCTACTTCTCCCACCTGGACCTGAGCCCCGGCTCCTCACAAGTCAGAGCCCACGGCCAGAAGG";
        $sSeq2.= "TGGCGGACGCGCTGAGCCTCGCCGTGGAGCGCCTGGACGACCTACCCCACGCGCTGTCCGCGCTGAGCCACCTGCACGCGTGCCAGCTGCGAGTGGACC";
        $sSeq2.= "CGGCCAGCTTCCAGGTGAGCGGCTGCCGTGCTGGGCCCCTGTCCCCGGGAGGGCCCCGGCGGGGTGGGTGCGGGGGGCGTGCGGGGCGGGTGCAGGCGA";
        $sSeq2.= "GTGAGCCTTGAGCGCTCGCCGCAGCTCCTGGGCCACTGCCTGCTGGTAACCCTCGCCCGGCACTACCCCGGAGACTTCAGCCCCGCGCTGCAGGCGTCG";
        $sSeq2.= "CTGGACAAGTTCCTGAGCCACGTTATCTCGGCGCTGGTTTCCGAGTACCGCTGAACTGTGGGTGGGTGGCCGCGGGATCCCCAGGCGACCTTCCCCGTG";
        $sSeq2.= "TTTGAGTAAAGCCTCTCCCAGGAGCAGCCTTCTTGCCGTGCTCTCTCGAGGTCAGGACGCGAGAGGAAGGCGC";

        $oExpected2 = new Sequence();
        $oExpected2->setId(0);
        $oExpected2->setSequence($sSeq2);
        $oExpected2->setSeqlength(2251);

        $this->assertEquals($oMySuperSeq2, $oExpected2);
    }

    public function testMaxiLength()
    {
        $sequenceAlignmentManager = new SequenceAlignmentManager($this->sequenceManager);
        $sequenceAlignmentManager->setFilename("data/clustal.txt");
        $sequenceAlignmentManager->setFormat("CLUSTAL");
        $sequenceAlignmentManager->parseFile();
        $iMyLength = $sequenceAlignmentManager->getMaxiLength();

        $iExpected = 426;
        $this->assertEquals($iMyLength, $iExpected);
    }

    public function testGapCount()
    {
        $sequenceAlignmentManager = new SequenceAlignmentManager($this->sequenceManager);
        $sequenceAlignmentManager->setFilename("data/clustal.txt");
        $sequenceAlignmentManager->setFormat("CLUSTAL");
        $sequenceAlignmentManager->parseFile();
        $iNumberGaps = $sequenceAlignmentManager->getGapCount();

        $iExpected = 1878;
        $this->assertEquals($iNumberGaps, $iExpected);
    }

    public function testGetIsFlush()
    {
        $sequenceAlignmentManager = new SequenceAlignmentManager($this->sequenceManager);
        $sequenceAlignmentManager->setFilename("data/clustal.txt");
        $sequenceAlignmentManager->setFormat("CLUSTAL");
        $sequenceAlignmentManager->parseFile();

        $bIsFlush = $sequenceAlignmentManager->getIsFlush();
        $this->assertTrue($bIsFlush);
    }

    public function testChatAtRes()
    {
        $sequenceAlignmentManager = new SequenceAlignmentManager($this->sequenceManager);
        $sequenceAlignmentManager->setFilename("data/clustal.txt");
        $sequenceAlignmentManager->setFormat("CLUSTAL");
        $sequenceAlignmentManager->parseFile();
        $sequenceAlignmentManager->sortAlpha("ASC");

        $sExpected = "E";
        $sCharAtRes = $sequenceAlignmentManager->charAtRes(10, 10);

        $this->assertEquals($sCharAtRes, $sExpected);
    }

    public function testSubstrBwRes()
    {
        $sequenceAlignmentManager = new SequenceAlignmentManager($this->sequenceManager);
        $sequenceAlignmentManager->setFilename("data/clustal.txt");
        $sequenceAlignmentManager->setFormat("CLUSTAL");
        $sequenceAlignmentManager->parseFile();
        $sequenceAlignmentManager->sortAlpha("ASC");

        $sExpected = "EP-PLSQETFSDLWKLLPENNVLSPLPS-QA-VDDLMLSPDDLAQWLTEDPGPDEAP---RMSEAAPHMAPTPAA-------PTPAAPAPAPSWP";
        $sExpected.= "LSSSVPSQKTYHGSYGFRLGFLHSGTAKSVTCTYSPDLNKMFCQLAKTCPVQLWVDSTPPPGSRVRAMAIYKQSQHMTEVVRRCPHHERCSD-SD";
        $sExpected.= "GLAPPQHLIRVEGNLRVEYSDDRNTFRHSVVVPYEPPEVGSDCTTIHYNYMCNSSCMGGMNRRPILTIITLEDSSGNLLGRNSFEVRVCACPGRD";
        $sExpected.= "RRTEEENFRKKGEPCHELP---PGSTKRALPN--NTS--SSPQ-PK-----KKPLDGEYFTLQIRGRERFEMFRELNEALELKDAQAGKEPAGSR";
        $sExpected.= "AHSSHL-----KSKKGQSTSRHKKFMFKTEGPDSD-";
        $sSubstrBwRes = $sequenceAlignmentManager->substrBwRes(10,10);
        $this->assertEquals($sSubstrBwRes, $sExpected);
    }

    public function testColToRes()
    {
        $sequenceAlignmentManager = new SequenceAlignmentManager($this->sequenceManager);
        $sequenceAlignmentManager->setFilename("data/clustal.txt");
        $sequenceAlignmentManager->setFormat("CLUSTAL");
        $sequenceAlignmentManager->parseFile();
        $sequenceAlignmentManager->sortAlpha("ASC");

        $iColToRes = $sequenceAlignmentManager->colToRes(10, 50);
        $iExpected = 47;

        $this->assertEquals($iExpected, $iColToRes);
    }

    public function testResToCol()
    {
        $sequenceAlignmentManager = new SequenceAlignmentManager($this->sequenceManager);
        $sequenceAlignmentManager->setFilename("data/clustal.txt");
        $sequenceAlignmentManager->setFormat("CLUSTAL");
        $sequenceAlignmentManager->parseFile();
        $sequenceAlignmentManager->sortAlpha("ASC");

        $iResToCol = $sequenceAlignmentManager->resToCol(10, 47);
        $iExpected = 50;

        $this->assertEquals($iExpected, $iResToCol);
    }

    public function testSubalign()
    {
        $sequenceAlignmentManager = new SequenceAlignmentManager($this->sequenceManager);
        $sequenceAlignmentManager->setFilename("data/clustal.txt");
        $sequenceAlignmentManager->setFormat("CLUSTAL");
        $sequenceAlignmentManager->parseFile();
        $sequenceAlignmentManager->sortAlpha("ASC");
        $sequenceAlignmentManager->subalign(5, 10);

        $iSequencesSize = $sequenceAlignmentManager->getSeqSet()->count();
        $this->assertEquals(6, $iSequencesSize);

        $oLastSeq = $sequenceAlignmentManager->getSeqSet()->offsetGet(5);
        $oExpected = new Sequence();
        $oExpected->setId("sp|P13481|P53_CHLAE");
        $oExpected->setSequence("MEEPQSDPSIEP-PLSQETFSDLWKLLPENNVLSPLPS-QA-VDDLMLSPDDLAQWLTEDPGPDEAP---RMSEAAPHMAPTPAA-------PTPAAPAPAPSWPLSSSVPSQKTYHGSYGFRLGFLHSGTAKSVTCTYSPDLNKMFCQLAKTCPVQLWVDSTPPPGSRVRAMAIYKQSQHMTEVVRRCPHHERCSD-SDGLAPPQHLIRVEGNLRVEYSDDRNTFRHSVVVPYEPPEVGSDCTTIHYNYMCNSSCMGGMNRRPILTIITLEDSSGNLLGRNSFEVRVCACPGRDRRTEEENFRKKGEPCHELP---PGSTKRALPN--NTS--SSPQ-PK-----KKPLDGEYFTLQIRGRERFEMFRELNEALELKDAQAGKEPAGSRAHSSHL-----KSKKGQSTSRHKKFMFKTEGPDSD-");
        $oExpected->setStart(0);
        $oExpected->setEnd(425);
        $oExpected->setSeqlength(426);

        $this->assertEquals($oExpected, $oLastSeq);
    }

    public function testSelect()
    {
        $sequenceAlignmentManager = new SequenceAlignmentManager($this->sequenceManager);
        $sequenceAlignmentManager->setFilename("data/clustal.txt");
        $sequenceAlignmentManager->setFormat("CLUSTAL");
        $sequenceAlignmentManager->parseFile();
        $sequenceAlignmentManager->sortAlpha("ASC");
        $sequenceAlignmentManager->select(1,2,3);

        $iSequencesSize = $sequenceAlignmentManager->getSeqSet()->count();
        $this->assertEquals(3, $iSequencesSize);

        $oLastSeq = $sequenceAlignmentManager->getSeqSet()->offsetGet(2);

        $oExpected = new Sequence();
        $oExpected->setId("sp|O57538|P53_XIPHE");
        $oExpected->setSequence("-ME----EADLTLPLSQDTFHDLWNNVFLSTEN--------------------ESLAPPE--G---L---LSQ------NMDFWE--------DPETMQETKNVPTAPTVPAISNYAGEHGFNLEFNDSGTAKSVTSTYSVKLGKLFCQLAKTTPIGVLVKEEPPQGAVIRATSVYKKTEHVGEVVKRCPHHQS----EDLSDNKSHLIRVEGSQLAQYFEDPNTRRHSVTVPYERPQLGSEMTTILLSFMCNSSCMGGMNRRPILTILTLETTEGEVLGRRCFEVRVCACPGRDRKTEEGNLEK--SGTKQTK-------KRKSAP---APDTS-TAKKSKSASSGEDEDKEIYTLSIRGRNRYLWFKSLNDGLELMDKTG-----------------PKIKQEIPAPSSGKRLLKGGSDSD---");
        $oExpected->setStart(0);
        $oExpected->setEnd(425);
        $oExpected->setSeqlength(426);

        $this->assertEquals($oExpected, $oLastSeq);
    }

    public function testResVar()
    {
        $sequenceAlignmentManager = new SequenceAlignmentManager($this->sequenceManager);
        $sequenceAlignmentManager->setFilename("data/clustal.txt");
        $sequenceAlignmentManager->setFormat("CLUSTAL");
        $sequenceAlignmentManager->parseFile();
        $sequenceAlignmentManager->sortAlpha("ASC");

        $aResVar = $sequenceAlignmentManager->resVar();
        $aExpected = [
          "INVARIANT" => [
            0 => 139,
            1 => 142,
            2 => 144,
            3 => 147,
            4 => 149,
            5 => 150,
            6 => 151,
            7 => 163,
            8 => 164,
            9 => 170,
            10 => 171,
            11 => 175,
            12 => 176,
            13 => 184,
            14 => 185,
            15 => 187,
            16 => 189,
            17 => 190,
            18 => 191,
            19 => 206,
            20 => 207,
            21 => 209,
            22 => 211,
            23 => 212,
            24 => 218,
            25 => 228,
            26 => 229,
            27 => 231,
            28 => 232,
            29 => 233,
            30 => 234,
            31 => 236,
            32 => 243,
            33 => 250,
            34 => 251,
            35 => 252,
            36 => 253,
            37 => 254,
            38 => 255,
            39 => 256,
            40 => 257,
            41 => 258,
            42 => 259,
            43 => 260,
            44 => 261,
            45 => 262,
            46 => 264,
            47 => 266,
            48 => 267,
            49 => 270,
            50 => 271,
            51 => 275,
            52 => 278,
            53 => 279,
            54 => 280,
            55 => 283,
            56 => 284,
            57 => 285,
            58 => 286,
            59 => 288,
            60 => 289,
            61 => 290,
            62 => 291,
            63 => 292,
            64 => 293,
            65 => 294,
            66 => 295,
            67 => 299,
            68 => 304,
            69 => 321,
            70 => 322,
            71 => 352,
          ],
          "VARIANT" => [
            0 => 0,
            1 => 1,
            2 => 2,
            3 => 3,
            4 => 4,
            5 => 5,
            6 => 6,
            7 => 7,
            8 => 8,
            9 => 9,
            10 => 10,
            11 => 11,
            12 => 12,
            13 => 13,
            14 => 14,
            15 => 15,
            16 => 16,
            17 => 17,
            18 => 18,
            19 => 19,
            20 => 20,
            21 => 21,
            22 => 22,
            23 => 23,
            24 => 24,
            25 => 25,
            26 => 26,
            27 => 27,
            28 => 28,
            29 => 29,
            30 => 30,
            31 => 31,
            32 => 32,
            33 => 33,
            34 => 34,
            35 => 35,
            36 => 36,
            37 => 37,
            38 => 38,
            39 => 39,
            40 => 40,
            41 => 41,
            42 => 42,
            43 => 43,
            44 => 44,
            45 => 45,
            46 => 46,
            47 => 47,
            48 => 48,
            49 => 49,
            50 => 50,
            51 => 51,
            52 => 52,
            53 => 53,
            54 => 54,
            55 => 55,
            56 => 56,
            57 => 57,
            58 => 58,
            59 => 59,
            60 => 60,
            61 => 61,
            62 => 62,
            63 => 63,
            64 => 64,
            65 => 65,
            66 => 66,
            67 => 67,
            68 => 68,
            69 => 69,
            70 => 70,
            71 => 71,
            72 => 72,
            73 => 73,
            74 => 74,
            75 => 75,
            76 => 76,
            77 => 77,
            78 => 78,
            79 => 79,
            80 => 80,
            81 => 81,
            82 => 82,
            83 => 83,
            84 => 84,
            85 => 85,
            86 => 86,
            87 => 87,
            88 => 88,
            89 => 89,
            90 => 90,
            91 => 91,
            92 => 92,
            93 => 93,
            94 => 94,
            95 => 95,
            96 => 96,
            97 => 97,
            98 => 98,
            99 => 99,
            100 => 100,
            101 => 101,
            102 => 102,
            103 => 103,
            104 => 104,
            105 => 105,
            106 => 106,
            107 => 107,
            108 => 108,
            109 => 109,
            110 => 110,
            111 => 111,
            112 => 112,
            113 => 113,
            114 => 114,
            115 => 115,
            116 => 116,
            117 => 117,
            118 => 118,
            119 => 119,
            120 => 120,
            121 => 121,
            122 => 122,
            123 => 123,
            124 => 124,
            125 => 125,
            126 => 126,
            127 => 127,
            128 => 128,
            129 => 129,
            130 => 130,
            131 => 131,
            132 => 132,
            133 => 133,
            134 => 134,
            135 => 135,
            136 => 136,
            137 => 137,
            138 => 138,
            139 => 140,
            140 => 141,
            141 => 143,
            142 => 145,
            143 => 146,
            144 => 148,
            145 => 152,
            146 => 153,
            147 => 154,
            148 => 155,
            149 => 156,
            150 => 157,
            151 => 158,
            152 => 159,
            153 => 160,
            154 => 161,
            155 => 162,
            156 => 165,
            157 => 166,
            158 => 167,
            159 => 168,
            160 => 169,
            161 => 172,
            162 => 173,
            163 => 174,
            164 => 177,
            165 => 178,
            166 => 179,
            167 => 180,
            168 => 181,
            169 => 182,
            170 => 183,
            171 => 186,
            172 => 188,
            173 => 192,
            174 => 193,
            175 => 194,
            176 => 195,
            177 => 196,
            178 => 197,
            179 => 198,
            180 => 199,
            181 => 200,
            182 => 201,
            183 => 202,
            184 => 203,
            185 => 204,
            186 => 205,
            187 => 208,
            188 => 210,
            189 => 213,
            190 => 214,
            191 => 215,
            192 => 216,
            193 => 217,
            194 => 219,
            195 => 220,
            196 => 221,
            197 => 222,
            198 => 223,
            199 => 224,
            200 => 225,
            201 => 226,
            202 => 227,
            203 => 230,
            204 => 235,
            205 => 237,
            206 => 238,
            207 => 239,
            208 => 240,
            209 => 241,
            210 => 242,
            211 => 244,
            212 => 245,
            213 => 246,
            214 => 247,
            215 => 248,
            216 => 249,
            217 => 263,
            218 => 265,
            219 => 268,
            220 => 269,
            221 => 272,
            222 => 273,
            223 => 274,
            224 => 276,
            225 => 277,
            226 => 281,
            227 => 282,
            228 => 287,
            229 => 296,
            230 => 297,
            231 => 298,
            232 => 300,
            233 => 301,
            234 => 302,
            235 => 303,
            236 => 305,
            237 => 306,
            238 => 307,
            239 => 308,
            240 => 309,
            241 => 310,
            242 => 311,
            243 => 312,
            244 => 313,
            245 => 314,
            246 => 315,
            247 => 316,
            248 => 317,
            249 => 318,
            250 => 319,
            251 => 320,
            252 => 323,
            253 => 324,
            254 => 325,
            255 => 326,
            256 => 327,
            257 => 328,
            258 => 329,
            259 => 330,
            260 => 331,
            261 => 332,
            262 => 333,
            263 => 334,
            264 => 335,
            265 => 336,
            266 => 337,
            267 => 338,
            268 => 339,
            269 => 340,
            270 => 341,
            271 => 342,
            272 => 343,
            273 => 344,
            274 => 345,
            275 => 346,
            276 => 347,
            277 => 348,
            278 => 349,
            279 => 350,
            280 => 351,
            281 => 353,
            282 => 354,
            283 => 355,
            284 => 356,
            285 => 357,
            286 => 358,
            287 => 359,
            288 => 360,
            289 => 361,
            290 => 362,
            291 => 363,
            292 => 364,
            293 => 365,
            294 => 366,
            295 => 367,
            296 => 368,
            297 => 369,
            298 => 370,
            299 => 371,
            300 => 372,
            301 => 373,
            302 => 374,
            303 => 375,
            304 => 376,
            305 => 377,
            306 => 378,
            307 => 379,
            308 => 380,
            309 => 381,
            310 => 382,
            311 => 383,
            312 => 384,
            313 => 385,
            314 => 386,
            315 => 387,
            316 => 388,
            317 => 389,
            318 => 390,
            319 => 391,
            320 => 392,
            321 => 393,
            322 => 394,
            323 => 395,
            324 => 396,
            325 => 397,
            326 => 398,
            327 => 399,
            328 => 400,
            329 => 401,
            330 => 402,
            331 => 403,
            332 => 404,
            333 => 405,
            334 => 406,
            335 => 407,
            336 => 408,
            337 => 409,
            338 => 410,
            339 => 411,
            340 => 412,
            341 => 413,
            342 => 414,
            343 => 415,
            344 => 416,
            345 => 417,
            346 => 418,
            347 => 419,
            348 => 420,
            349 => 421,
            350 => 422,
            351 => 423,
            352 => 424,
            353 => 425,
          ]
        ];

        $this->assertEquals($aExpected, $aResVar);
    }

    public function testConsensus()
    {
        $sequenceAlignmentManager = new SequenceAlignmentManager($this->sequenceManager);
        $sequenceAlignmentManager->setFilename("data/clustal.txt");
        $sequenceAlignmentManager->setFormat("CLUSTAL");
        $sequenceAlignmentManager->parseFile();
        $sequenceAlignmentManager->sortAlpha("ASC");

        $sConsensus = $sequenceAlignmentManager->consensus();
        $sExpected = "????????????????????????????????????????????????????????????????????????????????????????????????";
        $sExpected.= "???????????????????????????????????????????S??L?K??C?LAK???????????PP?????RA???YK???????VV?R?PHH";
        $sExpected.= "??????????????HL?R?EG?????Y?????????SV?VPYE?P??????T??????MCNSSCMGGMNRR?I?TI??LE???G??LGR??FEVR?";
        $sExpected.= "CACPGRDR???E????K????????????????KR?????????????????????????????E???????????????????????????????";
        $sExpected.= "??????????????????????????????????????????";

        $this->assertEquals($sExpected, $sConsensus);
    }

    public function testAddSequence()
    {
        $sequenceAlignmentManager = new SequenceAlignmentManager($this->sequenceManager);
        $sequenceAlignmentManager->setFilename("data/clustal.txt");
        $sequenceAlignmentManager->setFormat("CLUSTAL");
        $sequenceAlignmentManager->parseFile();
        $sequenceAlignmentManager->sortAlpha("ASC");

        $oMySuperSeq = $sequenceAlignmentManager->getSeqSet()->offsetGet(13);
        $sequenceAlignmentManager->addSequence($oMySuperSeq);
        $iCount = $sequenceAlignmentManager->getSeqSet()->count();

        $this->assertEquals(35, $iCount);
    }

    public function testDeleteSequence()
    {
        $sequenceAlignmentManager = new SequenceAlignmentManager($this->sequenceManager);
        $sequenceAlignmentManager->setFilename("data/clustal.txt");
        $sequenceAlignmentManager->setFormat("CLUSTAL");
        $sequenceAlignmentManager->parseFile();
        $sequenceAlignmentManager->sortAlpha("ASC");

        $sequenceAlignmentManager->deleteSequence("sp|O09185|P53_CRIGR");
        $iCount = $sequenceAlignmentManager->getSeqSet()->count();

        $this->assertEquals(33, $iCount);
    }
}