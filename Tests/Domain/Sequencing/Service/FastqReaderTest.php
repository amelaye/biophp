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
}
