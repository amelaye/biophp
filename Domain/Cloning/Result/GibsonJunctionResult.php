<?php
/**
 * Immutable value object describing whether two fragments already share Gibson homology
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Cloning\Result;

/**
 * hasOverlap() is false, and getOverlapLength()/getOverlapSequence() are 0/null, when no exact match
 * of at least the requested minimum length was found at the junction - a legitimate, expected outcome
 * for a junction that still needs primer tails designed (see GibsonAssemblyManager::designHomologyArms()),
 * not an error.
 * Class GibsonJunctionResult
 * @package Amelaye\BioPHP\Domain\Cloning\Result
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class GibsonJunctionResult
{
    /**
     * @var     int
     */
    private int $overlapLength;

    /**
     * @var     string|null
     */
    private ?string $overlapSequence = null;

    /**
     * GibsonJunctionResult constructor.
     * @param   int             $iOverlapLength     Zero when no overlap was found
     * @param   string|null     $sOverlapSequence   Null when no overlap was found
     */
    public function __construct(int $iOverlapLength, ?string $sOverlapSequence)
    {
        $this->overlapLength = $iOverlapLength;
        $this->overlapSequence = $sOverlapSequence;
    }

    /**
     * @return  bool
     */
    public function hasOverlap(): bool
    {
        return $this->overlapLength > 0;
    }

    /**
     * @return  int
     */
    public function getOverlapLength(): int
    {
        return $this->overlapLength;
    }

    /**
     * @return  string|null
     */
    public function getOverlapSequence(): ?string
    {
        return $this->overlapSequence;
    }
}
