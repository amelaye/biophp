<?php
namespace Tests\Domain\Alignment\Service;

use Amelaye\BioPHP\Domain\Alignment\Exception\InvalidAlignmentInputException;
use Amelaye\BioPHP\Domain\Alignment\Service\NeedlemanWunschAligner;
use Amelaye\BioPHP\Domain\Alignment\Service\NucleotideAmbiguityScoring;
use Amelaye\BioPHP\Domain\Alignment\Service\SimpleMatchMismatchScoring;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * SimpleMatchMismatchScoring gives N against N a full match and R against A a full mismatch : a
 * symbol standing for several bases is neither.
 */
class NucleotideAmbiguityScoringTest extends TestCase
{
    public static function pairs(): array
    {
        return [
            "same definite base" => ["A", "A", 1],
            "different definite bases" => ["A", "C", -1],
            "U is T" => ["U", "T", 1],
            "case does not matter" => ["a", "A", 1],
            "N against a base" => ["N", "G", 0],
            "N against N" => ["N", "N", 0],
            "X is N" => ["X", "A", 0],
            "R may be A" => ["R", "A", 0],
            "R cannot be C" => ["R", "C", -1],
            "R against Y share nothing" => ["R", "Y", -1],
            "R against S share G" => ["R", "S", 0],
            "B cannot be A" => ["B", "A", -1],
            "H against D share A and T" => ["H", "D", 0],
        ];
    }

    #[DataProvider("pairs")]
    public function testScoresAPairByTheBasesItMayStandFor(string $sFirst, string $sSecond, int $iExpected)
    {
        $oScoring = new NucleotideAmbiguityScoring(1, -1, 0);

        $this->assertSame($iExpected, $oScoring->score($sFirst, $sSecond));
        $this->assertSame($iExpected, $oScoring->score($sSecond, $sFirst), "the score is symmetric");
    }

    public function testCustomScoresAreHonored()
    {
        $oScoring = new NucleotideAmbiguityScoring(5, -4, -1);

        $this->assertSame(5, $oScoring->score("G", "G"));
        $this->assertSame(-4, $oScoring->score("G", "T"));
        $this->assertSame(-1, $oScoring->score("N", "G"));
    }

    public function testASymbolThatIsNoNucleotideCodeThrows()
    {
        $this->expectException(InvalidAlignmentInputException::class);
        (new NucleotideAmbiguityScoring())->score("A", "E");
    }

    public function testItIsMadeForDnaAndRnaOnly()
    {
        $oScoring = new NucleotideAmbiguityScoring();

        $this->assertTrue($oScoring->supportsMolType("DNA"));
        $this->assertTrue($oScoring->supportsMolType("RNA"));
        $this->assertFalse($oScoring->supportsMolType("PROTEIN"));
    }

    public function testAnAlignmentOfAReadWithAnNIsNotPenalisedAsAMismatch()
    {
        $oAligner = new NeedlemanWunschAligner();

        $oAmbiguous = $oAligner->align(new DnaSequence("ACGT"), new DnaSequence("ACNT"), new NucleotideAmbiguityScoring(1, -1, 0), -2);
        $oSimple = $oAligner->align(new DnaSequence("ACGT"), new DnaSequence("ACNT"), new SimpleMatchMismatchScoring(1, -1), -2);

        $this->assertEquals(3, $oAmbiguous->getScore());
        $this->assertEquals(2, $oSimple->getScore());
    }
}
