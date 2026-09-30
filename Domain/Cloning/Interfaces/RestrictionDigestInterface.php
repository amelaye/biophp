<?php
/**
 * Circular restriction digestion Interface
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 30 September 2026
 */
namespace Amelaye\BioPHP\Domain\Cloning\Interfaces;

use Amelaye\BioPHP\Domain\Cloning\Aggregate\Plasmid;
use Amelaye\BioPHP\Domain\Cloning\Result\RestrictionDigestResult;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\RestrictionEnzymeDefinition;

/**
 * Interface RestrictionDigestInterface - computes where a set of restriction enzymes cut a circular
 * Plasmid, and the fragments that digestion produces.
 * @package Amelaye\BioPHP\Domain\Cloning\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface RestrictionDigestInterface
{
    /**
     * @param   Plasmid                         $oPlasmid
     * @param   RestrictionEnzymeDefinition[]   $aEnzymes
     * @return  RestrictionDigestResult
     */
    public function digest(Plasmid $oPlasmid, array $aEnzymes) : RestrictionDigestResult;
}
