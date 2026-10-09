<?php
/**
 * Immutable value object describing one linear fragment produced by a restriction digest
 * Freely inspired by BioPHP's project biophp.org
 * Created 24 September 2026
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Cloning\ValueObject;

use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;

/**
 * Class RestrictionFragment
 * @package Amelaye\BioPHP\Domain\Cloning\ValueObject
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class RestrictionFragment
{
    /**
     * @var     DnaSequence
     */
    private DnaSequence $sequence;

    /**
     * @var     RestrictionEnd
     */
    private RestrictionEnd $leftEnd;

    /**
     * @var     RestrictionEnd
     */
    private RestrictionEnd $rightEnd;

    /**
     * RestrictionFragment constructor.
     * @param   DnaSequence     $oSequence
     * @param   RestrictionEnd  $oLeftEnd
     * @param   RestrictionEnd  $oRightEnd
     */
    public function __construct(DnaSequence $oSequence, RestrictionEnd $oLeftEnd, RestrictionEnd $oRightEnd)
    {
        $this->sequence = $oSequence;
        $this->leftEnd = $oLeftEnd;
        $this->rightEnd = $oRightEnd;
    }

    /**
     * @return  DnaSequence
     */
    public function getSequence(): DnaSequence
    {
        return $this->sequence;
    }

    /**
     * @return  int
     */
    public function getLength(): int
    {
        return $this->sequence->getLength();
    }

    /**
     * @return  RestrictionEnd
     */
    public function getLeftEnd(): RestrictionEnd
    {
        return $this->leftEnd;
    }

    /**
     * @return  RestrictionEnd
     */
    public function getRightEnd(): RestrictionEnd
    {
        return $this->rightEnd;
    }
}
