<?php
/**
 * Linear restriction digestion Interface
 * Freely inspired by BioPHP's project biophp.org
 * Created 9 October 2026
 * Last modified 9 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Cloning\Interfaces;

use Amelaye\BioPHP\Domain\Cloning\Result\LinearRestrictionDigestResult;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\RestrictionEnzymeDefinition;

/**
 * Interface LinearRestrictionDigestInterface - computes where a set of restriction enzymes of any
 * family (II, IIb, IIs) cut a linear DNA sequence, and the fragments that digestion produces. The
 * counterpart of RestrictionDigestInterface, which digests a circular Plasmid.
 * @package Amelaye\BioPHP\Domain\Cloning\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface LinearRestrictionDigestInterface
{
    /**
     * @param   DnaSequence                     $oSequence
     * @param   RestrictionEnzymeDefinition[]   $aEnzymes
     * @return  LinearRestrictionDigestResult
     */
    public function digest(DnaSequence $oSequence, array $aEnzymes): LinearRestrictionDigestResult;
}
