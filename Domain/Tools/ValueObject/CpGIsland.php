<?php
/**
 * Immutable value object describing one predicted CpG island
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Tools\ValueObject;

/**
 * Coordinates are 1-based inclusive, the convention the rest of this project uses. gcContent and
 * observedToExpectedRatio are computed over the island's own full span - once it is known, not
 * inherited from whichever single scanning window first triggered its detection - so they describe
 * the island actually reported, not an arbitrary window inside it.
 * Class CpGIsland
 * @package Amelaye\BioPHP\Domain\Tools\ValueObject
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class CpGIsland
{
    /**
     * @var     int         1-based inclusive
     */
    private int $start;

    /**
     * @var     int         1-based inclusive
     */
    private int $end;

    /**
     * @var     float
     */
    private float $gcContent;

    /**
     * @var     float
     */
    private float $observedToExpectedRatio;

    /**
     * CpGIsland constructor.
     * @param   int         $iStart
     * @param   int         $iEnd
     * @param   float       $fGcContent
     * @param   float       $fObservedToExpectedRatio
     */
    public function __construct(int $iStart, int $iEnd, float $fGcContent, float $fObservedToExpectedRatio)
    {
        if ($iStart < 1) {
            throw new \InvalidArgumentException(sprintf('Start must be at least 1, got %d.', $iStart));
        }

        if ($iEnd < $iStart) {
            throw new \InvalidArgumentException(
                sprintf('End (%d) must not be before start (%d).', $iEnd, $iStart)
            );
        }

        $this->start = $iStart;
        $this->end = $iEnd;
        $this->gcContent = $fGcContent;
        $this->observedToExpectedRatio = $fObservedToExpectedRatio;
    }

    /**
     * @return  int
     */
    public function getStart(): int
    {
        return $this->start;
    }

    /**
     * @return  int
     */
    public function getEnd(): int
    {
        return $this->end;
    }

    /**
     * @return  int
     */
    public function getLength(): int
    {
        return $this->end - $this->start + 1;
    }

    /**
     * @return  float
     */
    public function getGcContent(): float
    {
        return $this->gcContent;
    }

    /**
     * @return  float
     */
    public function getObservedToExpectedRatio(): float
    {
        return $this->observedToExpectedRatio;
    }
}
