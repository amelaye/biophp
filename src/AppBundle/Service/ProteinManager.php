<?php
namespace AppBundle\Service;

class ProteinManager
{
    var $id;
    var $name;
    var $sequence;


    /**
     * Returns the length of a protein sequence().
     * @return int
     */
    function seqlen() { 
        return strlen($this->sequence);
    }


    /**
     * Computes the molecular weight of a protein sequence.
     * @return boolean
     */
    function molwt()
    {
        $lowerlimit = 0;
        $upperlimit = 1;
        $wts = [
            "A" => [89.09, 89.09],
            "B" => [132.12, 133.1],
            "C" => [121.15, 121.15],
            "D" => [133.1, 133.1],
            "E" => [147.13, 147.13],
            "F" => [165.19, 165.19],
            "G" => [75.07, 75.07],
            "H" => [155.16, 155.16],
            "I" => [131.18, 131.18],
            "K" => [146.19, 146.19],
            "L" => [131.18, 131.18],
            "M" => [149.22, 149.22],
            "N" => [132.12, 132.12],
            "P" => [115.13, 115.13],
            "Q" => [146.15, 146.15],
            "R" => [174.21, 174.21],
            "S" => [105.09, 105.09],
            "T" => [119.12, 119.12],
            "V" => [117.15, 117.15],
            "W" => [204.22, 204.22],
            "X" => [75.07, 204.22],
            "Y" => [181.19, 181.19],
            "Z" => [146.15, 147.13]
        ];

        // Check if characters outside our 20-letter amino alphabet is included in the sequence.
        preg_match_all("/[^GAVLIPFYWSTCMNQDEKRHBXZ]/", $this->sequence, $match);
        // If there are unknown characters, then do not compute molwt and instead return FALSE.
        if (count($match[0]) > 0) {
            return FALSE;
        }

        // Otherwise, continue and calculate molecular weight of amino acid chain.
        $mwt = array(0, 0);
        $amino_len = $this->seqlen();
        for($i = 0; $i < $amino_len; $i++) {
            $amino = substr($this->sequence, $i, 1);
            $mwt[$lowerlimit] += $wts[$amino][$lowerlimit];
            $mwt[$upperlimit] += $wts[$amino][$upperlimit];
        }
        $mwt_water = 18.015;
        $mwt[$lowerlimit] = $mwt[$lowerlimit] - (($this->seqlen() - 1) * $mwt_water);
        $mwt[$upperlimit] = $mwt[$upperlimit] - (($this->seqlen() - 1) * $mwt_water);
        return $mwt;
    }
}
