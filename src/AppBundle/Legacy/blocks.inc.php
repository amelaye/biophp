<?php
// blocks.inc.php

class ProtFam_Blocks
{
    var $id;
    var $accession;
    var $dist_min;
    var $dist_max;
    var $desc;
    var $aa_triplet;
}

function parse_protfam_blocks($flines)
{
    // Initialize variables here.
    $desc_flag = FALSE;
    $desc_string = "";

    while ( list($no, $linestr) = each($flines) )
    { // OPENS 1st (outermost) while ( list($no, $linestr) = each($flines) )
        $linelabel = left($linestr, 2);
        $linedata = trim(substr($linestr, 5));
        $lineend = right($linedata, 1);

        if ($linelabel == "ID")
        {
            $id_tokens = preg_split("/;/", $linedata, -1, PREG_SPLIT_NO_EMPTY);
            $id = trim($id_tokens[0]);
        }

        /* AC - ACCESSION no and related info data field.
           Syntax:  AC   <accession no>; distance from previous block=(min,max)
           Example: AC   IPB002128C; distance from previous block=(30,31)
        */

        if ($linelabel == "AC")
        {
            $ac_tokens = preg_split("/;/", $linedata, -1, PREG_SPLIT_NO_EMPTY);
            $accession = trim($ac_tokens[0]);
            $distance_string = trim($ac_tokens[1]);
            $dist_tokens = preg_split("/\(/", $distance_string, -1, PREG_SPLIT_NO_EMPTY);
            // "distance from previous block=", "min,max)"
            $minmax_tokens = preg_split("/,/", $dist_tokens[1], -1, PREG_SPLIT_NO_EMPTY);
            // "min", "max)"
            $dist_min = (int) $minmax_tokens[0];
            // we remove the ")" at the end of "max)"
            $dist_max = (int) (substr($minmax_tokens[1], 0, strlen($minmax_tokens[1])-1));
        }

        if ($linelabel == "DE")
        {
            $desc_string .= $linedata . " ";
            $desc_flag = TRUE;
        }
        elseif ($desc_flag)
        {
            $desc = trim($desc_string);
            $desc_flag = FALSE;
        }

        /* BL - BLOCKS data field
           Example: BL   RDG;  width=55; seqs=158; 99.5%=1918; strength=2332
        */

        if ($linelabel == "BL")
        {
            $bl_tokens = preg_split("/;/", $linedata, -1, PREG_SPLIT_NO_EMPTY);
            $aa_triplet = trim($bl_tokens[0]);
        }

        if ($linelabel == "//") break;
    }

    $oProtFam = new ProtFam_Blocks();
    $oProtFam->id = $id;
    $oProtFam->accession = $accession;
    $oProtFam->desc = $desc;
    $oProtFam->dist_min = $dist_min;
    $oProtFam->dist_max = $dist_max;
    $oProtFam->aa_triplet = $aa_triplet;

    return $oProtFam;
}
?>