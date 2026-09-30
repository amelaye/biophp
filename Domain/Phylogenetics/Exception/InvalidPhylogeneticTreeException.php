<?php
/**
 * Raised when a PhylogeneticNode is built in violation of one of its invariants
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 30 September 2026
 */
namespace Amelaye\BioPHP\Domain\Phylogenetics\Exception;

/**
 * Class InvalidPhylogeneticTreeException
 * @package Amelaye\BioPHP\Domain\Phylogenetics\Exception
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class InvalidPhylogeneticTreeException extends \InvalidArgumentException
{
    /**
     * Builds the exception raised when a node with no children (a leaf) carries no name, which would
     * make it impossible to identify in the tree regardless of how the tree was built.
     * @return  InvalidPhylogeneticTreeException
     */
    public static function unnamedLeaf(): self
    {
        return new self("A leaf (a node with no children) must have a non-empty name.");
    }

    /**
     * Builds the exception raised when a child passed to a PhylogeneticNode is not itself one.
     * @param   mixed       $mGiven
     * @return  InvalidPhylogeneticTreeException
     */
    public static function childMustBeANode($mGiven): self
    {
        return new self(
            sprintf(
                'PhylogeneticNode children must be PhylogeneticNode instances, got %s.',
                is_object($mGiven) ? get_class($mGiven) : gettype($mGiven)
            )
        );
    }
}
