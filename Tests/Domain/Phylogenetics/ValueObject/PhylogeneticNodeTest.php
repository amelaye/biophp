<?php
namespace Tests\Domain\Phylogenetics\ValueObject;

use Amelaye\BioPHP\Domain\Phylogenetics\Exception\InvalidPhylogeneticTreeException;
use Amelaye\BioPHP\Domain\Phylogenetics\ValueObject\PhylogeneticNode;
use PHPUnit\Framework\TestCase;

class PhylogeneticNodeTest extends TestCase
{
    public function testALoneLeafExposesItsNameAndIsALeaf()
    {
        $oLeaf = new PhylogeneticNode("A", null, []);

        $this->assertEquals("A", $oLeaf->getName());
        $this->assertNull($oLeaf->getBranchLength());
        $this->assertTrue($oLeaf->isLeaf());
        $this->assertEquals(["A"], $oLeaf->getLeafNames());
        $this->assertEquals("A;", $oLeaf->toNewick());
    }

    public function testRejectsALeafWithNoName()
    {
        $this->expectException(InvalidPhylogeneticTreeException::class);
        $this->expectExceptionMessage("must have a non-empty name");

        new PhylogeneticNode(null, null, []);
    }

    public function testRejectsAChildThatIsNotAPhylogeneticNode()
    {
        $this->expectException(InvalidPhylogeneticTreeException::class);

        /** @noinspection PhpParamsInspection */
        new PhylogeneticNode(null, null, ["not a node"]);
    }

    public function testGetLeafNamesCollectsThemDepthFirstAcrossSeveralLevels()
    {
        $oA = new PhylogeneticNode("A", 1.0, []);
        $oB = new PhylogeneticNode("B", 2.0, []);
        $oInner = new PhylogeneticNode(null, 1.0, [$oA, $oB]);
        $oC = new PhylogeneticNode("C", 3.0, []);
        $oRoot = new PhylogeneticNode(null, null, [$oInner, $oC]);

        $this->assertFalse($oRoot->isLeaf());
        $this->assertEquals(["A", "B", "C"], $oRoot->getLeafNames());
    }

    public function testToNewickSerializesAMultiLevelTreeWithBranchLengths()
    {
        $oA = new PhylogeneticNode("A", 1.0, []);
        $oB = new PhylogeneticNode("B", 2.0, []);
        $oInner = new PhylogeneticNode(null, 1.0, [$oA, $oB]);
        $oC = new PhylogeneticNode("C", 3.0, []);
        $oD = new PhylogeneticNode("D", 4.0, []);
        $oRoot = new PhylogeneticNode(null, null, [$oInner, $oC, $oD]);

        $this->assertEquals("((A:1,B:2):1,C:3,D:4);", $oRoot->toNewick());
    }

    public function testToNewickOmitsBranchLengthWhenNullAndNameWhenUnnamed()
    {
        $oA = new PhylogeneticNode("A", null, []);
        $oB = new PhylogeneticNode("B", null, []);
        $oRoot = new PhylogeneticNode(null, null, [$oA, $oB]);

        $this->assertEquals("(A,B);", $oRoot->toNewick());
    }

    /**
     * Negative branch lengths are a known, legitimate neighbor-joining artifact (see
     * NeighborJoiningTreeBuilder's docblock) and must round-trip like any other value.
     */
    public function testANegativeBranchLengthIsAcceptedAndSerialized()
    {
        $oLeaf = new PhylogeneticNode("A", -0.5, []);

        $this->assertEquals(-0.5, $oLeaf->getBranchLength());
        $this->assertEquals("A:-0.5;", $oLeaf->toNewick());
    }
}
