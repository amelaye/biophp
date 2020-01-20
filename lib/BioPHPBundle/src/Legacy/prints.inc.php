<?php

require_once("etc.inc.php");
require_once("seq.inc.php");
require_once("seqdb.inc.php");

class PrintsMotif
{
    var $entry_name;
    var $entry_type;
    var $create_date;
    var $upd_date;
    var $desc;
}

function parse_motif_prints($flines)
{
    // Initialize variables (flags and string ) here.
    $desc_flag = FALSE;
    $desc_string = "";

    while ( list($no, $linestr) = each($flines) )
    { // OPENS 1st (outermost) while ( list($no, $linestr) = each($flines) )
        $linelabel = left($linestr, 3);
        $linedata = trim(substr($linestr, 4));

        // GC data field - seems to contain the entry name (one word?) in exactly one line.
        if ($linelabel == "gc;") $entry_name = $linedata;
        // GN data field - seems to contain the entry type (> 1 word) in exactly one line.
        if ($linelabel == "gn;") $entry_type = $linedata;

        // GA data field - DATE CREATED and UPDATED.  Assume exactly one line.
        // Example: ga; 16-NOV-1995; UPDATE 06-JUN-1999

        if ($linelabel == "ga;")
        {
            $date_tokens = preg_split("/;/", $linedata, -1, PREG_SPLIT_NO_EMPTY);
            $create_date = $date_tokens[0];
            array_shift($date_tokens);
            foreach($date_tokens as $keyval)
            {
                $keyval_tokens = preg_split("/\s+/", $keyval, -1, PREG_SPLIT_NO_EMPTY);
                $key = $keyval_tokens[0];
                // remove the first item from array (rep. the key name), leaving the key values.
                array_shift($keyval_tokens);
                // rebuild the value, joining them with a whitespace character.
                $val = implode(" ", $keyval_tokens);
                $aEntry[$key] = $val;
            }
            $upd_date = $aEntry["UPDATE"];
        }

        // GD data field - DESCRIPTION entry - mostly multiline, connect with whitespace.
        if ($linelabel == "gd;")
        {
            $desc_string .= $linedata . " ";
            $desc_flag = TRUE;
        }
        elseif ($desc_flag)
        {
            $desc = trim($desc_string);
            $desc_flag = FALSE;
            $desc_string = "";
        }

    }

    $oPrintsMotif = new PrintsMotif();
    $oPrintsMotif->entry_name = $entry_name;
    $oPrintsMotif->entry_type = $entry_type;
    $oPrintsMotif->desc = $desc;
    $oPrintsMotif->create_date = $create_date;
    $oPrintsMotif->upd_date = $upd_date;

    return $oPrintsMotif;

} // closes function parse_motif_prints()
?>