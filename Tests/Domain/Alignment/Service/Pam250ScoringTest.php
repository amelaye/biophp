<?php
namespace Tests\Domain\Alignment\Service;

use Amelaye\BioPHP\Api\DTO\Pam250MatrixDigitDTO;
use Amelaye\BioPHP\Api\Interfaces\Pam250MatrixDigitApiAdapter;
use Amelaye\BioPHP\Domain\Alignment\Exception\InvalidAlignmentInputException;
use Amelaye\BioPHP\Domain\Alignment\Service\Pam250Scoring;
use PHPUnit\Framework\TestCase;

class Pam250ScoringTest extends TestCase
{
    private function makeDto(string $sId, int $iValue): Pam250MatrixDigitDTO
    {
        $oDto = new Pam250MatrixDigitDTO();
        $oDto->setId($sId);
        $oDto->setValue($iValue);

        return $oDto;
    }

    /**
     * A stand-in adapter, not an HTTP mock : Pam250Scoring only depends on the interface, and
     * GetPam250MatrixArray() is a pure data transform with no network call, so there is nothing to
     * fake at the HTTP level the way Tests\Api\Pam250MatrixDigitTest does for the adapter itself.
     * Values below are taken verbatim from Tests/Api/samples/Pam250Matrix.php.
     */
    private function makeAdapter(): Pam250MatrixDigitApiAdapter
    {
        $aDtOs = [
            $this->makeDto("AA", 2),
            $this->makeDto("AC", -2),
            $this->makeDto("CA", -2),
            $this->makeDto("CC", 12),
            $this->makeDto("GG", 5),
        ];

        return new class ($aDtOs) implements Pam250MatrixDigitApiAdapter {
            private $dtOs;

            public function __construct(array $aDtOs)
            {
                $this->dtOs = $aDtOs;
            }

            public function getPam250Matrix(): array
            {
                return $this->dtOs;
            }

            public static function GetPam250MatrixArray(array $aPam250): array
            {
                $aMatrix = [];
                foreach ($aPam250 as $oEntry) {
                    $aMatrix[$oEntry->getId()] = $oEntry->getValue();
                }

                return $aMatrix;
            }
        };
    }

    public function testLooksUpAKnownPairFromTheMatrix()
    {
        $oScoring = new Pam250Scoring($this->makeAdapter());

        $this->assertEquals(12, $oScoring->score("C", "C"));
        $this->assertEquals(-2, $oScoring->score("A", "C"));
    }

    public function testComparisonIsCaseInsensitive()
    {
        $oScoring = new Pam250Scoring($this->makeAdapter());

        $this->assertEquals(5, $oScoring->score("g", "G"));
    }

    public function testAnUndefinedPairThrows()
    {
        $oScoring = new Pam250Scoring($this->makeAdapter());

        $this->expectException(InvalidAlignmentInputException::class);
        $this->expectExceptionMessage("X");

        $oScoring->score("A", "X");
    }

    /**
     * PAM250 scores amino acids : on DNA it read A, C, G and T as alanine, cysteine, glycine and
     * threonine.
     */
    public function testItIsMadeForProteinsOnly()
    {
        $oScoring = new Pam250Scoring($this->makeAdapter());

        $this->assertTrue($oScoring->supportsMolType("PROTEIN"));
        $this->assertFalse($oScoring->supportsMolType("DNA"));
        $this->assertFalse($oScoring->supportsMolType("RNA"));
    }

    public function testAnAlignerRefusesItOnDna()
    {
        $this->expectException(InvalidAlignmentInputException::class);
        $this->expectExceptionMessage("Pam250Scoring is not made for DNA sequences.");

        (new \Amelaye\BioPHP\Domain\Alignment\Service\NeedlemanWunschAligner())->align(
            new \Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence("ACGT"),
            new \Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence("ACGT"),
            new Pam250Scoring($this->makeAdapter()),
            -2
        );
    }
}
