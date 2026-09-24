<?php
namespace Tests\Domain\Sequence\ValueObject;

use Amelaye\BioPHP\Domain\Sequence\ValueObject\CircularDnaSequence;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\InvalidSequenceException;
use PHPUnit\Framework\TestCase;

class CircularDnaSequenceTest extends TestCase
{
    // Indices: 0:A 1:C 2:G 3:T 4:G 5:G 6:C 7:T 8:A 9:A
    private const SEQUENCE = "ACGTGGCTAA";

    public function testWrapsAndBehavesLikeADnaSequence()
    {
        $oCircular = new CircularDnaSequence(self::SEQUENCE);

        $this->assertEquals(self::SEQUENCE, $oCircular->getValue());
        $this->assertEquals(10, $oCircular->getLength());
        $this->assertInstanceOf(DnaSequence::class, $oCircular);
    }

    public function testRejectsAnEmptySequence()
    {
        $this->expectException(InvalidSequenceException::class);
        $this->expectExceptionMessage("A circular DNA sequence must not be empty.");

        new CircularDnaSequence("");
    }

    public function testRejectsASequenceThatIsOnlyWhitespace()
    {
        $this->expectException(InvalidSequenceException::class);

        new CircularDnaSequence("   \n\t");
    }

    public function testPositionModuloWrapsNegativeAndOversizedPositions()
    {
        $oCircular = new CircularDnaSequence(self::SEQUENCE);

        $this->assertEquals(0, $oCircular->positionModulo(0));
        $this->assertEquals(0, $oCircular->positionModulo(10));
        $this->assertEquals(3, $oCircular->positionModulo(23));
        $this->assertEquals(9, $oCircular->positionModulo(-1));
        $this->assertEquals(3, $oCircular->positionModulo(-7));
    }

    public function testRotatesToAGivenOrigin()
    {
        $oCircular = new CircularDnaSequence(self::SEQUENCE);

        $this->assertEquals("TGGCTAAACG", $oCircular->rotateTo(3)->getValue());
    }

    public function testRotatingByZeroOrALengthMultipleYieldsTheSameSequence()
    {
        $oCircular = new CircularDnaSequence(self::SEQUENCE);

        $this->assertEquals(self::SEQUENCE, $oCircular->rotateTo(0)->getValue());
        $this->assertEquals(self::SEQUENCE, $oCircular->rotateTo(10)->getValue());
        $this->assertEquals(self::SEQUENCE, $oCircular->rotateTo(20)->getValue());
        $this->assertEquals(self::SEQUENCE, $oCircular->rotateTo(-10)->getValue());
    }

    public function testRotatingByANegativePositionMatchesItsPositiveEquivalent()
    {
        $oCircular = new CircularDnaSequence(self::SEQUENCE);

        $this->assertEquals($oCircular->rotateTo(3)->getValue(), $oCircular->rotateTo(-7)->getValue());
    }

    public function testRotatingPastTheLengthMatchesItsInRangeEquivalent()
    {
        $oCircular = new CircularDnaSequence(self::SEQUENCE);

        $this->assertEquals($oCircular->rotateTo(3)->getValue(), $oCircular->rotateTo(13)->getValue());
    }

    public function testRotateToReturnsACircularDnaSequence()
    {
        $oCircular = new CircularDnaSequence(self::SEQUENCE);

        $this->assertInstanceOf(CircularDnaSequence::class, $oCircular->rotateTo(3));
    }

    public function testSlicesWithoutCrossingTheOrigin()
    {
        $oCircular = new CircularDnaSequence(self::SEQUENCE);

        $oFragment = $oCircular->sliceCircular(2, 4);

        $this->assertInstanceOf(DnaSequence::class, $oFragment);
        $this->assertEquals("GTGG", $oFragment->getValue());
    }

    public function testSlicesAcrossTheOrigin()
    {
        $oCircular = new CircularDnaSequence(self::SEQUENCE);

        $oFragment = $oCircular->sliceCircular(8, 4);

        $this->assertEquals("AAAC", $oFragment->getValue());
    }

    public function testSliceWithANegativeStartIsNormalizedFirst()
    {
        $oCircular = new CircularDnaSequence(self::SEQUENCE);

        // start = -2 -> positionModulo(-2) = 8, same fragment as testSlicesAcrossTheOrigin().
        $this->assertEquals("AAAC", $oCircular->sliceCircular(-2, 4)->getValue());
    }

    public function testSliceOfZeroLengthIsAnEmptyLinearSequence()
    {
        $oCircular = new CircularDnaSequence(self::SEQUENCE);

        $oFragment = $oCircular->sliceCircular(5, 0);

        $this->assertTrue($oFragment->isEmpty());
        $this->assertEquals("", $oFragment->getValue());
    }

    public function testSliceOfTheWholeLengthReturnsTheRotatedSequence()
    {
        $oCircular = new CircularDnaSequence(self::SEQUENCE);

        $this->assertEquals("TGGCTAAACG", $oCircular->sliceCircular(3, 10)->getValue());
    }

    public function testRejectsANegativeSliceLength()
    {
        $this->expectException(InvalidSequenceException::class);
        $this->expectExceptionMessage("must not be negative");

        (new CircularDnaSequence(self::SEQUENCE))->sliceCircular(0, -1);
    }

    public function testRejectsASliceLengthLongerThanTheSequence()
    {
        $this->expectException(InvalidSequenceException::class);
        $this->expectExceptionMessage("exceeds sequence length");

        (new CircularDnaSequence(self::SEQUENCE))->sliceCircular(0, 11);
    }

    public function testPreservesIupacDegeneratedSymbolsThroughRotationAndSlicing()
    {
        $sIupac = "ACGTMRWSYKVHDBXN";
        $oCircular = new CircularDnaSequence($sIupac);

        $this->assertEquals($sIupac, $oCircular->getValue());
        $this->assertEquals("MRWSYKVHDBXNACGT", $oCircular->rotateTo(4)->getValue());
        $this->assertEquals("XNACGT", $oCircular->sliceCircular(14, 6)->getValue());
    }
}
