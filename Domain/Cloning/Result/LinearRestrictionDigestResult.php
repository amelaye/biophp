<?php
/**
 * Immutable value object holding the outcome of a linear restriction digest
 * Freely inspired by BioPHP's project biophp.org
 * Created 9 October 2026
 * Last modified 9 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Cloning\Result;

use Amelaye\BioPHP\Domain\Cloning\ValueObject\RestrictionCut;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\RestrictionFragment;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;

/**
 * getCuts() lists every recognition site that cuts inside the sequence, including several
 * isoschizomers cutting at the same position ; getFragments() lists the distinct fragments, from
 * the left end of the sequence to its right end, so n distinct cut positions give n + 1 of them.
 * Class LinearRestrictionDigestResult
 * @package Amelaye\BioPHP\Domain\Cloning\Result
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class LinearRestrictionDigestResult
{
    /**
     * @var     DnaSequence
     */
    private DnaSequence $sequence;

    /**
     * @var     string[]
     */
    private array $enzymeNames;

    /**
     * @var     RestrictionCut[]
     */
    private array $cuts;

    /**
     * @var     RestrictionFragment[]
     */
    private array $fragments;

    /**
     * @var     string[]
     */
    private array $warnings;

    /**
     * LinearRestrictionDigestResult constructor.
     * @param   DnaSequence             $oSequence
     * @param   string[]                $aEnzymeNames
     * @param   RestrictionCut[]        $aCuts
     * @param   RestrictionFragment[]   $aFragments
     * @param   string[]                $aWarnings
     */
    public function __construct(DnaSequence $oSequence, array $aEnzymeNames, array $aCuts, array $aFragments, array $aWarnings = [])
    {
        $this->sequence = $oSequence;
        $this->enzymeNames = array_values($aEnzymeNames);
        $this->cuts = array_values($aCuts);
        $this->fragments = array_values($aFragments);
        $this->warnings = array_values($aWarnings);
    }

    /**
     * @return  DnaSequence
     */
    public function getSequence(): DnaSequence
    {
        return $this->sequence;
    }

    /**
     * @return  string[]
     */
    public function getEnzymeNames(): array
    {
        return $this->enzymeNames;
    }

    /**
     * @return  RestrictionCut[]
     */
    public function getCuts(): array
    {
        return $this->cuts;
    }

    /**
     * @return  RestrictionFragment[]
     */
    public function getFragments(): array
    {
        return $this->fragments;
    }

    /**
     * @return  string[]
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }
}
