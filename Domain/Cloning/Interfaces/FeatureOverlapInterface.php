<?php
/**
 * Circular-aware overlap queries between PlasmidFeature annotations Interface
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 30 September 2026
 */
namespace Amelaye\BioPHP\Domain\Cloning\Interfaces;

use Amelaye\BioPHP\Domain\Cloning\ValueObject\FeatureOverlap;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\Plasmid;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\PlasmidFeature;

/**
 * Interface FeatureOverlapInterface - answers "does this restriction site fall inside that CDS ?" and
 * "do these two annotations overlap ?" for the features of a circular Plasmid, correctly handling a
 * feature that crosses the origin.
 * @package Amelaye\BioPHP\Domain\Cloning\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface FeatureOverlapInterface
{
    /**
     * @param   PlasmidFeature  $oFirst
     * @param   PlasmidFeature  $oSecond
     * @param   int             $iPlasmidLength
     * @return  bool
     */
    public function overlaps(PlasmidFeature $oFirst, PlasmidFeature $oSecond, int $iPlasmidLength): bool;

    /**
     * @param   PlasmidFeature  $oFeature
     * @param   int             $iOneBasedPosition     1-based, the same convention as PlasmidFeature's
     * own start/end
     * @param   int             $iPlasmidLength
     * @return  bool
     */
    public function containsPosition(PlasmidFeature $oFeature, int $iOneBasedPosition, int $iPlasmidLength): bool;

    /**
     * @param   PlasmidFeature      $oTarget
     * @param   PlasmidFeature[]    $aCandidates    $oTarget itself, if present, is never returned
     * @param   int                 $iPlasmidLength
     * @return  PlasmidFeature[]    In the same relative order as $aCandidates
     */
    public function findOverlapping(PlasmidFeature $oTarget, array $aCandidates, int $iPlasmidLength): array;

    /**
     * @param   Plasmid     $oPlasmid
     * @param   int         $iOneBasedPosition
     * @return  PlasmidFeature[]    In the same relative order as $oPlasmid->getFeatures()
     */
    public function findFeaturesAtPosition(Plasmid $oPlasmid, int $iOneBasedPosition): array;

    /**
     * Every pair of $oPlasmid's own features that overlap each other. With n features this compares
     * every pair once (O(n^2)) rather than building an interval tree : a single plasmid's annotation
     * set is small enough (rarely more than a few dozen features) that the simpler algorithm is both
     * clearer and, in practice, at least as fast.
     * @param   Plasmid     $oPlasmid
     * @return  FeatureOverlap[]
     */
    public function findOverlappingPairs(Plasmid $oPlasmid): array;
}
