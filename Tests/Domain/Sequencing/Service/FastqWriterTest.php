<?php
namespace Tests\Domain\Sequencing\Service;

use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;
use Amelaye\BioPHP\Domain\Sequencing\Service\FastqReader;
use Amelaye\BioPHP\Domain\Sequencing\Service\FastqWriter;
use Amelaye\BioPHP\Domain\Sequencing\ValueObject\FastqRecord;
use PHPUnit\Framework\TestCase;

class FastqWriterTest extends TestCase
{
    private FastqWriter $writer;

    public function setUp(): void
    {
        $this->writer = new FastqWriter();
    }

    public function testWritesAReadAsFourLines()
    {
        $oRecord = new FastqRecord("read1 a description", new DnaSequence("ACGT"), "IIII");

        $this->assertSame("@read1 a description\nACGT\n+\nIIII\n", $this->writer->writeOne($oRecord));
    }

    public function testWritesSeveralReadsInOrder()
    {
        $sFastq = $this->writer->writeMany([
            new FastqRecord("r1", new DnaSequence("AC"), "!~"),
            new FastqRecord("r2", new DnaSequence("GT"), "II"),
        ]);

        $this->assertSame("@r1\nAC\n+\n!~\n@r2\nGT\n+\nII\n", $sFastq);
        $this->assertSame("", $this->writer->writeMany([]));
    }

    /**
     * What the writer writes, the reader reads back as it was - qualities starting with "@" or "+"
     * included, the symbols a careless parser takes for a header or a separator.
     */
    public function testWhatIsWrittenIsReadBack()
    {
        $aRecords = [
            new FastqRecord("a", new DnaSequence("ACGTN"), "@+I#~"),
            new FastqRecord("b description with spaces", new DnaSequence("TTTT"), "+@@+"),
            new FastqRecord("c", new DnaSequence("G"), "I"),
        ];

        $oResult = (new FastqReader())->read(explode("\n", rtrim($this->writer->writeMany($aRecords), "\n")));

        $this->assertSame([], $oResult->getWarnings());
        $this->assertEquals($aRecords, $oResult->getRecords());
    }

    public function testAnIdentifierWithANewlineIsRefused()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->writer->writeOne(new FastqRecord("two\nlines", new DnaSequence("AC"), "II"));
    }

    public function testOnlyFastqRecordsAreWritten()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->writer->writeMany(["not a record"]);
    }
}
