<?php
namespace Tests\Domain\Cloning\Service\Writer;

use Amelaye\BioPHP\Domain\Cloning\Service\Reader\BedFeatureReader;
use Amelaye\BioPHP\Domain\Cloning\Service\Writer\BedFeatureWriter;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\FeatureType;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\PlasmidFeature;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\Strand;
use PHPUnit\Framework\TestCase;

class BedFeatureWriterTest extends TestCase
{
    private $writer;

    public function setUp(): void
    {
        $this->writer = new BedFeatureWriter();
    }

    public function testAnEmptyFeatureListProducesAnEmptyDocument()
    {
        $this->assertEquals("", $this->writer->write("chr1", []));
    }

    /**
     * PlasmidFeature's 1-based inclusive 11..20 converts back to BED's zero-based half-open
     * [10, 20) : chromStart = start - 1 = 10, chromEnd = end = 20.
     */
    public function testConvertsOneBasedInclusiveCoordinatesBackToZeroBasedHalfOpen()
    {
        $oFeature = new PlasmidFeature("promoter1", FeatureType::PROMOTER, 11, 20, Strand::FORWARD);

        $sOutput = $this->writer->write("chr1", [$oFeature]);

        $this->assertEquals("chr1\t10\t20\tpromoter1\t0\t+\n", $sOutput);
    }

    public function testReusesTheOriginalBedScoreFromMetadataWhenPresent()
    {
        $oFeature = new PlasmidFeature(
            "geneA",
            FeatureType::MISC_FEATURE,
            1,
            10,
            Strand::REVERSE,
            null,
            null,
            null,
            ["bedScore" => "500"]
        );

        $sOutput = $this->writer->write("chr1", [$oFeature]);

        $this->assertEquals("chr1\t0\t10\tgeneA\t500\t-\n", $sOutput);
    }

    public function testRejectsAnOriginCrossingFeature()
    {
        $oFeature = new PlasmidFeature("crosser", FeatureType::MISC_FEATURE, 10, 5, Strand::NONE);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("crosses the origin");

        $this->writer->write("chr1", [$oFeature]);
    }

    /**
     * A full round trip through BedFeatureReader must reproduce name, coordinates and strand for
     * several features at once.
     */
    public function testARoundTripThroughTheReaderPreservesEveryFeature()
    {
        $aFeatures = [
            new PlasmidFeature("promoter1", FeatureType::PROMOTER, 1, 100, Strand::FORWARD),
            new PlasmidFeature("geneA", FeatureType::MISC_FEATURE, 151, 250, Strand::REVERSE),
        ];

        $sOutput = $this->writer->write("chr1", $aFeatures);
        $oResult = (new BedFeatureReader())->read(explode("\n", $sOutput));

        $this->assertCount(2, $oResult->getFeatures());
        $this->assertCount(0, $oResult->getWarnings());

        $this->assertEquals("promoter1", $oResult->getFeatures()[0]->getName());
        $this->assertEquals(1, $oResult->getFeatures()[0]->getStart());
        $this->assertEquals(100, $oResult->getFeatures()[0]->getEnd());
        $this->assertEquals(Strand::FORWARD, $oResult->getFeatures()[0]->getStrand());

        $this->assertEquals("geneA", $oResult->getFeatures()[1]->getName());
        $this->assertEquals(151, $oResult->getFeatures()[1]->getStart());
        $this->assertEquals(250, $oResult->getFeatures()[1]->getEnd());
        $this->assertEquals(Strand::REVERSE, $oResult->getFeatures()[1]->getStrand());
    }
}
