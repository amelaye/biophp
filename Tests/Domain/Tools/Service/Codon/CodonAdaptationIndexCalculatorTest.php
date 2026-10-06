<?php
namespace Tests\Domain\Tools\Service\Codon;

use Amelaye\BioPHP\Api\AminoApi;
use Amelaye\BioPHP\Api\ElementApi;
use Amelaye\BioPHP\Api\NucleotidApi;
use Amelaye\BioPHP\Domain\Sequence\Builder\SequenceBuilder;
use Amelaye\BioPHP\Domain\Sequence\Service\SequenceManager;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;
use Amelaye\BioPHP\Domain\Tools\Service\Codon\CodonAdaptationIndexCalculator;
use Amelaye\BioPHP\Domain\Tools\ValueObject\CodonUsageTable;
use PHPUnit\Framework\TestCase;

/**
 * The main fixture's expected score was computed by hand : reference counts TTT=30/TTC=10 (Phe,
 * max=30) and TAT=5/TAC=20 (Tyr, max=20), coding sequence ATG(Met, excluded) TTT(Phe, w=1)
 * TAC(Tyr, w=1) TTC(Phe, w=10/30) TAA(stop, excluded) -> geometric mean of {1, 1, 1/3} over 3 scored
 * codons = exp((ln(1)+ln(1)+ln(1/3))/3) = exp(ln(1/3)/3) = (1/3)^(1/3). Verified independently with a
 * standalone PHP script using only built-in math functions before being written here.
 */
class CodonAdaptationIndexCalculatorTest extends TestCase
{
    private $calculator;

    public function setUp(): void
    {
        require __DIR__ . '/../samples/Aminos.php';
        require __DIR__ . '/../samples/Nucleotids.php';
        require __DIR__ . '/../samples/Elements.php';

        $clientMock = $this->getMockBuilder('GuzzleHttp\Client')->getMock();
        $serializerMock = \JMS\Serializer\SerializerBuilder::create()->build();

        $apiAminoMock = $this->getMockBuilder(AminoApi::class)
            ->setConstructorArgs([$clientMock, $serializerMock])
            ->onlyMethods(['getAminos'])
            ->getMock();
        $apiAminoMock->method("getAminos")->willReturn($aAminosObjects);

        $apiNucleoMock = $this->getMockBuilder(NucleotidApi::class)
            ->setConstructorArgs([$clientMock, $serializerMock])
            ->onlyMethods(['getNucleotids'])
            ->getMock();
        $apiNucleoMock->method("getNucleotids")->willReturn($aNucleoObjects);

        $apiElementsMock = $this->getMockBuilder(ElementApi::class)
            ->setConstructorArgs([$clientMock, $serializerMock])
            ->onlyMethods(['getElements', 'getElement'])
            ->getMock();
        $apiElementsMock->method("getElements")->willReturn($aElementsObjects);
        $apiElementsMock->method("getElement")->willReturn($aElementsObjects[5]);

        $sequenceManager = new SequenceManager($apiAminoMock, $apiNucleoMock, $apiElementsMock);
        $sequenceBuilder = new SequenceBuilder($sequenceManager);

        $this->calculator = new CodonAdaptationIndexCalculator($sequenceBuilder);
    }

    public function testExcludesStopAndSingleCodonAminoAcidsAndScoresTheRest()
    {
        $oTable = new CodonUsageTable([
            "TTT" => 30, "TTC" => 10,
            "TAT" => 5, "TAC" => 20,
        ]);
        $oCds = new DnaSequence("ATGTTTTACTTCTAA");

        $oResult = $this->calculator->calculate($oCds, $oTable);

        $this->assertEquals(3, $oResult->getCodonsScored());
        $this->assertEqualsWithDelta(pow(1 / 3, 1 / 3), $oResult->getScore(), 0.0000001);
    }

    public function testAPerfectlyOptimalSequenceScoresOne()
    {
        $oTable = new CodonUsageTable([
            "TTT" => 30, "TTC" => 10,
            "TAT" => 5, "TAC" => 20,
        ]);
        // Only the most-used codon for each amino acid: w = 1 for every scored codon.
        $oCds = new DnaSequence("TTTTAC");

        $oResult = $this->calculator->calculate($oCds, $oTable);

        $this->assertEqualsWithDelta(1.0, $oResult->getScore(), 0.0000001);
        $this->assertEquals(2, $oResult->getCodonsScored());
    }

    public function testOnlyCompleteTrailingCodonsAreConsidered()
    {
        $oTable = new CodonUsageTable(["TTT" => 30, "TTC" => 10]);
        // "TTTTT" is one complete codon (TTT) plus two leftover bases, which must be ignored.
        $oCds = new DnaSequence("TTTTT");

        $oResult = $this->calculator->calculate($oCds, $oTable);

        $this->assertEquals(1, $oResult->getCodonsScored());
    }

    public function testThrowsWhenACodonHasNoUsableReferenceUsage()
    {
        $oTable = new CodonUsageTable(["TTT" => 30, "TTC" => 0]);
        $oCds = new DnaSequence("TTC");

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("TTC");

        $this->calculator->calculate($oCds, $oTable);
    }

    public function testThrowsWhenNoCodonCanBeScoredAtAll()
    {
        $oTable = new CodonUsageTable(["TTT" => 30, "TTC" => 10]);
        // Met and a stop codon only: nothing left to score.
        $oCds = new DnaSequence("ATGTAA");

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("No scorable codon");

        $this->calculator->calculate($oCds, $oTable);
    }
}
