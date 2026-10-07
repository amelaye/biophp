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

    /**
     * VCF 4.3 : "Telomeres are indicated by using positions 0 or N+1".
     */
    public function testAcceptsTelomericPositionZero()
    {
        $oVariant = new VcfVariant("chr1", 0, "bnd_tel", "N", [".[chr1:1["], null, "PASS");

        $this->assertSame(0, $oVariant->getPosition());
    }

    public function testRejectsANegativePosition()
    {
        $this->expectException(InvalidVcfRecordException::class);
        $this->expectExceptionMessage("POS must be at least 0");

        new VcfVariant("chr1", -1, null, "A", [], null, null);
    }

    public function testRejectsAnEmptyReference()
    {
        $this->expectException(InvalidVcfRecordException::class);
        $this->expectExceptionMessage("REF must not be empty");

        new VcfVariant("chr1", 1, null, "", [], null, null);
    }

    /**
     * REF only ever holds bases : a symbolic allele, a breakend or a "." belongs to ALT. The
     * IUPAC codes some reference genomes hold (GRCh37) are tolerated, in either case.
     */
    public function testRejectsAReferenceThatIsNoBases()
    {
        foreach (["<DEL>", ".", "-", "A C"] as $sReference) {
            try {
                new VcfVariant("chr1", 1, null, $sReference, ["G"], null, null);
                $this->fail("REF " . $sReference . " should have been rejected.");
            } catch (InvalidVcfRecordException $ex) {
                $this->assertStringContainsString("REF must be bases", $ex->getMessage());
            }
        }

        $this->assertEquals("acgtn", (new VcfVariant("chr1", 1, null, "acgtn", [], null, null))->getReference());
        $this->assertEquals("R", (new VcfVariant("chr3", 60830534, null, "R", ["A"], null, null))->getReference());
    }
}
