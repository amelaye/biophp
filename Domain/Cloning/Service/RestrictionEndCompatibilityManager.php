<?php
/**
 * Decides whether two restriction ends can be ligated together
 * Freely inspired by BioPHP's project biophp.org
 * Created 24 September 2026
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Cloning\Service;

use Amelaye\BioPHP\Domain\Cloning\Interfaces\RestrictionEndCompatibilityInterface;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\RestrictionEnd;

/**
 * Two blunt ends always ligate. Two sticky ends of the same kind (both 5' or both 3') ligate when
 * their overhang sequences, AS STORED, are literally equal - not their reverse complement.
 *
 * This looks backwards next to raw Watson-Crick pairing until the storage convention is taken into
 * account: CircularRestrictionDigestManager::buildEnd() always records the plasmid's own reference
 * ("top") strand bases at the gap, read left to right in fixed plasmid coordinates, and
 * buildFragments() reuses that exact same value both as the RightEnd of the fragment ending there
 * and as the LeftEnd of the fragment starting there. Relative to that single stored value, the two
 * usages sit on physically different strands : a fragment's LeftEnd usage already equals the true
 * protruding strand read 5'->3', while a fragment's RightEnd usage equals the reverse complement of
 * it. The intended comparison here is always between a RightEnd and a LeftEnd (self-religation of a
 * cut, or joining one fragment's end to a different fragment's end, as RestrictionFragment's own
 * accessors produce) ; substituting both roles' true values into the real antiparallel pairing
 * condition cancels the two opposite transforms and leaves plain equality of the stored strings.
 * The clearest proof of this is an invariant that must always hold regardless of the overhang
 * sequence : re-ligating a fragment to the very piece it was cut from restores the original
 * molecule, so checkCompatibility($fragment->getRightEnd(), $fragment->getLeftEnd()) - one cut,
 * compared against itself - must always be COMPATIBLE ; reverse-complement equality fails that
 * invariant for any non-palindromic overhang, while literal equality satisfies it unconditionally.
 *
 * A blunt end never ligates to a sticky one, a 5' overhang never ligates to a 3' one, and anything
 * involving an UNKNOWN end is reported indeterminate rather than guessed at.
 *
 * Comparing two ends that are both in the SAME role (two LeftEnds, or two RightEnds - e.g. to check
 * whether a fragment could be inserted flipped) is a different question this method does not answer
 * ; nothing in this codebase constructs that comparison today, but it would need the reverse
 * complement, not literal equality, since the role transform would no longer cancel out.
 * Class RestrictionEndCompatibilityManager
 * @package Amelaye\BioPHP\Domain\Cloning\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class RestrictionEndCompatibilityManager implements RestrictionEndCompatibilityInterface
{
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

        return $sFirstOverhang === $sSecondOverhang ? self::COMPATIBLE : self::INCOMPATIBLE;
    }
}
