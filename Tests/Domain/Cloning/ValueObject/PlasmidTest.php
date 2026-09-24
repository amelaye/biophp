<?php
namespace Tests\Domain\Cloning\ValueObject;

use Amelaye\BioPHP\Domain\Cloning\Exception\InvalidFeatureCoordinatesException;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\FeatureType;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\Plasmid;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\PlasmidFeature;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\Strand;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\CircularDnaSequence;
use PHPUnit\Framework\TestCase;

class PlasmidTest extends TestCase
{
    // Indices (0-based): 0:A 1:C 2:G 3:T 4:G 5:G 6:C 7:T 8:A 9:A 10:C 11:C 12:T 13:G 14:A
    //                     15:A 16:C 17:G 18:T 19:T
    private const SEQUENCE = "ACGTGGCTAACCTGAACGTT";

    private function makeSequence(): CircularDnaSequence
    {
        return new CircularDnaSequence(self::SEQUENCE);
    }

    public function testExposesNameSequenceLengthAndOptionalFields()
    {
        $oPlasmid = new Plasmid(
            "pUC19-like",
            $this->makeSequence(),
            [],
            "A demo plasmid",
            "ext-1",
            ["source" => "unit test", "tags" => ["demo", "circular"]]
        );

        $this->assertEquals("pUC19-like", $oPlasmid->getName());
        $this->assertEquals(self::SEQUENCE, $oPlasmid->getSequence()->getValue());
        $this->assertEquals(20, $oPlasmid->getLength());
        $this->assertEquals("A demo plasmid", $oPlasmid->getDescription());
        $this->assertEquals("ext-1", $oPlasmid->getExternalId());
        $this->assertEquals(["source" => "unit test", "tags" => ["demo", "circular"]], $oPlasmid->getMetadata());
        $this->assertEquals([], $oPlasmid->getFeatures());
    }

    public function testRejectsAnEmptyName()
    {
        $this->expectException(\InvalidArgumentException::class);

        new Plasmid("  ", $this->makeSequence());
    }

    public function testRejectsAFeaturesArrayContainingSomethingElse()
    {
        $this->expectException(\InvalidArgumentException::class);

        new Plasmid("p1", $this->makeSequence(), ["not a feature"]);
    }

    public function testRejectsNonSerializableMetadata()
    {
        $this->expectException(\InvalidArgumentException::class);

        new Plasmid("p1", $this->makeSequence(), [], null, null, ["bad" => new \stdClass()]);
    }

    public function testAcceptsAFeatureAtCoordinateOneAndAtTheMaximalLength()
    {
        $oAtOrigin = new PlasmidFeature("start", FeatureType::MISC_FEATURE, 1, 1);
        $oFullLength = new PlasmidFeature("whole", FeatureType::MISC_FEATURE, 1, 20);

        $oPlasmid = new Plasmid("p1", $this->makeSequence(), [$oAtOrigin, $oFullLength]);

        $this->assertCount(2, $oPlasmid->getFeatures());
        $this->assertEquals(self::SEQUENCE, $oPlasmid->extractFeatureSequence($oFullLength)->getValue());
    }

    public function testRejectsAFeatureStartBeyondTheSequenceLength()
    {
        $this->expectException(InvalidFeatureCoordinatesException::class);

        new Plasmid("p1", $this->makeSequence(), [
            new PlasmidFeature("X", FeatureType::CDS, 21, 21),
        ]);
    }

    public function testRejectsAFeatureEndBeyondTheSequenceLengthOnWithFeature()
    {
        $oPlasmid = new Plasmid("p1", $this->makeSequence());

        $this->expectException(InvalidFeatureCoordinatesException::class);

        $oPlasmid->withFeature(new PlasmidFeature("X", FeatureType::CDS, 1, 21));
    }

    public function testWithFeatureReturnsANewImmutablePlasmid()
    {
        $oOriginal = new Plasmid("p1", $this->makeSequence());
        $oFeature = new PlasmidFeature("ori", FeatureType::ORIGIN_OF_REPLICATION, 1, 5);

        $oWithFeature = $oOriginal->withFeature($oFeature);

        $this->assertCount(0, $oOriginal->getFeatures());
        $this->assertCount(1, $oWithFeature->getFeatures());
        $this->assertSame($oFeature, $oWithFeature->getFeatures()[0]);
    }

    public function testAllowsOverlappingFeatures()
    {
        $oFirst = new PlasmidFeature("siteA", FeatureType::RESTRICTION_SITE, 3, 12);
        $oSecond = new PlasmidFeature("siteB", FeatureType::RESTRICTION_SITE, 8, 15);

        $oPlasmid = new Plasmid("p1", $this->makeSequence(), [$oFirst, $oSecond]);

        $this->assertCount(2, $oPlasmid->getFeatures());
    }

    public function testFiltersFeaturesByType()
    {
        $oCds = new PlasmidFeature("geneA", FeatureType::CDS, 1, 5);
        $oPromoter = new PlasmidFeature("promA", FeatureType::PROMOTER, 6, 8);
        $oCds2 = new PlasmidFeature("geneB", FeatureType::CDS, 9, 12);

        $oPlasmid = new Plasmid("p1", $this->makeSequence(), [$oCds, $oPromoter, $oCds2]);

        $aCdsFeatures = $oPlasmid->getFeaturesByType(FeatureType::CDS);

        $this->assertCount(2, $aCdsFeatures);
        $this->assertSame($oCds, $aCdsFeatures[0]);
        $this->assertSame($oCds2, $aCdsFeatures[1]);
        $this->assertCount(0, $oPlasmid->getFeaturesByType(FeatureType::TERMINATOR));
    }

    public function testDetectsDuplicateFeatureNamesWithoutForbiddingThem()
    {
        $oUnique = new Plasmid("p1", $this->makeSequence(), [
            new PlasmidFeature("geneA", FeatureType::CDS, 1, 5),
            new PlasmidFeature("geneB", FeatureType::CDS, 6, 10),
        ]);
        $oDuplicated = new Plasmid("p1", $this->makeSequence(), [
            new PlasmidFeature("geneA", FeatureType::CDS, 1, 5),
            new PlasmidFeature("geneA", FeatureType::CDS, 6, 10),
        ]);

        $this->assertFalse($oUnique->hasDuplicateFeatureNames());
        $this->assertTrue($oDuplicated->hasDuplicateFeatureNames());
        $this->assertCount(2, $oDuplicated->getFeatures());
    }

    public function testExtractsTheForwardStrandSequenceOfAFeature()
    {
        $oFeature = new PlasmidFeature("geneA", FeatureType::CDS, 3, 10, Strand::FORWARD);
        $oPlasmid = new Plasmid("p1", $this->makeSequence(), [$oFeature]);

        $this->assertEquals("GTGGCTAA", $oPlasmid->extractFeatureSequence($oFeature)->getValue());
    }

    public function testExtractsTheReverseComplementForAReverseStrandFeature()
    {
        $oFeature = new PlasmidFeature("geneA", FeatureType::CDS, 3, 10, Strand::REVERSE);
        $oPlasmid = new Plasmid("p1", $this->makeSequence(), [$oFeature]);

        $this->assertEquals("TTAGCCAC", $oPlasmid->extractFeatureSequence($oFeature)->getValue());
    }

    public function testExtractsAFeatureThatCrossesTheOrigin()
    {
        $oFeature = new PlasmidFeature("crossOri", FeatureType::MISC_FEATURE, 18, 4, Strand::FORWARD);
        $oPlasmid = new Plasmid("p1", $this->makeSequence(), [$oFeature]);

        $this->assertEquals("GTTACGT", $oPlasmid->extractFeatureSequence($oFeature)->getValue());
    }

    public function testRotatingToOriginMovesTheSequenceAndEveryFeatureCoordinate()
    {
        $oFeature = new PlasmidFeature("geneA", FeatureType::CDS, 3, 10, Strand::FORWARD);
        $oPlasmid = new Plasmid("p1", $this->makeSequence(), [$oFeature]);

        $oRotated = $oPlasmid->rotateToOrigin(5);

        $this->assertEquals("GGCTAACCTGAACGTTACGT", $oRotated->getSequence()->getValue());

        $oRotatedFeature = $oRotated->getFeatures()[0];
        $this->assertEquals(19, $oRotatedFeature->getStart());
        $this->assertEquals(6, $oRotatedFeature->getEnd());
        $this->assertTrue($oRotatedFeature->crossesOrigin());

        // Same biological fragment before and after rotation.
        $this->assertEquals(
            $oPlasmid->extractFeatureSequence($oFeature)->getValue(),
            $oRotated->extractFeatureSequence($oRotatedFeature)->getValue()
        );
        $this->assertEquals("GTGGCTAA", $oRotated->extractFeatureSequence($oRotatedFeature)->getValue());
    }

    public function testRotatingPreservesEveryFeatureName()
    {
        $oPlasmid = new Plasmid("p1", $this->makeSequence(), [
            new PlasmidFeature("geneA", FeatureType::CDS, 1, 5),
            new PlasmidFeature("promA", FeatureType::PROMOTER, 6, 8),
        ]);

        $oRotated = $oPlasmid->rotateToOrigin(3);

        $this->assertEquals(
            ["geneA", "promA"],
            array_map(fn ($oFeature) => $oFeature->getName(), $oRotated->getFeatures())
        );
    }
}
