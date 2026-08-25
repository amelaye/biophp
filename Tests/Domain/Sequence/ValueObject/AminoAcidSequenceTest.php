<?php
namespace Tests\Domain\Sequence\ValueObject;

use Amelaye\BioPHP\Domain\Sequence\ValueObject\AminoAcidSequence;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\InvalidSequenceException;
use PHPUnit\Framework\TestCase;

class AminoAcidSequenceTest extends TestCase
{
    public function testWrapsTheStringAndExposesIt()
    {
        $oAmino = new AminoAcidSequence("GAVLIFYWKRH");

        $this->assertEquals("GAVLIFYWKRH", $oAmino->getValue());
        $this->assertEquals("GAVLIFYWKRH", (string) $oAmino);
        $this->assertEquals("PROTEIN", $oAmino->getMolType());
        $this->assertEquals(11, $oAmino->getLength());
    }

    public function testNormalizesCaseAndWhitespace()
    {
        $oAmino = new AminoAcidSequence("gavl ifyw\nkrh");

        $this->assertEquals("GAVLIFYWKRH", $oAmino->getValue());
    }

    public function testAcceptsTheTwentyAminoAcidsPlusUnknownAndStop()
    {
        $oAmino = new AminoAcidSequence("ACDEFGHIKLMNPQRSTVWYX*");

        $this->assertEquals("ACDEFGHIKLMNPQRSTVWYX*", $oAmino->getValue());
    }

    public function testRejectsASymbolOutsideTheAlphabet()
    {
        $this->expectException(InvalidSequenceException::class);
        $this->expectExceptionMessage('Invalid PROTEIN symbol "J" at position 2.');

        new AminoAcidSequence("GAJVL");
    }

    public function testHasStop()
    {
        $this->assertTrue((new AminoAcidSequence("GAVL*"))->hasStop());
        $this->assertFalse((new AminoAcidSequence("GAVL"))->hasStop());
    }

    public function testTruncateAtStop()
    {
        $oAmino = new AminoAcidSequence("GAVL*IFYW*");

        $this->assertEquals("GAVL", $oAmino->truncateAtStop()->getValue());
        $this->assertEquals("GAVL*IFYW*", $oAmino->getValue());
    }

    public function testTruncateAtStopReturnsTheWholeChainWhenThereIsNoStop()
    {
        $oAmino = new AminoAcidSequence("GAVL");

        $this->assertSame($oAmino, $oAmino->truncateAtStop());
    }

    public function testTruncateAtStopOnALeadingStopYieldsAnEmptyChain()
    {
        $oAmino = new AminoAcidSequence("*GAVL");

        $this->assertTrue($oAmino->truncateAtStop()->isEmpty());
    }

    public function testHasUnknownResidue()
    {
        $this->assertTrue((new AminoAcidSequence("GAXVL"))->hasUnknownResidue());
        $this->assertFalse((new AminoAcidSequence("GAVL"))->hasUnknownResidue());
    }

    public function testSubSequenceAndReverse()
    {
        $oAmino = new AminoAcidSequence("GAVLI");

        $this->assertEquals("AVL", $oAmino->subSequence(1, 3)->getValue());
        $this->assertEquals("ILVAG", $oAmino->reverse()->getValue());
        $this->assertInstanceOf(AminoAcidSequence::class, $oAmino->reverse());
    }

    public function testEquals()
    {
        $oAmino = new AminoAcidSequence("GAVL");

        $this->assertTrue($oAmino->equals(new AminoAcidSequence("gavl")));
        $this->assertFalse($oAmino->equals(new AminoAcidSequence("GAVLI")));
    }

    public function testIsValid()
    {
        $this->assertTrue(AminoAcidSequence::isValid("GAVLI"));
        $this->assertFalse(AminoAcidSequence::isValid("GAVLIJ"));
    }
}
