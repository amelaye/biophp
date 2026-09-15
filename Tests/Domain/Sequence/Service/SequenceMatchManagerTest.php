<?php
/**
 * SequenceMatchManager Testing
 * @author Amélie DUVERNET aka Amelaye
 * Freely inspired by BioPHP's project biophp.org
 * Created 14 november 2019
 * Last modified 14 november 2019
 */
namespace Tests\Domain\Sequence\Service;

use Amelaye\BioPHP\Api\AminoApi;
use Amelaye\BioPHP\Api\ElementApi;
use Amelaye\BioPHP\Api\NucleotidApi;
use Amelaye\BioPHP\Domain\Sequence\Entity\Sequence;
use Amelaye\BioPHP\Domain\Sequence\Entity\SubMatrix;
use Amelaye\BioPHP\Domain\Sequence\Service\SequenceManager;
use Amelaye\BioPHP\Domain\Sequence\Service\SequenceMatchManager;
use Amelaye\BioPHP\Domain\Sequence\Builder\SequenceBuilder;
use PHPUnit\Framework\TestCase;

class SequenceMatchManagerTest extends TestCase
{
    private $apiAminoMock;

    private $apiNucleoMock;

    private $apiElementsMock;

    private $sequence;

    private $subMatrix;

    public function setUp(): void
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
    }

    public function testCompareLetter()
    {
        $sequenceManager = new SequenceManager($this->apiAminoMock, $this->apiNucleoMock, $this->apiElementsMock);
        $sequenceBuilder = new SequenceBuilder($sequenceManager);
        $sequenceBuilder->setSequence($this->sequence);

        $sSeq1 = $sequenceBuilder->subSeq(2,100);
        $sSeq2 = $sequenceBuilder->subSeq(100,100);

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
        $sequenceManager = new SequenceManager($this->apiAminoMock, $this->apiNucleoMock, $this->apiElementsMock);
        $sequenceBuilder = new SequenceBuilder($sequenceManager);
        $sequenceBuilder->setSequence($this->sequence);

        $sSeq1 = $sequenceBuilder->subSeq(2,100);
        $sSeq2 = $sequenceBuilder->subSeq(100,100);

        $sequenceMatchManager = new SequenceMatchManager();
        $sequenceMatchManager->setSubMatrix($this->subMatrix);
        $iLevdist = $sequenceMatchManager->levdist($sSeq1, $sSeq2);

        $iExpected = 56;

        $this->assertEquals($iExpected, $iLevdist);
    }

    public function testXlevdist()
    {
        $sequenceManager = new SequenceManager($this->apiAminoMock, $this->apiNucleoMock, $this->apiElementsMock);
        $sequenceBuilder = new SequenceBuilder($sequenceManager);
        $sequenceBuilder->setSequence($this->sequence);

        $sSeq1 = $sequenceBuilder->subSeq(2,100);
        $sSeq2 = $sequenceBuilder->subSeq(100,100);

        $sequenceMatchManager = new SequenceMatchManager();
        $sequenceMatchManager->setSubMatrix($this->subMatrix);
        $iLevdist = $sequenceMatchManager->xlevdist($sSeq1, $sSeq2);;

        // xlevdist() is levdist() extended past the 255-character limit; on the very same
        // pair of sequences it must return the same distance as levdist()/native levenshtein()
        // in testLevdist() above.
        $iExpected = 56;

        $this->assertEquals($iExpected, $iLevdist);
    }

    /**
     * xlevdist() must agree with PHP's native levenshtein() - the oracle levdist() itself
     * delegates to - on strings short enough for both to run.
     */
    public function testXlevdistMatchesNativeLevenshtein()
    {
        $sequenceMatchManager = new SequenceMatchManager();
        $sequenceMatchManager->setSubMatrix($this->subMatrix);

        $aCases = [
            ["A", "XXXA"],
            ["ABC", ""],
            ["", ""],
            ["GCT", "T"],
            ["GG", "TTCG"],
            ["CT", "TCT"],
            ["ACGT", "ACGT"],
        ];
        foreach ($aCases as [$sSeq1, $sSeq2]) {
            $this->assertEquals(
                levenshtein($sSeq1, $sSeq2),
                $sequenceMatchManager->xlevdist($sSeq1, $sSeq2),
                "xlevdist(\"$sSeq1\", \"$sSeq2\") should match PHP's native levenshtein()"
            );
        }
    }

    public function testMatch()
    {
        $sequenceManager = new SequenceManager($this->apiAminoMock, $this->apiNucleoMock, $this->apiElementsMock);
        $sequenceBuilder = new SequenceBuilder($sequenceManager);
        $sequenceBuilder->setSequence($this->sequence);

        $sSeq1 = $sequenceBuilder->subSeq(2,100);
        $sSeq2 = $sequenceBuilder->subSeq(100,100);

        $sequenceMatchManager = new SequenceMatchManager();
        $sequenceMatchManager->setSubMatrix($this->subMatrix);
        $sMatch = $sequenceMatchManager->match($sSeq1, $sSeq2);;

        $sExpected = "......C..C..AG.CC.G..C..........C.......CC....C.C...G...G.C....C.C.....G....A...G..CTGG..GC.....G...";

        $this->assertEquals($sExpected, $sMatch);
    }

    /**
     * Regression test: "if (!isset($aMatrix) == FALSE)" is always true for a required, non-
     * nullable array parameter, so partialMatch() always overwrote the matrix its caller passed
     * in with the default one. Grouping A and W together is not in the default matrix set up in
     * setUp() (D/E, K/R/H, X), so this only passes once the supplied matrix is actually used.
     */
    public function testPartialMatchUsesTheSuppliedMatrix()
    {
        $sequenceMatchManager = new SequenceMatchManager();
        $sequenceMatchManager->setSubMatrix($this->subMatrix);

        $oCustomMatrix = new SubMatrix();
        $oCustomMatrix->addrule('A', 'W');
        $aCustomRules = $oCustomMatrix->getRules();

        $this->assertTrue($sequenceMatchManager->partialMatch('A', 'W', $aCustomRules));
        $this->assertFalse($sequenceMatchManager->partialMatch('A', 'W', $this->subMatrix->getRules()));
    }
}