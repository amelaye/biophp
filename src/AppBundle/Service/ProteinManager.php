<?php
/**
 * Protein Managing
 * @author Amélie DUVERNET akka Amelaye
 * Inspired by BioPHP's project biophp.org
 * Created 11 february 2019
 * Last modified 14 february 2019
 */
namespace AppBundle\Service;

use AppBundle\Entity\Protein;

class ProteinManager
{
    private $protein;
    private $wts;

    /**
     * Constructor
     * @param Protein $oProtein
     * @param array $aWts
     */
    public function __construct(Protein $oProtein, $aWts)
    {
        $this->protein  = $oProtein;
        $this->wts      = $aWts;
    }

    /**
     * Returns the length of a protein sequence().
     * @return int
     */
    public function seqlen() {
        return strlen($this->protein->getSequence());
    }


    /**
     * Computes the molecular weight of a protein sequence.
     * @return boolean
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
