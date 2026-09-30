<?php
namespace Tests\Domain\Phylogenetics\Service;

use Amelaye\BioPHP\Domain\Phylogenetics\Exception\InvalidNewickException;
use Amelaye\BioPHP\Domain\Phylogenetics\Service\NewickReader;
use PHPUnit\Framework\TestCase;

class NewickReaderTest extends TestCase
{
    private $reader;

    public function setUp(): void
    {
        $this->reader = new NewickReader();
    }

    public function testParsesALoneLeaf()
    {
        $oRoot = $this->reader->read("A;");

        $this->assertTrue($oRoot->isLeaf());
        $this->assertEquals("A", $oRoot->getName());
        $this->assertNull($oRoot->getBranchLength());
    }

    public function testParsesALeafWithABranchLength()
    {
        $oRoot = $this->reader->read("A:1.5;");

        $this->assertEquals("A", $oRoot->getName());
        $this->assertEqualsWithDelta(1.5, $oRoot->getBranchLength(), 0.0001);
    }

    public function testParsesATwoLeafTree()
    {
        $oRoot = $this->reader->read("(A:1,B:2);");

        $this->assertFalse($oRoot->isLeaf());
        $this->assertNull($oRoot->getName());
        $this->assertCount(2, $oRoot->getChildren());
        $this->assertEquals("A", $oRoot->getChildren()[0]->getName());
        $this->assertEquals(1.0, $oRoot->getChildren()[0]->getBranchLength());
        $this->assertEquals("B", $oRoot->getChildren()[1]->getName());
        $this->assertEquals(2.0, $oRoot->getChildren()[1]->getBranchLength());
    }

    public function testParsesATreeWithoutAnyBranchLength()
    {
        $oRoot = $this->reader->read("(A,B,C);");

        $this->assertEquals(["A", "B", "C"], $oRoot->getLeafNames());
        $this->assertNull($oRoot->getChildren()[0]->getBranchLength());
    }

    /**
     * The original hand-built tree NeighborJoiningTreeBuilderTest's additive fixture was derived
     * from (same topology and branch lengths, just written with (A,B)'s clade first rather than
     * last - child order is not meaningful for an unrooted tree). Confirms reader and writer agree
     * on the same tree shape.
     */
    public function testParsesANestedTreeMatchingTheNeighborJoiningFixture()
    {
        $oRoot = $this->reader->read("((A:1,B:2):1,C:3,D:4);");

        $this->assertEquals(["A", "B", "C", "D"], $oRoot->getLeafNames());
        $this->assertCount(3, $oRoot->getChildren());

        $oInner = $oRoot->getChildren()[0];
        $this->assertEquals(1.0, $oInner->getBranchLength());
        $this->assertEquals("A", $oInner->getChildren()[0]->getName());
        $this->assertEquals(1.0, $oInner->getChildren()[0]->getBranchLength());
        $this->assertEquals("B", $oInner->getChildren()[1]->getName());
        $this->assertEquals(2.0, $oInner->getChildren()[1]->getBranchLength());

        $this->assertEquals("C", $oRoot->getChildren()[1]->getName());
        $this->assertEquals(3.0, $oRoot->getChildren()[1]->getBranchLength());
        $this->assertEquals("D", $oRoot->getChildren()[2]->getName());
        $this->assertEquals(4.0, $oRoot->getChildren()[2]->getBranchLength());

        $this->assertEquals("((A:1,B:2):1,C:3,D:4);", $oRoot->toNewick());
    }

    public function testRejectsAnEmptyString()
    {
        $this->expectException(InvalidNewickException::class);
        $this->expectExceptionMessage("must not be empty");

        $this->reader->read("   ");
    }

    public function testRejectsAStringNotEndingWithASemicolon()
    {
        $this->expectException(InvalidNewickException::class);
        $this->expectExceptionMessage('must end with ";"');

        $this->reader->read("(A,B)");
    }

    public function testRejectsUnbalancedParentheses()
    {
        $this->expectException(InvalidNewickException::class);
        $this->expectExceptionMessage("Unbalanced parentheses");

        $this->reader->read("(A,B;");
    }

    public function testRejectsAnInvalidBranchLength()
    {
        $this->expectException(InvalidNewickException::class);
        $this->expectExceptionMessage('Invalid branch length "x"');

        $this->reader->read("A:x;");
    }

    public function testRejectsTrailingContentAfterTheRootSubtree()
    {
        $this->expectException(InvalidNewickException::class);
        $this->expectExceptionMessage("trailing content");

        $this->reader->read("(A,B))C;");
    }
}
