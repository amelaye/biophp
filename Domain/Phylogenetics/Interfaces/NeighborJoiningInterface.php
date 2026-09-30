<?php
/**
 * Neighbor-joining tree construction Interface
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 30 September 2026
 */
namespace Amelaye\BioPHP\Domain\Phylogenetics\Interfaces;

use Amelaye\BioPHP\Domain\Phylogenetics\ValueObject\DistanceMatrix;
use Amelaye\BioPHP\Domain\Phylogenetics\ValueObject\PhylogeneticNode;

/**
 * Interface NeighborJoiningInterface - builds an unrooted phylogenetic tree from a DistanceMatrix
 * using the Saitou & Nei (1987) neighbor-joining algorithm.
 * @package Amelaye\BioPHP\Domain\Phylogenetics\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface NeighborJoiningInterface
{
    /**
     * @param   DistanceMatrix      $oMatrix
     * @return  PhylogeneticNode    The root of the built tree : a single leaf for one taxon, a
     * two-child node for two, and an unrooted, trifurcating root for three or more
     */
    public function build(DistanceMatrix $oMatrix): PhylogeneticNode;
}
