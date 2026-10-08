<?php
namespace Tests\Domain\Sequencing\Service;

use Amelaye\BioPHP\Domain\Sequencing\Service\FastqReader;
use PHPUnit\Framework\TestCase;

class FastqReaderTest extends TestCase
{
    private $reader;

    public function setUp(): void
    {
        $this->reader = new FastqReader();
    }

    public function testReadsAWellFormedSingleRecord()
    {
        $oResult = $this->reader->read([
            "@read1 a description\n",
            "ACGT\n",
            "+\n",
            "IIII\n",
        ]);

        $this->assertCount(1, $oResult->getRecords());
        $this->assertCount(0, $oResult->getWarnings());

        $oRecord = $oResult->getRecords()[0];
        $this->assertEquals("read1 a description", $oRecord->getIdentifier());
        $this->assertEquals("ACGT", $oRecord->getSequence()->getValue());
        $this->assertEquals([40, 40, 40, 40], $oRecord->getPhredScores());
    }

    public function testReadsSeveralConsecutiveRecords()
    {
        $oResult = $this->reader->read([
            "@read1\n", "ACGT\n", "+\n", "IIII\n",
            "@read2\n", "TTTT\n", "+read2\n", "!!!!\n",
        ]);

        $this->assertCount(2, $oResult->getRecords());
        $this->assertCount(0, $oResult->getWarnings());
        $this->assertEquals("read1", $oResult->getRecords()[0]->getIdentifier());
        $this->assertEquals("read2", $oResult->getRecords()[1]->getIdentifier());
        $this->assertEquals([0, 0, 0, 0], $oResult->getRecords()[1]->getPhredScores());
    }

    public function testSkipsARecordMissingTheAtPrefixAndWarnsAboutIt()
    {
        $oResult = $this->reader->read([
            "read1\n", "ACGT\n", "+\n", "IIII\n",
        ]);

        $this->assertCount(0, $oResult->getRecords());
        $this->assertCount(1, $oResult->getWarnings());
        $this->assertStringContainsString('must start with "@"', $oResult->getWarnings()[0]);
    }

    public function testSkipsARecordMissingThePlusSeparatorAndWarnsAboutIt()
    {
        $oResult = $this->reader->read([
            "@read1\n", "ACGT\n", "no separator here\n", "IIII\n",
        ]);

        $this->assertCount(0, $oResult->getRecords());
        $this->assertCount(1, $oResult->getWarnings());
        $this->assertStringContainsString('must start with "+"', $oResult->getWarnings()[0]);
    }

    public function testSkipsARecordWhoseQualityLengthDoesNotMatchItsSequenceAndWarnsAboutIt()
    {
        $oResult = $this->reader->read([
            "@read1\n", "ACGT\n", "+\n", "III\n",
        ]);

        $this->assertCount(0, $oResult->getRecords());
        $this->assertCount(1, $oResult->getWarnings());
        $this->assertStringContainsString("Quality string length", $oResult->getWarnings()[0]);
    }

    public function testAnIncompleteTrailingRecordIsSkippedAndWarnedAboutRatherThanCrashing()
    {
        $oResult = $this->reader->read([
            "@read1\n", "ACGT\n", "+\n", "IIII\n",
            "@read2\n", "TTTT\n",
        ]);

        $this->assertCount(1, $oResult->getRecords());
        $this->assertCount(1, $oResult->getWarnings());
        $this->assertStringContainsString("incomplete record", $oResult->getWarnings()[0]);
    }

    public function testAnEmptyFileProducesNoRecordsAndNoWarnings()
    {
        $oResult = $this->reader->read([]);

        $this->assertCount(0, $oResult->getRecords());
        $this->assertCount(0, $oResult->getWarnings());
    }

    public function testAFullFixtureMixingValidAndInvalidRecordsProducesBothRecordsAndWarnings()
    {
        $oResult = $this->reader->read([
            "@read1\n", "ACGT\n", "+\n", "IIII\n",
            "not a header\n", "ACGT\n", "+\n", "IIII\n",
            "@read3\n", "TTTT\n", "+\n", "!!!!\n",
        ]);

        $this->assertCount(2, $oResult->getRecords());
        $this->assertCount(1, $oResult->getWarnings());
        $this->assertEquals("read1", $oResult->getRecords()[0]->getIdentifier());
        $this->assertEquals("read3", $oResult->getRecords()[1]->getIdentifier());
    }

    /**
     * The original Sanger FASTQ let the sequence and the quality wrap over several lines (Cock et
     * al. 2010) ; such a file used to be read four lines at a time, every record of it rejected.
     * A quality line may start with "@" or "+" : the quality runs until it is as long as the
     * sequence, whatever its first character.
     */
    public function testReadsRecordsWrappedOverSeveralLines()
    {
        $oResult = $this->reader->read([
            "@read1 wrapped\n", "ACGTAC\n", "GTAC\n", "+\n", "@IIII+\n", "IIII\n",
            "@read2\n", "TTTT\n", "+read2\n", "+!!!\n",
        ]);

        $this->assertCount(0, $oResult->getWarnings());
        $this->assertCount(2, $oResult->getRecords());
        $this->assertEquals("read1 wrapped", $oResult->getRecords()[0]->getIdentifier());
        $this->assertEquals("ACGTACGTAC", $oResult->getRecords()[0]->getSequence()->getValue());
        $this->assertEquals([31, 40, 40, 40, 40, 10, 40, 40, 40, 40], $oResult->getRecords()[0]->getPhredScores());
        $this->assertEquals([10, 0, 0, 0], $oResult->getRecords()[1]->getPhredScores());
    }

    /**
     * A quality left shorter than its sequence must not swallow the next record's header line.
     */
    public function testAShortQualityDoesNotSwallowTheNextRecord()
    {
        $oResult = $this->reader->read([
            "@read1\n", "ACGT\n", "+\n", "III\n",
            "@read2\n", "ACGT\n", "+\n", "IIII\n",
        ]);

        $this->assertCount(1, $oResult->getWarnings());
        $this->assertStringContainsString("Quality string length", $oResult->getWarnings()[0]);
        $this->assertCount(1, $oResult->getRecords());
        $this->assertEquals("read2", $oResult->getRecords()[0]->getIdentifier());
    }

    /**
     * A record cut short after its sequence took the next header for a bad sequence line and
     * resumed after it : the next record was lost too.
     */
    public function testARecordCutShortDoesNotTakeTheNextOneWithIt()
    {
        $oResult = $this->reader->read(["@r1", "ACGT", "@r2", "ACGT", "+", "IIII"]);

        $this->assertCount(1, $oResult->getWarnings());
        $this->assertCount(1, $oResult->getRecords());
        $this->assertEquals("r2", $oResult->getRecords()[0]->getIdentifier());
    }

    /**
     * A quality line longer than its sequence was left unread : the record was reported with a
     * quality length of 0, and the line itself as a record that does not start with "@".
     */
    public function testATooLongQualityIsReportedWithItsLength()
    {
        $oResult = $this->reader->read(["@r1", "ACGT", "+", "IIIII", "@r2", "ACGT", "+", "IIII"]);

        $this->assertCount(1, $oResult->getWarnings());
        $this->assertStringContainsString("Skipped record 1", $oResult->getWarnings()[0]);
        $this->assertStringContainsString("5", $oResult->getWarnings()[0]);
        $this->assertEquals(["r2"], array_map(fn($o) => $o->getIdentifier(), $oResult->getRecords()));
    }

    /**
     * A Phred+64 file (Illumina 1.3 to 1.7) was silently read as Phred+33, every score 31 too high.
     * A Phred+33 file holding high scores is not mistaken for one.
     */
    public function testWarnsAboutAFileThatLooksPhred64Encoded()
    {
        $oResult = $this->reader->read(["@r1", "ACGT", "+", "hhgB", "@r2", "ACGT", "+", "hhhh"]);
        $this->assertCount(2, $oResult->getRecords());
        $this->assertCount(1, $oResult->getWarnings());
        $this->assertStringContainsString("Phred+64", $oResult->getWarnings()[0]);

        $oResult = $this->reader->read(["@r1", "ACGT", "+", "IIJ#"]);
        $this->assertSame([], $oResult->getWarnings());
    }
}
