<?php

require_once("etc.inc.php");
require_once("seq.inc.php");
require_once("seqdb.inc.php");

class Promoter
{
    var $entry_name;
    var $data_type;
    var $insite_type;
    var $tax_div;

    var $accession;
    var $create_date;
    var $create_rel;
    var $sequpd_date;
    var $sequpd_rel;
    var $notupd_date;
    var $notupd_rel;
}

// parse_promoter_epd() parses an EPD data file and returns a Promoter object
// containing parsed data.
function parse_promoter_epd($flines)
{
    $accession = array();
    $date_r = array();
    $desc = "";
    $desc_lnctr = 0;
    $gename_r = array();
    $os_r = array();
    $os_linectr = 0;
    $os_str = "";
    $oc_linectr = 0;
    $oc_str = "";
    $ref_r = array();
    $ra_r = array();
    $ra_ctr = 0;
    $ra_str = "";
    $rl_ctr = 0;
    $rl_str = "";
    $db_r = array();
    $ft_r = array();
    $kw_str = "";
    $kw_r = array();

    $cc_string = "";
    $in_cc_flag = FALSE;
    $aComments = array();

    while ( list($no, $linestr) = each($flines) )
    { // OPENS 1st (outermost) while ( list($no, $linestr) = each($flines) )
        $linelabel = left($linestr, 2);
        $linedata = trim(substr($linestr, 5));
        $lineend = right($linedata, 1);

        // May 20, 2003: Added this IF statement to handle CC (COMMENT) lines.
        // CC - COMMENTS data field. Freetext. Entries may be subdivided into TOPICS.
        // For now, ignore topics and just assume it's one long string.
        // I placed this at the TOP (ahead of REFERENCES or RN section) to avoid
        // complications brought about by the call to PREV() inside RN.

        if ($linelabel == "CC")
        {
            if (left($linedata,3) == '-!-')
            {
                // START OF A COMMENT BLOCK
                if (strlen(trim($cc_string)) > 0)
                {
                    // There is a previous comment block that needs to be "saved".
                    $aComments[] = $cc_string;
                }
                $cc_string = "";
                $cc_string .= $linedata . " ";
            }
            else $cc_string .= $linedata . " ";

            $in_cc_flag = TRUE;
        }
        elseif ($in_cc_flag)
        {
            // automatically assume that $aComments contains something already.
            $aComments[] = trim($cc_string);
            $cc_string = "";
            $in_cc_flag = FALSE;
        }

        // ID - IDENTIFICATION data field.

        if (left($linestr, 2) == "ID")
        {
            $words = preg_split("/;/", substr($linestr, 5));
            // May 20, 2003: Changed \s to [\s]+ below.
            $endc = preg_split("/[\s]+/", $words[0]);
            $entry_name = $endc[0];
            $data_class = $endc[1];
            $insite_type = trim($words[1]);
            $tax_div = trim($words[2]);
            if (right($tax_div,1) == ".") $tax_div = substr($tax_div, 0, strlen($tax_div)-1);
        }

        // AC - ACCESSION data field.

        if (left($linestr, 2) == "AC")
        {
            $accstr = $linedata;
            // May 20, 2003: Commented out the line below.  We will not remove
            // the ; at the end of an AC line.  Instead, we use PREG_SPLIT_NO_EMPTY.
            // $accstr = substr($accstr, 0, strlen($accstr)-1);
            // May 20, 2003: Added the -1, PREG_SPLIT_NO_EMPTY arguments below.
            // $accline = preg_split("/;/", intrim($accstr);
            $accline = preg_split("/;/", $accstr, -1, PREG_SPLIT_NO_EMPTY);
            $accession = array_merge($accession, $accline);
        }

        // DT - DATE (of entry) data field.  Similar to Swissprot.

        if (left($linestr, 2) == "DT")
        { // OPENS if (left($linestr, 2) == "DT")
            // DT DD-MMM-YEAR (REL. XX, COMMENT)
            $datestr = $linedata;
            $datestr = substr($datestr, 0, strlen($datestr)-1);
            $words = preg_split("/\(/", $datestr);
            // ( "DD-MMM-YEAR ", "REL. XX, COMMENT")
            $firstcomma = strpos($words[1], ",");
            // May 20, 2003: Converted $comment below into uppercase.
            $comment = strtoupper(trim(substr($words[1], $firstcomma+1)));

            // ( "CREATED" => (date, rel), "LAST SEQUENCE UPDATE" => (date, rel),
            //   "LAST ANNOTATION UPDATE" => (date, rel), COMMENT1 => (date, rel),
            //   "COMMENT2" => (date, rel), ... )

            if ($comment == "CREATED")
            { // this DT line is a DATE CREATED line.
                $create_date = substr($words[0], 0, 11);
                $create_rel = substr($words[1], 5, ($firstcomma-5));
                $date_r[$comment] = array($create_date, $create_rel);
            }
            // NOTE 1: Edited this ELSEIF line. See notes at file end. - Serge
            // elseif ($comment == "LAST SEQUENCE UPDATE")
            elseif ( is_integer(strpos($comment, "LAST SEQUENCE UPDATE")) )
            { // this DT line represents LAST SEQUENCE UPDATE
                $sequpd_date = substr($words[0], 0, 11);
                $sequpd_rel = substr($words[1], 5, ($firstcomma-5));
                $date_r[$comment] = array($sequpd_date, $sequpd_rel);
            }
            // NOTE 1: Edited this ELSEIF line. See notes at file end. - Serge
            // elseif ($comment == "LAST ANNOTATION UPDATE")
            elseif ( is_integer(strpos($comment, "LAST ANNOTATION UPDATE")) )
            { // this DT line represents LAST ANNOTATION UPDATE
                $notupd_date = substr($words[0], 0, 11);
                $notupd_rel = substr($words[1], 5, ($firstcomma-5));
                $date_r[$comment] = array($notupd_date, $notupd_rel);
            }
            else
            {
                // For now, we do not check vs. duplicate comments.
                // We just overwrite the older comment with new one.
                $other_comment = $comment;
                $other_date = substr($words[0], 0, 11);
                $other_rel = substr($words[1], 5, ($firstcomma-5));
                $date_r[$comment] = array($other_date, $other_rel);
            }
        } // CLOSES if (left($linestr, 2) == "DT")

        // DE - DESCRIPTION data field. May be one or more lines. Concatenate and store as one string.
        // Keyword (FRAGMENT) or (FRAGMENTS) may be found at the end of this string.

        if (left($linestr, 2) == "DE")
        { // OPENS if (left($linestr, 2) == "DE")
            $desc_lnctr++;
            $linestr = $linedata;
            if ($desc_lnctr == 1) $desc .= $linestr;
            else $desc .= " " . $linestr;

            // Checks if (FRAGMENT) or (FRAGMENTS) is found at the end
            // of the DE line to determine if sequence is complete.
            if (right($linestr, 1) == ".")
            { // OPENS if (right($linestr, 1) == ".")
                if ( 	(strtoupper(right($linestr, 11)) == "(FRAGMENT).") or
                    (strtoupper(right($linestr, 12)) == "(FRAGMENTS).") )
                    $is_fragment = TRUE;
                else $is_fragment = FALSE;
            } // CLOSE if (right($linestr, 1) == ".")
        } // CLOSES if (left($linestr, 2) == "DE")


    } // CLOSES 1st (outermost) while ( list($no, $linestr) = each($flines) )

    $oPromoter = new Promoter();
    $oPromoter->entry_name = $entry_name;
    $oPromoter->data_class = $data_class;
    $oPromoter->insite_type = $insite_type;
    $oPromoter->tax_div = $tax_div;

    $oPromoter->accession = $accession;
    $oPromoter->create_date = $create_date;
    $oPromoter->create_rel = $create_rel;
    $oPromoter->sequpd_date = $sequpd_date;
    $oPromoter->sequpd_rel = $sequpd_rel;
    $oPromoter->notupd_date = $notupd_date;
    $oPromoter->notupd_rel = $notupd_rel;

    /*
    $seqobj->id = $protein_name;
    $seqobj->seqlength = $length;
    $seqobj->moltype = $moltype;
    $seqobj->date = $create_date;
    $seqobj->accession = $accession[0];
    $seqobj->source = $os_line;
    $seqobj->organism = $oc_line;
    $seqobj->sequence = $sequence;
    $seqobj->definition = $desc;
    */

    // FT_<keyword> is an ARRAY.
    // process_ft($swiss, $ft_r);

    return $oPromoter;
} // CLOSES parse_promoter_epd()

/* NOTES:

NOTE 1:

June 3, 2003 - I noticed that an EPD entry may have a period (.) at the end
while Swissprot entries do not have one.  This causes my Swissprot code to
run into problems because it uses an exact match of keyphrase (LAST SEQ...)
at a particular position within the DT line.  I'm changing exact match to
a substring containment test.
*/
?>