<?php
namespace Tests\Domain\Cloning\Service\Writer;

use Amelaye\BioPHP\Domain\Cloning\Aggregate\Plasmid;
use Amelaye\BioPHP\Domain\Cloning\Service\Mapper\GenbankPlasmidMapper;
use Amelaye\BioPHP\Domain\Cloning\Service\Writer\GenbankWriter;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\FeatureType;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\PlasmidFeature;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\Strand;
use Amelaye\BioPHP\Domain\Parser\Service\ParseGenbankManager;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\CircularDnaSequence;
use PHPUnit\Framework\TestCase;

class GenbankWriterTest extends TestCase
{
    private $writer;

    public function setUp(): void
    {
        $this->writer = new GenbankWriter();
    }

    /**
     * Every line hand-derived against the format described in GenbankWriter's own docblock :
     * - LOCUS : NCBI's fixed columns - name padded to 16 from column 13, length right-justified in
     *   columns 30-40, "bp" at 42, "DNA" at 48, "circular" at 56
     * - source : 5 spaces + "source" padded to 16 + "1..10"
     * - CDS : 5 spaces + "CDS" padded to 16 + "1..6" (FeatureType::CDS's fallback GenBank key,
     *   no "genbankKey" metadata here)
     * - each qualifier : 21 spaces + "/key=\"value\""
     * - ORIGIN sequence : "        1 " (9-column right-justified position + space) + the 10-base
     *   sequence lowercased, in a single group since 10 bases fit in one 10-base group
     */
    public function testWritesACompleteMinimalRecord()
    {
        $oPlasmid = new Plasmid(
            "pTest",
            new CircularDnaSequence("ACGTACGTAC"),
            [
                new PlasmidFeature(
                    "geneA",
                    FeatureType::CDS,
                    1,
                    6,
                    Strand::FORWARD,
                    null,
                    "test note",
                    null,
                    ["gene" => "geneA", "product" => "Protein A"]
                ),
            ],
            "Test plasmid",
            "ACC001"
        );

        $sExpected = "LOCUS       pTest                     10 bp    DNA     circular\n"
            . "DEFINITION  Test plasmid.\n"
            . "ACCESSION   ACC001\n"
            . "FEATURES             Location/Qualifiers\n"
            . "     source          1..10\n"
            . "     CDS             1..6\n"
            . "                     /gene=\"geneA\"\n"
            . "                     /product=\"Protein A\"\n"
            . "                     /note=\"test note\"\n"
            . "ORIGIN\n"
            . "        1 acgtacgtac\n"
            . "//\n";

        $this->assertEquals($sExpected, $this->writer->write($oPlasmid));
    }

    public function testOmitsTheDefinitionLineWhenThePlasmidHasNoDescription()
    {
        $oPlasmid = new Plasmid("pTest", new CircularDnaSequence("ACGT"));

        $sOutput = $this->writer->write($oPlasmid);

        $this->assertStringNotContainsString("DEFINITION", $sOutput);
    }

    public function testFallsBackToTheNameForAccessionWhenThereIsNoExternalId()
    {
        $oPlasmid = new Plasmid("pTest", new CircularDnaSequence("ACGT"));

        $sOutput = $this->writer->write($oPlasmid);

        $this->assertStringContainsString("ACCESSION   pTest\n", $sOutput);
    }

    public function testAReverseStrandFeatureIsWrittenAsAComplementLocation()
    {
        $oPlasmid = new Plasmid(
            "pTest",
            new CircularDnaSequence("ACGTACGTAC"),
            [new PlasmidFeature("geneB", FeatureType::MISC_FEATURE, 2, 8, Strand::REVERSE)]
        );

        $sOutput = $this->writer->write($oPlasmid);

        $this->assertStringContainsString("complement(2..8)", $sOutput);
    }

    /**
     * A feature crossing the origin used to make the whole write throw, though INSDC writes it as a
     * join() on a circular molecule - and GenbankPlasmidMapper imports such joins.
     */
    public function testWritesAnOriginCrossingFeatureAsAJoin()
    {
        $oPlasmid = new Plasmid(
            "pTest",
            new CircularDnaSequence("ACGTACGTAC"),
            [
                new PlasmidFeature("crosser", FeatureType::MISC_FEATURE, 8, 2, Strand::FORWARD),
                new PlasmidFeature("back", FeatureType::MISC_FEATURE, 9, 3, Strand::REVERSE),
            ]
        );

        $sOutput = $this->writer->write($oPlasmid);

        $this->assertStringContainsString("misc_feature    join(8..10,1..2)\n", $sOutput);
        $this->assertStringContainsString("misc_feature    complement(join(9..10,1..3))\n", $sOutput);
    }

    /**
     * A feature read in from GenBank keeps its exact original key via "genbankKey" metadata, rather
     * than falling back to the FeatureType-derived one.
     */
    public function testReusesTheOriginalGenbankKeyFromMetadataWhenPresent()
    {
        $oPlasmid = new Plasmid(
            "pTest",
            new CircularDnaSequence("ACGTACGTAC"),
            [new PlasmidFeature(
                "oriSite",
                FeatureType::ORIGIN_OF_REPLICATION,
                1,
                5,
                Strand::NONE,
                null,
                null,
                null,
                ["genbankKey" => "oriT"]
            )]
        );

        $sOutput = $this->writer->write($oPlasmid);

        $this->assertStringContainsString("     oriT            1..5\n", $sOutput);
    }

    /**
     * A sequence longer than 60 bases wraps onto a second ORIGIN line, whose position number picks
     * up at 61 - hand-derived from a 65-base sequence (60 "A" + 5 "C").
     */
    public function testWrapsTheOriginBlockAt60BasesPerLine()
    {
        $sSequence = str_repeat("A", 60) . str_repeat("C", 5);
        $oPlasmid = new Plasmid("pTest", new CircularDnaSequence($sSequence));

        $sOutput = $this->writer->write($oPlasmid);

        $sExpectedFirstLine = "        1 " . implode(" ", str_split(str_repeat("a", 60), 10));
        $this->assertStringContainsString($sExpectedFirstLine, $sOutput);
        $this->assertStringContainsString("       61 ccccc\n", $sOutput);
    }

    public function testEscapesAnEmbeddedDoubleQuoteInAQualifierValue()
    {
        $oPlasmid = new Plasmid(
            "pTest",
            new CircularDnaSequence("ACGTACGTAC"),
            [new PlasmidFeature(
                "geneA",
                FeatureType::CDS,
                1,
                6,
                Strand::FORWARD,
                null,
                'a "quoted" note'
            )]
        );

        $sOutput = $this->writer->write($oPlasmid);

        $this->assertStringContainsString('/note="a ""quoted"" note"', $sOutput);
    }

    /**
     * INSDC : "/codon_start has valid value of 1 or 2 or 3" ; it equals phase + 1, unquoted.
     */
    public function testWritesTheCdsPhaseAsCodonStart()
    {
        $oPlasmid = new Plasmid("pTest", new CircularDnaSequence("ACGTACGTAC"), [
            new PlasmidFeature("geneA", FeatureType::CDS, 1, 9, Strand::FORWARD, null, null, null, null, 2),
        ]);

        $this->assertStringContainsString(str_repeat(" ", 21) . "/codon_start=3\n", $this->writer->write($oPlasmid));
    }

    public function testWritesNoCodonStartWhenThePhaseIsUnknown()
    {
        $oPlasmid = new Plasmid("pTest", new CircularDnaSequence("ACGTACGTAC"), [
            new PlasmidFeature("geneA", FeatureType::CDS, 1, 9),
        ]);

        $this->assertStringNotContainsString("/codon_start", $this->writer->write($oPlasmid));
    }

    /**
     * INSDC deprecated the "promoter" and "terminator" keys on 15-DEC-2014 : a hand-built promoter
     * is written as "regulatory" with /regulatory_class="promoter".
     */
    public function testWritesAPromoterAsARegulatoryFeatureWithItsClass()
    {
        $oPlasmid = new Plasmid("pTest", new CircularDnaSequence("ACGTACGTAC"), [
            new PlasmidFeature("lacP", FeatureType::PROMOTER, 1, 5, Strand::FORWARD),
        ]);

        $sOutput = $this->writer->write($oPlasmid);

        $this->assertStringContainsString("     regulatory      1..5\n", $sOutput);
        $this->assertStringContainsString(str_repeat(" ", 21) . "/regulatory_class=\"promoter\"\n", $sOutput);
    }

    /**
     * Written by this writer, read back by the real parser and mapper : types, strand, coordinates
     * and the CDS phase survive, a feature crossing the origin on either strand included.
     */
    public function testAPlasmidSurvivesAWriteParseMapRoundTrip()
    {
        $oOriginal = new Plasmid("pRound", new CircularDnaSequence("ACGTACGTACGTACGTACGTACGTACGTACGTACGTACGT"), [
            new PlasmidFeature("lacP", FeatureType::PROMOTER, 1, 5, Strand::FORWARD),
            new PlasmidFeature("bla", FeatureType::CDS, 6, 20, Strand::REVERSE, null, null, null, null, 1),
            new PlasmidFeature("rrnB", FeatureType::TERMINATOR, 21, 25, Strand::FORWARD),
            new PlasmidFeature("ori", FeatureType::ORIGIN_OF_REPLICATION, 26, 35, Strand::FORWARD),
            new PlasmidFeature("lacZ", FeatureType::CDS, 36, 3, Strand::FORWARD, null, null, null, null, 0),
            new PlasmidFeature("rop", FeatureType::MISC_FEATURE, 38, 2, Strand::REVERSE),
            new PlasmidFeature("site", FeatureType::MISC_FEATURE, 4, 9, Strand::NONE),
        ]);

        $oParser = new ParseGenbankManager();
        $oParser->parseDataFile(explode("\n", $this->writer->write($oOriginal)));
        $oRestored = (new GenbankPlasmidMapper())
            ->map($oParser->getSequence(), $oParser->getGbSequence(), $oParser->getFeatures())
            ->getPlasmid();

        $fSummary = fn($o) => [$o->getType(), $o->getStart(), $o->getEnd(), $o->getStrand(), $o->getPhase()];
        $this->assertSame(
            array_map($fSummary, $oOriginal->getFeatures()),
            array_map($fSummary, $oRestored->getFeatures())
        );
        $this->assertTrue($oOriginal->getSequence()->equals($oRestored->getSequence()));
    }

    public function testTheLocusLineIsReadBackByTheColumnBasedParser()
    {
        $oPlasmid = new Plasmid("pUC19", new CircularDnaSequence(str_repeat("ACGT", 25)));

        $oParser = new ParseGenbankManager();
        $oParser->parseDataFile(explode("\n", $this->writer->write($oPlasmid)));

        $this->assertSame("pUC19", $oParser->getSequence()->getPrimAcc());
        $this->assertSame(100, $oParser->getSequence()->getSeqlength());
        $this->assertSame("DNA", $oParser->getSequence()->getMoltype());
        $this->assertSame("CIRCULAR", $oParser->getGbSequence()->getTopology());
    }

    /**
     * A feature with no metadata lost its name, read back as its key ("CDS"), and a DEFINITION
     * gained a period at each round trip ("X." then "X..").
     */
    public function testNamesAndDescriptionSurviveARoundTrip()
    {
        $oOriginal = new Plasmid("pName", new CircularDnaSequence(str_repeat("ACGT", 10)), [
            new PlasmidFeature("AmpR", FeatureType::CDS, 1, 12, Strand::FORWARD),
            new PlasmidFeature("lacO", FeatureType::MISC_FEATURE, 20, 30, Strand::REVERSE),
        ], "A test plasmid.");

        $oParser = new ParseGenbankManager();
        $oParser->parseDataFile(explode("\n", $this->writer->write($oOriginal)));
        $oRestored = (new GenbankPlasmidMapper())
            ->map($oParser->getSequence(), $oParser->getGbSequence(), $oParser->getFeatures())
            ->getPlasmid();

        $this->assertSame(["AmpR", "lacO"], array_map(fn($o) => $o->getName(), $oRestored->getFeatures()));
        $this->assertStringContainsString("DEFINITION  A test plasmid.\n", $this->writer->write($oRestored));
    }

    /**
     * Qualifier and DEFINITION lines ran past 79 characters, and the source feature lost the
     * /organism and /mol_type of the record it was read from.
     */
    public function testWrapsLongLinesAndKeepsTheSourceQualifiers()
    {
        $sNote = str_repeat("a long annotation note ", 8);
        $oParser = new ParseGenbankManager();
        $oParser->parseDataFile([
            "LOCUS       pLONG                     40 bp    DNA     circular SYN 01-JAN-2026\n",
            "DEFINITION  Cloning vector pLONG, " . trim(str_repeat("complete sequence ", 6)) . ".\n",
            "FEATURES             Location/Qualifiers\n",
            "     source          1..40\n",
            "                     /organism=\"synthetic construct\"\n",
            "                     /mol_type=\"other DNA\"\n",
            "     misc_feature    1..10\n",
            "                     /note=\"" . trim($sNote) . "\"\n",
            "ORIGIN\n",
            "        1 acgtacgtac gtacgtacgt acgtacgtac gtacgtacgt\n",
            "//\n",
        ]);
        $oPlasmid = (new GenbankPlasmidMapper())
            ->map($oParser->getSequence(), $oParser->getGbSequence(), $oParser->getFeatures())
            ->getPlasmid();

        $sOutput = $this->writer->write($oPlasmid);

        foreach (explode("\n", $sOutput) as $sLine) {
            $this->assertLessThanOrEqual(79, strlen($sLine), $sLine);
        }
        $this->assertStringContainsString("                     /organism=\"synthetic construct\"\n", $sOutput);
        $this->assertStringContainsString("                     /mol_type=\"other DNA\"\n", $sOutput);

        $oReparsed = new ParseGenbankManager();
        $oReparsed->parseDataFile(explode("\n", $sOutput));
        $this->assertEquals($oParser->getSequence()->getDescription(), $oReparsed->getSequence()->getDescription());
        $aNotes = array_values(array_filter($oReparsed->getFeatures(), fn($o) => $o->getFtQual() === "note"));
        $this->assertEquals(trim($sNote), $aNotes[0]->getFtValue());
    }

    /**
     * INSDC cannot write an unstranded feature : a Strand::NONE feature came back FORWARD. It is
     * now marked with a qualifier of BioPHP's own, its location staying a plain range that any
     * other reader takes as the direct strand.
     */
    public function testAnUnstrandedFeatureIsMarkedAndReadBack()
    {
        $oPlasmid = new Plasmid("pTest", new CircularDnaSequence("ACGTACGTAC"), [
            new PlasmidFeature("site", FeatureType::MISC_FEATURE, 2, 8, Strand::NONE),
            new PlasmidFeature("fwd", FeatureType::MISC_FEATURE, 2, 8, Strand::FORWARD),
        ]);

        $sOutput = $this->writer->write($oPlasmid);
        $this->assertStringContainsString("     misc_feature    2..8\n                     /biophp_strand=\"none\"\n", $sOutput);
        $this->assertSame(1, substr_count($sOutput, "biophp_strand"));

        $oParser = new ParseGenbankManager();
        $oParser->parseDataFile(explode("\n", $sOutput));
        $oRestored = (new GenbankPlasmidMapper())
            ->map($oParser->getSequence(), $oParser->getGbSequence(), $oParser->getFeatures())
            ->getPlasmid();
        $this->assertSame([Strand::NONE, Strand::FORWARD], array_map(fn($o) => $o->getStrand(), $oRestored->getFeatures()));
    }

    /**
     * Two features of one key at one location, the second named after its key, came back as one :
     * with no /label the reader had nothing to split them on, and the second one's note was folded
     * into the first.
     */
    public function testTwoFeaturesAtOneLocationSurviveARoundTrip()
    {
        $oPlasmid = new Plasmid("pTest", new CircularDnaSequence("ACGTACGTAC"), [
            new PlasmidFeature("misc_feature", FeatureType::MISC_FEATURE, 1, 10, Strand::FORWARD, null, "n1"),
            new PlasmidFeature("lacO", FeatureType::MISC_FEATURE, 1, 10, Strand::FORWARD, null, "n2"),
            new PlasmidFeature("alone", FeatureType::MISC_FEATURE, 3, 5, Strand::FORWARD, null, "n3"),
            new PlasmidFeature("misc_feature", FeatureType::MISC_FEATURE, 6, 8, Strand::FORWARD, null, "n4"),
        ]);

        $sOutput = $this->writer->write($oPlasmid);
        // A feature alone at its location keeps its output : no /label for a name that is its key
        $this->assertStringContainsString("     misc_feature    6..8\n                     /note=\"n4\"\n", $sOutput);

        $oParser = new ParseGenbankManager();
        $oParser->parseDataFile(explode("\n", $sOutput));
        $oRestored = (new GenbankPlasmidMapper())
            ->map($oParser->getSequence(), $oParser->getGbSequence(), $oParser->getFeatures())
            ->getPlasmid();

        $this->assertSame(
            [["misc_feature", "n1"], ["lacO", "n2"], ["alone", "n3"], ["misc_feature", "n4"]],
            array_map(fn($o) => [$o->getName(), $o->getNote()], $oRestored->getFeatures())
        );
    }
}
