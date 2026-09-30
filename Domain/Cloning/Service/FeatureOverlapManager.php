<?php
/**
 * Circular-aware overlap queries between PlasmidFeature annotations
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 30 September 2026
 */
namespace Amelaye\BioPHP\Domain\Cloning\Service;

use Amelaye\BioPHP\Domain\Cloning\Interfaces\FeatureOverlapInterface;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\FeatureOverlap;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\Plasmid;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\PlasmidFeature;

/**
 * A feature that crosses the origin (start > end) is treated as two ordinary, linear 1-based
 * intervals - [start, plasmidLength] and [1, end] - and every query below reduces to comparing those
 * linear intervals with the standard "s1 <= e2 && s2 <= e1" overlap test. containsPosition() uses the
 * same 1-based convention as PlasmidFeature's own getStart()/getEnd() ; a position coming from a
 * RestrictionCut (zero-based, see CircularRestrictionDigestManager's own docblock) must be shifted by
 * one before being passed in here, exactly as Plasmid::extractFeatureSequence() already does the
 * reverse conversion for sliceCircular().
 * Class FeatureOverlapManager
 * @package Amelaye\BioPHP\Domain\Cloning\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class FeatureOverlapManager implements FeatureOverlapInterface
{
    /**
     * @param   PlasmidFeature  $oFirst
     * @param   PlasmidFeature  $oSecond
     * @param   int             $iPlasmidLength
     * @return  bool
     */
    public function overlaps(PlasmidFeature $oFirst, PlasmidFeature $oSecond, int $iPlasmidLength): bool
    {
        foreach ($this->toIntervals($oFirst, $iPlasmidLength) as $aFirstInterval) {
            foreach ($this->toIntervals($oSecond, $iPlasmidLength) as $aSecondInterval) {
                if ($aFirstInterval[0] <= $aSecondInterval[1] && $aSecondInterval[0] <= $aFirstInterval[1]) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param   PlasmidFeature  $oFeature
     * @param   int             $iOneBasedPosition
     * @param   int             $iPlasmidLength
     * @return  bool
     */
    public function containsPosition(PlasmidFeature $oFeature, int $iOneBasedPosition, int $iPlasmidLength): bool
    {
        foreach ($this->toIntervals($oFeature, $iPlasmidLength) as $aInterval) {
            if ($iOneBasedPosition >= $aInterval[0] && $iOneBasedPosition <= $aInterval[1]) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param   PlasmidFeature      $oTarget
     * @param   PlasmidFeature[]    $aCandidates
     * @param   int                 $iPlasmidLength
     * @return  PlasmidFeature[]
     */
    public function findOverlapping(PlasmidFeature $oTarget, array $aCandidates, int $iPlasmidLength): array
    {
        return array_values(array_filter(
            $aCandidates,
            function (PlasmidFeature $oCandidate) use ($oTarget, $iPlasmidLength) {
                return $oCandidate !== $oTarget && $this->overlaps($oTarget, $oCandidate, $iPlasmidLength);
            }
        ));
    }

    /**
     * @param   Plasmid     $oPlasmid
     * @param   int         $iOneBasedPosition
     * @return  PlasmidFeature[]
     */
    public function findFeaturesAtPosition(Plasmid $oPlasmid, int $iOneBasedPosition): array
    {
        $iLength = $oPlasmid->getLength();

        return array_values(array_filter(
            $oPlasmid->getFeatures(),
            function (PlasmidFeature $oFeature) use ($iOneBasedPosition, $iLength) {
                return $this->containsPosition($oFeature, $iOneBasedPosition, $iLength);
            }
        ));
    }

    /**
     * @param   Plasmid     $oPlasmid
     * @return  FeatureOverlap[]
     */
    public function findOverlappingPairs(Plasmid $oPlasmid): array
    {
        $aFeatures = $oPlasmid->getFeatures();
        $iLength = $oPlasmid->getLength();
        $iCount = count($aFeatures);

        $aPairs = [];
        for ($i = 0; $i < $iCount; $i++) {
            for ($j = $i + 1; $j < $iCount; $j++) {
                if ($this->overlaps($aFeatures[$i], $aFeatures[$j], $iLength)) {
                    $aPairs[] = new FeatureOverlap($aFeatures[$i], $aFeatures[$j]);
                }
            }
        }

        return $aPairs;
    }

    /**
     * Reduces a feature to one linear 1-based interval, or two when it crosses the origin.
     * @param   PlasmidFeature  $oFeature
     * @param   int             $iPlasmidLength
     * @return  array           int[2][] : one or two [start, end] pairs
     */
    private function toIntervals(PlasmidFeature $oFeature, int $iPlasmidLength): array
    {
        if (!$oFeature->crossesOrigin()) {
            return [[$oFeature->getStart(), $oFeature->getEnd()]];
        }

        return [
            [$oFeature->getStart(), $iPlasmidLength],
            [1, $oFeature->getEnd()],
        ];
    }
}
