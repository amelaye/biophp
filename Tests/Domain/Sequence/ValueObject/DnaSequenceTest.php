<?php
namespace Tests\Domain\Sequence\ValueObject;

use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\InvalidSequenceException;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\RnaSequence;
use PHPUnit\Framework\TestCase;

class DnaSequenceTest extends TestCase
{
    public function testWrapsTheStringAndExposesIt()
    {
        $oDna = new DnaSequence("ATGCGT");

        $this->assertEquals("ATGCGT", $oDna->getValue());
        $this->assertEquals("ATGCGT", (string) $oDna);
        $this->assertEquals("DNA", $oDna->getMolType());
        $this->assertEquals("ACGTMRWSYKVHDBXN", $oDna->getAlphabet());
        $this->assertEquals(6, $oDna->getLength());
        $this->assertFalse($oDna->isEmpty());
    }

    public function testNormalizesCaseAndWhitespace()
    {
        $oDna = new DnaSequence("atg cgt\n aaa\t");

        $this->assertEquals("ATGCGTAAA", $oDna->getValue());
        $this->assertEquals(9, $oDna->getLength());
    }

    public function testAcceptsAnEmptySequence()
    {
        $oDna = new DnaSequence("");

        $this->assertEquals("", $oDna->getValue());
        $this->assertTrue($oDna->isEmpty());
        $this->assertEquals(0, $oDna->getLength());
    }

    public function testAcceptsDegeneratedSymbols()
    {
        $oDna = new DnaSequence("ACGTMRWSYKVHDBXN");

        $this->assertEquals("ACGTMRWSYKVHDBXN", $oDna->getValue());
    }

    public function testRejectsUracil()
    {
        $this->expectException(InvalidSequenceException::class);
        $this->expectExceptionMessage('Invalid DNA symbol "U" at position 3.');

        new DnaSequence("ATGUC");
    }

    public function testRejectsAnyForeignSymbol()
    {
        $this->expectException(InvalidSequenceException::class);
        $this->expectExceptionMessage('Invalid DNA symbol "1" at position 0.');

        new DnaSequence("1ATG");
    }

    public function testIsValid()
    {
        $this->assertTrue(DnaSequence::isValid("ATGC"));
        $this->assertFalse(DnaSequence::isValid("ATGU"));
    }

    public function testComplementHandlesDegeneratedSymbols()
    {
        $oDna = new DnaSequence("ACGTMRWSYKVHDBXN");

        $this->assertEquals("TGCAKYWSRMBDHVXN", $oDna->complement()->getValue());
    }

    public function testReverseComplement()
    {
        $oDna = new DnaSequence("ATGCGT");

        $this->assertEquals("ACGCAT", $oDna->reverseComplement()->getValue());
    }

    public function testReverse()
    {
        $oDna = new DnaSequence("ATGCGT");

        $this->assertEquals("TGCGTA", $oDna->reverse()->getValue());
    }

    public function testTransformationsReturnNewInstances()
    {
        $oDna = new DnaSequence("ATGCGT");
        $oComplement = $oDna->complement();

        $this->assertNotSame($oDna, $oComplement);
        $this->assertEquals("ATGCGT", $oDna->getValue());
        $this->assertInstanceOf(DnaSequence::class, $oComplement);
    }

    public function testGcContent()
    {
        $this->assertEquals(50.0, (new DnaSequence("ATGC"))->getGcContent());
        $this->assertEquals(100.0, (new DnaSequence("GCS"))->getGcContent());
        $this->assertEquals(0.0, (new DnaSequence("AT"))->getGcContent());
    }

    public function testGcContentOfAnEmptySequenceIsZero()
    {
        $this->assertEquals(0.0, (new DnaSequence(""))->getGcContent());
    }

    public function testSubSequence()
    {
        $oDna = new DnaSequence("ATGCGT");

        $this->assertEquals("GCG", $oDna->subSequence(2, 3)->getValue());
        $this->assertEquals("GCGT", $oDna->subSequence(2)->getValue());
        $this->assertInstanceOf(DnaSequence::class, $oDna->subSequence(0, 1));
    }

    public function testCountSymbol()
    {
        $oDna = new DnaSequence("ATGCGT");

        $this->assertEquals(2, $oDna->countSymbol("G"));
        $this->assertEquals(2, $oDna->countSymbol("g"));
        $this->assertEquals(0, $oDna->countSymbol("N"));
    }

    public function testEquals()
    {
        $oDna = new DnaSequence("ATGC");

        $this->assertTrue($oDna->equals(new DnaSequence("atgc")));
        $this->assertFalse($oDna->equals(new DnaSequence("ATGG")));
        $this->assertFalse($oDna->equals(new RnaSequence("AUGC")));
    }

    public function testToRna()
    {
        $oRna = (new DnaSequence("ATGCTT"))->toRna();

        $this->assertInstanceOf(RnaSequence::class, $oRna);
        $this->assertEquals("AUGCUU", $oRna->getValue());
    }
}
