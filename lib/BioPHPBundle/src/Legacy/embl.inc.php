<?php

require_once("etc.inc.php");
require_once("seq.inc.php");
require_once("seqdb.inc.php");

class EmblSeq
{
    var $entry_name;
    var $moltype;
    var $data_class;
    var $length;

    var $accession;
    var $create_date;
    var $create_rel;
    var $sequpd_date;
    var $sequpd_rel;
    var $notupd_date;
    var $notupd_rel;

    var $desc;
}

// parse_na_embl() parses an EMBL DNA data file and returns an EmblSeq object
// containing parsed data.
function parse_na_embl($flines)
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
        // Example: ID   AF039870   standard; DNA; GSS; 526 BP.

        if (left($linestr, 2) == "ID")
        { // OPENS if (left($linestr, 2) == "ID")
            $words = preg_split("/;/", substr($linestr, 5));
            // May 20, 2003: Changed \s to [\s]+ below.
            $endc = preg_split("/[\s]+/", $words[0]);
            $entry_name = $endc[0];
            // May 20, 2003: Added the -1 and PREG_SPLIT_NO_EMPTY arguments below.
            $namesrc = preg_split("/_/", $entry_name, -1, PREG_SPLIT_NO_EMPTY);
            $na_name = $namesrc[0];
            $na_source = $namesrc[1];
            $data_class = $endc[1];
            // May 20, 2003: Enclosed $words[1] within a trim() function.
            $moltype = trim($words[1]);

            $bp_tokens = preg_split("/\s+/", $words[3], -1, PREG_SPLIT_NO_EMPTY);
            $length = (int) (trim($bp_tokens[0]));
        } // CLOSES if (left($linestr, 2) == "ID")

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
            elseif ( is_integer(strpos($comment, "LAST UPDATED")) )
            { // this DT line represents LAST SEQUENCE UPDATE
                $sequpd_date = substr($words[0], 0, 11);
                $sequpd_rel = substr($words[1], 5, ($firstcomma-5));
                $date_r[$comment] = array($sequpd_date, $sequpd_rel);
            }
        } // CLOSES if (left($linestr, 2) == "DT")

        // SV - SEQUENCE VERSION data field - exactly one per entry.

        if ($linelabel == "SV") $seq_ver = $linedata;

        // DE - DESCRIPTION data field. May be one or more lines. Concatenate and store as one string.
        // Keyword (FRAGMENT) or (FRAGMENTS) may be found at the end of this string.

        if (left($linestr, 2) == "DE")
        { // OPENS if (left($linestr, 2) == "DE")
            $desc_lnctr++;
            $linestr = $linedata;
            if ($desc_lnctr == 1) $desc .= $linestr;
            else $desc .= " " . $linestr;
        } // CLOSES if (left($linestr, 2) == "DE")

        if ($linelabel == "//") break;

    } // CLOSES 1st (outermost) while ( list($no, $linestr) = each($flines) )

    $oEmblSeq = new EmblSeq();
    $oEmblSeq->entry_name = $na_name;
    $oEmblSeq->data_class = $data_class;
    $oEmblSeq->moltype = $moltype;
    $oEmblSeq->length = $length;
    $oEmblSeq->seq_ver = $seq_ver;

    $oEmblSeq->accession = $accession;
    $oEmblSeq->create_date = $create_date;
    $oEmblSeq->create_rel = $create_rel;
    $oEmblSeq->sequpd_date = $sequpd_date;
    $oEmblSeq->sequpd_rel = $sequpd_rel;
    $oEmblSeq->notupd_date = $notupd_date;
    $oEmblSeq->notupd_rel = $notupd_rel;
    $oEmblSeq->desc = $desc;

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

    return $oEmblSeq;
} // CLOSES parse_na_embl()

?>