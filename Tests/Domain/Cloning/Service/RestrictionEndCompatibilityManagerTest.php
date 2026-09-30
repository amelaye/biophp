<?php
namespace Tests\Domain\Cloning\Service;

use Amelaye\BioPHP\Domain\Cloning\Service\CircularRestrictionDigestManager;
use Amelaye\BioPHP\Domain\Cloning\Service\RestrictionEndCompatibilityManager;
use Amelaye\BioPHP\Domain\Cloning\Aggregate\Plasmid;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\RestrictionEnd;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\CircularDnaSequence;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\RestrictionEnzymeDefinition;
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

    public function testTwoIdenticalFivePrimeOverhangsAreCompatible()
    {
        // EcoRI's own overhang, AATT: two ends carrying the same stored overhang always ligate.
        $this->assertEquals(
            RestrictionEndCompatibilityManager::COMPATIBLE,
            $this->manager->checkCompatibility(RestrictionEnd::fivePrime("AATT"), RestrictionEnd::fivePrime("AATT"))
        );
    }

    public function testTwoIdenticalThreePrimeOverhangsAreCompatible()
    {
        // AatII's own overhang, ACGT.
        $this->assertEquals(
            RestrictionEndCompatibilityManager::COMPATIBLE,
            $this->manager->checkCompatibility(RestrictionEnd::threePrime("ACGT"), RestrictionEnd::threePrime("ACGT"))
        );
    }

    /**
     * AATT and GGCC are both palindromes, like every other overhang above, so on their own they
     * cannot tell literal-equality and reverse-complement-equality apart: reverseComplement("GGCC")
     * is "GGCC" too, and either formula agrees they are still not equal to "AATT". The two tests
     * below use a non-palindromic overhang instead, which is the only way to actually distinguish
     * the two candidate formulas and lock in that literal equality is the correct one - see
     * RestrictionEndCompatibilityManager's own class docblock for the worked proof.
     */
    public function testTwoNonComplementaryFivePrimeOverhangsAreIncompatible()
    {
        $this->assertEquals(
            RestrictionEndCompatibilityManager::INCOMPATIBLE,
            $this->manager->checkCompatibility(RestrictionEnd::fivePrime("AATT"), RestrictionEnd::fivePrime("GGCC"))
        );
    }

    public function testTwoIdenticalNonPalindromicOverhangsAreCompatible()
    {
        // reverseComplement("AGGT") is "ACCT", not "AGGT": a formula based on reverse-complement
        // equality would wrongly call this pair incompatible.
        $this->assertEquals(
            RestrictionEndCompatibilityManager::COMPATIBLE,
            $this->manager->checkCompatibility(RestrictionEnd::fivePrime("AGGT"), RestrictionEnd::fivePrime("AGGT"))
        );
    }

    public function testTwoReverseComplementaryButNotIdenticalOverhangsAreIncompatible()
    {
        // The inverse mistake: a formula based on reverse-complement equality would wrongly call
        // this pair compatible, since reverseComplement("ACCT") is exactly "AGGT".
        $this->assertEquals(
            RestrictionEndCompatibilityManager::INCOMPATIBLE,
            $this->manager->checkCompatibility(RestrictionEnd::fivePrime("AGGT"), RestrictionEnd::fivePrime("ACCT"))
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
        // BamHI (GATC) and a hypothetical enzyme sharing the same overhang.
        $this->assertEquals(
            RestrictionEndCompatibilityManager::COMPATIBLE,
            $this->manager->checkCompatibility(RestrictionEnd::fivePrime("GATC"), RestrictionEnd::fivePrime("GATC"))
        );
    }

    /**
     * The strongest possible regression guard: re-ligating a single fragment's own two ends must
     * always restore the original circular molecule, for ANY overhang, palindromic or not. Both
     * getLeftEnd() and getRightEnd() come from the exact same cut here (a single site, so
     * CircularRestrictionDigestManager::buildFragments() reuses one RestrictionEnd on both sides),
     * so this exercises the real fragment/digest pipeline end to end rather than comparing two
     * hand-built RestrictionEnd instances.
     */
    public function testASingleCutsFragmentAlwaysReligatesToItselfWithAFivePrimeOverhang()
    {
        // "GGGGGG" cut after 2 G's leaves "GGGG" as the 5' overhang - not a palindrome
        // (reverseComplement("GGGG") is "CCCC").
        $oEnzyme = new RestrictionEnzymeDefinition(
            "SynFivePrime",
            [],
            RestrictionEnzymeDefinition::TYPE_II,
            "GG'GGGG",
            "(GGGGGG)",
            6,
            2,
            4,
            6
        );
        $oPlasmid = new Plasmid("p1", new CircularDnaSequence("TTTTGGGGGGTTTT"));

        $oResult = (new CircularRestrictionDigestManager())->digest($oPlasmid, [$oEnzyme]);

        $oFragment = $oResult->getFragments()[0];
        $this->assertEquals("GGGG", $oFragment->getRightEnd()->getOverhangSequence());
        $this->assertEquals(
            RestrictionEndCompatibilityManager::COMPATIBLE,
            $this->manager->checkCompatibility($oFragment->getRightEnd(), $oFragment->getLeftEnd())
        );
    }

    public function testASingleCutsFragmentAlwaysReligatesToItselfWithAThreePrimeOverhang()
    {
        // "CCCCCC" cut after 4 C's, lower cut back at the match start, leaves "CCCC" as the 3'
        // overhang - not a palindrome (reverseComplement("CCCC") is "GGGG").
        $oEnzyme = new RestrictionEnzymeDefinition(
            "SynThreePrime",
            [],
            RestrictionEnzymeDefinition::TYPE_II,
            "C_CCC'CC",
            "(CCCCCC)",
            6,
            4,
            -4,
            6
        );
        $oPlasmid = new Plasmid("p1", new CircularDnaSequence("TTTTCCCCCCTTTT"));

        $oResult = (new CircularRestrictionDigestManager())->digest($oPlasmid, [$oEnzyme]);

        $oFragment = $oResult->getFragments()[0];
        $this->assertEquals("CCCC", $oFragment->getRightEnd()->getOverhangSequence());
        $this->assertEquals(
            RestrictionEndCompatibilityManager::COMPATIBLE,
            $this->manager->checkCompatibility($oFragment->getRightEnd(), $oFragment->getLeftEnd())
        );
    }
}
