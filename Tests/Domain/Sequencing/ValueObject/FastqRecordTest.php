<?php
namespace Tests\Domain\Sequencing\ValueObject;

use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;
use Amelaye\BioPHP\Domain\Sequencing\Exception\InvalidFastqRecordException;
use Amelaye\BioPHP\Domain\Sequencing\ValueObject\FastqRecord;
use PHPUnit\Framework\TestCase;

class FastqRecordTest extends TestCase
{
    public function testExposesIdentifierSequenceAndRawQuality()
    {
        $oRecord = new FastqRecord("read1", new DnaSequence("ACGT"), "IIII");

        $this->assertEquals("read1", $oRecord->getIdentifier());
        $this->assertEquals("ACGT", $oRecord->getSequence()->getValue());
        $this->assertEquals("IIII", $oRecord->getQuality());
        $this->assertEquals(4, $oRecord->getLength());
    }

    /**
     * "I" is ASCII 73 ; under Phred+33 that is 73 - 33 = 40, a very high quality score real Illumina
     * reads commonly carry.
     */
    public function testDecodesAUniformHighQualityStringAsPhred40EveryBase()
    {
        $oRecord = new FastqRecord("read1", new DnaSequence("ACGT"), "IIII");

        $this->assertEquals([40, 40, 40, 40], $oRecord->getPhredScores());
        $this->assertEqualsWithDelta(40.0, $oRecord->getMeanPhredScore(), 0.0001);
    }

    /**
     * Hand-derived : "!"=ASCII 33->Phred 0, "#"=35->2, "4"=52->19, "?"=63->30, "I"=73->40 ;
     * mean = (0+2+19+30+40)/5 = 91/5 = 18.2.
     */
    public function testDecodesAMixedQualityStringPerBaseAndAveragesThem()
    {
        $oRecord = new FastqRecord("read1", new DnaSequence("ACGTN"), "!#4?I");

        $this->assertEquals([0, 2, 19, 30, 40], $oRecord->getPhredScores());
        $this->assertEqualsWithDelta(18.2, $oRecord->getMeanPhredScore(), 0.0001);
    }

    public function testAZeroLengthReadHasAZeroMeanQualityScore()
    {
        $oRecord = new FastqRecord("read1", new DnaSequence(""), "");

        $this->assertEquals([], $oRecord->getPhredScores());
        $this->assertEquals(0.0, $oRecord->getMeanPhredScore());
    }

    public function testRejectsAnEmptyIdentifier()
    {
        $this->expectException(InvalidFastqRecordException::class);
        $this->expectExceptionMessage("identifier must not be empty");

        new FastqRecord("", new DnaSequence("ACGT"), "IIII");
    }

    public function testRejectsAQualityStringShorterThanTheSequence()
    {
        $this->expectException(InvalidFastqRecordException::class);
        $this->expectExceptionMessage("Quality string length (3) must match sequence length (4)");

        new FastqRecord("read1", new DnaSequence("ACGT"), "III");
    }

    /**
     * A space (ASCII 32) falls just below the Phred+33 floor of "!" (ASCII 33, Phred 0).
     */
    public function testRejectsAQualitySymbolBelowThePhredPlus33Floor()
    {
        $this->expectException(InvalidFastqRecordException::class);
        $this->expectExceptionMessage('ASCII 32');

        new FastqRecord("read1", new DnaSequence("AC"), "I ");
    }
}
