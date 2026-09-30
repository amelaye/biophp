<?php
/**
 * Immutable value object describing the primer tails needed to create a Gibson junction
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 30 September 2026
 */
namespace Amelaye\BioPHP\Domain\Cloning\ValueObject;

/**
 * Both tails are given 5' -> 3', ready to be prepended to the primer's own annealing portion (this
 * class only designs the homology arm, not the annealing portion itself, which needs its own melting
 * temperature calculation). downstreamForwardPrimerTail goes on the forward primer that amplifies the
 * downstream fragment ; upstreamReversePrimerTail goes on the reverse primer that amplifies the
 * upstream fragment. After PCR, the two products share this same sequence at the junction, which is
 * what lets Gibson assembly's exonuclease chew-back and annealing join them.
 * Class GibsonHomologyArms
 * @package Amelaye\BioPHP\Domain\Cloning\ValueObject
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class GibsonHomologyArms
{
    /**
     * @var     string
     */
    private $downstreamForwardPrimerTail;

    /**
     * @var     string
     */
    private $upstreamReversePrimerTail;

    /**
     * GibsonHomologyArms constructor.
     * @param   string      $sDownstreamForwardPrimerTail   5' -> 3', must not be empty
     * @param   string      $sUpstreamReversePrimerTail     5' -> 3', must not be empty
     */
    public function __construct(string $sDownstreamForwardPrimerTail, string $sUpstreamReversePrimerTail)
    {
        if ($sDownstreamForwardPrimerTail === "" || $sUpstreamReversePrimerTail === "") {
            throw new \InvalidArgumentException("Gibson homology arm tails must not be empty.");
        }

        $this->downstreamForwardPrimerTail = $sDownstreamForwardPrimerTail;
        $this->upstreamReversePrimerTail = $sUpstreamReversePrimerTail;
    }

    /**
     * @return  string
     */
    public function getDownstreamForwardPrimerTail(): string
    {
        return $this->downstreamForwardPrimerTail;
    }

    /**
     * @return  string
     */
    public function getUpstreamReversePrimerTail(): string
    {
        return $this->upstreamReversePrimerTail;
    }

    /**
     * @return  int
     */
    public function getOverlapLength(): int
    {
        return strlen($this->downstreamForwardPrimerTail);
    }
}
