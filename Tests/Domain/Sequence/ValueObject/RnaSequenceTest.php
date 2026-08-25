<?php
namespace Tests\Domain\Sequence\ValueObject;

use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\InvalidSequenceException;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\RnaSequence;
use PHPUnit\Framework\TestCase;

class RnaSequenceTest extends TestCase
{
    public function testWrapsTheStringAndExposesIt()
    {
        $oRna = new RnaSequence("AUGCGU");

        $this->assertEquals("AUGCGU", $oRna->getValue());
        $this->assertEquals("AUGCGU", (string) $oRna);
        $this->assertEquals("RNA", $oRna->getMolType());
        $this->assertEquals("ACGUMRWSYKVHDBXN", $oRna->getAlphabet());
        $this->assertEquals(6, $oRna->getLength());
    }

    public function testNormalizesCaseAndWhitespace()
    {
        $oRna = new RnaSequence("aug cgu\n");

        $this->assertEquals("AUGCGU", $oRna->getValue());
    }

    public function testRejectsThymine()
    {
        $this->expectException(InvalidSequenceException::class);
        $this->expectExceptionMessage('Invalid RNA symbol "T" at position 1.');

        new RnaSequence("AT GC");
    }

    public function testAcceptsDegeneratedSymbols()
    {
        $oRna = new RnaSequence("ACGUMRWSYKVHDBXN");

        $this->assertEquals("ACGUMRWSYKVHDBXN", $oRna->getValue());
    }

    public function testComplementHandlesDegeneratedSymbols()
    {
        $oRna = new RnaSequence("ACGUMRWSYKVHDBXN");

        $this->assertEquals("UGCAKYWSRMBDHVXN", $oRna->complement()->getValue());
    }

    public function testReverseComplement()
    {
        $oRna = new RnaSequence("AUGCGU");

        $this->assertEquals("ACGCAU", $oRna->reverseComplement()->getValue());
    }

    public function testGcContent()
    {
        $this->assertEquals(50.0, (new RnaSequence("AUGC"))->getGcContent());
        $this->assertEquals(0.0, (new RnaSequence(""))->getGcContent());
    }

    public function testToDna()
    {
        $oDna = (new RnaSequence("AUGCUU"))->toDna();

        $this->assertInstanceOf(DnaSequence::class, $oDna);
        $this->assertEquals("ATGCTT", $oDna->getValue());
    }

    public function testTranscriptionIsReversible()
    {
        $oDna = new DnaSequence("ATGCTTAAA");

        $this->assertTrue($oDna->equals($oDna->toRna()->toDna()));
    }

    public function testEquals()
    {
        $oRna = new RnaSequence("AUGC");

        $this->assertTrue($oRna->equals(new RnaSequence("augc")));
        $this->assertFalse($oRna->equals(new RnaSequence("AUGG")));
    }
}
