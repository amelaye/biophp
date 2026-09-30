<?php
/**
 * Immutable value object holding the outcome of a circular restriction digest
 * Freely inspired by BioPHP's project biophp.org
 * Created 24 September 2026
 * Last modified 30 September 2026
 */
namespace Amelaye\BioPHP\Domain\Cloning\Result;

use Amelaye\BioPHP\Domain\Cloning\Aggregate\Plasmid;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\RestrictionCut;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\RestrictionFragment;

/**
 * getCuts() lists every recognition site found, including several isoschizomers cutting at the same
 * position; getFragments() lists the distinct linear fragments that digest actually produces, so its
 * count only ever equals the number of distinct cut positions, never the number of raw cuts.
 * Class RestrictionDigestResult
 * @package Amelaye\BioPHP\Domain\Cloning\Result
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class RestrictionDigestResult
{
    /**
     * @var     Plasmid
     */
    private $plasmid;

    /**
     * @var     string[]
     */
    private $enzymeNames;

    /**
     * @var     RestrictionCut[]
     */
    private $cuts;

    /**
     * @var     RestrictionFragment[]
     */
    private $fragments;

    /**
     * @var     string[]
     */
    private $warnings;

    /**
     * RestrictionDigestResult constructor.
     * @param   Plasmid                 $oPlasmid
     * @param   string[]                $aEnzymeNames
     * @param   RestrictionCut[]        $aCuts
     * @param   RestrictionFragment[]   $aFragments
     * @param   string[]                $aWarnings
     */
    public function __construct(Plasmid $oPlasmid, array $aEnzymeNames, array $aCuts, array $aFragments, array $aWarnings = [])
    {
        foreach ($aCuts as $oCut) {
            if (!$oCut instanceof RestrictionCut) {
                throw new \InvalidArgumentException("Restriction digest cuts must be RestrictionCut instances.");
            }
        }

        foreach ($aFragments as $oFragment) {
            if (!$oFragment instanceof RestrictionFragment) {
                throw new \InvalidArgumentException("Restriction digest fragments must be RestrictionFragment instances.");
            }
        }

        $this->plasmid = $oPlasmid;
        $this->enzymeNames = array_values($aEnzymeNames);
        $this->cuts = array_values($aCuts);
        $this->fragments = array_values($aFragments);
        $this->warnings = array_values($aWarnings);
    }

    /**
     * @return  Plasmid
     */
    public function getPlasmid(): Plasmid
    {
        return $this->plasmid;
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
