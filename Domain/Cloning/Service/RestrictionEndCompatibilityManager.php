<?php
/**
 * Decides whether two restriction ends can be ligated together
 * Freely inspired by BioPHP's project biophp.org
 * Created 24 September 2026
 * Last modified 24 September 2026
 */
namespace Amelaye\BioPHP\Domain\Cloning\Service;

use Amelaye\BioPHP\Domain\Cloning\ValueObject\RestrictionEnd;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;

/**
 * Two blunt ends always ligate. Two sticky ends of the same kind (both 5' or both 3') ligate when
 * their overhang sequences are the reverse complement of one another, which is the actual
 * Watson-Crick pairing condition for two single-stranded protrusions meeting in antiparallel
 * orientation - reusing DnaSequence::reverseComplement() rather than re-deriving IUPAC complement
 * rules. A blunt end never ligates to a sticky one, a 5' overhang never ligates to a 3' one, and
 * anything involving an UNKNOWN end is reported indeterminate rather than guessed at.
 * Class RestrictionEndCompatibilityManager
 * @package Amelaye\BioPHP\Domain\Cloning\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class RestrictionEndCompatibilityManager
{
    const COMPATIBLE = "COMPATIBLE";
    const INCOMPATIBLE = "INCOMPATIBLE";
    const INDETERMINATE = "INDETERMINATE";

    /**
     * @param   RestrictionEnd  $oFirst
     * @param   RestrictionEnd  $oSecond
     * @return  string          One of self::COMPATIBLE, self::INCOMPATIBLE, self::INDETERMINATE
     */
    public function checkCompatibility(RestrictionEnd $oFirst, RestrictionEnd $oSecond): string
    {
        if (!$oFirst->isDeterminate() || !$oSecond->isDeterminate()) {
            return self::INDETERMINATE;
        }

        if ($oFirst->isBlunt() && $oSecond->isBlunt()) {
            return self::COMPATIBLE;
        }

        if ($oFirst->getType() !== $oSecond->getType()) {
            return self::INCOMPATIBLE;
        }

        $sFirstOverhang = $oFirst->getOverhangSequence();
        $sSecondOverhang = $oSecond->getOverhangSequence();

        if (strlen($sFirstOverhang) !== strlen($sSecondOverhang)) {
            return self::INCOMPATIBLE;
        }

        $oSecondReverseComplement = (new DnaSequence($sSecondOverhang))->reverseComplement();

        return $sFirstOverhang === $oSecondReverseComplement->getValue() ? self::COMPATIBLE : self::INCOMPATIBLE;
    }
}
