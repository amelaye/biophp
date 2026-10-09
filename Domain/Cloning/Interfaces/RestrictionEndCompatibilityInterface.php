<?php
/**
 * Restriction end ligation compatibility Interface
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Cloning\Interfaces;

use Amelaye\BioPHP\Domain\Cloning\ValueObject\RestrictionEnd;

/**
 * Interface RestrictionEndCompatibilityInterface - decides whether two restriction ends can be
 * ligated together.
 * @package Amelaye\BioPHP\Domain\Cloning\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface RestrictionEndCompatibilityInterface
{
    const COMPATIBLE = "COMPATIBLE";
    const INCOMPATIBLE = "INCOMPATIBLE";
    const INDETERMINATE = "INDETERMINATE";

    /**
     * @param   RestrictionEnd  $oFirst
     * @param   RestrictionEnd  $oSecond
     * @return  string          One of self::COMPATIBLE, self::INCOMPATIBLE, self::INDETERMINATE
     */
    public function checkCompatibility(RestrictionEnd $oFirst, RestrictionEnd $oSecond) : string;
}
