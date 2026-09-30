<?php
namespace Tests\Domain\Cloning\Service;

use Amelaye\BioPHP\Domain\Cloning\Service\CircularRestrictionDigestManager;
use Amelaye\BioPHP\Domain\Cloning\Aggregate\Plasmid;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\RestrictionEnd;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\CircularDnaSequence;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\RestrictionEnzymeDefinition;
use PHPUnit\Framework\TestCase;

/**
 * Cleavage position fixtures are the real ones from RestrictionEnzymeCatalog's own data (EcoRI,
 * AatI, AatII, AcsI). Every expected position, fragment and overhang below was first computed and
 * cross-checked with a standalone PHP script before being written here, to satisfy the project rule
 * that the cleavagePositionUpper/Lower convention must be locked by known fixtures, not deduced.
 */
class CircularRestrictionDigestManagerTest extends TestCase
{
    private $manager;

    public function setUp(): void
    {
        $this->manager = new CircularRestrictionDigestManager();
    }

    private function makePlasmid(string $sSequence, string $sName = "p1"): Plasmid
    {
        return new Plasmid($sName, new CircularDnaSequence($sSequence));
    }

    private function ecoRI(string $sName = "EcoRI"): RestrictionEnzymeDefinition
    {
        return new RestrictionEnzymeDefinition(
            $sName,
            [],
            RestrictionEnzymeDefinition::TYPE_II,
            "G'AATT_C",
            "(GAATTC)",
            6,
            1,
            4,
            6
        );
    }

    private function aatI(): RestrictionEnzymeDefinition
    {
        return new RestrictionEnzymeDefinition(
            "AatI",
            [],
            RestrictionEnzymeDefinition::TYPE_II,
            "AGG'CCT",
            "(AGGCCT)",
            6,
            3,
            0,
            6
        );
    }

    private function aatII(): RestrictionEnzymeDefinition
    {
        return new RestrictionEnzymeDefinition(
            "AatII",
            [],
            RestrictionEnzymeDefinition::TYPE_II,
            "G_ACGT'C",
            "(GACGTC)",
            6,
            5,
            -4,
            6
        );
    }

    private function acsI(): RestrictionEnzymeDefinition
    {
        return new RestrictionEnzymeDefinition(
            "AcsI",
            [],
            RestrictionEnzymeDefinition::TYPE_II,
            "R'AATT_Y",
            "(AAATTC|AAATTT|GAATTC|GAATTT)",
            6,
            1,
            4,
            4
        );
    }

    private function totalFragmentLength(array $aFragments): int
    {
        return array_sum(array_map(function ($oFragment) {
            return $oFragment->getLength();
        }, $aFragments));
    }

    public function testAPlasmidWithoutASiteIsNotDigested()
    {
        $oPlasmid = $this->makePlasmid("AAAAAAAAAAAAAAAAAAAA");

        $oResult = $this->manager->digest($oPlasmid, [$this->ecoRI()]);

        $this->assertCount(0, $oResult->getCuts());
        $this->assertCount(0, $oResult->getFragments());
    }

    public function testASingleSiteProducesOneFragmentSpanningTheWholePlasmid()
    {
        $oPlasmid = $this->makePlasmid("TTTTGAATTCTTTT");

        $oResult = $this->manager->digest($oPlasmid, [$this->ecoRI()]);

        $this->assertCount(1, $oResult->getCuts());
        $oCut = $oResult->getCuts()[0];
        $this->assertEquals(4, $oCut->getRecognitionPosition());
        $this->assertEquals(5, $oCut->getUpperCutPosition());
        $this->assertEquals(9, $oCut->getLowerCutPosition());
        $this->assertEquals(RestrictionEnd::FIVE_PRIME, $oCut->getEnd()->getType());
        $this->assertEquals("AATT", $oCut->getEnd()->getOverhangSequence());

        $this->assertCount(1, $oResult->getFragments());
        $oFragment = $oResult->getFragments()[0];
        $this->assertEquals(14, $oFragment->getLength());
        $this->assertEquals("AATTCTTTTTTTTG", $oFragment->getSequence()->getValue());
        $this->assertEquals(14, $this->totalFragmentLength($oResult->getFragments()));
    }

    public function testTwoDistinctSitesProduceTwoFragments()
    {
        $oPlasmid = $this->makePlasmid("GAATTCTTTTTTTTTTGAATTCTTTTTTTTTT");

        $oResult = $this->manager->digest($oPlasmid, [$this->ecoRI()]);

        $this->assertCount(2, $oResult->getCuts());
        $this->assertCount(2, $oResult->getFragments());
        foreach ($oResult->getFragments() as $oFragment) {
            $this->assertEquals(16, $oFragment->getLength());
            $this->assertEquals("AATTCTTTTTTTTTTG", $oFragment->getSequence()->getValue());
        }
        $this->assertEquals(32, $this->totalFragmentLength($oResult->getFragments()));
    }

    public function testThreeDistinctSitesProduceThreeFragmentsNeverFour()
    {
        $oPlasmid = $this->makePlasmid("GAATTCTTTGAATTCTTTGAATTCTTTTTTTT");

        $oResult = $this->manager->digest($oPlasmid, [$this->ecoRI()]);

        $this->assertCount(3, $oResult->getCuts());
        $this->assertCount(3, $oResult->getFragments());

        $aLengths = array_map(function ($oFragment) {
            return $oFragment->getLength();
        }, $oResult->getFragments());

        $this->assertEquals([9, 9, 14], $aLengths);
        $this->assertEquals(32, $this->totalFragmentLength($oResult->getFragments()));
    }

    public function testASiteCrossingTheOriginIsFoundAndCutCorrectly()
    {
        // "GAATTC" only exists by wrapping the last 2 symbols ("GA") with the first 4 ("ATTC").
        $oPlasmid = $this->makePlasmid("ATTCTTTTTTTTGA");

        $oResult = $this->manager->digest($oPlasmid, [$this->ecoRI()]);

        $this->assertCount(1, $oResult->getCuts());
        $oCut = $oResult->getCuts()[0];
        $this->assertEquals(12, $oCut->getRecognitionPosition());
        $this->assertEquals(13, $oCut->getUpperCutPosition());
        $this->assertEquals(3, $oCut->getLowerCutPosition());
        $this->assertEquals("AATT", $oCut->getEnd()->getOverhangSequence());

        $this->assertCount(1, $oResult->getFragments());
        $this->assertEquals("AATTCTTTTTTTTG", $oResult->getFragments()[0]->getSequence()->getValue());
    }

    public function testTwoEnzymesCuttingAtTheSamePositionCollapseIntoOneFragmentBoundary()
    {
        $oPlasmid = $this->makePlasmid("GAATTCTTTTTTTTTTGAATTCTTTTTTTTTT");

        $oResult = $this->manager->digest($oPlasmid, [$this->ecoRI("EcoRI"), $this->ecoRI("EcoRI-like")]);

        // Two enzymes x two sites = four raw cuts, but still only two distinct fragment boundaries.
        $this->assertCount(4, $oResult->getCuts());
        $this->assertCount(2, $oResult->getFragments());
        $this->assertEquals(32, $this->totalFragmentLength($oResult->getFragments()));
    }

    public function testAnAmbiguousIupacPatternIsFoundAndWarnedAbout()
    {
        $oPlasmid = $this->makePlasmid("TTTTAAATTTTTTT");

        $oResult = $this->manager->digest($oPlasmid, [$this->acsI()]);

        $this->assertCount(1, $oResult->getCuts());
        $this->assertEquals(4, $oResult->getCuts()[0]->getRecognitionPosition());
        $this->assertEquals("AATT", $oResult->getCuts()[0]->getEnd()->getOverhangSequence());

        $this->assertNotEmpty($oResult->getWarnings());
        $this->assertStringContainsString("AcsI", $oResult->getWarnings()[0]);
    }

    public function testABluntCutterProducesABluntEndWithNoOverhang()
    {
        $oPlasmid = $this->makePlasmid("TTTTAGGCCTTTTT");

        $oResult = $this->manager->digest($oPlasmid, [$this->aatI()]);

        $oCut = $oResult->getCuts()[0];
        $this->assertEquals(7, $oCut->getUpperCutPosition());
        $this->assertEquals(7, $oCut->getLowerCutPosition());
        $this->assertEquals(RestrictionEnd::BLUNT, $oCut->getEnd()->getType());
        $this->assertNull($oCut->getEnd()->getOverhangSequence());
    }

    public function testAFivePrimeCutterProducesTheExpectedOverhang()
    {
        $oPlasmid = $this->makePlasmid("TTTTGAATTCTTTT");

        $oResult = $this->manager->digest($oPlasmid, [$this->ecoRI()]);

        $oEnd = $oResult->getCuts()[0]->getEnd();
        $this->assertEquals(RestrictionEnd::FIVE_PRIME, $oEnd->getType());
        $this->assertEquals("AATT", $oEnd->getOverhangSequence());
    }

    public function testAThreePrimeCutterProducesTheExpectedOverhang()
    {
        $oPlasmid = $this->makePlasmid("TTTTGACGTCTTTT");

        $oResult = $this->manager->digest($oPlasmid, [$this->aatII()]);

        $oCut = $oResult->getCuts()[0];
        $this->assertEquals(9, $oCut->getUpperCutPosition());
        $this->assertEquals(5, $oCut->getLowerCutPosition());
        $this->assertEquals(RestrictionEnd::THREE_PRIME, $oCut->getEnd()->getType());
        $this->assertEquals("ACGT", $oCut->getEnd()->getOverhangSequence());
    }

    /**
     * EcoRI's site GAATTC is palindromic: RC(GAATTC) is GAATTC again, so the reverse-strand search
     * findSites() also runs finds the exact same physical cut a second time, from the other side. This
     * locks in that the dedup in digest() collapses it back down to the single real cut - and that the
     * one which is kept is reported as the forward-strand find.
     */
    public function testAPalindromicSiteIsNotDoubleCountedAsAReverseStrandCut()
    {
        $oPlasmid = $this->makePlasmid("TTTTGAATTCTTTT");

        $oResult = $this->manager->digest($oPlasmid, [$this->ecoRI()]);

        $this->assertCount(1, $oResult->getCuts());
        $this->assertFalse($oResult->getCuts()[0]->isReverseStrand());
    }

    /**
     * A synthetic BsaI-like Type IIS enzyme: recognizes GGTCTC and cuts outside its own recognition
     * sequence (own-strand offset 7, complementary-strand offset 11 from the match start), leaving a
     * 4-base 5' overhang - not palindromic, since RC("GGTCTC") is "GAGACC", a different string. All
     * fixture positions below were computed and cross-checked with a standalone PHP script (verified
     * three independent ways: algebraic derivation, reproduction of the already-trusted EcoRI values
     * when reinterpreted through the same mirrored-strand code path, and manual base-by-base tracing)
     * before being written here.
     */
    private function bsaILike(): RestrictionEnzymeDefinition
    {
        return new RestrictionEnzymeDefinition(
            "Syn-BsaI-like",
            [],
            RestrictionEnzymeDefinition::TYPE_IIS,
            "GGTCTC",
            "(GGTCTC)",
            6,
            7,
            4,
            6
        );
    }

    public function testANonPalindromicSiteFoundOnlyOnTheReverseStrandIsStillDetected()
    {
        // "GAGACC" is RC("GGTCTC"): the enzyme's actual recognition sequence sits on the bottom
        // strand here, invisible to a forward-only search.
        $oPlasmid = $this->makePlasmid("AAAAGAGACC" . str_repeat("T", 20));

        $oResult = $this->manager->digest($oPlasmid, [$this->bsaILike()]);

        $this->assertCount(1, $oResult->getCuts());
        $oCut = $oResult->getCuts()[0];
        $this->assertTrue($oCut->isReverseStrand());
        $this->assertEquals(9, $oCut->getRecognitionPosition());
        $this->assertEquals(29, $oCut->getUpperCutPosition());
        $this->assertEquals(3, $oCut->getLowerCutPosition());
        $this->assertEquals(RestrictionEnd::FIVE_PRIME, $oCut->getEnd()->getType());
        $this->assertEquals("TAAA", $oCut->getEnd()->getOverhangSequence());
        $this->assertCount(1, $oResult->getFragments());
    }

    public function testForwardAndReverseStrandSitesOfANonPalindromicEnzymeBothProduceDistinctCuts()
    {
        $oPlasmid = $this->makePlasmid("AAAAGGTCTC" . str_repeat("T", 20) . "GAGACCTTTT");

        $oResult = $this->manager->digest($oPlasmid, [$this->bsaILike()]);

        $this->assertCount(2, $oResult->getCuts());

        $oForwardCut = $oResult->getCuts()[0];
        $this->assertFalse($oForwardCut->isReverseStrand());
        $this->assertEquals(4, $oForwardCut->getRecognitionPosition());
        $this->assertEquals(11, $oForwardCut->getUpperCutPosition());
        $this->assertEquals(15, $oForwardCut->getLowerCutPosition());

        $oReverseCut = $oResult->getCuts()[1];
        $this->assertTrue($oReverseCut->isReverseStrand());
        $this->assertEquals(35, $oReverseCut->getRecognitionPosition());
        $this->assertEquals(25, $oReverseCut->getUpperCutPosition());
        $this->assertEquals(29, $oReverseCut->getLowerCutPosition());

        $this->assertCount(2, $oResult->getFragments());
        $this->assertEquals(40, $this->totalFragmentLength($oResult->getFragments()));
    }

    public function testAnEnzymeLongerThanThePlasmidIsSkippedWithAWarning()
    {
        $oPlasmid = $this->makePlasmid("AAAAA");

        $oResult = $this->manager->digest($oPlasmid, [$this->ecoRI()]);

        $this->assertCount(0, $oResult->getCuts());
        $this->assertCount(0, $oResult->getFragments());
        $this->assertNotEmpty($oResult->getWarnings());
    }

    public function testRejectsAnEnzymesArrayContainingSomethingElse()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->manager->digest($this->makePlasmid("AAAAAAAAAA"), ["not an enzyme"]);
    }
}
