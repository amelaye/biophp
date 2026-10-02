<?php
/**
 * Builds an unrooted phylogenetic tree from a distance matrix via neighbor-joining
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Phylogenetics\Service;

use Amelaye\BioPHP\Domain\Phylogenetics\Interfaces\NeighborJoiningInterface;
use Amelaye\BioPHP\Domain\Phylogenetics\ValueObject\DistanceMatrix;
use Amelaye\BioPHP\Domain\Phylogenetics\ValueObject\PhylogeneticNode;

/**
 * Implements the classic Saitou & Nei (1987) algorithm. At each step, among the currently active
 * clusters, it joins the pair (i, j) minimizing Q(i,j) = (n-2)*D(i,j) - r_i - r_j, where r_i is the
 * sum of i's distances to every other active cluster ; this is what makes neighbor-joining prefer a
 * pair that is close to each other AND far from everything else, rather than simply the closest pair
 * (which is what UPGMA does, and which assumes a molecular clock this algorithm does not need). Ties
 * in Q keep the first pair encountered, in (label) iteration order - an arbitrary but deterministic
 * choice, the same kind already made by this project's alignment tie-breaking.
 *
 * The reduction stops at exactly 3 active clusters rather than 2, because the classic pairwise
 * branch-length formula needs an r_i computed over at least one OTHER active cluster besides the
 * pair being joined ; with only 2 remaining there is none. The last 3 clusters are instead resolved
 * directly into an unrooted, trifurcating root - the natural, un-arbitrary representation of an
 * unrooted tree's last split, and the standard way a neighbor-joining tree is reported. A
 * DistanceMatrix of exactly 1 or 2 taxa is handled as its own trivial case, needing no iteration at
 * all.
 *
 * On a distance matrix that is perfectly additive (exactly fits some tree), neighbor-joining is
 * guaranteed to recover that tree's exact topology and branch lengths - this is the property this
 * class's regression test fixture is built on and hand-verified against.
 * Class NeighborJoiningTreeBuilder
 * @package Amelaye\BioPHP\Domain\Phylogenetics\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class NeighborJoiningTreeBuilder implements NeighborJoiningInterface
{
    /**
     * @param   DistanceMatrix      $oMatrix
     * @return  PhylogeneticNode
     */
    public function build(DistanceMatrix $oMatrix): PhylogeneticNode
    {
        $aLabels = $oMatrix->getLabels();
        $iTaxaCount = count($aLabels);

        if ($iTaxaCount === 1) {
            return new PhylogeneticNode($aLabels[0], null, []);
        }

        $aRawMatrix = $oMatrix->getMatrix();

        /** @var array<int,array{name: string|null, children: PhylogeneticNode[]}> $aPending */
        $aPending = [];
        $aActive = [];
        $aDistance = [];

        for ($i = 0; $i < $iTaxaCount; $i++) {
            $aPending[$i] = ["name" => $aLabels[$i], "children" => []];
            $aActive[] = $i;
            $aDistance[$i] = $aRawMatrix[$i];
        }

        $iNextId = $iTaxaCount;

        while (count($aActive) > 3) {
            $iActiveCount = count($aActive);

            $aTotal = [];
            foreach ($aActive as $iId) {
                $fSum = 0.0;
                foreach ($aActive as $iOtherId) {
                    if ($iOtherId !== $iId) {
                        $fSum += $aDistance[$iId][$iOtherId];
                    }
                }
                $aTotal[$iId] = $fSum;
            }

            $iBestP = 0;
            $iBestQ = 1;
            $fBestScore = null;

            for ($p = 0; $p < $iActiveCount; $p++) {
                for ($q = $p + 1; $q < $iActiveCount; $q++) {
                    $iId1 = $aActive[$p];
                    $iId2 = $aActive[$q];
                    $fScore = ($iActiveCount - 2) * $aDistance[$iId1][$iId2] - $aTotal[$iId1] - $aTotal[$iId2];

                    if ($fBestScore === null || $fScore < $fBestScore) {
                        $fBestScore = $fScore;
                        $iBestP = $p;
                        $iBestQ = $q;
                    }
                }
            }

            $iIdA = $aActive[$iBestP];
            $iIdB = $aActive[$iBestQ];
            $fDab = $aDistance[$iIdA][$iIdB];

            $fBranchA = 0.5 * $fDab + ($aTotal[$iIdA] - $aTotal[$iIdB]) / (2 * ($iActiveCount - 2));
            $fBranchB = $fDab - $fBranchA;

            $oNodeA = new PhylogeneticNode($aPending[$iIdA]["name"], $fBranchA, $aPending[$iIdA]["children"]);
            $oNodeB = new PhylogeneticNode($aPending[$iIdB]["name"], $fBranchB, $aPending[$iIdB]["children"]);

            $iNewId = $iNextId++;
            $aPending[$iNewId] = ["name" => null, "children" => [$oNodeA, $oNodeB]];

            foreach ($aActive as $iOtherId) {
                if ($iOtherId === $iIdA || $iOtherId === $iIdB) {
                    continue;
                }
                $fNewDistance = 0.5 * ($aDistance[$iIdA][$iOtherId] + $aDistance[$iIdB][$iOtherId] - $fDab);
                $aDistance[$iNewId][$iOtherId] = $fNewDistance;
                $aDistance[$iOtherId][$iNewId] = $fNewDistance;
            }

            $aActive = array_values(array_filter(
                $aActive,
                static fn(int $iId) => $iId !== $iIdA && $iId !== $iIdB
            ));
            $aActive[] = $iNewId;
        }

        if (count($aActive) === 2) {
            [$iIdA, $iIdB] = $aActive;
            $fHalf = $aDistance[$iIdA][$iIdB] / 2.0;

            $oNodeA = new PhylogeneticNode($aPending[$iIdA]["name"], $fHalf, $aPending[$iIdA]["children"]);
            $oNodeB = new PhylogeneticNode($aPending[$iIdB]["name"], $fHalf, $aPending[$iIdB]["children"]);

            return new PhylogeneticNode(null, null, [$oNodeA, $oNodeB]);
        }

        [$iIdP, $iIdQ, $iIdR] = $aActive;
        $fDpq = $aDistance[$iIdP][$iIdQ];
        $fDpr = $aDistance[$iIdP][$iIdR];
        $fDqr = $aDistance[$iIdQ][$iIdR];

        $oNodeP = new PhylogeneticNode(
            $aPending[$iIdP]["name"],
            ($fDpq + $fDpr - $fDqr) / 2.0,
            $aPending[$iIdP]["children"]
        );
        $oNodeQ = new PhylogeneticNode(
            $aPending[$iIdQ]["name"],
            ($fDpq + $fDqr - $fDpr) / 2.0,
            $aPending[$iIdQ]["children"]
        );
        $oNodeR = new PhylogeneticNode(
            $aPending[$iIdR]["name"],
            ($fDpr + $fDqr - $fDpq) / 2.0,
            $aPending[$iIdR]["children"]
        );

        return new PhylogeneticNode(null, null, [$oNodeP, $oNodeQ, $oNodeR]);
    }
}
