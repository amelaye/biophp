<?php
namespace Tests\Domain\Cloning\ValueObject;

use Amelaye\BioPHP\Domain\Cloning\Exception\InvalidFeatureCoordinatesException;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\FeatureType;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\PlasmidFeature;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\Strand;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PlasmidFeatureTest extends TestCase
{
    public function testExposesAllItsProperties()
    {
        $oFeature = new PlasmidFeature(
            "AmpR",
            FeatureType::MARKER,
            3,
            10,
            Strand::FORWARD,
            "#FF00AA",
            "Ampicillin resistance",
            "ext-42"
        );

        $this->assertEquals("AmpR", $oFeature->getName());
        $this->assertEquals(FeatureType::MARKER, $oFeature->getType());
        $this->assertEquals(3, $oFeature->getStart());
        $this->assertEquals(10, $oFeature->getEnd());
        $this->assertEquals(Strand::FORWARD, $oFeature->getStrand());
        $this->assertEquals("#FF00AA", $oFeature->getColor());
        $this->assertEquals("Ampicillin resistance", $oFeature->getNote());
        $this->assertEquals("ext-42", $oFeature->getExternalId());
    }

    public function testDefaultsToNoStrandAndNoOptionalFields()
    {
        $oFeature = new PlasmidFeature("ori", FeatureType::ORIGIN_OF_REPLICATION, 1, 5);

        $this->assertEquals(Strand::NONE, $oFeature->getStrand());
        $this->assertNull($oFeature->getColor());
        $this->assertNull($oFeature->getNote());
        $this->assertNull($oFeature->getExternalId());
    }

    public function testRejectsAnEmptyName()
    {
        $this->expectException(\InvalidArgumentException::class);

        new PlasmidFeature("  ", FeatureType::CDS, 1, 5);
    }

    public function testRejectsAnInvalidType()
    {
        $this->expectException(\InvalidArgumentException::class);

        new PlasmidFeature("X", "NOT_A_TYPE", 1, 5);
    }

    public function testRejectsAnInvalidStrand()
    {
        $this->expectException(\InvalidArgumentException::class);

        new PlasmidFeature("X", FeatureType::CDS, 1, 5, "SIDEWAYS");
    }

    public function testRejectsAStartBelowOne()
    {
        $this->expectException(InvalidFeatureCoordinatesException::class);

        new PlasmidFeature("X", FeatureType::CDS, 0, 5);
    }

    public function testRejectsAnEndBelowOne()
    {
        $this->expectException(InvalidFeatureCoordinatesException::class);

        new PlasmidFeature("X", FeatureType::CDS, 1, 0);
    }

    public function testAcceptsAStrictSixDigitHexColor()
    {
        $oFeature = new PlasmidFeature("X", FeatureType::CDS, 1, 5, Strand::NONE, "#1a2B3c");

        $this->assertEquals("#1a2B3c", $oFeature->getColor());
    }

    #[DataProvider("invalidColorProvider")]
    public function testRejectsAnyColorNotInStrictHexNotation(string $sColor)
    {
        $this->expectException(\InvalidArgumentException::class);

        new PlasmidFeature("X", FeatureType::CDS, 1, 5, Strand::NONE, $sColor);
    }

    public static function invalidColorProvider(): array
    {
        return [
            "named color" => ["red"],
            "three-digit shorthand" => ["#fff"],
            "missing hash" => ["1A2B3C"],
            "too long" => ["#1A2B3C4D"],
        ];
    }

    public function testDoesNotCrossTheOriginWhenStartIsAtOrBeforeEnd()
    {
        $oFeature = new PlasmidFeature("X", FeatureType::CDS, 3, 10);

        $this->assertFalse($oFeature->crossesOrigin());
    }

    public function testCrossesTheOriginWhenStartIsAfterEnd()
    {
        $oFeature = new PlasmidFeature("X", FeatureType::CDS, 18, 4);

        $this->assertTrue($oFeature->crossesOrigin());
    }

    public function testComputesTheLengthOfAFeatureThatDoesNotCrossTheOrigin()
    {
        $oFeature = new PlasmidFeature("X", FeatureType::CDS, 3, 10);

        $this->assertEquals(8, $oFeature->getLength(20));
    }

    public function testComputesTheLengthOfAFeatureThatCrossesTheOrigin()
    {
        // 18..20 (3 symbols) + 1..4 (4 symbols) = 7, on a 20 bp plasmid.
        $oFeature = new PlasmidFeature("X", FeatureType::CDS, 18, 4);

        $this->assertEquals(7, $oFeature->getLength(20));
    }

    public function testALengthOneFeatureAtEachEdgeIsNeverZeroLength()
    {
        $oAtOrigin = new PlasmidFeature("X", FeatureType::CDS, 1, 1);
        $oAtEnd = new PlasmidFeature("Y", FeatureType::CDS, 20, 20);

        $this->assertEquals(1, $oAtOrigin->getLength(20));
        $this->assertEquals(1, $oAtEnd->getLength(20));
    }
}
