<?php
/**
 * Gibson assembly junction analysis and primer design
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 30 September 2026
 */
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
 * designHomologyArms() follows the standard convention (as used by tools such as NEBuilder) : the
 * upstream fragment's own 3' end becomes the tail added to the DOWNSTREAM fragment's forward primer,
 * and the downstream fragment's own 5' start, reverse-complemented, becomes the tail added to the
 * UPSTREAM fragment's reverse primer. Only the homology arm itself is designed here ; the primer's own
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

        $sDownstreamForwardPrimerTail = $oUpstream
            ->subSequence($oUpstream->getLength() - $iOverlapLength, $iOverlapLength)
            ->getValue();

        $sUpstreamReversePrimerTail = $oDownstream
            ->subSequence(0, $iOverlapLength)
            ->reverseComplement()
            ->getValue();

        return new GibsonHomologyArms($sDownstreamForwardPrimerTail, $sUpstreamReversePrimerTail);
    }
}
