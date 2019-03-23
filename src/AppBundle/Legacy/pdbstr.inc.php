<?php
//pdbstr.inc.php

require_once("etc.inc.php");
require_once("seqdb.inc.php");
require_once("seq.inc.php");

class Protein_PDBSTR
{
// MEMBER section
    var $entry_id;
    var $moltype;
    var $length;
    var $entry_group;
    var $create_date;
    var $upd_date;
}

function parse_protein_pdbstr($flines)
{
    // initialize variables here

    while ( list($no, $linestr) = each($flines) )
    {
        $linelabel = trim(substr($linestr,0,12));
        $linedata = trim(substr($linestr,12));

        /* MEMBER section - for now, assume that all data items on this line are mandatory.
           Example: MEMBER      1YIC_01       108    PROTEIN      1YIC  97/02/18   97/07/23
        */

        if ($linelabel == "MEMBER")
        {
            $member_tokens = preg_split("/\s+/", $linedata, -1, PREG_SPLIT_NO_EMPTY);
            array_walk($member_tokens, "trim_element");
            $entry_id = $member_tokens[0];
            $length = $member_tokens[1];
            $moltype = $member_tokens[2];
            $entry_group = $member_tokens[3];
            $create_date = $member_tokens[4];
            $upd_date = $member_tokens[5];
        }

        if ($linelabel == "//") break;
    }

    $oProtein = new Protein_PDBSTR();
    $oProtein->entry_id = $entry_id;
    $oProtein->moltype = $moltype;
    $oProtein->length = $length;
    $oProtein->entry_group = $entry_group;
    $oProtein->create_date = $create_date;
    $oProtein->upd_date = $upd_date;

    return $oProtein;
}
?>