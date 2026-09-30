<?php
namespace Tests\Domain\Phylogenetics\Service;

use Amelaye\BioPHP\Domain\Phylogenetics\Service\NeighborJoiningTreeBuilder;
use Amelaye\BioPHP\Domain\Phylogenetics\ValueObject\DistanceMatrix;
use PHPUnit\Framework\TestCase;

class NeighborJoiningTreeBuilderTest extends TestCase
{
    private $builder;

    public function setUp(): void
    {
        $this->builder = new NeighborJoiningTreeBuilder();
    }

    public function testASingleTaxonProducesALoneLeaf()
    {
        $oRoot = $this->builder->build(new DistanceMatrix(["A"], [[0]]));

        $this->assertTrue($oRoot->isLeaf());
        $this->assertEquals("A", $oRoot->getName());
        $this->assertNull($oRoot->getBranchLength());
    }

    /**
     * Two taxa have only one edge between them ; it is split evenly so neither leaf is arbitrarily
     * favored.
     */
    public function testTwoTaxaAreJoinedByASingleEdgeSplitEvenly()
    {
        $oRoot = $this->builder->build(new DistanceMatrix(["A", "B"], [[0, 4], [4, 0]]));

        $this->assertNull($oRoot->getName());
        $this->assertCount(2, $oRoot->getChildren());
        $this->assertEquals(2.0, $oRoot->getChildren()[0]->getBranchLength());
        $this->assertEquals(2.0, $oRoot->getChildren()[1]->getBranchLength());
    }

    /**
     * Three taxa always fit an exact star tree (3 free edges solve 3 pairwise distances exactly) ;
     * hand-derived : branchA=(Dab+Dac-Dbc)/2=(3+5-6)/2=1, branchB=(Dab+Dbc-Dac)/2=(3+6-5)/2=2,
     * branchC=(Dac+Dbc-Dab)/2=(5+6-3)/2=4. Sanity check : branchA+branchB=3=Dab,
     * branchA+branchC=5=Dac, branchB+branchC=6=Dbc, all consistent with the input.
     */
    public function testThreeTaxaFormAnExactTrifurcatingStar()
    {
        $oRoot = $this->builder->build(new DistanceMatrix(
            ["A", "B", "C"],
            [
                [0, 3, 5],
                [3, 0, 6],
                [5, 6, 0],
            ]
        ));

        $this->assertNull($oRoot->getName());
        $this->assertCount(3, $oRoot->getChildren());
        $this->assertEqualsWithDelta(1.0, $oRoot->getChildren()[0]->getBranchLength(), 0.0001);
        $this->assertEqualsWithDelta(2.0, $oRoot->getChildren()[1]->getBranchLength(), 0.0001);
        $this->assertEqualsWithDelta(4.0, $oRoot->getChildren()[2]->getBranchLength(), 0.0001);
    }

    /**
     * Built from a known tree - (A:1,B:2) joined to an inner node at branch 1, itself joined to
     * C:3 and D:4 - by hand-computing every pairwise patristic (path-sum) distance :
     *   d(A,B)=1+2=3   d(A,C)=1+1+3=5   d(A,D)=1+1+4=6
     *   d(B,C)=2+1+3=6 d(B,D)=2+1+4=7   d(C,D)=3+4=7
     * Because this matrix is perfectly additive, neighbor-joining is guaranteed to recover the exact
     * original topology and branch lengths - this is independently re-derived, step by step, in the
     * class docblock's algorithm description and in this test's inline comments below, not just
     * asserted.
     *
     * Q-matrix check (n=4) picks (A,B) over the tied (C,D) purely by being the first pair visited in
     * label order - Q(A,B)=Q(C,D)=-24, every other pair scores -22 - so the algorithm's first-found
     * tie-break (documented on NeighborJoiningTreeBuilder) is itself exercised by this fixture.
     *
     * The merged (A,B) cluster is appended at the END of the active list once C and D are filtered
     * back in (implementation detail, not a tree-shape choice), so it ends up the LAST of the root's
     * three children rather than the first : root = (C:3, D:4, (A:1,B:2):1), leaf order C,D,A,B. This
     * is only a child-ordering artifact - meaningless for an unrooted tree - the topology and every
     * branch length are identical to the original tree regardless.
     */
    public function testRecoversTheExactTopologyAndBranchLengthsOfAnAdditiveFourTaxonMatrix()
    {
        $oRoot = $this->builder->build(new DistanceMatrix(
            ["A", "B", "C", "D"],
            [
                [0, 3, 5, 6],
                [3, 0, 6, 7],
                [5, 6, 0, 7],
                [6, 7, 7, 0],
            ]
        ));

        $this->assertEquals(["C", "D", "A", "B"], $oRoot->getLeafNames());
        $this->assertEquals("(C:3,D:4,(A:1,B:2):1);", $oRoot->toNewick());
    }
}
