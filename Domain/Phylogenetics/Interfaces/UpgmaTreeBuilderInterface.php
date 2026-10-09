<?php
/**
 * UPGMA tree construction Interface
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Phylogenetics\Interfaces;

use Amelaye\BioPHP\Domain\Phylogenetics\ValueObject\DistanceMatrix;
use Amelaye\BioPHP\Domain\Phylogenetics\ValueObject\PhylogeneticNode;

/**
 * Interface UpgmaTreeBuilderInterface - builds a rooted, ultrametric tree from a DistanceMatrix
 * using UPGMA (average linkage clustering).
 * @package Amelaye\BioPHP\Domain\Phylogenetics\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface UpgmaTreeBuilderInterface
{
    /**
     * @param   DistanceMatrix      $oMatrix
     * @return  PhylogeneticNode    The root of the built tree : a single leaf for one taxon,
     * otherwise a rooted, strictly bifurcating tree
     */
    public function build(DistanceMatrix $oMatrix): PhylogeneticNode;
}
