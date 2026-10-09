<?php
/**
 * Protein properties : net charge, isoelectric point, hydropathy Interface
 * Freely inspired by BioPHP's project biophp.org
 * Created 9 October 2026
 * Last modified 9 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Tools\Interfaces;

use Amelaye\BioPHP\Domain\Sequence\ValueObject\AminoAcidSequence;

/**
 * Interface ProteinPropertiesInterface
 * @package Amelaye\BioPHP\Domain\Tools\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface ProteinPropertiesInterface
{
    /**
     * @param   AminoAcidSequence   $oProtein
     * @param   float               $fPh
     * @param   array               $aPk        The pK set, as bioapi serves it (NTERMINUS, CTERMINUS, K, R, H, D, E, C, Y)
     * @return  float               The net charge of the protein at that pH
     */
    public function netCharge(AminoAcidSequence $oProtein, float $fPh, array $aPk): float;

    /**
     * @param   AminoAcidSequence   $oProtein
     * @param   array               $aPk        The pK set, as bioapi serves it
     * @return  float               The pH at which the net charge is zero
     */
    public function isoelectricPoint(AminoAcidSequence $oProtein, array $aPk): float;

    /**
     * @param   AminoAcidSequence   $oProtein
     * @return  float               The grand average of hydropathy (Kyte and Doolittle, 1982)
     */
    public function gravy(AminoAcidSequence $oProtein): float;
}
