<?php
namespace Tests\Domain\Sequence\ValueObject;

use Amelaye\BioPHP\Domain\Sequence\Entity\Sequence;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\AminoAcidSequence;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\InvalidSequenceException;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\MolecularSequenceFactory;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\RnaSequence;
use PHPUnit\Framework\TestCase;

class MolecularSequenceFactoryTest extends TestCase
{
    public function testBuildsADnaSequence()
    {
        $oValue = MolecularSequenceFactory::fromMolType("DNA", "ATGC");

        $this->assertInstanceOf(DnaSequence::class, $oValue);
        $this->assertEquals("ATGC", $oValue->getValue());
    }

    public function testBuildsARnaSequence()
    {
        $oValue = MolecularSequenceFactory::fromMolType("RNA", "AUGC");

        $this->assertInstanceOf(RnaSequence::class, $oValue);
        $this->assertEquals("AUGC", $oValue->getValue());
    }

    public function testAcceptsTheRawMoleculeTypesOfTheParsers()
    {
        $this->assertInstanceOf(DnaSequence::class, MolecularSequenceFactory::fromMolType("ss-DNA", "ATGC"));
        $this->assertInstanceOf(RnaSequence::class, MolecularSequenceFactory::fromMolType("mRNA", "AUGC"));
        $this->assertInstanceOf(AminoAcidSequence::class, MolecularSequenceFactory::fromMolType("PRT;", "GAVLI"));
    }

    public function testGenbankMessengerRnaStoredWithThymineIsWrappedAsDna()
    {
        $oValue = MolecularSequenceFactory::fromMolType("mRNA", "aagactgcatccggctccag");

        $this->assertInstanceOf(DnaSequence::class, $oValue);
        $this->assertEquals("AAGACTGCATCCGGCTCCAG", $oValue->getValue());
    }

    public function testFallsBackOnTheDeclaredTypeWhenNeitherThymineNorUracilIsPresent()
    {
        $this->assertInstanceOf(RnaSequence::class, MolecularSequenceFactory::fromMolType("RNA", "ACGACG"));
        $this->assertInstanceOf(DnaSequence::class, MolecularSequenceFactory::fromMolType("DNA", "ACGACG"));
    }

    public function testRejectsAnUnknownMoleculeType()
    {
        $this->expectException(InvalidSequenceException::class);
        $this->expectExceptionMessage('Unsupported molecule type "XNA".');

        MolecularSequenceFactory::fromMolType("XNA", "ATGC");
    }

    public function testRejectsAnInvalidSymbolForTheResolvedType()
    {
        $this->expectException(InvalidSequenceException::class);

        MolecularSequenceFactory::fromMolType("DNA", "ATGCJ");
    }

    public function testFromEntity()
    {
        $oSequence = new Sequence();
        $oSequence->setMolType("ss-DNA");
        $oSequence->setSequence("atgcgt");

        $oValue = MolecularSequenceFactory::fromEntity($oSequence);

        $this->assertInstanceOf(DnaSequence::class, $oValue);
        $this->assertEquals("ATGCGT", $oValue->getValue());
    }

    public function testFromEntityRejectsARecordWithoutMoleculeType()
    {
        $oSequence = new Sequence();
        $oSequence->setSequence("ATGC");

        $this->expectException(InvalidSequenceException::class);
        $this->expectExceptionMessage('Unsupported molecule type "".');

        MolecularSequenceFactory::fromEntity($oSequence);
    }
}
