<?php
/**
 * Protein Managing
 * Inspired by BioPHP's project biophp.org
 * Created 11 february 2019
 * Last modified 25 october 2019
 */
namespace AppBundle\Service;

use AppBundle\Bioapi\Bioapi;
use AppBundle\Entity\Protein;

/**
 * This class represents the end-products of genetic processes of translation and
 * transcription -- the proteins.  While a protein's primary structure (its amino
 * acid sequence) is ably represented as a Sequence object, its secondary and tertiary
 * structures are not. This is the main rationale for creating a separate Protein
 * class.
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 * @package AppBundle\Service
 */
class ProteinManager
{
    private $protein;

    private $wts;

    /**
     * Constructor
     * @param Bioapi $bioapi
     */
    public function __construct(Bioapi $bioapi)
    {
        $this->wts      = $bioapi->getAminoweights();
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
     * @return int
     * @group Legacy
     */
    public function seqlen() {
        return strlen($this->protein->getSequence());
    }


    /**
     * Computes the molecular weight of a protein sequence.
     * @return boolean
     * @group Legacy
     */
    public function molwt()
    {
        $lowerlimit = 0;
        $upperlimit = 1;

        // Check if characters outside our 20-letter amino alphabet is included in the sequence.
        preg_match_all("/[^GAVLIPFYWSTCMNQDEKRHBXZ]/", $this->protein->getSequence(), $match);
        // If there are unknown characters, then do not compute molwt and instead return FALSE.
        if (count($match[0]) > 0) {
            return false;
        }

        // Otherwise, continue and calculate molecular weight of amino acid chain.
        $aMolecularWeight = [0, 0];
        $amino_len = $this->seqlen();
        for($i = 0; $i < $amino_len; $i++) {
            $amino = substr($this->protein->getSequence(), $i, 1);
            $aMolecularWeight[$lowerlimit] += $this->wts[$amino][$lowerlimit];
            $aMolecularWeight[$upperlimit] += $this->wts[$amino][$upperlimit];
        }
        $mwt_water = 18.015;
        $aMolecularWeight[$lowerlimit] = $aMolecularWeight[$lowerlimit] - (($this->seqlen() - 1) * $mwt_water);
        $aMolecularWeight[$upperlimit] = $aMolecularWeight[$upperlimit] - (($this->seqlen() - 1) * $mwt_water);
        return $aMolecularWeight;
    }
}
