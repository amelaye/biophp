<?php
/**
 * Immutable value object describing one node of a phylogenetic tree
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Phylogenetics\ValueObject;

use Amelaye\BioPHP\Domain\Phylogenetics\Exception\InvalidPhylogeneticTreeException;

/**
 * A leaf is a node with no children and must carry a name (the taxon it represents) ; an internal
 * node commonly has no name, since Newick and neighbor-joining rarely name ancestral nodes. Branch
 * length is the distance to this node's OWN parent, so it is meaningless - and left null - on the
 * root of a tree. Negative branch lengths are accepted without validation : neighbor-joining can
 * legitimately produce one when the input distances are slightly non-additive (measurement noise),
 * a well documented property of the algorithm, not an error this value object should reject.
 * Class PhylogeneticNode
 * @package Amelaye\BioPHP\Domain\Phylogenetics\ValueObject
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class PhylogeneticNode
{
    /**
     * @var     string|null
     */
    private ?string $name = null;

    /**
     * @var     float|null      Distance to this node's own parent ; null when not specified (e.g. the root)
     */
    private ?float $branchLength = null;

    /**
     * @var     PhylogeneticNode[]
     */
    private ?array $children = null;

    /**
     * PhylogeneticNode constructor.
     * @param   string|null             $sName
     * @param   float|null              $fBranchLength
     * @param   PhylogeneticNode[]      $aChildren      Empty for a leaf
     * @throws  InvalidPhylogeneticTreeException
     */
    public function __construct(?string $sName, ?float $fBranchLength, array $aChildren = [])
    {
        foreach ($aChildren as $oChild) {
            if (!$oChild instanceof self) {
                throw InvalidPhylogeneticTreeException::childMustBeANode($oChild);
            }
        }

        if ($aChildren === [] && ($sName === null || $sName === "")) {
            throw InvalidPhylogeneticTreeException::unnamedLeaf();
        }

        $this->name = $sName;
        $this->branchLength = $fBranchLength;
        $this->children = array_values($aChildren);
    }

    /**
     * @return  string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return  float|null
     */
    public function getBranchLength(): ?float
    {
        return $this->branchLength;
    }

    /**
     * @return  PhylogeneticNode[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @return  bool
     */
    public function isLeaf(): bool
    {
        return $this->children === [];
    }

    /**
     * @return  string[]        The name of every leaf under this node, depth-first, left to right
     */
    public function getLeafNames(): array
    {
        if ($this->isLeaf()) {
            return [$this->name];
        }

        $aNames = [];
        foreach ($this->children as $oChild) {
            foreach ($oChild->getLeafNames() as $sLeafName) {
                $aNames[] = $sLeafName;
            }
        }

        return $aNames;
    }

    /**
     * Serializes this node and its whole subtree as a Newick string, semicolon included.
     * @return  string
     */
    public function toNewick(): string
    {
        return $this->toNewickFragment() . ";";
    }

    /**
     * @return  string      This node's subtree, without the terminating ";"
     */
    private function toNewickFragment(): string
    {
        $sFragment = "";

        if ($this->children !== []) {
            $aChildFragments = [];
            foreach ($this->children as $oChild) {
                $aChildFragments[] = $oChild->toNewickFragment();
            }
            $sFragment .= "(" . implode(",", $aChildFragments) . ")";
        }

        $sFragment .= $this->name ?? "";

        if ($this->branchLength !== null) {
            $sFragment .= ":" . $this->branchLength;
        }

        return $sFragment;
    }
}
