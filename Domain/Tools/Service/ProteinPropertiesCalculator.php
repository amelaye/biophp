<?php
/**
 * Protein properties : net charge, isoelectric point, hydropathy
 * Freely inspired by BioPHP's project biophp.org
 * Created 9 October 2026
 * Last modified 9 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Tools\Service;

use Amelaye\BioPHP\Domain\Sequence\ValueObject\AminoAcidSequence;
use Amelaye\BioPHP\Domain\Tools\Interfaces\ProteinPropertiesInterface;

/**
 * The net charge is the sum of the partial charges of the groups that carry one - the N-terminus,
 * Lys, Arg and His (positive), the C-terminus, Asp, Glu, Cys and Tyr (negative) - each given by the
 * Henderson-Hasselbalch equation, 1 / (1 + 10^(pH - pK)) for a base and 1 / (1 + 10^(pK - pH)) for an
 * acid. The pK values are the caller's (bioapi serves three sets : EMBOSS, DTASelect, Solomon) : a
 * protein has no isoelectric point of its own, only one for a given set. The isoelectric point is the
 * pH where that charge is zero, found by bisection - the charge falls as the pH rises, always, so it
 * crosses zero once. The terminal groups are the ones of the set, not residue-specific ones.
 * The GRAVY is the mean of the Kyte and Doolittle (1982) hydropathy of the residues ; the values are
 * Biopython's (Bio.SeqUtils.ProtParamData.kd). A residue without a value (B, Z, J, X, U, O, the
 * stop) is left out of the mean rather than counted as zero, which would pull it towards neutral.
 * Class ProteinPropertiesCalculator
 * @package Amelaye\BioPHP\Domain\Tools\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class ProteinPropertiesCalculator implements ProteinPropertiesInterface
{
    private const POSITIVE_GROUPS = ["K", "R", "H"];

    private const NEGATIVE_GROUPS = ["D", "E", "C", "Y"];

    /**
     * Kyte and Doolittle, J Mol Biol 157:105-132 (1982)
     */
    public const KYTE_DOOLITTLE = [
        "A" => 1.8, "R" => -4.5, "N" => -3.5, "D" => -3.5, "C" => 2.5, "Q" => -3.5, "E" => -3.5,
        "G" => -0.4, "H" => -3.2, "I" => 4.5, "L" => 3.8, "K" => -3.9, "M" => 1.9, "F" => 2.8,
        "P" => -1.6, "S" => -0.8, "T" => -0.7, "W" => -0.9, "Y" => -1.3, "V" => 4.2,
    ];

    /**
     * pH bounds of the search : every charge is positive at 0 (the N-terminus alone carries almost
     * one) and, for a real protein, negative at 14. A protein made of some thirty Arg and no acidic
     * residue is still slightly positive there (each Arg keeps about 0.03 at pK 12.5) : its
     * isoelectric point is then the upper bound, 14, and not a pH the search could not reach.
     */
    private const PH_MIN = 0.0;

    private const PH_MAX = 14.0;

    /**
     * @param   AminoAcidSequence   $oProtein
     * @param   float               $fPh
     * @param   array               $aPk
     * @return  float
     * @throws  \InvalidArgumentException  When the protein is empty or the pK set lacks a group
     */
    public function netCharge(AminoAcidSequence $oProtein, float $fPh, array $aPk): float
    {
        $aPk = $this->normalizePk($aPk);
        $this->assertNotEmpty($oProtein);

        return $this->chargeOf($this->chargedGroups($oProtein), $fPh, $aPk);
    }

    /**
     * @param   AminoAcidSequence   $oProtein
     * @param   array               $aPk
     * @return  float
     * @throws  \InvalidArgumentException  When the protein is empty or the pK set lacks a group
     */
    public function isoelectricPoint(AminoAcidSequence $oProtein, array $aPk): float
    {
        $aPk = $this->normalizePk($aPk);
        $this->assertNotEmpty($oProtein);
        $aGroups = $this->chargedGroups($oProtein);

        $fLow = self::PH_MIN;
        $fHigh = self::PH_MAX;
        while ($fHigh - $fLow > 1e-9) {
            $fMiddle = ($fLow + $fHigh) / 2;
            if ($this->chargeOf($aGroups, $fMiddle, $aPk) > 0.0) {
                $fLow = $fMiddle;
            } else {
                $fHigh = $fMiddle;
            }
        }

        return ($fLow + $fHigh) / 2;
    }

    /**
     * @param   AminoAcidSequence   $oProtein
     * @return  float
     * @throws  \InvalidArgumentException  When no residue has a hydropathy value
     */
    public function gravy(AminoAcidSequence $oProtein): float
    {
        $fSum = 0.0;
        $iResidues = 0;
        foreach (str_split($oProtein->getValue()) as $sResidue) {
            if (isset(self::KYTE_DOOLITTLE[$sResidue])) {
                $fSum += self::KYTE_DOOLITTLE[$sResidue];
                $iResidues++;
            }
        }
        if ($iResidues === 0) {
            throw new \InvalidArgumentException("The protein has no residue with a Kyte-Doolittle hydropathy value.");
        }

        return $fSum / $iResidues;
    }

    /**
     * @param   array   $aGroups    Group => how many of them the protein has
     * @param   float   $fPh
     * @param   array   $aPk        Normalized
     * @return  float
     */
    private function chargeOf(array $aGroups, float $fPh, array $aPk): float
    {
        $fCharge = 1.0 / (1.0 + 10 ** ($fPh - $aPk["NTERMINUS"]));
        foreach (self::POSITIVE_GROUPS as $sGroup) {
            $fCharge += $aGroups[$sGroup] / (1.0 + 10 ** ($fPh - $aPk[$sGroup]));
        }
        $fCharge -= 1.0 / (1.0 + 10 ** ($aPk["CTERMINUS"] - $fPh));
        foreach (self::NEGATIVE_GROUPS as $sGroup) {
            $fCharge -= $aGroups[$sGroup] / (1.0 + 10 ** ($aPk[$sGroup] - $fPh));
        }

        return $fCharge;
    }

    /**
     * @param   AminoAcidSequence   $oProtein
     * @return  int[]               Residue => count, for the residues that carry a charge
     */
    private function chargedGroups(AminoAcidSequence $oProtein): array
    {
        $aGroups = [];
        foreach (array_merge(self::POSITIVE_GROUPS, self::NEGATIVE_GROUPS) as $sGroup) {
            $aGroups[$sGroup] = $oProtein->countSymbol($sGroup);
        }

        return $aGroups;
    }

    /**
     * @param   array   $aPk
     * @return  float[]     The pK set with upper-case keys, every group checked
     * @throws  \InvalidArgumentException
     */
    private function normalizePk(array $aPk): array
    {
        $aPk = array_change_key_case($aPk, CASE_UPPER);
        foreach (array_merge(["NTERMINUS", "CTERMINUS"], self::POSITIVE_GROUPS, self::NEGATIVE_GROUPS) as $sKey) {
            if (!isset($aPk[$sKey]) || !is_numeric($aPk[$sKey])) {
                throw new \InvalidArgumentException(sprintf('The pK set has no value for "%s".', $sKey));
            }
            $aPk[$sKey] = (float) $aPk[$sKey];
        }

        return $aPk;
    }

    /**
     * @param   AminoAcidSequence   $oProtein
     * @throws  \InvalidArgumentException
     */
    private function assertNotEmpty(AminoAcidSequence $oProtein): void
    {
        if ($oProtein->isEmpty()) {
            throw new \InvalidArgumentException("The protein is empty.");
        }
    }
}
