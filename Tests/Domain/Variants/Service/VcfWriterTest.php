<?php
namespace Tests\Domain\Variants\Service;

use Amelaye\BioPHP\Domain\Variants\Service\VcfReader;
use Amelaye\BioPHP\Domain\Variants\Service\VcfWriter;
use Amelaye\BioPHP\Domain\Variants\ValueObject\VcfVariant;
use PHPUnit\Framework\TestCase;

class VcfWriterTest extends TestCase
{
    private VcfWriter $writer;

    public function setUp(): void
    {
        $this->writer = new VcfWriter();
    }

    public function testWritesTheHeaderAndAVariantLine()
    {
        $sVcf = $this->writer->write([
            new VcfVariant("chr1", 12345, "rs1", "A", ["G", "T"], 50.5, "PASS", ["DP" => "14", "DB" => true]),
        ]);

        $this->assertSame(
            "##fileformat=VCFv4.3\n"
            . "#CHROM\tPOS\tID\tREF\tALT\tQUAL\tFILTER\tINFO\n"
            . "chr1\t12345\trs1\tA\tG,T\t50.5\tPASS\tDP=14;DB\n",
            $sVcf
        );
    }

    public function testMissingValuesAreDots()
    {
        $sVcf = $this->writer->write([new VcfVariant("2", 7, null, "C", [], null, null, [])]);

        $this->assertStringEndsWith("2\t7\t.\tC\t.\t.\t.\t.\n", $sVcf);
    }

    public function testAnEmptyFileHasItsHeaderOnly()
    {
        $this->assertSame("##fileformat=VCFv4.3\n#CHROM\tPOS\tID\tREF\tALT\tQUAL\tFILTER\tINFO\n", $this->writer->write([]));
    }

    public function testExtraMetaLinesComeAfterTheFileFormat()
    {
        $sVcf = $this->writer->write([], ["##contig=<ID=1,length=249250621>", "INFO=<ID=DP,Number=1,Type=Integer,Description=\"Depth\">"]);

        $this->assertSame(
            "##fileformat=VCFv4.3\n##contig=<ID=1,length=249250621>\n##INFO=<ID=DP,Number=1,Type=Integer,Description=\"Depth\">\n"
            . "#CHROM\tPOS\tID\tREF\tALT\tQUAL\tFILTER\tINFO\n",
            $sVcf
        );
    }

    public function testAnInfoValueIsPercentEncoded()
    {
        $sVcf = $this->writer->write([
            new VcfVariant("1", 5, null, "A", ["T"], null, null, ["NOTE" => "a;b=c:d%e\tf", "AF" => "0.5,0.25"]),
        ]);

        $this->assertStringEndsWith("NOTE=a%3Bb%3Dc%3Ad%25e%09f;AF=0.5,0.25\n", $sVcf);
    }

    /**
     * The reader keeps an encoded comma as "%2C" ; written back, it used to become "%252C", which
     * a VCF 4.3 consumer reads as the text "%2C" and no more as a comma.
     */
    public function testAnEncodedCommaSurvivesReadingThenWriting()
    {
        $sLine = "chr1\t5\t.\tA\tG\t.\t.\tDESC=a%2Cb;NOTE=50%25%3B";
        $oResult = (new VcfReader())->read(["#CHROM\tPOS\tID\tREF\tALT\tQUAL\tFILTER\tINFO", $sLine]);

        $sVcf = $this->writer->write($oResult->getVariants());

        $this->assertStringEndsWith("\tDESC=a%2Cb;NOTE=50%25%3B\n", $sVcf);
    }

    /**
     * Every kind of line VcfReader understands - multi-allelic, symbolic, breakend, a telomere at
     * position 0, a missing ALT, encoded INFO values, a float quality - is read back as it was.
     */
    public function testWhatIsWrittenIsReadBack()
    {
        $aVariants = [
            new VcfVariant("chr1", 100, "rs1", "A", ["G", "T"], 29.5, "PASS", ["DP" => "14", "DB" => true]),
            new VcfVariant("chr1", 200, null, "ACGT", ["A", "<DEL>"], 1.0E-5, "q10", ["SVTYPE" => "DEL"]),
            new VcfVariant("chr2", 0, null, "N", [".[13:123457["], null, null, []),
            new VcfVariant("chr2", 300, "x", "G", ["G]17:198982]"], 50.0, "PASS", ["NOTE" => "a;b=c:d%e"]),
            new VcfVariant("3", 7, null, "C", [], null, null, []),
            new VcfVariant("3", 8, null, "C", ["*"], 0.0, "PASS", ["AF" => "0.5,0.25"]),
        ];

        $oResult = (new VcfReader())->read(explode("\n", rtrim($this->writer->write($aVariants), "\n")));

        $this->assertSame([], $oResult->getWarnings());
        $this->assertEquals($aVariants, $oResult->getVariants());
    }

    public function testAFieldWithABlankIsRefused()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->writer->write([new VcfVariant("chr 1", 5, null, "A", ["T"], null, null, [])]);
    }

    public function testAnInfoKeyThatWouldBreakTheLineIsRefused()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->writer->write([new VcfVariant("1", 5, null, "A", ["T"], null, null, ["A=B" => "x"])]);
    }

    public function testOnlyVariantsAreWritten()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->writer->write(["not a variant"]);
    }
}
