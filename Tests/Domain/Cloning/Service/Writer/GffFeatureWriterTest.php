<?php
namespace Tests\Domain\Cloning\Service\Writer;

use Amelaye\BioPHP\Domain\Cloning\Service\Reader\GffFeatureReader;
use Amelaye\BioPHP\Domain\Cloning\Service\Writer\GffFeatureWriter;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\FeatureType;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\PlasmidFeature;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\Strand;
use PHPUnit\Framework\TestCase;

class GffFeatureWriterTest extends TestCase
{
    private $writer;

    public function setUp(): void
    {
        $this->writer = new GffFeatureWriter();
    }

    public function testWritesThePragmaLineEvenWithNoFeatures()
    {
        $this->assertEquals("##gff-version 3\n", $this->writer->write("TESTPLAS", []));
    }

    public function testWritesAPromoterOnTheForwardStrandUsingTheFeatureTypeFallbackMapping()
    {
        $oFeature = new PlasmidFeature("P_lac", FeatureType::PROMOTER, 1, 20, Strand::FORWARD);

        $sOutput = $this->writer->write("TESTPLAS", [$oFeature]);

        $this->assertEquals(
            "##gff-version 3\nTESTPLAS\t.\tpromoter\t1\t20\t.\t+\t.\tName=P_lac\n",
            $sOutput
        );
    }

    public function testReusesTheOriginalGffTypeFromMetadataWhenPresentInsteadOfTheFeatureTypeFallback()
    {
        $oFeature = new PlasmidFeature(
            "site1",
            FeatureType::MISC_FEATURE,
            30,
            38,
            Strand::NONE,
            null,
            null,
            null,
            ["gffType" => "misc_feature"]
        );

        $sOutput = $this->writer->write("TESTPLAS", [$oFeature]);

        $this->assertStringContainsString("\tmisc_feature\t", $sOutput);
    }

    /**
     * FeatureType::TAG has no dedicated Sequence Ontology term and no "gffType" metadata here, so it
     * must fall back to the honest generic term rather than a plausible-looking guess.
     */
    public function testAFeatureTypeWithNoSoEquivalentFallsBackToTheGenericTerm()
    {
        $oFeature = new PlasmidFeature("tag1", FeatureType::TAG, 1, 10, Strand::NONE);

        $sOutput = $this->writer->write("TESTPLAS", [$oFeature]);

        $this->assertStringContainsString("\tsequence_feature\t", $sOutput);
    }

    public function testRejectsAnOriginCrossingFeature()
    {
        $oFeature = new PlasmidFeature("crosser", FeatureType::MISC_FEATURE, 10, 5, Strand::NONE);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("crosses the origin");

        $this->writer->write("TESTPLAS", [$oFeature]);
    }

    /**
     * A Note containing GFF3's own reserved column-9 characters (";", "=", ",", "%") must survive a
     * write-then-read round trip unchanged, via percent-encoding on the way out and rawurldecode()
     * (GffFeatureReader's own decoding) on the way back in.
     */
    public function testANoteWithReservedCharactersRoundTripsThroughTheReader()
    {
        $sTrickyNote = 'has a semicolon; a comma, an equals=sign and a percent%sign';
        $oFeature = new PlasmidFeature(
            "cds1",
            FeatureType::CDS,
            1,
            10,
            Strand::FORWARD,
            null,
            $sTrickyNote
        );

        $sOutput = $this->writer->write("TESTPLAS", [$oFeature]);
        $oResult = (new GffFeatureReader())->read(explode("\n", $sOutput));

        $this->assertCount(1, $oResult->getFeatures());
        $this->assertEquals($sTrickyNote, $oResult->getFeatures()[0]->getNote());
    }

    /**
     * A full round trip through GffFeatureReader must reproduce name, type, coordinates and strand
     * for several features at once.
     */
    public function testARoundTripThroughTheReaderPreservesEveryFeature()
    {
        $aFeatures = [
            new PlasmidFeature("P_lac", FeatureType::PROMOTER, 1, 20, Strand::FORWARD),
            new PlasmidFeature("AmpR", FeatureType::CDS, 25, 45, Strand::REVERSE, null, "beta-lactamase"),
        ];

        $sOutput = $this->writer->write("TESTPLAS", $aFeatures);
        $oResult = (new GffFeatureReader())->read(explode("\n", $sOutput));

        $this->assertCount(2, $oResult->getFeatures());
        $this->assertCount(0, $oResult->getWarnings());

        $this->assertEquals("P_lac", $oResult->getFeatures()[0]->getName());
        $this->assertEquals(FeatureType::PROMOTER, $oResult->getFeatures()[0]->getType());
        $this->assertEquals(1, $oResult->getFeatures()[0]->getStart());
        $this->assertEquals(20, $oResult->getFeatures()[0]->getEnd());
        $this->assertEquals(Strand::FORWARD, $oResult->getFeatures()[0]->getStrand());

        $this->assertEquals("AmpR", $oResult->getFeatures()[1]->getName());
        $this->assertEquals(FeatureType::CDS, $oResult->getFeatures()[1]->getType());
        $this->assertEquals(Strand::REVERSE, $oResult->getFeatures()[1]->getStrand());
        $this->assertEquals("beta-lactamase", $oResult->getFeatures()[1]->getNote());
    }
}
