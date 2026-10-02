<?php
/**
 * Class of constants naming the strand a PlasmidFeature lies on
 * Freely inspired by BioPHP's project biophp.org
 * Created 24 September 2026
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Cloning\ValueObject;

/**
 * Class Strand
 * @package Amelaye\BioPHP\Domain\Cloning\ValueObject
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class Strand
{
    const FORWARD = "FORWARD";
    const REVERSE = "REVERSE";
    const NONE = "NONE";

    /**
     * @var     string[]
     */
    const VALID_STRANDS = [self::FORWARD, self::REVERSE, self::NONE];
}
