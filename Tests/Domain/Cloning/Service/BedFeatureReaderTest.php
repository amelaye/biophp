<?php
namespace Tests\Domain\Cloning\Service;

use Amelaye\BioPHP\Domain\Cloning\Service\BedFeatureReader;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\FeatureType;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\Strand;
use PHPUnit\Framework\TestCase;

class BedFeatureReaderTest extends TestCase
{
    private $reader;

    public function setUp(): void
    {
        $this->reader = new BedFeatureReader();
    }

    public function testSkipsBlankLinesCommentsTrackAndBrowserLines()
    {
        $oResult = $this->reader->read([
            "\n",
            "# just a comment\n",
            "track name=test description=\"a track\"\n",
            "browser position chr1:1-100\n",
        ]);

        $this->assertCount(0, $oResult->getFeatures());
        $this->assertCount(0, $oResult->getWarnings());
    }

    /**
     * BED is zero-based, half-open : [10,20) covers the 10 bases 10..19 (0-based), i.e. bases
     * 11..20 in PlasmidFeature's 1-based inclusive convention - hand-converted :
     * start = chromStart + 1 = 11, end = chromEnd = 20.
     */
    public function testMapsAMinimalThreeColumnLineConvertingCoordinatesAndFallingBackOnName()
    {
        $oResult = $this->reader->read([
            "chr1\t10\t20\n",
        ]);

        $this->assertCount(1, $oResult->getFeatures());
        $oFeature = $oResult->getFeatures()[0];
        $this->assertEquals("chr1:10-20", $oFeature->getName());
        $this->assertEquals(FeatureType::MISC_FEATURE, $oFeature->getType());
        $this->assertEquals(11, $oFeature->getStart());
        $this->assertEquals(20, $oFeature->getEnd());
        $this->assertEquals(Strand::NONE, $oFeature->getStrand());
        $this->assertEquals("chr1", $oFeature->getMetadata()["bedChrom"]);
    }

    public function testMapsASixColumnLineWithNameScoreAndForwardStrand()
    {
        $oResult = $this->reader->read([
            "chr1\t0\t100\tpromoter1\t500\t+\n",
        ]);

        $oFeature = $oResult->getFeatures()[0];
        $this->assertEquals("promoter1", $oFeature->getName());
        $this->assertEquals(1, $oFeature->getStart());
        $this->assertEquals(100, $oFeature->getEnd());
        $this->assertEquals(Strand::FORWARD, $oFeature->getStrand());
        $this->assertEquals("500", $oFeature->getMetadata()["bedScore"]);
    }

    public function testMapsTheReverseStrandSymbol()
    {
        $oResult = $this->reader->read([
            "chr1\t0\t10\tgeneA\t.\t-\n",
        ]);

        $this->assertEquals(Strand::REVERSE, $oResult->getFeatures()[0]->getStrand());
    }

    /**
     * Columns beyond strand (thickStart, thickEnd, itemRgb, block structure - "BED12") describe
     * sub-feature structure this reader does not reconstruct ; they must be tolerated, not rejected.
     */
    public function testExtraColumnsBeyondStrandAreIgnoredWithoutError()
    {
        $oResult = $this->reader->read([
            "chr1\t0\t10\tgeneA\t0\t+\t0\t10\t0\t1\t10\t0\n",
        ]);

        $this->assertCount(1, $oResult->getFeatures());
        $this->assertCount(0, $oResult->getWarnings());
    }

    public function testAMalformedLineWithTooFewColumnsIsSkippedAndWarnedAboutRatherThanCrashing()
    {
        $oResult = $this->reader->read([
            "chr1\t10\n",
        ]);

        $this->assertCount(0, $oResult->getFeatures());
        $this->assertCount(1, $oResult->getWarnings());
        $this->assertStringContainsString("at least 3 tab-separated", $oResult->getWarnings()[0]);
    }

    public function testANonNumericCoordinateIsSkippedAndWarnedAboutRatherThanCrashing()
    {
        $oResult = $this->reader->read([
            "chr1\tx\t20\n",
        ]);

        $this->assertCount(0, $oResult->getFeatures());
        $this->assertCount(1, $oResult->getWarnings());
        $this->assertStringContainsString("non-numeric", $oResult->getWarnings()[0]);
    }

    /**
     * A zero-length BED interval (chromStart == chromEnd) has no meaningful 1-based equivalent ;
     * naively converting it would wrongly produce start > end, this codebase's convention for an
     * origin-crossing feature, so it must be rejected instead.
     */
    public function testAZeroLengthIntervalIsSkippedAndWarnedAboutRatherThanMisreadAsOriginCrossing()
    {
        $oResult = $this->reader->read([
            "chr1\t10\t10\n",
        ]);

        $this->assertCount(0, $oResult->getFeatures());
        $this->assertCount(1, $oResult->getWarnings());
        $this->assertStringContainsString("chromStart (10) must be strictly less than chromEnd (10)", $oResult->getWarnings()[0]);
    }

    public function testAFullFixtureMixingValidAndInvalidLinesProducesBothFeaturesAndWarnings()
    {
        $oResult = $this->reader->read([
            "track name=test\n",
            "chr1\t0\t100\tpromoter1\t500\t+\n",
            "chr1\t150\t250\tgeneA\t.\t-\n",
            "malformed line\n",
            "chr1\t10\t10\tzero\n",
        ]);

        $this->assertCount(2, $oResult->getFeatures());
        $this->assertCount(2, $oResult->getWarnings());
    }
}
