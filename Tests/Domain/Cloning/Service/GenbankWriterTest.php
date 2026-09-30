<?php
namespace Tests\Domain\Cloning\Service;

use Amelaye\BioPHP\Domain\Cloning\Aggregate\Plasmid;
use Amelaye\BioPHP\Domain\Cloning\Service\GenbankWriter;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\FeatureType;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\PlasmidFeature;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\Strand;
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
     * - LOCUS : "LOCUS       " (12 chars) + name padded to 20 + "10 bp    DNA     circular"
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

        $sExpected = "LOCUS       pTest               10 bp    DNA     circular\n"
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

    public function testRejectsAnOriginCrossingFeature()
    {
        $oPlasmid = new Plasmid(
            "pTest",
            new CircularDnaSequence("ACGTACGTAC"),
            [new PlasmidFeature("crosser", FeatureType::MISC_FEATURE, 8, 2, Strand::NONE)]
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("crosses the origin");

        $this->writer->write($oPlasmid);
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
}
