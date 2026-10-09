<?php
namespace Tests\Domain\Alignment\Service;

use Amelaye\BioPHP\Domain\Alignment\Exception\InvalidAlignmentInputException;
use Amelaye\BioPHP\Domain\Alignment\Service\NeedlemanWunschAligner;
use Amelaye\BioPHP\Domain\Alignment\Service\SimpleMatchMismatchScoring;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\AminoAcidSequence;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;
use PHPUnit\Framework\TestCase;

/**
 * Every expected alignment and score below was first worked out by hand on the full dynamic
 * programming matrix (match=+1, mismatch=-1, gap=-2 unless stated otherwise), then cross-checked by
 * running the exact same case through a standalone PHP script before being written here, per the
 * project rule that alignment fixtures must be locked against independently computed values, not
 * deduced from the algorithm under test.
 */
class NeedlemanWunschAlignerTest extends TestCase
{
    private $aligner;
    private $scoring;

    public function setUp(): void
    {
        $this->aligner = new NeedlemanWunschAligner();
        $this->scoring = new SimpleMatchMismatchScoring(1, -1);
    }

    public function testAlignsTwoIdenticalSequencesWithNoGaps()
    {
        $oResult = $this->aligner->align(new DnaSequence("ACGT"), new DnaSequence("ACGT"), $this->scoring, -2);

        $this->assertEquals("ACGT", $oResult->getAlignedFirst());
        $this->assertEquals("ACGT", $oResult->getAlignedSecond());
        $this->assertEquals(4, $oResult->getScore());
        $this->assertEquals(1.0, $oResult->getIdentity());
    }

    /**
     * "ACGT" against "AGT" : the DP matrix's bottom-right cell is 1, and the traceback recovers a
     * single "C" deleted from the first sequence rather than any other, equally-scoring arrangement of
     * three gaps, because the diagonal move is preferred whenever scores tie.
     */
    public function testAlignsTwoSequencesOfDifferentLengthWithAGap()
    {
        $oResult = $this->aligner->align(new DnaSequence("ACGT"), new DnaSequence("AGT"), $this->scoring, -2);

        $this->assertEquals("ACGT", $oResult->getAlignedFirst());
        $this->assertEquals("A-GT", $oResult->getAlignedSecond());
        $this->assertEquals(1, $oResult->getScore());
        $this->assertEquals(0, $oResult->getFirstStart());
        $this->assertEquals(3, $oResult->getFirstEnd());
        $this->assertEquals(0, $oResult->getSecondStart());
        $this->assertEquals(2, $oResult->getSecondEnd());
    }

    public function testAligningAnEmptySequenceProducesAnAllGapAlignment()
    {
        $oResult = $this->aligner->align(new DnaSequence(""), new DnaSequence("AC"), $this->scoring, -2);

        $this->assertEquals("--", $oResult->getAlignedFirst());
        $this->assertEquals("AC", $oResult->getAlignedSecond());
        $this->assertEquals(-4, $oResult->getScore());
        $this->assertEquals(0, $oResult->getFirstStart());
        $this->assertEquals(-1, $oResult->getFirstEnd());
    }

    public function testWorksGenericallyOnAminoAcidSequences()
    {
        $oResult = $this->aligner->align(
            new AminoAcidSequence("MKV"),
            new AminoAcidSequence("MKV"),
            $this->scoring,
            -2
        );

        $this->assertEquals("MKV", $oResult->getAlignedFirst());
        $this->assertEquals("MKV", $oResult->getAlignedSecond());
        $this->assertEquals(3, $oResult->getScore());
    }

    public function testRejectsANonNegativeGapPenalty()
    {
        $this->expectException(InvalidAlignmentInputException::class);
        $this->expectExceptionMessage("strictly negative");

        $this->aligner->align(new DnaSequence("AC"), new DnaSequence("AC"), $this->scoring, 0);
    }
}
