<?php
namespace Tests\Domain\Phylogenetics\Service;

use Amelaye\BioPHP\Domain\Phylogenetics\Service\NeighborJoiningTreeBuilder;
use Amelaye\BioPHP\Domain\Phylogenetics\Service\NewickReader;
use Amelaye\BioPHP\Domain\Phylogenetics\Service\UpgmaTreeBuilder;
use Amelaye\BioPHP\Domain\Phylogenetics\ValueObject\DistanceMatrix;
use Amelaye\BioPHP\Domain\Phylogenetics\ValueObject\PhylogeneticNode;
use PHPUnit\Framework\TestCase;

/**
 * What a tree builder must keep whatever the data : neighbour-joining gives back the tree a
 * distance matrix was made from when that matrix is additive, UPGMA draws every leaf at the same
 * distance from the root, and a tree written as Newick is read back as it was.
 */
class TreePropertiesTest extends TestCase
{
    /**
     * @param   PhylogeneticNode    $oNode
     * @param   float               $fDepth     The length from the root down to this node's parent
     * @param   array               $aDepths    Filled with leaf name => length from the root
     */
    private static function leafDepths(PhylogeneticNode $oNode, float $fDepth, array &$aDepths): void
    {
        $fHere = $fDepth + ($oNode->getBranchLength() ?? 0.0);
        if ($oNode->isLeaf()) {
            $aDepths[$oNode->getName()] = $fHere;
            return;
        }
        foreach ($oNode->getChildren() as $oChild) {
            self::leafDepths($oChild, $fHere, $aDepths);
        }
    }

    /**
     * @return  float   The length of the path between two leaves
     */
    private static function pathLength(PhylogeneticNode $oRoot, string $sFirst, string $sSecond): float
    {
        // length of the path from the root to each leaf, node by node
        $aPaths = [];
        $fWalk = function (PhylogeneticNode $oNode, array $aChain, float $fDepth) use (&$fWalk, &$aPaths) {
            $fDepth += $oNode->getBranchLength() ?? 0.0;
            $aChain[] = [spl_object_id($oNode), $fDepth];
            if ($oNode->isLeaf()) {
                $aPaths[$oNode->getName()] = $aChain;
                return;
            }
            foreach ($oNode->getChildren() as $oChild) {
                $fWalk($oChild, $aChain, $fDepth);
            }
        };
        $fWalk($oRoot, [], 0.0);

        // the deepest node the two chains share is their last common ancestor
        $fCommon = 0.0;
        foreach ($aPaths[$sFirst] as $i => [$iId, $fDepth]) {
            if (isset($aPaths[$sSecond][$i]) && $aPaths[$sSecond][$i][0] === $iId) {
                $fCommon = $fDepth;
            }
        }
        $fFirst = end($aPaths[$sFirst])[1];
        $fSecond = end($aPaths[$sSecond])[1];

        return ($fFirst - $fCommon) + ($fSecond - $fCommon);
    }

    /**
     * An unrooted tree of five leaves (A, B on u ; C on w ; D, E on v ; u-w-v), its branch lengths
     * chosen by hand : the distance matrix is the sum of the branches between two leaves.
     */
    public function testNeighborJoiningRecoversTheTreeOfAnAdditiveMatrix()
    {
        $aEdges = ["A-u" => 2, "B-u" => 3, "u-w" => 4, "C-w" => 1, "w-v" => 2, "D-v" => 5, "E-v" => 6];
        $aNodes = ["A", "B", "C", "D", "E", "u", "v", "w"];
        $aDistance = [];
        foreach ($aNodes as $sFrom) {
            foreach ($aNodes as $sTo) {
                $aDistance[$sFrom][$sTo] = $sFrom === $sTo ? 0 : INF;
            }
        }
        foreach ($aEdges as $sEdge => $fLength) {
            [$sFrom, $sTo] = explode("-", $sEdge);
            $aDistance[$sFrom][$sTo] = $aDistance[$sTo][$sFrom] = $fLength;
        }
        foreach ($aNodes as $sVia) {
            foreach ($aNodes as $sFrom) {
                foreach ($aNodes as $sTo) {
                    $aDistance[$sFrom][$sTo] = min($aDistance[$sFrom][$sTo], $aDistance[$sFrom][$sVia] + $aDistance[$sVia][$sTo]);
                }
            }
        }
        $aLeaves = ["A", "B", "C", "D", "E"];
        $aRows = [];
        foreach ($aLeaves as $sFrom) {
            $aRows[] = array_map(fn($sTo) => $aDistance[$sFrom][$sTo], $aLeaves);
        }

        $oTree = (new NeighborJoiningTreeBuilder())->build(new DistanceMatrix($aLeaves, $aRows));

        foreach ($aLeaves as $sFrom) {
            foreach ($aLeaves as $sTo) {
                if ($sFrom < $sTo) {
                    $this->assertEqualsWithDelta($aDistance[$sFrom][$sTo], self::pathLength($oTree, $sFrom, $sTo), 1e-9, "$sFrom-$sTo");
                }
            }
        }
    }

    public function testUpgmaDrawsEveryLeafAtTheSameDistanceFromTheRoot()
    {
        mt_srand(20261009);
        for ($iRun = 0; $iRun < 10; $iRun++) {
            $iSize = mt_rand(3, 8);
            $aLabels = array_map(fn($i) => "T" . $i, range(1, $iSize));
            $aRows = array_fill(0, $iSize, array_fill(0, $iSize, 0.0));
            for ($i = 0; $i < $iSize; $i++) {
                for ($j = $i + 1; $j < $iSize; $j++) {
                    $aRows[$i][$j] = $aRows[$j][$i] = mt_rand(1, 100) / 4;
                }
            }

            $aDepths = [];
            self::leafDepths((new UpgmaTreeBuilder())->build(new DistanceMatrix($aLabels, $aRows)), 0.0, $aDepths);

            $this->assertCount($iSize, $aDepths);
            foreach ($aDepths as $sName => $fDepth) {
                $this->assertEqualsWithDelta(reset($aDepths), $fDepth, 1e-9, "run $iRun, $sName");
            }
        }
    }

    public function testATreeWrittenAsNewickIsReadBackAsItWas()
    {
        $oTree = new PhylogeneticNode("root", null, [
            new PhylogeneticNode("Homo sapiens", 0.1, []),
            new PhylogeneticNode("it's_ok", 1.5e-7, []),
            new PhylogeneticNode(null, 2.5, [
                new PhylogeneticNode("A_b", 1e-10, []),
                new PhylogeneticNode("x(y)", 3.0, []),
                new PhylogeneticNode("a,b;c:d", 0.0, []),
            ]),
            new PhylogeneticNode("big", 12345678.9, []),
        ]);

        $sNewick = $oTree->toNewick();
        $oRead = (new NewickReader())->read($sNewick);

        $this->assertSame($sNewick, $oRead->toNewick());
        $this->assertEquals($oTree, $oRead);
        $this->assertSame(["Homo sapiens", "it's_ok", "A_b", "x(y)", "a,b;c:d", "big"], $oRead->getLeafNames());
    }

    public function testATreeBuiltFromAMatrixSurvivesTheNewickRoundTrip()
    {
        $oMatrix = new DistanceMatrix(
            ["a", "b", "c", "d"],
            [[0, 5.5, 9, 9.25], [5.5, 0, 10, 10.125], [9, 10, 0, 8.0625], [9.25, 10.125, 8.0625, 0]]
        );

        foreach ([new UpgmaTreeBuilder(), new NeighborJoiningTreeBuilder()] as $oBuilder) {
            $oTree = $oBuilder->build($oMatrix);
            $this->assertEquals($oTree, (new NewickReader())->read($oTree->toNewick()), get_class($oBuilder));
        }
    }
}
