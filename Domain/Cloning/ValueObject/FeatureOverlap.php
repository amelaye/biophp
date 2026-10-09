<?php
/**
 * Immutable value object pairing two PlasmidFeature that overlap each other
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Cloning\ValueObject;

/**
 * Order is not meaningful : getFirst()/getSecond() simply mirror the order the two features were
 * found in, not any priority between them.
 * Class FeatureOverlap
 * @package Amelaye\BioPHP\Domain\Cloning\ValueObject
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class FeatureOverlap
{
    /**
     * @var     PlasmidFeature
     */
    private PlasmidFeature $first;

    /**
     * @var     PlasmidFeature
     */
    private PlasmidFeature $second;

    /**
     * FeatureOverlap constructor.
     * @param   PlasmidFeature  $oFirst
     * @param   PlasmidFeature  $oSecond
     */
    public function __construct(PlasmidFeature $oFirst, PlasmidFeature $oSecond)
    {
        $this->first = $oFirst;
        $this->second = $oSecond;
    }

    /**
     * @return  PlasmidFeature
     */
    public function getFirst(): PlasmidFeature
    {
        return $this->first;
    }

    /**
     * @return  PlasmidFeature
     */
    public function getSecond(): PlasmidFeature
    {
        return $this->second;
    }
}
