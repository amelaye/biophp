<?php
namespace Tests\Domain\Tools\Service\Codon;

use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;
use Amelaye\BioPHP\Domain\Tools\Service\Codon\CodonUsageTableBuilder;
use PHPUnit\Framework\TestCase;

class CodonUsageTableBuilderTest extends TestCase
{
    private $builder;

    public function setUp(): void
    {
        $this->builder = new CodonUsageTableBuilder();
    }

    public function testCountsCodonsAcrossSeveralSequences()
    {
        $oTable = $this->builder->build([
            new DnaSequence("ATGTTT"),
            new DnaSequence("ATGTTC"),
            new DnaSequence("ATGTTT"),
        ]);

        $this->assertEquals(3, $oTable->getCount("ATG"));
        $this->assertEquals(2, $oTable->getCount("TTT"));
        $this->assertEquals(1, $oTable->getCount("TTC"));
        $this->assertEquals(0, $oTable->getCount("AAA"));
    }

    public function testOnlyCompleteTrailingCodonsAreCounted()
    {
        // "ATGTT" is one complete codon (ATG) plus two leftover bases, which must be ignored.
        $oTable = $this->builder->build([new DnaSequence("ATGTT")]);

        $this->assertEquals(1, $oTable->getCount("ATG"));
        $this->assertEquals([], array_diff($oTable->getCodons(), ["ATG"]));
    }

    public function testAnEmptyListOfSequencesProducesAnEmptyTable()
    {
        $oTable = $this->builder->build([]);

        $this->assertEquals([], $oTable->getCodons());
    }

    public function testRejectsASequenceThatIsNotADnaSequence()
    {
        $this->expectException(\InvalidArgumentException::class);

        /** @noinspection PhpParamsInspection */
        $this->builder->build(["not a sequence"]);
    }
}
