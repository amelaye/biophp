<?php
namespace Tests\Domain\Cloning\Service;

use Amelaye\BioPHP\Domain\Cloning\Service\GibsonAssemblyManager;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;
use PHPUnit\Framework\TestCase;

/**
 * The main fixture (upstream ending "...AGCTTGGA", downstream starting "AGCTTGGA...") and its
 * designed homology arms were computed by hand and cross-checked with a standalone PHP script before
 * being written here, per the project's rule for every biology-bearing fixture.
 */
class GibsonAssemblyManagerTest extends TestCase
{
    private $manager;

    public function setUp(): void
    {
        $this->manager = new GibsonAssemblyManager();
    }

    public function testFindsTheLongestExactOverlapWithinRange()
    {
        $oUpstream = new DnaSequence("TTTTTAGCTTGGA");
        $oDownstream = new DnaSequence("AGCTTGGACCCCC");

        $oResult = $this->manager->checkJunction($oUpstream, $oDownstream, 5, 12);

        $this->assertTrue($oResult->hasOverlap());
        $this->assertEquals(8, $oResult->getOverlapLength());
        $this->assertEquals("AGCTTGGA", $oResult->getOverlapSequence());
    }

    public function testReportsNoOverlapWhenNoneExistsRatherThanThrowing()
    {
        $oResult = $this->manager->checkJunction(
            new DnaSequence("AAAAAAAAAA"),
            new DnaSequence("CCCCCCCCCC"),
            5,
            8
        );

        $this->assertFalse($oResult->hasOverlap());
        $this->assertEquals(0, $oResult->getOverlapLength());
        $this->assertNull($oResult->getOverlapSequence());
    }

    public function testStopsSearchingAtTheConfiguredMaximum()
    {
        // Verified by a standalone script across every k from 1 to 8: the two fragments' longest
        // exact suffix/prefix match is actually 8 ("ACGTACGT"), but within [3,6] only k=4 matches
        // (k=5 and k=6 do not) - capping the search must report that 4, not the true longest match
        // that only appears once the range is widened past the configured maximum.
        $oUpstream = new DnaSequence("AAAAACGTACGTACGT");
        $oDownstream = new DnaSequence("ACGTACGTACGTTTTT");

        $oResult = $this->manager->checkJunction($oUpstream, $oDownstream, 3, 6);

        $this->assertEquals(4, $oResult->getOverlapLength());
        $this->assertEquals("ACGT", $oResult->getOverlapSequence());
    }

    public function testRejectsAMinimumOverlapBelowOne()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->manager->checkJunction(new DnaSequence("ACGT"), new DnaSequence("ACGT"), 0, 4);
    }

    public function testRejectsAMaximumOverlapBelowTheMinimum()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->manager->checkJunction(new DnaSequence("ACGT"), new DnaSequence("ACGT"), 5, 4);
    }

    public function testDesignsHomologyArmsFromTheFragmentEnds()
    {
        $oUpstream = new DnaSequence("TTTTTAGCTTGGA");
        $oDownstream = new DnaSequence("AGCTTGGACCCCC");

        $oArms = $this->manager->designHomologyArms($oUpstream, $oDownstream, 8);

        $this->assertEquals("AGCTTGGA", $oArms->getDownstreamForwardPrimerTail());
        $this->assertEquals("TCCAAGCT", $oArms->getUpstreamReversePrimerTail());
        $this->assertEquals(8, $oArms->getOverlapLength());
    }

    public function testRejectsAnOverlapLengthExceedingTheUpstreamFragment()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("upstream");

        $this->manager->designHomologyArms(new DnaSequence("ACGT"), new DnaSequence("ACGTACGTACGT"), 5);
    }

    public function testRejectsAnOverlapLengthExceedingTheDownstreamFragment()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("downstream");

        $this->manager->designHomologyArms(new DnaSequence("ACGTACGTACGT"), new DnaSequence("ACGT"), 5);
    }
}
