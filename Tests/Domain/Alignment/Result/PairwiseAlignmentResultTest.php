<?php
namespace Tests\Domain\Alignment\Result;

use Amelaye\BioPHP\Domain\Alignment\Exception\InvalidAlignmentInputException;
use Amelaye\BioPHP\Domain\Alignment\Result\PairwiseAlignmentResult;
use PHPUnit\Framework\TestCase;

class PairwiseAlignmentResultTest extends TestCase
{
    public function testExposesItsConstructorArguments()
    {
        $oResult = new PairwiseAlignmentResult("ACGT", "A-GT", 1, 0, 3, 0, 2);

        $this->assertEquals("ACGT", $oResult->getAlignedFirst());
        $this->assertEquals("A-GT", $oResult->getAlignedSecond());
        $this->assertEquals(1, $oResult->getScore());
        $this->assertEquals(4, $oResult->getLength());
        $this->assertEquals(0, $oResult->getFirstStart());
        $this->assertEquals(3, $oResult->getFirstEnd());
        $this->assertEquals(0, $oResult->getSecondStart());
        $this->assertEquals(2, $oResult->getSecondEnd());
    }

    public function testRejectsAnEmptyAlignedSequence()
    {
        $this->expectException(InvalidAlignmentInputException::class);

        new PairwiseAlignmentResult("", "AC", 0, 0, -1, 0, 1);
    }

    public function testRejectsAlignedSequencesOfDifferentLength()
    {
        $this->expectException(InvalidAlignmentInputException::class);

        new PairwiseAlignmentResult("ACG", "AC", 0, 0, 2, 0, 1);
    }

    public function testIdentityIgnoresGapColumnsInBothNumeratorAndDenominator()
    {
        // Columns: A/A match, C/- gap (ignored), G/G match, T/A mismatch -> 2 matches out of 3 compared.
        $oResult = new PairwiseAlignmentResult("ACGT", "A-GA", 0, 0, 3, 0, 2);

        $this->assertEqualsWithDelta(2 / 3, $oResult->getIdentity(), 0.0001);
    }

    public function testIdentityIsOneForAPerfectMatch()
    {
        $oResult = new PairwiseAlignmentResult("ACGT", "ACGT", 4, 0, 3, 0, 3);

        $this->assertEquals(1.0, $oResult->getIdentity());
    }

    public function testIdentityIsZeroWhenEveryColumnIsAGap()
    {
        $oResult = new PairwiseAlignmentResult("--", "AC", -4, 0, -1, 0, 1);

        $this->assertEquals(0.0, $oResult->getIdentity());
    }

    public function testIdentityOverLengthCountsTheGapColumnsAsBlastAndEmbossDo()
    {
        $oResult = new PairwiseAlignmentResult("ACGT----", "ACGTTTTT", 0, 0, 3, 0, 7);

        // Over aligned positions only, the four gap columns vanish : 4 / 4.
        $this->assertEquals(1.0, $oResult->getIdentity());
        // Over the whole alignment, as BLAST and EMBOSS needle report it : 4 / 8.
        $this->assertEquals(0.5, $oResult->getIdentityOverLength());
    }
}
