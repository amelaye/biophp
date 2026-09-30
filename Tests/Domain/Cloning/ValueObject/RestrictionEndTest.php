<?php
namespace Tests\Domain\Cloning\ValueObject;

use Amelaye\BioPHP\Domain\Cloning\ValueObject\RestrictionEnd;
use PHPUnit\Framework\TestCase;

class RestrictionEndTest extends TestCase
{
    public function testBluntHasNoOverhang()
    {
        $oEnd = RestrictionEnd::blunt();

        $this->assertEquals(RestrictionEnd::BLUNT, $oEnd->getType());
        $this->assertNull($oEnd->getOverhangSequence());
        $this->assertTrue($oEnd->isBlunt());
        $this->assertTrue($oEnd->isDeterminate());
    }

    public function testFivePrimeCarriesItsOverhang()
    {
        $oEnd = RestrictionEnd::fivePrime("AATT");

        $this->assertEquals(RestrictionEnd::FIVE_PRIME, $oEnd->getType());
        $this->assertEquals("AATT", $oEnd->getOverhangSequence());
        $this->assertFalse($oEnd->isBlunt());
    }

    public function testThreePrimeCarriesItsOverhang()
    {
        $oEnd = RestrictionEnd::threePrime("ACGT");

        $this->assertEquals(RestrictionEnd::THREE_PRIME, $oEnd->getType());
        $this->assertEquals("ACGT", $oEnd->getOverhangSequence());
    }

    public function testUnknownIsNotDeterminate()
    {
        $oEnd = RestrictionEnd::unknown();

        $this->assertEquals(RestrictionEnd::UNKNOWN, $oEnd->getType());
        $this->assertNull($oEnd->getOverhangSequence());
        $this->assertFalse($oEnd->isDeterminate());
    }

    public function testRejectsAnInvalidType()
    {
        $this->expectException(\InvalidArgumentException::class);

        new RestrictionEnd("SIDEWAYS");
    }

    public function testRejectsAnOverhangSequenceOnABluntEnd()
    {
        $this->expectException(\InvalidArgumentException::class);

        new RestrictionEnd(RestrictionEnd::BLUNT, "AATT");
    }

    public function testRejectsAMissingOverhangSequenceOnAStickyEnd()
    {
        $this->expectException(\InvalidArgumentException::class);

        new RestrictionEnd(RestrictionEnd::FIVE_PRIME);
    }
}
