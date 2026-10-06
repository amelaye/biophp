<?php
namespace Tests\Domain\Variants\Service;

use Amelaye\BioPHP\Domain\Variants\Service\VcfReader;
use PHPUnit\Framework\TestCase;

class VcfReaderTest extends TestCase
{
    private $reader;

    public function setUp(): void
    {
        $this->reader = new VcfReader();
    }

    public function testSkipsMetaInformationAndColumnHeaderLines()
    {
        $oResult = $this->reader->read([
            "##fileformat=VCFv4.2\n",
            "##INFO=<ID=DP,Number=1,Type=Integer,Description=\"Total Depth\">\n",
            "#CHROM\tPOS\tID\tREF\tALT\tQUAL\tFILTER\tINFO\n",
            "\n",
        ]);

        $this->assertCount(0, $oResult->getVariants());
        $this->assertCount(0, $oResult->getWarnings());
    }

    public function testMapsASimpleSnpWithIdQualityFilterAndInfo()
    {
        $oResult = $this->reader->read([
            "chr1\t100\trs123\tA\tG\t50.5\tPASS\tDP=20;AF=0.5\n",
        ]);

        $this->assertCount(1, $oResult->getVariants());
        $oVariant = $oResult->getVariants()[0];
        $this->assertEquals("chr1", $oVariant->getChrom());
        $this->assertEquals(100, $oVariant->getPosition());
        $this->assertEquals("rs123", $oVariant->getId());
        $this->assertEquals("A", $oVariant->getReference());
        $this->assertEquals(["G"], $oVariant->getAlternates());
        $this->assertEquals(50.5, $oVariant->getQuality());
        $this->assertTrue($oVariant->isPass());
        $this->assertEquals(["DP" => "20", "AF" => "0.5"], $oVariant->getInfo());
    }

    public function testMapsSeveralCommaSeparatedAlternateAlleles()
    {
        $oResult = $this->reader->read([
            "chr1\t200\t.\tC\tT,G\t30\t.\tDP=10\n",
        ]);

        $oVariant = $oResult->getVariants()[0];
        $this->assertEquals(["T", "G"], $oVariant->getAlternates());
    }

    public function testMissingIdQualityAndFilterColumnsBecomeNull()
    {
        $oResult = $this->reader->read([
            "chr1\t200\t.\tC\tT\t.\t.\t.\n",
        ]);

        $oVariant = $oResult->getVariants()[0];
        $this->assertNull($oVariant->getId());
        $this->assertNull($oVariant->getQuality());
        $this->assertNull($oVariant->getFilter());
        $this->assertEquals([], $oVariant->getInfo());
    }

    public function testAFlagOnlyInfoKeyMapsToTrue()
    {
        $oResult = $this->reader->read([
            "chr1\t100\t.\tA\tG\t.\t.\tDP=20;SOMATIC\n",
        ]);

        $this->assertEquals(["DP" => "20", "SOMATIC" => true], $oResult->getVariants()[0]->getInfo());
    }

    public function testAMalformedLineWithTooFewColumnsIsSkippedAndWarnedAboutRatherThanCrashing()
    {
        $oResult = $this->reader->read([
            "chr1\t100\t.\tA\n",
        ]);

        $this->assertCount(0, $oResult->getVariants());
        $this->assertCount(1, $oResult->getWarnings());
        $this->assertStringContainsString("at least 8 tab-separated", $oResult->getWarnings()[0]);
    }

    public function testANonNumericPositionIsSkippedAndWarnedAboutRatherThanCrashing()
    {
        $oResult = $this->reader->read([
            "chr1\tx\t.\tA\tG\t.\t.\t.\n",
        ]);

        $this->assertCount(0, $oResult->getVariants());
        $this->assertCount(1, $oResult->getWarnings());
        $this->assertStringContainsString("non-numeric POS", $oResult->getWarnings()[0]);
    }

    public function testANonNumericQualityIsSkippedAndWarnedAboutRatherThanCrashing()
    {
        $oResult = $this->reader->read([
            "chr1\t100\t.\tA\tG\tbad\t.\t.\n",
        ]);

        $this->assertCount(0, $oResult->getVariants());
        $this->assertCount(1, $oResult->getWarnings());
        $this->assertStringContainsString("non-numeric QUAL", $oResult->getWarnings()[0]);
    }

    public function testExtraFormatAndSampleColumnsAreIgnoredWithoutError()
    {
        $oResult = $this->reader->read([
            "chr1\t100\t.\tA\tG\t.\t.\t.\tGT:DP\t0/1:20\n",
        ]);

        $this->assertCount(1, $oResult->getVariants());
        $this->assertCount(0, $oResult->getWarnings());
    }

    public function testAFullFixtureMixingValidAndInvalidLinesProducesBothVariantsAndWarnings()
    {
        $oResult = $this->reader->read([
            "##fileformat=VCFv4.2\n",
            "#CHROM\tPOS\tID\tREF\tALT\tQUAL\tFILTER\tINFO\n",
            "chr1\t100\trs123\tA\tG\t50.5\tPASS\tDP=20\n",
            "chr1\t200\t.\tC\tT,G\t30\t.\tDP=10\n",
            "malformed line\n",
            "chr1\tx\t.\tA\tG\t.\t.\t.\n",
        ]);

        $this->assertCount(2, $oResult->getVariants());
        $this->assertCount(2, $oResult->getWarnings());
    }

    /**
     * VCF 4.3 : "Telomeres are indicated by using positions 0 or N+1".
     */
    public function testReadsATelomericBreakendAtPositionZero()
    {
        $oResult = $this->reader->read(["chr1\t0\tbnd_tel\tN\t.[chr1:1[\t.\tPASS\tSVTYPE=BND\n"]);

        $this->assertCount(0, $oResult->getWarnings());
        $this->assertSame(0, $oResult->getVariants()[0]->getPosition());
    }

    public function testDecodesPercentEncodedInfoValues()
    {
        $oResult = $this->reader->read(["chr1\t10\t.\tA\tG\t.\t.\tNOTE=a%3Bb%3dc%3Ad%25e%253B\n"]);

        $this->assertSame("a;b=c:d%e%3B", $oResult->getVariants()[0]->getInfo()["NOTE"]);
    }

    /**
     * An INFO value stays one unsplit string in which a literal comma is the list delimiter, so an
     * encoded comma must stay encoded to remain distinguishable from it.
     */
    public function testKeepsAnEncodedCommaEncodedInInfoValues()
    {
        $oResult = $this->reader->read(["chr1\t10\t.\tA\tG,T\t.\t.\tLIST=x%2Cy,z\n"]);

        $this->assertSame("x%2Cy,z", $oResult->getVariants()[0]->getInfo()["LIST"]);
    }
}
