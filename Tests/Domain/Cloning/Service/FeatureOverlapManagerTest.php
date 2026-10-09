<?php
namespace Tests\Domain\Cloning\Service;

use Amelaye\BioPHP\Domain\Cloning\Service\FeatureOverlapManager;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\FeatureType;
use Amelaye\BioPHP\Domain\Cloning\Aggregate\Plasmid;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\PlasmidFeature;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\CircularDnaSequence;
use PHPUnit\Framework\TestCase;

class FeatureOverlapManagerTest extends TestCase
{
    private $manager;

    public function setUp(): void
    {
        $this->manager = new FeatureOverlapManager();
    }

    private function feature(string $sName, int $iStart, int $iEnd): PlasmidFeature
    {
        return new PlasmidFeature($sName, FeatureType::MISC_FEATURE, $iStart, $iEnd);
    }

    public function testTwoOrdinaryOverlappingFeaturesOverlap()
    {
        $oA = $this->feature("A", 1, 10);
        $oB = $this->feature("B", 8, 15);

        $this->assertTrue($this->manager->overlaps($oA, $oB, 20));
    }

    public function testTwoOrdinaryDisjointFeaturesDoNotOverlap()
    {
        $oA = $this->feature("A", 1, 5);
        $oB = $this->feature("B", 10, 15);

        $this->assertFalse($this->manager->overlaps($oA, $oB, 20));
    }

    public function testFeaturesTouchingAtASingleBaseOverlap()
    {
        $oA = $this->feature("A", 1, 10);
        $oB = $this->feature("B", 10, 15);

        $this->assertTrue($this->manager->overlaps($oA, $oB, 20));
    }

    /**
     * D crosses the origin (18..3 on a 20 bp plasmid), unwrapping to [18,20] and [1,3] : it must be
     * detected as overlapping a feature sitting entirely in the wrapped-around [1,3] region, even
     * though D's own start (18) is numerically far from that feature's coordinates.
     */
    public function testAnOriginCrossingFeatureOverlapsAFeatureInItsWrappedRegion()
    {
        $oD = $this->feature("D", 18, 3);
        $oE = $this->feature("E", 1, 2);

        $this->assertTrue($this->manager->overlaps($oD, $oE, 20));
    }

    public function testAnOriginCrossingFeatureDoesNotOverlapAFeatureStrictlyInTheMiddle()
    {
        $oD = $this->feature("D", 18, 3);
        $oF = $this->feature("F", 8, 12);

        $this->assertFalse($this->manager->overlaps($oD, $oF, 20));
    }

    public function testContainsPositionForAnOrdinaryFeature()
    {
        $oA = $this->feature("A", 5, 10);

        $this->assertTrue($this->manager->containsPosition($oA, 5, 20));
        $this->assertTrue($this->manager->containsPosition($oA, 10, 20));
        $this->assertFalse($this->manager->containsPosition($oA, 4, 20));
        $this->assertFalse($this->manager->containsPosition($oA, 11, 20));
    }

    public function testContainsPositionForAnOriginCrossingFeature()
    {
        $oD = $this->feature("D", 18, 3);

        $this->assertTrue($this->manager->containsPosition($oD, 19, 20));
        $this->assertTrue($this->manager->containsPosition($oD, 2, 20));
        $this->assertFalse($this->manager->containsPosition($oD, 10, 20));
    }

    public function testFindOverlappingExcludesTheTargetItselfAndNonOverlappingCandidates()
    {
        $oA = $this->feature("A", 1, 10);
        $oB = $this->feature("B", 8, 15);
        $oC = $this->feature("C", 16, 20);

        $aFound = $this->manager->findOverlapping($oA, [$oA, $oB, $oC], 20);

        $this->assertCount(1, $aFound);
        $this->assertSame($oB, $aFound[0]);
    }

    public function testFindFeaturesAtPositionOnAPlasmid()
    {
        $oA = $this->feature("A", 1, 10);
        $oB = $this->feature("B", 8, 15);
        $oC = $this->feature("C", 16, 20);
        $oPlasmid = new Plasmid("p1", new CircularDnaSequence(str_repeat("A", 20)), [$oA, $oB, $oC]);

        $aFound = $this->manager->findFeaturesAtPosition($oPlasmid, 9);

        $this->assertCount(2, $aFound);
        $this->assertSame($oA, $aFound[0]);
        $this->assertSame($oB, $aFound[1]);
    }

    public function testFindOverlappingPairsOnAPlasmid()
    {
        $oA = $this->feature("A", 1, 10);
        $oB = $this->feature("B", 8, 15);
        $oC = $this->feature("C", 16, 20);
        $oPlasmid = new Plasmid("p1", new CircularDnaSequence(str_repeat("A", 20)), [$oA, $oB, $oC]);

        $aPairs = $this->manager->findOverlappingPairs($oPlasmid);

        $this->assertCount(1, $aPairs);
        $this->assertSame($oA, $aPairs[0]->getFirst());
        $this->assertSame($oB, $aPairs[0]->getSecond());
    }

    public function testFindOverlappingPairsReturnsEmptyWhenNoFeatureOverlaps()
    {
        $oA = $this->feature("A", 1, 5);
        $oB = $this->feature("B", 10, 15);
        $oPlasmid = new Plasmid("p1", new CircularDnaSequence(str_repeat("A", 20)), [$oA, $oB]);

        $this->assertCount(0, $this->manager->findOverlappingPairs($oPlasmid));
    }
}
