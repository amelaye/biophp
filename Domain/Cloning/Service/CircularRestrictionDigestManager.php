<?php
/**
 * Computes restriction enzyme cuts and fragments on a circular Plasmid
 * Freely inspired by BioPHP's project biophp.org
 * Created 24 September 2026
 * Last modified 30 September 2026
 */
namespace Amelaye\BioPHP\Domain\Cloning\Service;

use Amelaye\BioPHP\Domain\Cloning\ValueObject\Plasmid;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\RestrictionCut;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\RestrictionDigestResult;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\RestrictionEnd;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\RestrictionFragment;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\CircularDnaSequence;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\RestrictionEnzymeDefinition;

/**
 * The upper/lower cleavage position convention is locked by RestrictionEnzymeCatalog's fixtures and
 * verified against known biology : cleavagePositionUpper is the offset, from the start of a match, of
 * the upper-strand cut ; cleavagePositionLower is that same offset for the lower strand, expressed
 * relative to the upper one (RestrictionEnzymeDTO's own wording). A positive relative offset produces
 * a 5' overhang, a negative one a 3' overhang, zero a blunt end - checked here against EcoRI (5',
 * offset +4, overhang "AATT"), AatII (3', offset -4, overhang "ACGT") and AatI (blunt, offset 0).
 * Sites are searched using RestrictionEnzymeDefinition::getComputingPattern(), the IUPAC-to-regex
 * conversion bioapi already provides ; no ambiguous symbol is ever injected into a regular expression
 * by this class. An origin-crossing site is found by searching a sequence extended with its own
 * prefix, keeping only matches that start before the true sequence length.
 * Class CircularRestrictionDigestManager
 * @package Amelaye\BioPHP\Domain\Cloning\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class CircularRestrictionDigestManager
{
    /**
     * @param   Plasmid                         $oPlasmid
     * @param   RestrictionEnzymeDefinition[]   $aEnzymes
     * @return  RestrictionDigestResult
     */
    public function digest(Plasmid $oPlasmid, array $aEnzymes): RestrictionDigestResult
    {
        $oSequence = $oPlasmid->getSequence();
        $iLength = $oSequence->getLength();
        $sValue = $oSequence->getValue();

        $aCuts = [];
        $aWarnings = [];

        foreach ($aEnzymes as $oEnzyme) {
            if (!$oEnzyme instanceof RestrictionEnzymeDefinition) {
                throw new \InvalidArgumentException("Restriction digest enzymes must be RestrictionEnzymeDefinition instances.");
            }

            if ($oEnzyme->getRecognitionLength() > $iLength) {
                $aWarnings[] = sprintf(
                    'Enzyme "%s" recognition length %d exceeds plasmid length %d; no site can fit, skipped.',
                    $oEnzyme->getName(),
                    $oEnzyme->getRecognitionLength(),
                    $iLength
                );
                continue;
            }

            $aPositions = $this->findSites($sValue, $iLength, $oEnzyme);

            if ($oEnzyme->hasAmbiguousBases() && count($aPositions) > 0) {
                $aWarnings[] = sprintf(
                    'Enzyme "%s" has an ambiguous IUPAC recognition pattern; %d site(s) matched by expansion.',
                    $oEnzyme->getName(),
                    count($aPositions)
                );
            }

            foreach ($aPositions as $iPosition) {
                $aCuts[] = $this->buildCut($oSequence, $oEnzyme, $iPosition);
            }
        }

        usort($aCuts, function (RestrictionCut $oLeft, RestrictionCut $oRight) {
            return $oLeft->getRecognitionPosition() <=> $oRight->getRecognitionPosition();
        });

        $aFragments = $this->buildFragments($oPlasmid, $aCuts);

        return new RestrictionDigestResult(
            $oPlasmid,
            array_map(function (RestrictionEnzymeDefinition $oEnzyme) {
                return $oEnzyme->getName();
            }, $aEnzymes),
            $aCuts,
            $aFragments,
            $aWarnings
        );
    }

    /**
     * Finds every zero-based position $oEnzyme's recognition pattern starts at, including one that
     * crosses the origin, without ever finding the same circular occurrence twice.
     * @param   string      $sValue     The plasmid's normalized sequence
     * @param   int         $iLength    The plasmid's length
     * @param   RestrictionEnzymeDefinition     $oEnzyme
     * @return  int[]                   Ascending, deduplicated, zero-based positions
     */
    private function findSites(string $sValue, int $iLength, RestrictionEnzymeDefinition $oEnzyme): array
    {
        $sHaystack = $sValue . substr($sValue, 0, min($oEnzyme->getRecognitionLength() - 1, $iLength));

        if (!preg_match_all('/' . $oEnzyme->getComputingPattern() . '/i', $sHaystack, $aMatches, PREG_OFFSET_CAPTURE)) {
            return [];
        }

        $aPositions = [];
        foreach ($aMatches[0] as $aMatch) {
            $iOffset = $aMatch[1];
            if ($iOffset < $iLength) {
                $aPositions[] = $iOffset;
            }
        }

        $aPositions = array_values(array_unique($aPositions));
        sort($aPositions, SORT_NUMERIC);

        return $aPositions;
    }

    /**
     * @param   CircularDnaSequence             $oSequence
     * @param   RestrictionEnzymeDefinition     $oEnzyme
     * @param   int                              $iPosition     Zero-based recognition position
     * @return  RestrictionCut
     */
    private function buildCut(CircularDnaSequence $oSequence, RestrictionEnzymeDefinition $oEnzyme, int $iPosition): RestrictionCut
    {
        $iUpper = $oSequence->positionModulo($iPosition + $oEnzyme->getCleavagePositionUpper());
        $iLower = $oSequence->positionModulo($iPosition + $oEnzyme->getCleavagePositionUpper() + $oEnzyme->getCleavagePositionLower());

        $oEnd = $this->buildEnd($oSequence, $iUpper, $iLower, $oEnzyme->getCleavagePositionLower());

        return new RestrictionCut($oEnzyme->getName(), $iPosition, $iUpper, $iLower, $oEnd);
    }

    /**
     * @param   CircularDnaSequence     $oSequence
     * @param   int                     $iUpper                 Zero-based upper cut position
     * @param   int                     $iLower                 Zero-based lower cut position
     * @param   int                     $iCleavagePositionLower The signed distance between the two,
     * whose sign decides the overhang type
     * @return  RestrictionEnd
     */
    private function buildEnd(CircularDnaSequence $oSequence, int $iUpper, int $iLower, int $iCleavagePositionLower): RestrictionEnd
    {
        if ($iCleavagePositionLower === 0) {
            return RestrictionEnd::blunt();
        }

        // Walking forward (wrapping past the origin when needed) from the upper cut for a 5'
        // overhang, or from the lower cut for a 3' one, always lands exactly on the other cut : this
        // must not be done by comparing min(iUpper, iLower), which breaks once the pair wraps past
        // the origin and the "earlier" position is no longer the smaller of the two zero-based values.
        $iStart = $iCleavagePositionLower > 0 ? $iUpper : $iLower;
        $sOverhang = $oSequence->sliceCircular($iStart, abs($iCleavagePositionLower))->getValue();

        return $iCleavagePositionLower > 0 ? RestrictionEnd::fivePrime($sOverhang) : RestrictionEnd::threePrime($sOverhang);
    }

    /**
     * Builds the linear fragments a digest actually produces : one per distinct upper cut position,
     * none when there is no cut at all, exactly one spanning the whole plasmid when there is a single
     * one. Several cuts sharing the same upper cut position (isoschizomers) collapse into one
     * boundary, keeping the first one's RestrictionEnd.
     * @param   Plasmid             $oPlasmid
     * @param   RestrictionCut[]    $aCuts      Already sorted by recognition position
     * @return  RestrictionFragment[]
     */
    private function buildFragments(Plasmid $oPlasmid, array $aCuts): array
    {
        if (count($aCuts) === 0) {
            return [];
        }

        $aEndsByPosition = [];
        foreach ($aCuts as $oCut) {
            $iPosition = $oCut->getUpperCutPosition();
            if (!array_key_exists($iPosition, $aEndsByPosition)) {
                $aEndsByPosition[$iPosition] = $oCut->getEnd();
            }
        }

        ksort($aEndsByPosition, SORT_NUMERIC);
        $aPositions = array_keys($aEndsByPosition);
        $iCount = count($aPositions);
        $iLength = $oPlasmid->getLength();
        $oSequence = $oPlasmid->getSequence();

        $aFragments = [];
        for ($i = 0; $i < $iCount; $i++) {
            $iStart = $aPositions[$i];
            $iEnd = $aPositions[($i + 1) % $iCount];
            $iFragmentLength = (($iEnd - $iStart - 1 + $iLength) % $iLength) + 1;

            $aFragments[] = new RestrictionFragment(
                $oSequence->sliceCircular($iStart, $iFragmentLength),
                $aEndsByPosition[$iStart],
                $aEndsByPosition[$iEnd]
            );
        }

        return $aFragments;
    }
}
