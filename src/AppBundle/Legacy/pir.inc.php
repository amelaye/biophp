<?php
require_once("etc.inc.php");
require_once("seq.inc.php");

class PIRSeq
{
// NOTE: This is not yet a complete listing of all properties!

    var $entry_name;
    var $entry_type;
    var $title;
    var $accession;
    var $organism;			// common name of source organism
    var $species;			// scientific name of source organism

    var $create_date;
    var $seqrev_date;
    var $txtchg_date;

    var $length;
    var $molwt;
    var $checksum;
    var $keywords;
}

// parse_protein_pir_codata() parses a PIR (Codata) data file and returns a
// PIRSeq object containing parsed data.
function parse_protein_pir_codata($flines, $sql_db = "NONE")
{
    /*
    $seqarr = array();
    $inseq_flag = false;
    $seqdata_flag = false;
    $accession_flag = false;
    $ref_array = array();
    $feature_array = array();
    $entry_ctr = 0;
    $ref_ctr = 0;
    $maxlength = 0;
    $minlength = 999999;
    $tot_seqlength = 0;

    $in_source_flag = FALSE;
    $source_string = "";
 .)
    $aTaxonomy = array();
    $tax_string = "";
    $in_organism_flag = FALSE;

    $wordarray = array();
    $keywords_string = "";
    $in_keywords_flag = FALSE;
    */

    // PIR starts here
    $title_flag = FALSE;
    $title_string = "";

    $orgasm_flag = FALSE;
    $orgasm_string = "";

    $date_flag = FALSE;
    $date_string = "";

    $acc_flag = FALSE;
    $acc_string = "";

    $kw_flag = FALSE;
    $kw_string = "";

    while( list($lineno, $linestr) = each($flines) )
    { // OPENS outermost while( list($lineno, $linestr) = each($flines) )

        $linelabel = trim(substr($linestr, 0, 16));
        $linedata = trim(substr($linestr, 16));

        /* ENTRY data field - contains $entry_name and $entry_type data items.
        Syntax:
           ENTRY           entry_name #key1 val1 #key2 val2 ...
        Example:
           ENTRY           RHTDTO  #type complete
           ENTRY           RHPGT  #type complete
           ENTRY           I50412  #type fragment
        */

        if ($linelabel == "ENTRY")
        {
            $entry_ctr++;
            $ref_ctr = 0;
            $ref_array = array();

            // This is the beginning of a SEQUENCE ENTRY.
            $seqdata = "";

            // ENTRY           entry_name #key1 val1 #key2 val2 ...
            // preg_splitting this by the # symbol would produce this:
            // entry_name, key1 val1, key2 val2

            $line_tokens = preg_split("/#/", $linedata, -1, PREG_SPLIT_NO_EMPTY);

            // Assume that first token is always the entry name.
            $entry_name = $line_tokens[0];

            // remove the first item from the array (rep. entry name) and process
            // the succeeding key-value pairs.
            array_shift($line_tokens);
            foreach($line_tokens as $keyval)
            {
                $keyval_tokens = preg_split("/\s+/", $keyval, -1, PREG_SPLIT_NO_EMPTY);
                $key = $keyval_tokens[0];
                // remove the first item from array (rep. the key name), leaving the key values.
                array_shift($keyval_tokens);
                // rebuild the value, joining them with a whitespace character.
                $val = implode(" ", $keyval_tokens);
                $aEntry[$key] = $val;
            }
            $entry_type = $aEntry["type"];

            $inseq_flag = true;
        }

        /* TITLE data field - contains $title data item - may be multi-line.
           Example:
           TITLE           R-phycoerythrin alpha-1 chain - red alga (Gastroclonium
           coulteri) (fragment)
        */

        if ($linelabel == "TITLE")
        {
            $title_string .= $linedata . " ";
            $title_flag = TRUE;
        }
        elseif ( ($linelabel == "") and ($title_flag) )
            $title_string .= $linedata . " ";
        elseif ( ($linelabel != "") and ($title_flag) )
        {
            $title = trim($title_string);
            $title_flag = FALSE;
            $title_string = "";
        }

        /* ORGANISM data field - may be multiline.
           Example:
           ORGANISM        #formal_name Oryctolagus cuniculus #common_name domestic
           rabbit
        */

        if ($linelabel == "ORGANISM")
        {
            $orgasm_string .= $linedata . " ";
            $orgasm_flag = TRUE;
        }
        elseif ( ($linelabel == "") and ($orgasm_flag) )
            $orgasm_string .= $linedata . " ";
        elseif ( ($linelabel != "") and ($orgasm_flag) )
        {
            $organism = trim($orgasm_string);
            // formal_name blah blah, common_name yakity yak
            $orgasm_tokens = preg_split("/#/", $organism, -1, PREG_SPLIT_NO_EMPTY);

            foreach($orgasm_tokens as $keyval)
            {
                $keyval_tokens = preg_split("/\s+/", $keyval, -1, PREG_SPLIT_NO_EMPTY);
                $key = $keyval_tokens[0];
                // remove the first item from array (rep. the key name), leaving the key values.
                array_shift($keyval_tokens);
                // rebuild the value, joining them with a whitespace character.
                $val = implode(" ", $keyval_tokens);
                $aEntry[$key] = $val;
            }
            $organism = $aEntry["common_name"];
            $species = $aEntry["formal_name"];

            $orgasm_flag = FALSE;
            $orgasm_string = "";
        }

        /* DATE data field - may be multiline.  Handle the same way as ORGANISM.
           Example:
           DATE            15-Jun-2001 #sequence_revision 15-Jun-2001 #text_change
           15-Jun-2001
        */

        if ($linelabel == "DATE")
        {
            $date_string .= $linedata . " ";
            $date_flag = TRUE;
        }
        elseif ( ($linelabel == "") and ($date_flag) )
            $date_string .= $linedata . " ";
        elseif ( ($linelabel != "") and ($date_flag) )
        {
            $date = trim($date_string);
            // create_date #key1 dateval1 #key2 dateval2
            $date_tokens = preg_split("/#/", $date, -1, PREG_SPLIT_NO_EMPTY);
            $create_date = $date_tokens[0];
            array_shift($date_tokens);
            // $date_tokens after array_shift: key1 dateval1, key2 dateval2

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
            $seqrev_date = $aEntry["sequence_revision"];
            $txtchg_date = $aEntry["text_change"];

            $date_flag = FALSE;
            $date_string = "";
        }

        /* ACCESSIONS data field - may have one or more accessions separated by semicolon (;),
        may be multiline.
           Example:
           ACCESSIONS      PT0622; PT0680; PT0582; PT0673
        */

        if ($linelabel == "ACCESSIONS")
        {
            $acc_string .= $linedata . " ";
            $acc_flag = TRUE;
        }
        elseif ( ($linelabel == "") and ($acc_flag) )
            $acc_string .= $linedata . " ";
        elseif ( ($linelabel != "") and ($acc_flag) )
        {
            $accession = trim($acc_string);
            $acc_tokens = preg_split("/;/", $accession, -1, PREG_SPLIT_NO_EMPTY);
            array_walk($acc_tokens, "trim_element");
            $acc_flag = FALSE;
            $acc_string = "";
        }

        /* KEYWORDS data field - similar to ACCESSIONS in format and handling
           Example:
           KEYWORDS        amidated carboxyl end; cutaneous gland; hormone;
                              pyroglutamic acid
        */

        if ($linelabel == "KEYWORDS")
        {
            $kw_string .= $linedata . " ";
            $kw_flag = TRUE;
        }
        elseif ( ($linelabel == "") and ($kw_flag) )
            $kw_string .= $linedata . " ";
        elseif ( ($linelabel != "") and ($kw_flag) )
        {
            $keywords = trim($kw_string);
            $kw_tokens = preg_split("/;/", $keywords, -1, PREG_SPLIT_NO_EMPTY);
            array_walk($kw_tokens, "trim_element");

            $kw_flag = FALSE;
            $kw_string = "";
        }

        /* SUMMARY data field - contains (sequence) length, molecular weight, and checksum data items.
           We can safely assume that this is always exactly one line.
           Example:
           SUMMARY         #length 3  #molecular-weight 380  #checksum 465
        */

        if ($linelabel == "SUMMARY")
        {
            $sum_tokens = preg_split("/#/", $linedata, -1, PREG_SPLIT_NO_EMPTY);
            // result: length 3, molecular-weight 380, checksum 465
            foreach($sum_tokens as $keyval)
            {
                $keyval_tokens = preg_split("/\s+/", $keyval, -1, PREG_SPLIT_NO_EMPTY);
                $key = $keyval_tokens[0];
                // remove the first item from array (rep. the key name), leaving the key values.
                array_shift($keyval_tokens);
                // rebuild the value, joining them with a whitespace character.
                $val = implode(" ", $keyval_tokens);
                $aEntry[$key] = $val;
            }
            $molwt = (float) $aEntry["molecular-weight"];
            $length = (int) $aEntry["length"];
            $checksum = $aEntry["checksum"];
        }

        // End of record (EOR) marker
        if ($linelabel == "//") break;

    } // CLOSES outermost while( list($lineno, $linestr) = each($flines) )

    $oPIRSeq = new PIRSeq();
    $oPIRSeq->entry_name = $entry_name;
    $oPIRSeq->entry_type = $entry_type;
    $oPIRSeq->title = $title;
    $oPIRSeq->organism = $organism;
    $oPIRSeq->species = $species;
    $oPIRSeq->create_date = $create_date;
    $oPIRSeq->seqrev_date = $seqrev_date;
    $oPIRSeq->txtchg_date = $txtchg_date;
    $oPIRSeq->accession = $acc_tokens;
    $oPIRSeq->keywords = $kw_tokens;
    $oPIRSeq->molwt = $molwt;
    $oPIRSeq->length = $length;
    $oPIRSeq->checksum = $checksum;

    /*
    $oPIRSeq->moltype = $seqobj_moltype;
    $oPIRSeq->seqlength = $seqobj_seqlength;
    $oPIRSeq->date = $seqobj_date;
    $oPIRSeq->strands = $seqobj_strands;
    $oPIRSeq->topology = $seqobj_topology;
    $oPIRSeq->division = $seqobj_division;
    $seqobj->seqarray = $seqarr;
    */

    return $oPIRSeq;
} // Closes parse_protein_pir_codata() function definition

?>
