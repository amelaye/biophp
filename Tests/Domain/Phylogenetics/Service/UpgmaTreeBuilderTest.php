<?php
namespace Tests\Domain\Phylogenetics\Service;

use Amelaye\BioPHP\Domain\Phylogenetics\Service\UpgmaTreeBuilder;
use Amelaye\BioPHP\Domain\Phylogenetics\ValueObject\DistanceMatrix;
use PHPUnit\Framework\TestCase;

class UpgmaTreeBuilderTest extends TestCase
{
    private $builder;

    public function setUp(): void
    {
        $this->builder = new UpgmaTreeBuilder();
    }

    public function testASingleTaxonProducesALoneLeaf()
    {
        $oRoot = $this->builder->build(new DistanceMatrix(["A"], [[0]]));

        $this->assertTrue($oRoot->isLeaf());
        $this->assertEquals("A", $oRoot->getName());
        $this->assertNull($oRoot->getBranchLength());
    }

    /**
     * Two taxa merge directly at height = distance/2, so each branch length is also distance/2.
     */
    public function testTwoTaxaMeetAtHalfTheirDistance()
    {
        $oRoot = $this->builder->build(new DistanceMatrix(["A", "B"], [[0, 10], [10, 0]]));

        $this->assertNull($oRoot->getName());
        $this->assertCount(2, $oRoot->getChildren());
        $this->assertEquals(5.0, $oRoot->getChildren()[0]->getBranchLength());
        $this->assertEquals(5.0, $oRoot->getChildren()[1]->getBranchLength());
    }

    /**
     * Three taxa, using the a/b/c corner of the 5-taxon fixture below : d(a,b)=17, d(a,c)=21,
     * d(b,c)=30. Hand-derived (and cross-checked with a standalone script before being written
     * here) : merge a,b first (smallest distance, height=8.5, both branches 8.5) ; the merged
     * cluster's distance to c is the SIZE-WEIGHTED average - here both a and b have size 1, so it's
     * simply (21+30)/2=25.5 ; final merge at height=25.5/2=12.75, branch for the (a,b) cluster =
     * 12.75-8.5=4.25, branch for c = 12.75-0=12.75. The newly merged (a,b) cluster is appended at
     * the END of the active list (implementation detail, not a tree-shape choice - see
     * NeighborJoiningTreeBuilderTest for the same artifact), so the final root's first child is c,
     * not the (a,b) cluster ; meaningless ordering for a rooted tree, the heights are what matter.
     */
    public function testThreeTaxaMirrorTheFirstTwoMergesOfTheFiveTaxonFixture()
    {
        $oRoot = $this->builder->build(new DistanceMatrix(
            ["a", "b", "c"],
            [
                [0, 17, 21],
                [17, 0, 30],
                [21, 30, 0],
            ]
        ));

        $this->assertEquals(["c", "a", "b"], $oRoot->getLeafNames());

        $this->assertEquals("c", $oRoot->getChildren()[0]->getName());
        $this->assertEqualsWithDelta(12.75, $oRoot->getChildren()[0]->getBranchLength(), 0.0001);

        $oAbCluster = $oRoot->getChildren()[1];
        $this->assertEqualsWithDelta(4.25, $oAbCluster->getBranchLength(), 0.0001);
        $this->assertEqualsWithDelta(8.5, $oAbCluster->getChildren()[0]->getBranchLength(), 0.0001);
        $this->assertEqualsWithDelta(8.5, $oAbCluster->getChildren()[1]->getBranchLength(), 0.0001);
    }

    /**
     * The classic 5-taxon UPGMA textbook example (a,b,c,d,e). Hand-derived step by step, and
     * independently cross-checked with a standalone script implementing the same size-weighted
     * algorithm before being written here :
     *   1. merge (a,b) : d=17, height=8.5, both branches 8.5
     *   2. d((a,b),c)=(21+30)/2=25.5, d((a,b),d)=(31+34)/2=32.5, d((a,b),e)=(23+21)/2=22
     *      -> merge (a,b) with e : d=22, height=11, branch (a,b)=11-8.5=2.5, branch e=11-0=11
     *   3. d(((a,b)e),c)=(2*25.5+1*39)/3=30, d(((a,b)e),d)=(2*32.5+1*43)/3=36, d(c,d)=28 unchanged
     *      -> merge c,d : d=28, height=14, both branches 14
     *   4. d((c,d),((a,b)e)) = (1*30+1*36)/2=33 -> final merge : height=16.5,
     *      branch ((a,b)e)=16.5-11=5.5, branch (c,d)=16.5-14=2.5
     * This is genuine, size-weighted UPGMA - the deliberate correction UpgmaTreeBuilder's own
     * docblock documents over biotools' legacy simple-average (WPGMA-style) implementation ; this
     * fixture's step 3 (weights 2 and 1, not a 50/50 average) is exactly where the two algorithms
     * would diverge, so it specifically exercises the corrected behavior. Child order within each
     * cluster follows the same append-at-the-end-of-the-active-list artifact documented on the
     * 3-taxon test above (e.g. "e" ends up listed before its "(a,b)" sibling) - meaningless for a
     * rooted tree ; the heights (8.5, 11, 14, 16.5) are the actual hand-derived, cross-checked
     * result this test verifies.
     */
    public function testRecoversTheClassicFiveTaxonUpgmaTree()
    {
        $oRoot = $this->builder->build(new DistanceMatrix(
            ["a", "b", "c", "d", "e"],
            [
                [0, 17, 21, 31, 23],
                [17, 0, 30, 34, 21],
                [21, 30, 0, 28, 39],
                [31, 34, 28, 0, 43],
                [23, 21, 39, 43, 0],
            ]
        ));

        $this->assertEquals("((e:11,(a:8.5,b:8.5):2.5):5.5,(c:14,d:14):2.5);", $oRoot->toNewick());
    }
}
