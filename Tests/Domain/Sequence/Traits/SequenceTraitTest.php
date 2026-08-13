<?php
namespace Tests\Domain\Sequence\Traits;

use Amelaye\BioPHP\Domain\Sequence\Traits\SequenceTrait;
use PHPUnit\Framework\TestCase;

class SequenceTraitTest extends TestCase
{
    /**
     * @return object
     */
    private function makeTraitObject()
    {
        return new class {
            use SequenceTrait;
        };
    }

    public function testCompDNA()
    {
        $object = $this->makeTraitObject();
        $this->assertEquals("TGCA", $object->compDNA("ACGT"));
    }

    public function testCompDNAWithAmbiguityCodes()
    {
        $object = $this->makeTraitObject();
        $this->assertEquals("TGCARYWSMKHBDV", $object->compDNA("ACGTYRWSKMDVHB"));
    }

    public function testRevCompDNAOnSelfComplementarySequence()
    {
        $object = $this->makeTraitObject();
        $this->assertEquals("ACGT", $object->revCompDNA("ACGT"));
    }

    public function testRevCompDNA()
    {
        $object = $this->makeTraitObject();
        $this->assertEquals("CCTT", $object->revCompDNA("AAGG"));
    }

    public function testCompDNAUppercasesLowercaseInput()
    {
        $object = $this->makeTraitObject();
        $this->assertEquals("TGCA", $object->compDNA("acgt"));
    }

    public function testCompDNALeavesNonBaseCharactersInPlace()
    {
        $object = $this->makeTraitObject();
        $this->assertEquals("TG CA", $object->compDNA("AC GT"));
    }

    public function testCompDNAThrowsTypeErrorOnNonStringInput()
    {
        $object = $this->makeTraitObject();
        $this->expectException(\TypeError::class);
        $object->compDNA([]);
    }

    public function testRevCompDNAThrowsTypeErrorOnNonStringInput()
    {
        $object = $this->makeTraitObject();
        $this->expectException(\TypeError::class);
        $object->revCompDNA([]);
    }

    /**
     * biotools' FindPalindromeManager::dnaIsPalindrome() considers a sequence palindromic
     * when it equals its own reverse complement - this is the exact input pair it tests.
     */
    public function testRevCompDNAMatchesItselfForAPalindromicSequence()
    {
        $object = $this->makeTraitObject();
        $this->assertEquals("AAATTT", $object->revCompDNA("AAATTT"));
    }

    /**
     * Same biotools scenario, non-palindromic input: revCompDNA() must NOT return the
     * original sequence back.
     */
    public function testRevCompDNADoesNotMatchItselfForANonPalindromicSequence()
    {
        $object = $this->makeTraitObject();
        $this->assertEquals("AAACTT", $object->revCompDNA("AAGTTT"));
        $this->assertNotEquals("AAGTTT", $object->revCompDNA("AAGTTT"));
    }

    public function testCleanSequenceValidDna()
    {
        $object = $this->makeTraitObject();
        $this->assertNull($object->cleanSequence("ACGTN", "DNA"));
    }

    public function testCleanSequenceInvalidDna()
    {
        $object = $this->makeTraitObject();
        $this->assertFalse($object->cleanSequence("ACGZ", "DNA"));
    }

    public function testCleanSequenceValidRna()
    {
        $object = $this->makeTraitObject();
        $this->assertNull($object->cleanSequence("ACGUN", "RNA"));
    }

    public function testCleanSequenceInvalidRna()
    {
        $object = $this->makeTraitObject();
        $this->assertFalse($object->cleanSequence("ACGT", "RNA"));
    }

    public function testCleanSequenceUnknownMolTypeReturnsNull()
    {
        $object = $this->makeTraitObject();
        $this->assertNull($object->cleanSequence("ACGT", "PROTEIN"));
    }
}
