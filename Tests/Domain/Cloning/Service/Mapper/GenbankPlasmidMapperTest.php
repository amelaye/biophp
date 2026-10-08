<?php
namespace Tests\Domain\Cloning\Service\Mapper;

use Amelaye\BioPHP\Domain\Cloning\Service\Mapper\GenbankPlasmidMapper;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\FeatureType;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\Strand;
use Amelaye\BioPHP\Domain\Parser\Service\ParseGenbankManager;
use Amelaye\BioPHP\Domain\Sequence\Entity\Feature;
use Amelaye\BioPHP\Domain\Sequence\Entity\GbSequence;
use Amelaye\BioPHP\Domain\Sequence\Entity\Sequence;
use PHPUnit\Framework\TestCase;

class GenbankPlasmidMapperTest extends TestCase
{
    private $mapper;

    public function setUp(): void
    {
        $this->mapper = new GenbankPlasmidMapper();
    }

    /**
     * A small, self-contained circular GenBank record (LOCUS/FEATURES/ORIGIN), fed to the real,
     * unmodified ParseGenbankManager - not hand-built entities - to prove the mapper composes with
     * the actual parser output end to end. Column offsets were verified against
     * ParseGenbankManagerTest's own testGenbankComplementLocationIsMinusStrand() fixture style.
     */
    private function parseSmallCircularFixture(): ParseGenbankManager
    {
        $aLines = [
            "LOCUS       TESTPLAS                  40 bp    DNA     circular SYN 01-JAN-2026\n",
            "FEATURES             Location/Qualifiers\n",
            "     source          1..40\n",
            "                     /organism=\"synthetic construct\"\n",
            "     CDS             5..25\n",
            "                     /gene=\"AmpR\"\n",
            "                     /product=\"beta-lactamase\"\n",
            "     misc_feature    complement(30..38)\n",
            "                     /note=\"test site\"\n",
            "ORIGIN\n",
            "        1 acgtacgtac gtacgtacgt acgtacgtac gtacgtacgt\n",
            "//\n",
        ];

        $oParser = new ParseGenbankManager();
        $oParser->parseDataFile($aLines);

        return $oParser;
    }

    public function testMapsASmallCircularGenbankFixtureEndToEnd()
    {
        $oParser = $this->parseSmallCircularFixture();

        $oResult = $this->mapper->map($oParser->getSequence(), $oParser->getGbSequence(), $oParser->getFeatures());

        $oPlasmid = $oResult->getPlasmid();
        $this->assertEquals("TESTPLAS", $oPlasmid->getName());
        $this->assertEquals(40, $oPlasmid->getLength());
        $this->assertEquals("ACGTACGTACGTACGTACGTACGTACGTACGTACGTACGT", $oPlasmid->getSequence()->getValue());
        $this->assertEquals([], $oResult->getWarnings());

        // "source" describes the whole molecule, not a plasmid annotation: it must not surface.
        $this->assertCount(2, $oPlasmid->getFeatures());
    }

    public function testMapsPromoterCdsMarkerAndOriginFeatureTypes()
    {
        // GenBank has no dedicated "marker" key: a selectable marker like AmpR is, structurally,
        // just a CDS - indistinguishable at the key level from any other coding sequence. The model
        // must not guess MARKER from the name (PlasmidFeature's own rule), so this proves a
        // marker-role CDS still maps to FeatureType::CDS, alongside a promoter and an origin.
        $oPromoter = new Feature();
        $oPromoter->setFtKey("promoter");
        $oPromoter->setFtFrom(1);
        $oPromoter->setFtTo(20);
        $oPromoter->setStrand("+");
        $oPromoter->setFtQual("label");
        $oPromoter->setFtValue("P_lac");

        $oMarkerCds = new Feature();
        $oMarkerCds->setFtKey("CDS");
        $oMarkerCds->setFtFrom(25);
        $oMarkerCds->setFtTo(45);
        $oMarkerCds->setStrand("+");
        $oMarkerCds->setFtQual("gene");
        $oMarkerCds->setFtValue("AmpR");

        $oOrigin = new Feature();
        $oOrigin->setFtKey("rep_origin");
        $oOrigin->setFtFrom(50);
        $oOrigin->setFtTo(70);
        $oOrigin->setStrand("+");
        $oOrigin->setFtQual("note");
        $oOrigin->setFtValue("ColE1 origin");

        $oResult = $this->mapper->map(
            $this->makeSequence(80),
            $this->makeGbSequence("CIRCULAR"),
            [$oPromoter, $oMarkerCds, $oOrigin]
        );

        $aFeatures = $oResult->getPlasmid()->getFeatures();
        $this->assertCount(3, $aFeatures);

        $this->assertEquals("P_lac", $aFeatures[0]->getName());
        $this->assertEquals(FeatureType::PROMOTER, $aFeatures[0]->getType());

        $this->assertEquals("AmpR", $aFeatures[1]->getName());
        $this->assertEquals(FeatureType::CDS, $aFeatures[1]->getType());

        $this->assertEquals("rep_origin", $aFeatures[2]->getName());
        $this->assertEquals(FeatureType::ORIGIN_OF_REPLICATION, $aFeatures[2]->getType());
        $this->assertEquals("ColE1 origin", $aFeatures[2]->getNote());
    }

    public function testPreservesTheReverseStrandOfAComplementFeature()
    {
        $oParser = $this->parseSmallCircularFixture();

        $oResult = $this->mapper->map($oParser->getSequence(), $oParser->getGbSequence(), $oParser->getFeatures());

        $aMiscFeatures = $oResult->getPlasmid()->getFeaturesByType(FeatureType::MISC_FEATURE);
        $this->assertCount(1, $aMiscFeatures);
        $this->assertEquals(Strand::REVERSE, $aMiscFeatures[0]->getStrand());
        $this->assertEquals(30, $aMiscFeatures[0]->getStart());
        $this->assertEquals(38, $aMiscFeatures[0]->getEnd());
    }

    public function testPreservesAFeatureCrossingTheOrigin()
    {
        // A single-segment complement(75..5) wrap already comes through from the parser as
        // from=75, to=5 (from > to), matching PlasmidFeature's own origin-crossing convention.
        $oWrapping = new Feature();
        $oWrapping->setFtKey("misc_feature");
        $oWrapping->setFtFrom(75);
        $oWrapping->setFtTo(5);
        $oWrapping->setStrand("-");
        $oWrapping->setFtQual("note");
        $oWrapping->setFtValue("wraps the origin");

        $oResult = $this->mapper->map($this->makeSequence(80), $this->makeGbSequence("CIRCULAR"), [$oWrapping]);

        $oFeature = $oResult->getPlasmid()->getFeatures()[0];
        $this->assertEquals(75, $oFeature->getStart());
        $this->assertEquals(5, $oFeature->getEnd());
        $this->assertTrue($oFeature->crossesOrigin());
        $this->assertEquals(Strand::REVERSE, $oFeature->getStrand());
    }

    public function testRejectsALinearRecord()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("LINEAR");

        $this->mapper->map($this->makeSequence(40), $this->makeGbSequence("LINEAR"), []);
    }

    /**
     * ParseDbAbstractManager::parseLocationBounds() (shared by the GenBank and EMBL parsers)
     * discards the raw location text, so an origin-crossing join() cannot be told apart from a
     * genuine simple feature once parsed - a known, documented limitation (see
     * GenbankPlasmidMapper's own class docblock) that is out of scope to fix here. What the mapper
     * CAN and does detect honestly is a feature the parser itself could not resolve coordinates
     * for; that case must produce a warning and be skipped, never silently dropped or crashed on.
     */
    public function testWarnsAboutAndSkipsAFeatureWithUnrepresentableCoordinatesInsteadOfCrashing()
    {
        $oDegenerate = new Feature();
        $oDegenerate->setFtKey("misc_feature");
        $oDegenerate->setFtFrom(null);
        $oDegenerate->setFtTo(10);
        $oDegenerate->setStrand("+");
        $oDegenerate->setFtQual("note");
        $oDegenerate->setFtValue("unparsed location");

        $oResult = $this->mapper->map($this->makeSequence(40), $this->makeGbSequence("CIRCULAR"), [$oDegenerate]);

        $this->assertCount(0, $oResult->getPlasmid()->getFeatures());
        $this->assertCount(1, $oResult->getWarnings());
        $this->assertStringContainsString("misc_feature", $oResult->getWarnings()[0]);
    }

    public function testWarnsAboutAndSkipsAFeatureBeyondThePlasmidLengthInsteadOfCrashing()
    {
        $oTooFar = new Feature();
        $oTooFar->setFtKey("misc_feature");
        $oTooFar->setFtFrom(1);
        $oTooFar->setFtTo(999);
        $oTooFar->setStrand("+");
        $oTooFar->setFtQual("note");
        $oTooFar->setFtValue("out of range");

        $oResult = $this->mapper->map($this->makeSequence(40), $this->makeGbSequence("CIRCULAR"), [$oTooFar]);

        $this->assertCount(0, $oResult->getPlasmid()->getFeatures());
        $this->assertCount(1, $oResult->getWarnings());
    }

    public function testPreservesTheOriginalGenbankKeyInMetadata()
    {
        $oFeature = new Feature();
        $oFeature->setFtKey("rep_origin");
        $oFeature->setFtFrom(1);
        $oFeature->setFtTo(10);
        $oFeature->setStrand("+");
        $oFeature->setFtQual("note");
        $oFeature->setFtValue("ori");

        $oResult = $this->mapper->map($this->makeSequence(40), $this->makeGbSequence("CIRCULAR"), [$oFeature]);

        $this->assertEquals(
            "rep_origin",
            $oResult->getPlasmid()->getFeatures()[0]->getMetadata()["genbankKey"]
        );
    }

    private function makeSequence(int $iLength): Sequence
    {
        $oSequence = new Sequence();
        $oSequence->setPrimAcc("TESTPLAS");
        $oSequence->setSequence(str_repeat("ACGT", (int) ceil($iLength / 4)));

        return $oSequence;
    }

    private function makeGbSequence(string $sTopology): GbSequence
    {
        $oGbSequence = new GbSequence();
        $oGbSequence->setPrimAcc("TESTPLAS");
        $oGbSequence->setTopology($sTopology);

        return $oGbSequence;
    }

    private function mapLines(array $aLines)
    {
        $oParser = new ParseGenbankManager();
        $oParser->parseDataFile($aLines);

        return $this->mapper->map($oParser->getSequence(), $oParser->getGbSequence(), $oParser->getFeatures());
    }

    private function makeFeature(string $sKey, int $iFrom, int $iTo, string $sQual, string $sValue): Feature
    {
        $oFeature = new Feature();
        $oFeature->setFtKey($sKey);
        $oFeature->setFtFrom($iFrom);
        $oFeature->setFtTo($iTo);
        $oFeature->setStrand("+");
        $oFeature->setFtQual($sQual);
        $oFeature->setFtValue($sValue);

        return $oFeature;
    }

    /**
     * INSDC : oriT is the "origin of transfer; region of a DNA molecule where transfer is initiated
     * during the process of conjugation or mobilization", not an origin of replication.
     */
    public function testAnOriTIsNotMistakenForAnOriginOfReplication()
    {
        $oResult = $this->mapper->map($this->makeSequence(40), $this->makeGbSequence("CIRCULAR"), [
            $this->makeFeature("oriT", 5, 25, "note", "transfer origin"),
            $this->makeFeature("rep_origin", 28, 38, "note", "ColE1"),
        ]);

        $aFeatures = $oResult->getPlasmid()->getFeatures();
        $this->assertSame(FeatureType::MISC_FEATURE, $aFeatures[0]->getType());
        $this->assertSame("oriT", $aFeatures[0]->getMetadata()["genbankKey"]);
        $this->assertSame(FeatureType::ORIGIN_OF_REPLICATION, $aFeatures[1]->getType());
    }

    public function testAnInvalidCodonStartIsSkippedAndWarnedAbout()
    {
        $oResult = $this->mapper->map($this->makeSequence(40), $this->makeGbSequence("CIRCULAR"), [
            $this->makeFeature("CDS", 5, 25, "codon_start", "4"),
        ]);

        $this->assertCount(0, $oResult->getPlasmid()->getFeatures());
        $this->assertStringContainsString('invalid /codon_start "4"', $oResult->getWarnings()[0]);
    }

    public function testConvertsCodonStartIntoThePhase()
    {
        $oResult = $this->mapLines([
            "LOCUS       TESTPLAS                  40 bp    DNA     circular SYN 01-JAN-2026\n",
            "FEATURES             Location/Qualifiers\n",
            "     CDS             complement(5..25)\n",
            "                     /gene=\"geneA\"\n",
            "                     /codon_start=2\n",
            "     CDS             28..38\n",
            "                     /gene=\"geneB\"\n",
            "ORIGIN\n",
            "        1 acgtacgtac gtacgtacgt acgtacgtac gtacgtacgt\n",
            "//\n",
        ]);

        $aFeatures = $oResult->getPlasmid()->getFeatures();
        $this->assertSame(1, $aFeatures[0]->getPhase());
        $this->assertNull($aFeatures[1]->getPhase());
    }

    /**
     * End to end through the real parser : the plasmid annotations it used to drop (rep_origin,
     * oriT, promoter, terminator, the "regulatory" + /regulatory_class form INSDC has required since
     * 15-DEC-2014) all reach the plasmid with the right type, and a qualifier-less feature is named
     * after its key.
     */
    public function testMapsTypicalPlasmidAnnotationsReadByTheRealParser()
    {
        $oResult = $this->mapLines([
            "LOCUS       TESTPLAS                  40 bp    DNA     circular SYN 01-JAN-2026\n",
            "FEATURES             Location/Qualifiers\n",
            "     regulatory      1..4\n",
            "                     /regulatory_class=\"promoter\"\n",
            "                     /label=\"lac promoter\"\n",
            "     promoter        5..8\n",
            "                     /label=\"T7 promoter\"\n",
            "     regulatory      9..12\n",
            "                     /regulatory_class=\"terminator\"\n",
            "     terminator      13..16\n",
            "     regulatory      17..20\n",
            "                     /regulatory_class=\"ribosome_binding_site\"\n",
            "     rep_origin      21..30\n",
            "     oriT            31..35\n",
            "ORIGIN\n",
            "        1 acgtacgtac gtacgtacgt acgtacgtac gtacgtacgt\n",
            "//\n",
        ]);

        $this->assertSame([], $oResult->getWarnings());
        $aActual = array_map(
            fn($o) => [$o->getName(), $o->getType()],
            $oResult->getPlasmid()->getFeatures()
        );
        $this->assertSame([
            ["lac promoter", FeatureType::PROMOTER],
            ["T7 promoter", FeatureType::PROMOTER],
            ["regulatory", FeatureType::TERMINATOR],
            ["terminator", FeatureType::TERMINATOR],
            ["regulatory", FeatureType::MISC_FEATURE],
            ["rep_origin", FeatureType::ORIGIN_OF_REPLICATION],
            ["oriT", FeatureType::MISC_FEATURE],
        ], $aActual);
        $this->assertSame("ribosome_binding_site", $oResult->getPlasmid()->getFeatures()[4]->getMetadata()["regulatoryClass"]);
    }

    /**
     * A join() crossing the origin of a circular record used to collapse into its lowest start and
     * highest end (1..40 : the whole plasmid). Its segments, read in the order they are transcribed,
     * give 35..5, crossing the origin, on either strand.
     */
    public function testAJoinCrossingTheOriginOfAGenbankRecordCrossesItInThePlasmid()
    {
        $aLines = [
            "LOCUS       TESTPLAS                  40 bp    DNA     circular SYN 01-JAN-2026\n",
            "FEATURES             Location/Qualifiers\n",
            "     CDS             join(35..40,1..5)\n",
            "                     /gene=\"wrap\"\n",
            "     misc_feature    join(complement(1..5),complement(35..40))\n",
            "                     /note=\"reverse wrap\"\n",
            "ORIGIN\n",
            "        1 acgtacgtac gtacgtacgt acgtacgtac gtacgtacgt\n",
            "//\n",
        ];
        $oParser = new ParseGenbankManager();
        $oParser->parseDataFile($aLines);

        $oResult = $this->mapper->map($oParser->getSequence(), $oParser->getGbSequence(), $oParser->getFeatures());
        $aFeatures = $oResult->getPlasmid()->getFeatures();

        $this->assertCount(2, $aFeatures);
        foreach ($aFeatures as $oFeature) {
            $this->assertEquals([35, 5], [$oFeature->getStart(), $oFeature->getEnd()]);
            $this->assertTrue($oFeature->crossesOrigin());
        }
        $this->assertEquals(Strand::FORWARD, $aFeatures[0]->getStrand());
        $this->assertEquals(Strand::REVERSE, $aFeatures[1]->getStrand());
        // Two segments meeting at the origin are one span : nothing was lost.
        $this->assertSame([], $oResult->getWarnings());
    }

    /**
     * Two features at the same location were merged, the second label dropped ; a spliced CDS
     * was flattened into one span, its intron included, without a word.
     */
    public function testKeepsFeaturesSharingALocationApartAndWarnsAboutASplicedOne()
    {
        $oParser = new ParseGenbankManager();
        $oParser->parseDataFile([
            "LOCUS       TESTPLAS                  40 bp    DNA     circular SYN 01-JAN-2026\n",
            "FEATURES             Location/Qualifiers\n",
            "     misc_feature    1..10\n",
            "                     /label=\"first\"\n",
            "     misc_feature    1..10\n",
            "                     /label=\"second\"\n",
            "     CDS             join(12..20,25..33)\n",
            "                     /gene=\"spliced\"\n",
            "ORIGIN\n",
            "        1 acgtacgtac gtacgtacgt acgtacgtac gtacgtacgt\n",
            "//\n",
        ]);

        $oResult = $this->mapper->map($oParser->getSequence(), $oParser->getGbSequence(), $oParser->getFeatures());

        $this->assertSame(["first", "second", "spliced"], array_map(fn($o) => $o->getName(), $oResult->getPlasmid()->getFeatures()));
        $this->assertCount(1, $oResult->getWarnings());
        $this->assertStringContainsString("join(12..20,25..33)", $oResult->getWarnings()[0]);
    }
}
