<?php
namespace Tests\Domain\Alignment\Service;

use Amelaye\BioPHP\Domain\Alignment\Exception\InvalidAlignmentInputException;
use Amelaye\BioPHP\Domain\Alignment\Service\SemiGlobalAligner;
use Amelaye\BioPHP\Domain\Alignment\Service\SimpleMatchMismatchScoring;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;
use PHPUnit\Framework\TestCase;

/**
 * The main fixture ("CGTA" / "GTAGG") was worked out by hand on the full 4x5 dynamic programming
 * matrix (match=+1, mismatch=-1, gap=-2, zero borders, no floor on the recurrence itself), locating
 * the best-scoring cell in the last row/column and tracing it back to a border, then cross-checked by
 * running the exact same case through a standalone PHP script, before being written here - the same
 * discipline used for the Needleman-Wunsch and Smith-Waterman fixtures.
 */
class SemiGlobalAlignerTest extends TestCase
{
    private $aligner;
    private $scoring;

    public function setUp(): void
    {
        $this->aligner = new SemiGlobalAligner();
        $this->scoring = new SimpleMatchMismatchScoring(1, -1);
    }

    /**
     * The suffix "GTA" of the first sequence overlaps the prefix "GTA" of the second : the leading
     * "C" of the first sequence and the trailing "GG" of the second must both be free, contributing
     * nothing to the score, unlike a global alignment which would be forced to score them.
     */
    public function testFindsASuffixPrefixOverlapWithFreeFlankingGaps()
    {
        $oResult = $this->aligner->align(new DnaSequence("CGTA"), new DnaSequence("GTAGG"), $this->scoring, -2);

        $this->assertEquals("GTA", $oResult->getAlignedFirst());
        $this->assertEquals("GTA", $oResult->getAlignedSecond());
        $this->assertEquals(3, $oResult->getScore());
        $this->assertEquals(1, $oResult->getFirstStart());
        $this->assertEquals(3, $oResult->getFirstEnd());
        $this->assertEquals(0, $oResult->getSecondStart());
        $this->assertEquals(2, $oResult->getSecondEnd());
    }

    /**
     * The primer-in-vector use case this aligner exists for : a short sequence fully contained inside
     * a longer one scores as if the longer sequence's untouched flanks on BOTH sides were never there.
     */
    public function testAShortSequenceFullyContainedInALongerOneScoresWithoutPenalizingEitherFlank()
    {
        $oResult = $this->aligner->align(
            new DnaSequence("ACGT"),
            new DnaSequence("TTTTACGTTTTT"),
            $this->scoring,
            -2
        );

        $this->assertEquals("ACGT", $oResult->getAlignedFirst());
        $this->assertEquals("ACGT", $oResult->getAlignedSecond());
        $this->assertEquals(4, $oResult->getScore());
        $this->assertEquals(0, $oResult->getFirstStart());
        $this->assertEquals(3, $oResult->getFirstEnd());
        $this->assertEquals(4, $oResult->getSecondStart());
        $this->assertEquals(7, $oResult->getSecondEnd());
    }

    public function testTwoIdenticalSequencesAlignEndToEndWithNoGaps()
    {
        $oResult = $this->aligner->align(new DnaSequence("ACGT"), new DnaSequence("ACGT"), $this->scoring, -2);

        $this->assertEquals("ACGT", $oResult->getAlignedFirst());
        $this->assertEquals("ACGT", $oResult->getAlignedSecond());
        $this->assertEquals(4, $oResult->getScore());
    }

    public function testRejectsANonNegativeGapPenalty()
    {
        $this->expectException(InvalidAlignmentInputException::class);
        $this->expectExceptionMessage("strictly negative");

        $this->aligner->align(new DnaSequence("AC"), new DnaSequence("AC"), $this->scoring, 0);
    }
}
