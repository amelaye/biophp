<?php

require_once("etc.inc.php");
require_once("seq.inc.php");

class AAIndex
{
    var $accession;
    var $desc;
    var $lit_ref;

    var $author;
    var $title;
    var $journal;
    var $comment;
    var $corel_accno;
    var $index_data;
}

// parse_amino_aaindex() parses entries in the AAINDEX1 file and returns
// an AAINDEX object containing parsed data.
function parse_amino_aaindex($flines)
{
    $desc_flag = FALSE;
    $desc_string = "";

    $aRefs = array();

    while ( list($no, $linestr) = each($flines) )
    { // OPENS 1st (outermost) while ( list($no, $linestr) = each($flines) )
        $linelabel = left($linestr, 1);
        $linedata = trim(substr($linestr, 2));

        // H - (HEADER?) ACCESSION NO data field - one string (word) in one line.
        if ($linelabel == "H") $accession = $linedata;

        // D - DESCRIPTION data field. Multilines connected with " ".  Next lines
        // have no "D" at start (like GenBank style, unlike Swissprot's).

        if ($linelabel == "D")
        {
            $desc_string = $linedata . " ";
            $desc_flag = TRUE;
        }
        elseif (($linelabel == " ") and ($desc_flag))
            $desc_string .= $linedata . " ";
        elseif ( ($linelabel != " ") and ($desc_flag) )
        {
            $desc = trim($desc_string);
            $desc_flag = FALSE;
        }

        /* R - (REFERENCES?) LITDB ENTRY NO, PMID AND OTHER REFERENCES data field
        From sample data, it appears to be only one line, of the form:
           Syntax: R DBNAME1:ID_NO1 DBNAME2:ID_NO2 ...
           Example: R LIT:1810048b PMID:1575719
           NOTE: There are "blank or empty R lines", i.e. R following by nothing.
        */
        if ( ($linelabel == "R") and (strlen(trim($linedata)) > 0) )
        {
            $ref_tokens = preg_split("/\s+/", $linedata);
            if (count($ref_tokens) == 0) $ref_tokens = array($linedata);
            foreach($ref_tokens as $ref_item)
            {
                $item_tokens = preg_split("/\:/", $ref_item, -1, PREG_SPLIT_NO_EMPTY);
                $dbname = $item_tokens[0];
                $entry_no = $item_tokens[1];
                $aRefs[$dbname] = $entry_no;
            }
        }

        if ($linelabel == "//") break;

    } // CLOSES 1st (outermost) while ( list($no, $linestr) = each($flines) )

    $oAAIndex = new AAIndex();
    $oAAIndex->accession = $accession;
    $oAAIndex->desc = $desc;
    $oAAIndex->lit_ref = $aRefs;

    return $oAAIndex;

} // CLOSES parse_amino_aaindex_transfac() function
?>