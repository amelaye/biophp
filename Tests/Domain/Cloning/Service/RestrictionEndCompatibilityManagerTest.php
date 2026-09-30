<?php
namespace Tests\Domain\Cloning\Service;

use Amelaye\BioPHP\Domain\Cloning\Service\RestrictionEndCompatibilityManager;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\RestrictionEnd;
use PHPUnit\Framework\TestCase;

class RestrictionEndCompatibilityManagerTest extends TestCase
{
    private $manager;

    public function setUp(): void
    {
        $this->manager = new RestrictionEndCompatibilityManager();
    }

    public function testTwoBluntEndsAreCompatible()
    {
        $this->assertEquals(
            RestrictionEndCompatibilityManager::COMPATIBLE,
            $this->manager->checkCompatibility(RestrictionEnd::blunt(), RestrictionEnd::blunt())
        );
    }

    public function testTwoSelfComplementaryFivePrimeOverhangsAreCompatible()
    {
        // EcoRI's own overhang, AATT, is a palindrome and therefore self-compatible.
        $this->assertEquals(
            RestrictionEndCompatibilityManager::COMPATIBLE,
            $this->manager->checkCompatibility(RestrictionEnd::fivePrime("AATT"), RestrictionEnd::fivePrime("AATT"))
        );
    }

    public function testTwoSelfComplementaryThreePrimeOverhangsAreCompatible()
    {
        // AatII's own overhang, ACGT, is also a palindrome.
        $this->assertEquals(
            RestrictionEndCompatibilityManager::COMPATIBLE,
            $this->manager->checkCompatibility(RestrictionEnd::threePrime("ACGT"), RestrictionEnd::threePrime("ACGT"))
        );
    }

    public function testTwoNonComplementaryFivePrimeOverhangsAreIncompatible()
    {
        $this->assertEquals(
            RestrictionEndCompatibilityManager::INCOMPATIBLE,
            $this->manager->checkCompatibility(RestrictionEnd::fivePrime("AATT"), RestrictionEnd::fivePrime("GGCC"))
        );
    }

    public function testOverhangsOfDifferentLengthAreIncompatible()
    {
        $this->assertEquals(
            RestrictionEndCompatibilityManager::INCOMPATIBLE,
            $this->manager->checkCompatibility(RestrictionEnd::fivePrime("AATT"), RestrictionEnd::fivePrime("AAT"))
        );
    }

    public function testAFivePrimeAndAThreePrimeEndAreIncompatible()
    {
        $this->assertEquals(
            RestrictionEndCompatibilityManager::INCOMPATIBLE,
            $this->manager->checkCompatibility(RestrictionEnd::fivePrime("AATT"), RestrictionEnd::threePrime("AATT"))
        );
    }

    public function testABluntEndAndAStickyEndAreIncompatible()
    {
        $this->assertEquals(
            RestrictionEndCompatibilityManager::INCOMPATIBLE,
            $this->manager->checkCompatibility(RestrictionEnd::blunt(), RestrictionEnd::fivePrime("AATT"))
        );
    }

    public function testAnUnknownEndMakesCompatibilityIndeterminate()
    {
        $this->assertEquals(
            RestrictionEndCompatibilityManager::INDETERMINATE,
            $this->manager->checkCompatibility(RestrictionEnd::unknown(), RestrictionEnd::fivePrime("AATT"))
        );
        $this->assertEquals(
            RestrictionEndCompatibilityManager::INDETERMINATE,
            $this->manager->checkCompatibility(RestrictionEnd::blunt(), RestrictionEnd::unknown())
        );
        $this->assertEquals(
            RestrictionEndCompatibilityManager::INDETERMINATE,
            $this->manager->checkCompatibility(RestrictionEnd::unknown(), RestrictionEnd::unknown())
        );
    }

    public function testTwoDifferentCompatibleOverhangsFromDifferentEnzymesLigate()
    {
        // BamHI (GATC) and a hypothetical enzyme sharing the same self-complementary overhang.
        $this->assertEquals(
            RestrictionEndCompatibilityManager::COMPATIBLE,
            $this->manager->checkCompatibility(RestrictionEnd::fivePrime("GATC"), RestrictionEnd::fivePrime("GATC"))
        );
    }
}
