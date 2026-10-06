<?php
namespace Tests\Domain\Cloning\Service\Reader;

use Amelaye\BioPHP\Domain\Cloning\Service\Reader\GffFeatureReader;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\FeatureType;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\Strand;
use PHPUnit\Framework\TestCase;

class GffFeatureReaderTest extends TestCase
{
    private $reader;

    public function setUp(): void
    {
        $this->reader = new GffFeatureReader();
    }

    public function testSkipsBlankLinesAndCommentAndPragmaLines()
    {
        $oResult = $this->reader->read([
            "##gff-version 3\n",
            "\n",
            "# just a comment\n",
            "##sequence-region TESTPLAS 1 40\n",
        ]);

        $this->assertCount(0, $oResult->getFeatures());
        $this->assertCount(0, $oResult->getWarnings());
    }

    public function testMapsAPromoterOnTheForwardStrandUsingTheNameAttribute()
    {
        $oResult = $this->reader->read([
            "TESTPLAS\tmanual\tpromoter\t1\t20\t.\t+\t.\tID=prom1;Name=P_lac\n",
        ]);

        $this->assertCount(1, $oResult->getFeatures());
        $oFeature = $oResult->getFeatures()[0];
        $this->assertEquals("P_lac", $oFeature->getName());
        $this->assertEquals(FeatureType::PROMOTER, $oFeature->getType());
        $this->assertEquals(1, $oFeature->getStart());
        $this->assertEquals(20, $oFeature->getEnd());
        $this->assertEquals(Strand::FORWARD, $oFeature->getStrand());
        $this->assertEquals("promoter", $oFeature->getMetadata()["gffType"]);
    }

    public function testMapsACdsWithANoteOnTheReverseStrand()
    {
        $oResult = $this->reader->read([
            "TESTPLAS\tmanual\tCDS\t25\t45\t.\t-\t0\tID=cds1;Name=AmpR;Note=beta-lactamase\n",
        ]);

        $oFeature = $oResult->getFeatures()[0];
        $this->assertEquals("AmpR", $oFeature->getName());
        $this->assertEquals(FeatureType::CDS, $oFeature->getType());
        $this->assertEquals(Strand::REVERSE, $oFeature->getStrand());
        $this->assertEquals("beta-lactamase", $oFeature->getNote());
    }

    /**
     * "misc_feature" (and any other term this reader does not recognize, including GenBank-style keys
     * that are not standard Sequence Ontology terms) must fall back to MISC_FEATURE rather than being
     * guessed at, exactly like GenbankPlasmidMapper's own equivalent fallback.
     */
    public function testAnUnrecognizedTypeFallsBackToMiscFeatureAndNameFallsBackToId()
    {
        $oResult = $this->reader->read([
            "TESTPLAS\tmanual\tmisc_feature\t30\t38\t.\t+\t.\tID=site1;Note=test site\n",
        ]);

        $oFeature = $oResult->getFeatures()[0];
        $this->assertEquals("site1", $oFeature->getName());
        $this->assertEquals(FeatureType::MISC_FEATURE, $oFeature->getType());
    }

    public function testPercentEncodedAttributeValuesAreDecoded()
    {
        $oResult = $this->reader->read([
            "TESTPLAS\tmanual\tCDS\t1\t10\t.\t+\t0\tID=cds1;Note=beta-lactamase%20resistance\n",
        ]);

        $this->assertEquals("beta-lactamase resistance", $oResult->getFeatures()[0]->getNote());
    }

    public function testAMalformedLineWithTooFewColumnsIsSkippedAndWarnedAboutRatherThanCrashing()
    {
        $oResult = $this->reader->read([
            "TESTPLAS\tmanual\tpromoter\t1\t20\n",
        ]);

        $this->assertCount(0, $oResult->getFeatures());
        $this->assertCount(1, $oResult->getWarnings());
        $this->assertStringContainsString("9 tab-separated", $oResult->getWarnings()[0]);
    }

    /**
     * GFF3 never writes start > end, not even for an origin-crossing feature (that one is written as
     * end + landmark length) ; this is a real, expected input shape to reject cleanly, not a bug.
     */
    public function testAStartAfterEndIsSkippedAndWarnedAboutRatherThanCrashing()
    {
        $oResult = $this->reader->read([
            "TESTPLAS\tmanual\tCDS\t10\t5\t.\t+\t0\tID=bad1\n",
        ]);

        $this->assertCount(0, $oResult->getFeatures());
        $this->assertCount(1, $oResult->getWarnings());
        $this->assertStringContainsString("start (10) is after end (5)", $oResult->getWarnings()[0]);
    }

    public function testANonNumericCoordinateIsSkippedAndWarnedAboutRatherThanCrashing()
    {
        $oResult = $this->reader->read([
            "TESTPLAS\tmanual\trep_origin\t5\tx\t.\t+\t.\tID=bad2\n",
        ]);

        $this->assertCount(0, $oResult->getFeatures());
        $this->assertCount(1, $oResult->getWarnings());
        $this->assertStringContainsString("non-numeric", $oResult->getWarnings()[0]);
    }

    public function testStopsReadingAtTheFastaPragmaWithoutCrashing()
    {
        $oResult = $this->reader->read([
            "TESTPLAS\tmanual\tpromoter\t1\t20\t.\t+\t.\tID=prom1\n",
            "##FASTA\n",
            ">TESTPLAS some description\n",
            "ACGTACGTACGT\n",
        ]);

        $this->assertCount(1, $oResult->getFeatures());
        $this->assertCount(0, $oResult->getWarnings());
    }

    public function testAFullFixtureMixingValidAndInvalidLinesProducesBothFeaturesAndWarnings()
    {
        $oResult = $this->reader->read([
            "##gff-version 3\n",
            "TESTPLAS\tmanual\tpromoter\t1\t20\t.\t+\t.\tID=prom1;Name=P_lac\n",
            "TESTPLAS\tmanual\tCDS\t25\t45\t.\t+\t0\tID=cds1;Name=AmpR;Note=beta-lactamase\n",
            "malformed line with too few columns\n",
            "TESTPLAS\tmanual\tCDS\t10\t5\t.\t+\t0\tID=bad1\n",
        ]);

        $this->assertCount(2, $oResult->getFeatures());
        $this->assertCount(2, $oResult->getWarnings());
    }

    public function testKeepsTheCdsPhase()
    {
        $oResult = $this->reader->read(["TESTPLAS\t.\tCDS\t4\t30\t.\t-\t2\tID=cds1\n"]);

        $this->assertSame(2, $oResult->getFeatures()[0]->getPhase());
    }

    public function testAMissingCdsPhaseIsReadAsUnknown()
    {
        $oResult = $this->reader->read(["TESTPLAS\t.\tCDS\t4\t30\t.\t+\t.\tID=cds1\n"]);

        $this->assertCount(0, $oResult->getWarnings());
        $this->assertNull($oResult->getFeatures()[0]->getPhase());
    }

    public function testIgnoresThePhaseColumnOfANonCdsFeature()
    {
        $oResult = $this->reader->read(["TESTPLAS\t.\texon\t4\t30\t.\t+\t1\tID=ex1\n"]);

        $this->assertNull($oResult->getFeatures()[0]->getPhase());
    }

    public function testAnInvalidCdsPhaseIsSkippedAndWarnedAbout()
    {
        $oResult = $this->reader->read(["TESTPLAS\t.\tCDS\t4\t30\t.\t+\t3\tID=cds1\n"]);

        $this->assertCount(0, $oResult->getFeatures());
        $this->assertStringContainsString('invalid CDS phase "3"', $oResult->getWarnings()[0]);
    }

    /**
     * GFF3 specification, columns 4 and 5 : a feature crossing the origin of a circular landmark is
     * written with end = its real end + the landmark length ; on a 40 bp landmark, 35..45 is 35..5.
     */
    public function testFoldsAFeatureCrossingTheOriginOfACircularLandmarkBack()
    {
        $oResult = $this->reader->read([
            "TESTPLAS\t.\tCDS\t35\t45\t.\t+\t0\tID=cds1\n",
            "TESTPLAS\t.\tregion\t1\t40\t.\t.\t.\tID=TESTPLAS;Is_circular=true\n",
        ]);

        $this->assertCount(0, $oResult->getWarnings());
        $oFeature = $oResult->getFeatures()[0];
        $this->assertSame(35, $oFeature->getStart());
        $this->assertSame(5, $oFeature->getEnd());
        $this->assertTrue($oFeature->crossesOrigin());
        $this->assertSame(11, $oFeature->getLength(40));
    }

    public function testDoesNotFoldAFeatureOnALinearLandmark()
    {
        $oResult = $this->reader->read([
            "TESTPLAS\t.\tregion\t1\t40\t.\t.\t.\tID=TESTPLAS\n",
            "TESTPLAS\t.\tCDS\t35\t45\t.\t+\t0\tID=cds1\n",
        ]);

        $this->assertSame(45, $oResult->getFeatures()[1]->getEnd());
    }

    public function testAFeatureLongerThanTheCircularLandmarkIsSkippedAndWarnedAbout()
    {
        $oResult = $this->reader->read([
            "TESTPLAS\t.\tregion\t1\t40\t.\t.\t.\tID=TESTPLAS;Is_circular=true\n",
            "TESTPLAS\t.\tCDS\t3\t43\t.\t+\t0\tID=cds1\n",
        ]);

        $this->assertCount(1, $oResult->getFeatures());
        $this->assertStringContainsString("does not fit the circular landmark", $oResult->getWarnings()[0]);
    }
}
