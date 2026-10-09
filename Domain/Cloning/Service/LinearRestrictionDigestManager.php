<?php
/**
 * Computes restriction enzyme cuts and fragments on a linear DNA sequence
 * Freely inspired by BioPHP's project biophp.org
 * Created 9 October 2026
 * Last modified 9 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Cloning\Service;

use Amelaye\BioPHP\Domain\Cloning\Interfaces\LinearRestrictionDigestInterface;
use Amelaye\BioPHP\Domain\Cloning\Result\LinearRestrictionDigestResult;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\RestrictionCut;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\RestrictionEnd;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\RestrictionFragment;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\RestrictionEnzymeDefinition;

/**
 * The same geometry as CircularRestrictionDigestManager (see its docblock for the derivation of the
 * two cut positions, on either strand), without the wrapping : a site is searched on both strands
 * but must lie wholly inside the sequence, and a cut is kept only when both its strands are cut
 * inside the sequence. A Type IIs enzyme, which cuts away from its site, therefore loses a site near
 * an end of the sequence - a warning says which. Positions are zero-based junctions : a cut at u
 * leaves u bases on its left. The fragments are the top strand between two distinct upper cuts ; the
 * two ends of the sequence itself are blunt, the only way a bare strand can be taken.
 * Class LinearRestrictionDigestManager
 * @package Amelaye\BioPHP\Domain\Cloning\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class LinearRestrictionDigestManager implements LinearRestrictionDigestInterface
{
    /**
     * @param   DnaSequence                     $oSequence
     * @param   RestrictionEnzymeDefinition[]   $aEnzymes
     * @return  LinearRestrictionDigestResult
     * @throws  \InvalidArgumentException  When an enzyme is not a RestrictionEnzymeDefinition
     */
    public function digest(DnaSequence $oSequence, array $aEnzymes): LinearRestrictionDigestResult
    {
        $iLength = $oSequence->getLength();
        $sValue = $oSequence->getValue();
        $sReverseValue = $oSequence->reverseComplement()->getValue();

        $aCuts = [];
        $aWarnings = [];

        foreach ($aEnzymes as $oEnzyme) {
            if (!$oEnzyme instanceof RestrictionEnzymeDefinition) {
                throw new \InvalidArgumentException("Restriction digest enzymes must be RestrictionEnzymeDefinition instances.");
            }

            if ($oEnzyme->getRecognitionLength() > $iLength) {
                $aWarnings[] = sprintf(
                    'Enzyme "%s" recognition length %d exceeds sequence length %d; no site can fit, skipped.',
                    $oEnzyme->getName(),
                    $oEnzyme->getRecognitionLength(),
                    $iLength
                );
                continue;
            }

            $aSites = [];
            foreach ($this->searchPattern($sValue, $oEnzyme) as $iPosition) {
                $aSites[] = ["position" => $iPosition, "reverse" => false];
            }
            foreach ($this->searchPattern($sReverseValue, $oEnzyme) as $iOffset) {
                $aSites[] = ["position" => $iLength - 1 - $iOffset, "reverse" => true];
            }

            $aEnzymeCuts = [];
            foreach ($aSites as $aSite) {
                $oCut = $this->buildCut($oSequence, $oEnzyme, $aSite["position"], $aSite["reverse"], $aWarnings);
                if ($oCut === null) {
                    continue;
                }
                $sKey = $oCut->getUpperCutPosition() . ":" . $oCut->getLowerCutPosition();
                if (!array_key_exists($sKey, $aEnzymeCuts)) {
                    $aEnzymeCuts[$sKey] = $oCut;
                }
            }

            foreach ($aEnzymeCuts as $oCut) {
                $aCuts[] = $oCut;
            }
        }

        usort($aCuts, function (RestrictionCut $oLeft, RestrictionCut $oRight) {
            return [$oLeft->getUpperCutPosition(), $oLeft->getEnzymeName()] <=> [$oRight->getUpperCutPosition(), $oRight->getEnzymeName()];
        });

        return new LinearRestrictionDigestResult(
            $oSequence,
            array_map(fn(RestrictionEnzymeDefinition $oEnzyme) => $oEnzyme->getName(), $aEnzymes),
            $aCuts,
            $this->buildFragments($oSequence, $aCuts, $aWarnings),
            $aWarnings
        );
    }

    /**
     * Every zero-based position the recognition pattern starts at within a strand, overlapping
     * sites included (HhaI GCGC twice in GCGCGC).
     * @param   string                          $sStrand
     * @param   RestrictionEnzymeDefinition     $oEnzyme
     * @return  int[]
     */
    private function searchPattern(string $sStrand, RestrictionEnzymeDefinition $oEnzyme): array
    {
        if (!preg_match_all('/(?=' . $oEnzyme->getComputingPattern() . ')/i', $sStrand, $aMatches, PREG_OFFSET_CAPTURE)) {
            return [];
        }

        return array_map(fn(array $aMatch) => $aMatch[1], $aMatches[0]);
    }

    /**
     * @param   DnaSequence                     $oSequence
     * @param   RestrictionEnzymeDefinition     $oEnzyme
     * @param   int                             $iPosition      Zero-based axis coordinate of the
     * recognition sequence's own 5' start
     * @param   bool                            $bReverseStrand
     * @param   string[]                        $aWarnings      Added to when the cut falls outside
     * @return  RestrictionCut|null     Null when either strand would be cut outside the sequence
     */
    private function buildCut(
        DnaSequence $oSequence,
        RestrictionEnzymeDefinition $oEnzyme,
        int $iPosition,
        bool $bReverseStrand,
        array &$aWarnings
    ): ?RestrictionCut {
        $iCleavageUpper = $oEnzyme->getCleavagePositionUpper();
        $iCleavageLower = $oEnzyme->getCleavagePositionLower();

        if ($bReverseStrand) {
            $iUpper = $iPosition + 1 - $iCleavageUpper - $iCleavageLower;
            $iLower = $iPosition + 1 - $iCleavageUpper;
        } else {
            $iUpper = $iPosition + $iCleavageUpper;
            $iLower = $iPosition + $iCleavageUpper + $iCleavageLower;
        }

        $iLength = $oSequence->getLength();
        if ($iUpper < 1 || $iUpper > $iLength - 1 || $iLower < 1 || $iLower > $iLength - 1) {
            $aWarnings[] = sprintf(
                'Enzyme "%s" site at position %d would cut outside the sequence (cuts at %d and %d); ignored.',
                $oEnzyme->getName(),
                $iPosition,
                $iUpper,
                $iLower
            );

            return null;
        }

        return new RestrictionCut(
            $oEnzyme->getName(),
            $iPosition,
            $iUpper,
            $iLower,
            $this->buildEnd($oSequence, $iUpper, $iLower, $iCleavageLower),
            $bReverseStrand
        );
    }

    /**
     * @param   DnaSequence     $oSequence
     * @param   int             $iUpper
     * @param   int             $iLower
     * @param   int             $iCleavagePositionLower     The signed distance between the two cuts
     * @return  RestrictionEnd
     */
    private function buildEnd(DnaSequence $oSequence, int $iUpper, int $iLower, int $iCleavagePositionLower): RestrictionEnd
    {
        if ($iCleavagePositionLower === 0) {
            return RestrictionEnd::blunt();
        }

        $iStart = $iCleavagePositionLower > 0 ? $iUpper : $iLower;
        $sOverhang = substr($oSequence->getValue(), $iStart, abs($iCleavagePositionLower));

        return $iCleavagePositionLower > 0 ? RestrictionEnd::fivePrime($sOverhang) : RestrictionEnd::threePrime($sOverhang);
    }

    /**
     * @param   DnaSequence         $oSequence
     * @param   RestrictionCut[]    $aCuts
     * @param   string[]            $aWarnings
     * @return  RestrictionFragment[]   Empty when nothing cuts
     */
    private function buildFragments(DnaSequence $oSequence, array $aCuts, array &$aWarnings): array
    {
        if (count($aCuts) === 0) {
            return [];
        }

        $aEndsByPosition = [];
        foreach ($aCuts as $oCut) {
            $iPosition = $oCut->getUpperCutPosition();
            if (!array_key_exists($iPosition, $aEndsByPosition)) {
                $aEndsByPosition[$iPosition] = $oCut->getEnd();
            } elseif ($aEndsByPosition[$iPosition]->getType() !== $oCut->getEnd()->getType()
                || $aEndsByPosition[$iPosition]->getOverhangSequence() !== $oCut->getEnd()->getOverhangSequence()) {
                if ($aEndsByPosition[$iPosition]->isDeterminate()) {
                    $aWarnings[] = sprintf(
                        'Two cuts at upper strand position %d nick the lower strand at different places :'
                        . ' the end they leave is unknown.',
                        $iPosition
                    );
                }
                $aEndsByPosition[$iPosition] = RestrictionEnd::unknown();
            }
        }

        ksort($aEndsByPosition, SORT_NUMERIC);
        $aPositions = array_keys($aEndsByPosition);
        $sValue = $oSequence->getValue();

        $aFragments = [];
        $iStart = 0;
        $oLeftEnd = RestrictionEnd::blunt();
        foreach ($aPositions as $iPosition) {
            $aFragments[] = new RestrictionFragment(new DnaSequence(substr($sValue, $iStart, $iPosition - $iStart)), $oLeftEnd, $aEndsByPosition[$iPosition]);
            $iStart = $iPosition;
            $oLeftEnd = $aEndsByPosition[$iPosition];
        }
        $aFragments[] = new RestrictionFragment(new DnaSequence(substr($sValue, $iStart)), $oLeftEnd, RestrictionEnd::blunt());

        return $aFragments;
    }
}
