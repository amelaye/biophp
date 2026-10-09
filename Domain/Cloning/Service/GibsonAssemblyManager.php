<?php
/**
 * Gibson assembly junction analysis and primer design
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 7 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Cloning\Service;

use Amelaye\BioPHP\Domain\Cloning\Interfaces\GibsonAssemblyInterface;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\GibsonHomologyArms;
use Amelaye\BioPHP\Domain\Cloning\Result\GibsonJunctionResult;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;

/**
 * checkJunction() deliberately does NOT reuse the alignment engine (Domain/Alignment, including its
 * own semi-global/overlap aligner) : Gibson's exonuclease chew-back and annealing need the two
 * fragments' homology arm to be essentially IDENTICAL, not merely "well aligned" under some scoring
 * scheme that would tolerate mismatches or gaps a real reaction would not anneal through. An anchored
 * exact-match search - does upstream's very last k bases equal downstream's very first k bases,
 * for the longest k in range - is both the scientifically appropriate check here and simpler than
 * configuring an aligner strictly enough to approximate one.
 * designHomologyArms() splits the requested overlap between the two primers : the upstream
 * fragment's last ceil(k/2) bases become the tail added to the DOWNSTREAM fragment's forward primer,
 * and the downstream fragment's first floor(k/2) bases, reverse-complemented, become the tail added
 * to the UPSTREAM fragment's reverse primer. Each PCR product then ends, or starts, with the
 * other's half, so the two products share exactly k bases. Carrying k bases on each primer would
 * give a 2k overlap instead. Only the homology arm itself is designed here ; the primer's own
 * annealing portion (with its own melting-temperature requirement) is a separate concern this class
 * does not address.
 * Class GibsonAssemblyManager
 * @package Amelaye\BioPHP\Domain\Cloning\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class GibsonAssemblyManager implements GibsonAssemblyInterface
{
    /**
     * @param   DnaSequence     $oUpstream
     * @param   DnaSequence     $oDownstream
     * @param   int             $iMinOverlapLength
     * @param   int             $iMaxOverlapLength
     * @return  GibsonJunctionResult
     */
    public function checkJunction(
        DnaSequence $oUpstream,
        DnaSequence $oDownstream,
        int $iMinOverlapLength,
        int $iMaxOverlapLength
    ): GibsonJunctionResult {
        if ($iMinOverlapLength < 1) {
            throw new \InvalidArgumentException(
                sprintf('Minimum overlap length must be at least 1, got %d.', $iMinOverlapLength)
            );
        }

        if ($iMaxOverlapLength < $iMinOverlapLength) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Maximum overlap length (%d) must not be less than the minimum (%d).',
                    $iMaxOverlapLength,
                    $iMinOverlapLength
                )
            );
        }

        $sUpstream = $oUpstream->getValue();
        $sDownstream = $oDownstream->getValue();
        $iCap = min($iMaxOverlapLength, strlen($sUpstream), strlen($sDownstream));

        for ($k = $iCap; $k >= $iMinOverlapLength; $k--) {
            $sUpstreamSuffix = substr($sUpstream, -$k);
            $sDownstreamPrefix = substr($sDownstream, 0, $k);

            if ($sUpstreamSuffix === $sDownstreamPrefix) {
                return new GibsonJunctionResult($k, $sUpstreamSuffix);
            }
        }

        return new GibsonJunctionResult(0, null);
    }

    /**
     * @param   DnaSequence     $oUpstream
     * @param   DnaSequence     $oDownstream
     * @param   int             $iOverlapLength
     * @return  GibsonHomologyArms
     */
    public function designHomologyArms(DnaSequence $oUpstream, DnaSequence $oDownstream, int $iOverlapLength): GibsonHomologyArms
    {
        if ($iOverlapLength < 1) {
            throw new \InvalidArgumentException(
                sprintf('Overlap length must be at least 1, got %d.', $iOverlapLength)
            );
        }

        if ($iOverlapLength > $oUpstream->getLength()) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Overlap length %d exceeds the upstream fragment length of %d.',
                    $iOverlapLength,
                    $oUpstream->getLength()
                )
            );
        }

        if ($iOverlapLength > $oDownstream->getLength()) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Overlap length %d exceeds the downstream fragment length of %d.',
                    $iOverlapLength,
                    $oDownstream->getLength()
                )
            );
        }

        $iDownstreamShare = intdiv($iOverlapLength, 2);
        $iUpstreamShare = $iOverlapLength - $iDownstreamShare;

        $sDownstreamForwardPrimerTail = $oUpstream
            ->subSequence($oUpstream->getLength() - $iUpstreamShare, $iUpstreamShare)
            ->getValue();

        $sUpstreamReversePrimerTail = $oDownstream
            ->subSequence(0, $iDownstreamShare)
            ->reverseComplement()
            ->getValue();

        return new GibsonHomologyArms($sDownstreamForwardPrimerTail, $sUpstreamReversePrimerTail);
    }
}
