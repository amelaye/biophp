<?php
namespace Tests\Domain\Alignment\Service;

use Amelaye\BioPHP\Domain\Alignment\Exception\InvalidAlignmentInputException;
use Amelaye\BioPHP\Domain\Alignment\Service\SimpleMatchMismatchScoring;
use Amelaye\BioPHP\Domain\Alignment\Service\SmithWatermanAligner;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;
use PHPUnit\Framework\TestCase;

/**
 * The main fixture (TACGTA / GACGTG) was worked out by hand on the full 6x6 dynamic programming
 * matrix (match=+2, mismatch=-1, gap=-2), locating the single highest-scoring cell and tracing it
 * back to zero, then cross-checked by running the exact same case through a standalone PHP script,
 * before being written here - the same discipline used for the Needleman-Wunsch fixtures.
 */
class SmithWatermanAlignerTest extends TestCase
{
    private $aligner;

    public function setUp(): void
    {
        $this->aligner = new SmithWatermanAligner();
    }

    /**
     * Both sequences share the internal "ACGT" but differ everywhere else (T/A flanks vs G/G
     * flanks, which score as mismatches) : a local aligner should clip straight to that shared core
     * and ignore the flanks entirely, unlike a global aligner which would be forced to score them.
     */
    public function testFindsALocallySimilarCoreAndIgnoresDissimilarFlanks()
    {
        $oResult = $this->aligner->align(
            new DnaSequence("TACGTA"),
            new DnaSequence("GACGTG"),
            new SimpleMatchMismatchScoring(2, -1),
            -2
        );

        $this->assertEquals("ACGT", $oResult->getAlignedFirst());
        $this->assertEquals("ACGT", $oResult->getAlignedSecond());
        $this->assertEquals(8, $oResult->getScore());
        $this->assertEquals(1, $oResult->getFirstStart());
        $this->assertEquals(4, $oResult->getFirstEnd());
        $this->assertEquals(1, $oResult->getSecondStart());
        $this->assertEquals(4, $oResult->getSecondEnd());
    }

    public function testTwoIdenticalSequencesAlignEndToEnd()
    {
        $oResult = $this->aligner->align(
            new DnaSequence("ACGT"),
            new DnaSequence("ACGT"),
            new SimpleMatchMismatchScoring(2, -1),
            -2
        );

        $this->assertEquals("ACGT", $oResult->getAlignedFirst());
        $this->assertEquals("ACGT", $oResult->getAlignedSecond());
        $this->assertEquals(8, $oResult->getScore());
        $this->assertEquals(0, $oResult->getFirstStart());
        $this->assertEquals(3, $oResult->getFirstEnd());
    }

    /**
     * With a mismatch/gap cost this punishing, no pair of substrings of "AAAA" and "TTTT" can score
     * above zero, so there is nothing to report : PairwiseAlignmentResult itself rejects an empty
     * aligned pair, which is the signal a caller should treat as "no local similarity found".
     */
    public function testNoLocalSimilarityRaisesRatherThanReturningAnEmptyAlignment()
    {
        $this->expectException(InvalidAlignmentInputException::class);

        $this->aligner->align(
            new DnaSequence("AAAA"),
            new DnaSequence("TTTT"),
            new SimpleMatchMismatchScoring(1, -100),
            -100
        );
    }

    public function testRejectsANonNegativeGapPenalty()
    {
        $this->expectException(InvalidAlignmentInputException::class);
        $this->expectExceptionMessage("strictly negative");

        $this->aligner->align(
            new DnaSequence("AC"),
            new DnaSequence("AC"),
            new SimpleMatchMismatchScoring(1, -1),
            0
        );
    }
}
