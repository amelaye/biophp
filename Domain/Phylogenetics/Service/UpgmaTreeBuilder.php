<?php
/**
 * Builds a rooted, ultrametric tree from a distance matrix via UPGMA
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Phylogenetics\Service;

use Amelaye\BioPHP\Domain\Phylogenetics\Interfaces\UpgmaTreeBuilderInterface;
use Amelaye\BioPHP\Domain\Phylogenetics\ValueObject\DistanceMatrix;
use Amelaye\BioPHP\Domain\Phylogenetics\ValueObject\PhylogeneticNode;

/**
 * Migrated from biotools' Service/DistanceAmongSequencesManager.php::upgmaClustering(), with one
 * deliberate scientific correction : the legacy newArray() always averages a merged cluster's two
 * distances 50/50 - ($d[k][x] + $d[k][y]) / 2 - regardless of how many original taxa x and y
 * already represent. That is WPGMA (Weighted Pair Group Method, despite the name a SIMPLE, unweighted
 * average at every step), not UPGMA ; true UPGMA weights the average by cluster size -
 * (|x|*d[k][x] + |y|*d[k][y]) / (|x|+|y|) - which is what makes it equal the average of every
 * original pairwise distance between the two merged groups. This class implements genuine,
 * size-weighted UPGMA. At each step it merges the two active clusters with the smallest distance
 * (ties keep the first pair encountered, the same deterministic convention NeighborJoiningTreeBuilder
 * already uses) into a new cluster at height = distance/2, giving each side a branch length of
 * (newHeight - thatSide'sOwnHeight) - the defining "ultrametric" property of a UPGMA tree : every
 * leaf ends up exactly as far from the root as every other leaf.
 * Class UpgmaTreeBuilder
 * @package Amelaye\BioPHP\Domain\Phylogenetics\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class UpgmaTreeBuilder implements UpgmaTreeBuilderInterface
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

        /** @var array<int,array{name: string|null, children: PhylogeneticNode[], height: float, size: int}> $aPending */
        $aPending = [];
        $aActive = [];
        $aDistance = [];

        for ($i = 0; $i < $iTaxaCount; $i++) {
            $aPending[$i] = ["name" => $aLabels[$i], "children" => [], "height" => 0.0, "size" => 1];
            $aActive[] = $i;
            $aDistance[$i] = $aRawMatrix[$i];
        }

        $iNextId = $iTaxaCount;

        while (count($aActive) > 1) {
            $iActiveCount = count($aActive);

            $iBestP = 0;
            $iBestQ = 1;
            $fBestDistance = null;

            for ($p = 0; $p < $iActiveCount; $p++) {
                for ($q = $p + 1; $q < $iActiveCount; $q++) {
                    $iId1 = $aActive[$p];
                    $iId2 = $aActive[$q];

                    if ($fBestDistance === null || $aDistance[$iId1][$iId2] < $fBestDistance) {
                        $fBestDistance = $aDistance[$iId1][$iId2];
                        $iBestP = $p;
                        $iBestQ = $q;
                    }
                }
            }

            $iIdA = $aActive[$iBestP];
            $iIdB = $aActive[$iBestQ];

            $fNewHeight = $fBestDistance / 2.0;
            $iSizeA = $aPending[$iIdA]["size"];
            $iSizeB = $aPending[$iIdB]["size"];

            $oNodeA = new PhylogeneticNode(
                $aPending[$iIdA]["name"],
                $fNewHeight - $aPending[$iIdA]["height"],
                $aPending[$iIdA]["children"]
            );
            $oNodeB = new PhylogeneticNode(
                $aPending[$iIdB]["name"],
                $fNewHeight - $aPending[$iIdB]["height"],
                $aPending[$iIdB]["children"]
            );

            $iNewId = $iNextId++;
            $iNewSize = $iSizeA + $iSizeB;
            $aPending[$iNewId] = [
                "name" => null,
                "children" => [$oNodeA, $oNodeB],
                "height" => $fNewHeight,
                "size" => $iNewSize,
            ];

            foreach ($aActive as $iOtherId) {
                if ($iOtherId === $iIdA || $iOtherId === $iIdB) {
                    continue;
                }
                $fNewDistance = ($iSizeA * $aDistance[$iIdA][$iOtherId] + $iSizeB * $aDistance[$iIdB][$iOtherId]) / $iNewSize;
                $aDistance[$iNewId][$iOtherId] = $fNewDistance;
                $aDistance[$iOtherId][$iNewId] = $fNewDistance;
            }

            $aActive = array_values(array_filter(
                $aActive,
                static fn(int $iId) => $iId !== $iIdA && $iId !== $iIdB
            ));
            $aActive[] = $iNewId;
        }

        $iRootId = $aActive[0];

        return new PhylogeneticNode(null, null, $aPending[$iRootId]["children"]);
    }
}
