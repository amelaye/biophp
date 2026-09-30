<?php
/**
 * Gibson assembly junction analysis and primer design Interface
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 30 September 2026
 */
namespace Amelaye\BioPHP\Domain\Cloning\Interfaces;

use Amelaye\BioPHP\Domain\Cloning\ValueObject\GibsonHomologyArms;
use Amelaye\BioPHP\Domain\Cloning\Result\GibsonJunctionResult;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;

/**
 * Interface GibsonAssemblyInterface - answers "do these two fragments already share the homology a
 * Gibson junction needs ?" and, when they do not, designs the primer tails that would give them one.
 * @package Amelaye\BioPHP\Domain\Cloning\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface GibsonAssemblyInterface
{
    /**
     * @param   DnaSequence     $oUpstream          The fragment whose 3' end forms the junction
     * @param   DnaSequence     $oDownstream        The fragment whose 5' end forms the junction
     * @param   int             $iMinOverlapLength  Shortest overlap worth reporting, strictly positive
     * @param   int             $iMaxOverlapLength  Longest overlap to search for, at least
     * $iMinOverlapLength
     * @return  GibsonJunctionResult
     */
    public function checkJunction(
        DnaSequence $oUpstream,
        DnaSequence $oDownstream,
        int $iMinOverlapLength,
        int $iMaxOverlapLength
    ): GibsonJunctionResult;

    /**
     * @param   DnaSequence     $oUpstream          The fragment whose 3' end forms the junction
     * @param   DnaSequence     $oDownstream        The fragment whose 5' end forms the junction
     * @param   int             $iOverlapLength     Strictly positive, at most either fragment's length
     * @return  GibsonHomologyArms
     */
    public function designHomologyArms(DnaSequence $oUpstream, DnaSequence $oDownstream, int $iOverlapLength): GibsonHomologyArms;
}
