<?php
namespace Tests\Domain\Variants\ValueObject;

use Amelaye\BioPHP\Domain\Variants\Exception\InvalidVcfRecordException;
use Amelaye\BioPHP\Domain\Variants\ValueObject\VcfVariant;
use PHPUnit\Framework\TestCase;

class VcfVariantTest extends TestCase
{
    public function testExposesEveryField()
    {
        $oVariant = new VcfVariant("chr1", 100, "rs123", "A", ["G"], 50.5, "PASS", ["DP" => "20"]);

        $this->assertEquals("chr1", $oVariant->getChrom());
        $this->assertEquals(100, $oVariant->getPosition());
        $this->assertEquals("rs123", $oVariant->getId());
        $this->assertEquals("A", $oVariant->getReference());
        $this->assertEquals(["G"], $oVariant->getAlternates());
        $this->assertEquals(50.5, $oVariant->getQuality());
        $this->assertEquals("PASS", $oVariant->getFilter());
        $this->assertEquals(["DP" => "20"], $oVariant->getInfo());
        $this->assertTrue($oVariant->isPass());
    }

    public function testEveryOptionalFieldAcceptsNull()
    {
        $oVariant = new VcfVariant("chr1", 1, null, "A", [], null, null);

        $this->assertNull($oVariant->getId());
        $this->assertEquals([], $oVariant->getAlternates());
        $this->assertNull($oVariant->getQuality());
        $this->assertNull($oVariant->getFilter());
        $this->assertFalse($oVariant->isPass());
    }

    public function testIsPassIsFalseForAnyFilterOtherThanExactlyPass()
    {
        $oVariant = new VcfVariant("chr1", 1, null, "A", ["G"], null, "q10");

        $this->assertFalse($oVariant->isPass());
    }

    public function testRejectsAnEmptyChrom()
    {
        $this->expectException(InvalidVcfRecordException::class);
        $this->expectExceptionMessage("CHROM must not be empty");

        new VcfVariant("", 1, null, "A", [], null, null);
    }

    public function testRejectsAPositionBelowOne()
    {
        $this->expectException(InvalidVcfRecordException::class);
        $this->expectExceptionMessage("POS must be at least 1");

        new VcfVariant("chr1", 0, null, "A", [], null, null);
    }

    public function testRejectsAnEmptyReference()
    {
        $this->expectException(InvalidVcfRecordException::class);
        $this->expectExceptionMessage("REF must not be empty");

        new VcfVariant("chr1", 1, null, "", [], null, null);
    }
}
