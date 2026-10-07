<?php
/**
 * Protein Managing
 * Inspired by BioPHP's project biophp.org
 * Created 11 february 2019
 * Last modified 7 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Sequence\Service;

use Amelaye\BioPHP\Api\Interfaces\AminoApiAdapter;
use Amelaye\BioPHP\Domain\Sequence\Entity\Protein;
use Amelaye\BioPHP\Domain\Sequence\Interfaces\ProteinInterface;

/**
 * We can have manipulation with proteins
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 * @package Amelaye\BioPHP\Domain\Sequence\Service
 */
class ProteinManager implements ProteinInterface
{
    /**
     * @var Protein
     */
    private ?Protein $protein = null;

    /**
     * @var array
     */
    private ?array $aminos = null;

    /**
     * @var AminoApiAdapter
     */
    private AminoApiAdapter $aminoApi;

    /**
     * Constructor
     * @param AminoApiAdapter $aminoApi
     */
    public function __construct(AminoApiAdapter $aminoApi)
    {
        $this->aminoApi = $aminoApi;
        $this->aminos   = $aminoApi->getAminos();
    }

    /**
     * @param $oProtein
     */
    public function setProtein(Protein $oProtein)
    {
        $this->protein = $oProtein;
    }

    /**
     * Returns the length of a protein sequence().
     * @return  int  An integer representing the number of amino acids in the protein.
     */
    public function seqlen() : int
    {
        return strlen($this->protein->getSequence());
    }


    /**
     * Computes the molecular weight of a protein sequence. Every residue the amino acid weight
     * table holds is weighed, selenocysteine (U) and pyrrolysine (O) included, whatever its case ;
     * the stop a translated ORF ends with ("*") is no residue and is left out.
     * @return  boolean|array   An array of the form: ( lower_molwt, upper_molwt ), FALSE when the
     * sequence holds a symbol the table does not (an internal stop among them)
     */
    public function molwt()
    {
        $iLowerLimit = 0;
        $iUpperLimit = 1;

        $sSequence = rtrim(strtoupper($this->protein->getSequence()), "*");
        $wts = $this->aminoApi::GetAminoweights($this->aminos);
        unset($wts["*"]);

        // If there are unknown characters, then do not compute molwt and instead return FALSE.
        if (strspn($sSequence, implode("", array_keys($wts))) !== strlen($sSequence)) {
            return false;
        }

        // Otherwise, continue and calculate molecular weight of amino acid chain.
        $aMolecularWeight = [0, 0];
        $iAminoLength = strlen($sSequence);

        for($i = 0; $i < $iAminoLength; $i++) {
            $amino = substr($sSequence, $i, 1);
            $aMolecularWeight[$iLowerLimit] += $wts[$amino][$iLowerLimit];
            $aMolecularWeight[$iUpperLimit] += $wts[$amino][$iUpperLimit];
        }
        $fMwtWater = 18.015;
        // A chain of n residues loses (n-1) water molecules during polymerization. An empty
        // chain (n=0) loses none: max(0, ...) keeps it from gaining a spurious water molecule.
        $iWaterLosses = max(0, $iAminoLength - 1);
        $aMolecularWeight[$iLowerLimit] = $aMolecularWeight[$iLowerLimit] - ($iWaterLosses * $fMwtWater);
        $aMolecularWeight[$iUpperLimit] = $aMolecularWeight[$iUpperLimit] - ($iWaterLosses * $fMwtWater);
        return $aMolecularWeight;
    }
}
