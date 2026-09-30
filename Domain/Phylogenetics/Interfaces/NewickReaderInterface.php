<?php
/**
 * Newick tree reading Interface
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 30 September 2026
 */
namespace Amelaye\BioPHP\Domain\Phylogenetics\Interfaces;

use Amelaye\BioPHP\Domain\Phylogenetics\ValueObject\PhylogeneticNode;

/**
 * Interface NewickReaderInterface - parses a Newick string into a PhylogeneticNode tree. Only one
 * tree per call is supported ; a file holding several trees, one per line, is read by calling read()
 * once per line.
 * @package Amelaye\BioPHP\Domain\Phylogenetics\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface NewickReaderInterface
{
    /**
     * @param   string      $sNewick    A single Newick tree description, terminated with ";"
     * @return  PhylogeneticNode        The root of the parsed tree
     */
    public function read(string $sNewick): PhylogeneticNode;
}
