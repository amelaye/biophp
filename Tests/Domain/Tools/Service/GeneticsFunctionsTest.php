<?php
namespace Tests\Domain\Tools\Service;

use Amelaye\BioPHP\Domain\Tools\Service\GeneticsFunctions;
use PHPUnit\Framework\TestCase;

class GeneticsFunctionsTest extends TestCase
{
    public function testCountACGT()
    {
        $this->assertEquals(8, GeneticsFunctions::CountACGT("ACGTACGT"));
    }

    public function testCountACGTIgnoresAmbiguityCodesAndLowerCase()
    {
        $this->assertEquals(4, GeneticsFunctions::CountACGT("ACGTN"));
        $this->assertEquals(4, GeneticsFunctions::CountACGT("acgtACGT"));
    }

    public function testCountYRWSKMDVHB()
    {
        $geneticsFunctions = new GeneticsFunctions();
        $this->assertEquals(10, $geneticsFunctions->CountYRWSKMDVHB("YRWSKMDVHB"));
        $this->assertEquals(0, $geneticsFunctions->CountYRWSKMDVHB("ACGT"));
    }

    public function testCountCG()
    {
        $this->assertEquals(4, GeneticsFunctions::CountCG("GCGC"));
        $this->assertEquals(0, GeneticsFunctions::CountCG("ATAT"));
    }

    public function testCreateInversionOnSelfComplementarySequence()
    {
        $dnaComplements = ['A' => 'T', 'T' => 'A', 'G' => 'C', 'C' => 'G'];
        $this->assertEquals("ACGT", GeneticsFunctions::CreateInversion("ACGT", $dnaComplements));
    }

    public function testCreateInversion()
    {
        $dnaComplements = ['A' => 'T', 'T' => 'A', 'G' => 'C', 'C' => 'G'];
        $this->assertEquals("CCTT", GeneticsFunctions::CreateInversion("AAGG", $dnaComplements));
    }

    public function testRemoveNonCodingProt()
    {
        $this->assertEquals("ARNDCE", GeneticsFunctions::RemoveNonCodingProt("ARN123DCE!"));
    }

    public function testRemoveNonCodingProtUppercasesInput()
    {
        $this->assertEquals("ARNDCE", GeneticsFunctions::RemoveNonCodingProt("arndce"));
    }

    public function testRemoveNonCodingProtKeepsStopCodon()
    {
        $this->assertEquals("ARN*DCE", GeneticsFunctions::RemoveNonCodingProt("ARN*DCE"));
    }
}
