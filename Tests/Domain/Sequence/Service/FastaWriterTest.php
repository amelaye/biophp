<?php
namespace Tests\Domain\Sequence\Service;

use Amelaye\BioPHP\Domain\Sequence\Service\FastaWriter;
use PHPUnit\Framework\TestCase;

class FastaWriterTest extends TestCase
{
    private $writer;

    public function setUp(): void
    {
        $this->writer = new FastaWriter();
    }

    public function testWritesAShortSingleLineRecord()
    {
        $this->assertEquals(">seq1 a description\nACGT\n", $this->writer->writeOne("seq1 a description", "ACGT"));
    }

    /**
     * 75 bases wrap at the documented 70-column width : 70 on the first line, the remaining 5 on
     * the second.
     */
    public function testWrapsALongSequenceAt70Columns()
    {
        $sSequence = str_repeat("A", 75);
        $sExpected = ">seq1\n" . str_repeat("A", 70) . "\n" . str_repeat("A", 5) . "\n";

        $this->assertEquals($sExpected, $this->writer->writeOne("seq1", $sSequence));
    }

    public function testAnEmptySequenceProducesOnlyTheHeaderLine()
    {
        $this->assertEquals(">seq1\n", $this->writer->writeOne("seq1", ""));
    }

    public function testRejectsAnEmptyHeader()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("must not be empty");

        $this->writer->writeOne("", "ACGT");
    }

    public function testRejectsAHeaderContainingANewline()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("must not contain a newline");

        $this->writer->writeOne("seq1\nrogue", "ACGT");
    }

    public function testWriteManyConcatenatesEveryRecordInOrder()
    {
        $sOutput = $this->writer->writeMany([
            ["header" => "seq1", "sequence" => "ACGT"],
            ["header" => "seq2", "sequence" => "TTTT"],
        ]);

        $this->assertEquals(">seq1\nACGT\n>seq2\nTTTT\n", $sOutput);
    }
}
