// This code was written by Serge Gregorio, Jr. (serge525@hotmail.com)
// and is under the General Public License (GPL) 2.0 license.
// Last updated: June 2003

<?php

require_once("etc.inc.php");
require_once("seqdb.inc.php");

class Genome_Entrez
{
    var $entry_name;
    var $moltype;
    var $length;
    var $entry_date;
    var $division;
    var $topology;
    var $strands;

    var $definition;
    var $prim_acc;
    var $accession;
    var $version;
    var $ncbi_gi_id;
    var $keywords;

    var $source;
    var $organism;
    var $taxonomy;

    var $reference;
}

// =======================================================

// parse_<datafield>_<object>_<dbformat>() is a set of functions
// that handle individual data fields of a particular data file. 

function parse_locus_genome_entrez($linestr)
{
    $tmp = array();

    $tmp["ENTRY_NAME"] = trim(substr($linestr, 12, 16));
    $tmp["LENGTH"] = trim(substr($linestr, 29, 11)) * 1;
    $tmp["MOLTYPE"] = trim(substr($linestr, 47, 6));

    // DATE: Converts from 25-DEC-2002 => 25-12-2002
    $dt = strtoupper(substr($linestr, 68, 11));
    if ( !(is_blankstr($dt)) ) $tmp["ENTRY_DATE"] = right($dt,4) ."-". monthno(substr($dt, 3, 3)) ."-". left($dt, 2);
    // if (strlen(trim($entry_date)) != 0) $entry_date = right($dt,4) ."-". monthno(substr($dt, 3, 3)) ."-". left($dt, 2);

    $tmp["STRANDS"] = strtoupper(substr($linestr, 44, 2));
    $tmp["TOPOLOGY"] = topo_code(substr($linestr, 55, 8));
    $tmp["DIVISION"] = strtoupper(substr($linestr, 64, 3));

    return $tmp;
}

// =======================================================

// GENOME ENTREZ parser	that returns an associative array.
// Entrez doesn't seem to have the ff. GenBank DNA data fields: SEGMENT

function parse_genome_entrez($flines)
{
    // Put here code that skips unnecessary file headers/descriptors
    while ( list($no, $linestr) = each($flines) )
    {
        $linelabel = substr($linestr, 0, 5);
        if ($linelabel == "LOCUS") break;
    }

    $aTemp = array();

    // LOCUS label detected - we are at the start of a new record - LOCUS is mandatory, exactly one line.

    // initialize global variables used by other data fields
    $ref_ctr = 0;
    $ref_array = array();
    $seqdata = "";
    $inseq_flag = true;

    // $tmp = ( LOCUS => (ENTRY_NAME => XX1234, LENGTH => 910, MOLTYPE => DNA, ... ) )
    $aTemp["LOCUS"] = parse_locus_genome_entrez($linestr);

    // DEFINITION data field - mandatory, comes after LOCUS, no need to check $linelabel.
    $tmpstr = "";
    while ( list($no, $linestr) = each($flines) )
    {
        $linelabel = trim(substr($linestr,0,12));
        if ( (is_notmt($linelabel)) and ($linelabel != "DEFINITION") ) break;
        $linedata = trim(substr($linestr,12));
        $tmpstr .= $linedata . " ";
    }
    $aTemp["DEFINITION"] = $tmpstr;
    // $tmp = ( LOCUS => (ENTRY_NAME => XX1234, ..), DEFINITION => "The quick brown fox..."  )

    $linelabel = trim(substr($linestr,0,12));
    $linedata = trim(substr($linestr,12));
    $tmpstr = $linedata . " ";
    while ( list($no, $linestr) = each($flines) )
    {
        $linelabel = trim(substr($linestr,0,12));
        if ( (is_notmt($linelabel)) and ($linelabel != "ACCESSION") ) break;
        $linedata = trim(substr($linestr,12));
        $tmpstr .= $linedata . " ";
    }
    $aTemp["ACCESSION"] = explode(" ", trim($tmpstr));
    // $aTemp = ( LOCUS => , DEFINITION => , ACCESSION => (K03160, K03161, ...) )

    // Assume that VERSION line is made up of exactly 2 or 3 tokens in exactly one line.
    // Assume further that VERSION data field is mandatory. (It doesn't say so in GBREL.TXT.)

    $linedata = trim(substr($linestr,12));
    $ver_tokens = preg_split("/\s+/", $linedata);
    $aVer = array();
    $aVer["VERSION"] = trim($ver_tokens[0]);
    if (count($ver_tokens) == 2) $aVer["NCBI_GI_ID"] = trim($ver_tokens[1]);
    $aTemp["VERSION"] = $aVer;
    // $aTemp = ( LOCUS => , DEFINITION => , ACCESSION => , VERSION => (VERSION => KX03150, NCBI_GI_ID => 12345678) )

    list($no, $linestr) = each($flines);
    // KEYWORDS data field - mandatory, multidata, multiline.
    $linedata = trim(substr($linestr,12));
    // if the keyword in the first line is a exactly a period (.) then don't bother.
    if ($linedata != ".")
    {
        $tmpstr = $linedata . " ";
        while ( list($no, $linestr) = each($flines) )
        {
            $linelabel = trim(substr($linestr,0,12));
            if (is_notmt($linelabel)) break;
            $linedata = trim(substr($linestr,12));
            $tmpstr .= $linedata . " ";
        }
        // remove leading/trailing whitespaces from $keyword_string
        $keywords_string = trim($tmpstr);
        // remove the last character (which is always a period) from $keyword_string.
        $keywords_string = substr($keywords_string, 0, (strlen($keywords_string)-1));
        $kw_tokens = preg_split("/;/", trim($keywords_string), -1, PREG_SPLIT_NO_EMPTY);
        array_walk($kw_tokens, "trim_element");
        prev($flines);
    }
    else $kw_tokens = NULL;
    $aTemp["KEYWORDS"] = $kw_tokens;

    // SOURCE data field - assume to be mandatory.
    $tmpstr = "";
    while ( list($no, $linestr) = each($flines) )
    {
        $linelabel = trim(substr($linestr,0,12));
        if ( (is_notmt($linelabel)) and ($linelabel != "SOURCE") ) break;
        $linedata = trim(substr($linestr,12));
        $tmpstr .= $linedata . " ";
    }
    $aTemp["SOURCE"] = trim($tmpstr);
    // $aTemp = ( LOCUS => , DEFINITION => , ACCESSION => , VERSION =>, SOURCE => "Homo sapiens" )

    $linedata = trim(substr($linestr,12));
    $aTemp["ORGANISM"] = $linedata;
    // $aTemp = ( LOCUS => , DEFINITION => , ACCESSION => , VERSION =>, SOURCE =>,
    //            ORGANISM => "homo sapiens" )

    /*
    SOURCE      Homo sapiens
         ORGANISM  Homo sapiens
                   Eukaryota; Metazoa; Chordata; Craniata; Vertebrata; Euteleostomi;
                 Mammalia; Eutheria; Primates; Catarrhini; Hominidae; Homo.
    REFERENCE   1  (bases 1 to 1037383)
    */

    $tmpstr = "";
    while ( list($no, $linestr) = each($flines) )
    {
        $linelabel = trim(substr($linestr,0,12));
        if (is_notmt($linelabel)) break;
        $linedata = trim(substr($linestr,12));
        $tmpstr .= $linedata . " ";
    }
    // remove leading/trailing whitespaces
    $tax_string = trim($tmpstr);
    // remove the last character which is always a period (.)
    $tax_string = substr($tax_string, 0, (strlen($tax_string)-1));
    $aTaxonomy = preg_split("/;/", trim($tax_string), -1, PREG_SPLIT_NO_EMPTY);
    array_walk($aTaxonomy, "trim_element");
    $aTemp["TAXONOMY"] = $aTaxonomy;
    // $aTemp = ( LOCUS => , DEFINITION => , ACCESSION => , VERSION =>, SOURCE =>,
    //            ORGANISM => "homo sapiens", "TAXONOMY" => (Eukaryota, Metazoa, Chordata,...) )

    /* REFERENCE data field
 Example: REFERENCE   1  (bases 1 to 1037383)
    Output: ( (REFNO => 1, BASERANGE => (bases 1 to 10), AUTHORS => (Gregorio,S.E., Cruz,B.E.), TITLE => "Mol micro",
    JOURNAL => "Appl. Environ.", MEDLINE => 12345, PUBMED => 12345), (REFNO => 2, ... ) )
 */

    $ref_all = array();
    while (1)
    {
        $linedata = trim(substr($linestr,12));
        // at this point, we are at the line with REFERENCE x (base y of z) in it.
        $ref_tokens = preg_split("/\s+/", $linedata, -1, PREG_SPLIT_NO_EMPTY);

        $ref_rec = array();
        $ref_rec["REFNO"] = $ref_tokens[0];
        array_shift($ref_tokens);
        $ref_rec["BASERANGE"] = implode(" ", $ref_tokens);
        // $aTemp["REFERENCE"] = $ref_rec;

        while ( list($no, $linestr) = each($flines) )
        {
            if (left($linestr,2) !== "  ") break;
            // Assume that AUTHOR, TITLE, and other subkeys are all optional, but occur in a fixed order.
            $linelabel = trim(substr($linestr,0,12));
            $funcname = "parse_" . strtolower($linelabel) . "_genome_entrez(&\$flines, \$linestr)";
            eval("\$value = " . $funcname . ";");
            $ref_rec[$linelabel] = $value;
        }
        $ref_all[] = $ref_rec;
        if ( (substr($linestr,0,2) !== "  ") and (trim(substr($linestr,0,12)) != "REFERENCE") ) break;
    }

    $aTemp["REFERENCE"] = $ref_all;

    return $aTemp;
}

function parse_medline_genome_entrez(&$flines, $linestr)
{
    return trim(substr($linestr,12));
}

function parse_pubmed_genome_entrez(&$flines, $linestr)
{
    return trim(substr($linestr,12));
}

function parse_title_genome_entrez(&$flines, $linestr)
{
    $linedata = trim(substr($linestr,12));
    $title_string = $linedata . " ";
    while ( list($no, $linestr) = each($flines) )
    {
        $linelabel = trim(substr($linestr,0,12));
        if ( is_notmt($linelabel) ) break;
        $linedata = trim(substr($linestr,12));
        $title_string .= $linedata . " ";
    }
    prev($flines);
    return $title_string;
}

function parse_journal_genome_entrez(&$flines, $linestr)
{
    $linedata = trim(substr($linestr,12));
    $jour_string = $linedata . " ";
    while ( list($no, $linestr) = each($flines) )
    {
        $linelabel = trim(substr($linestr,0,12));
        if ( is_notmt($linelabel) ) break;
        $linedata = trim(substr($linestr,12));
        $jour_string .= $linedata . " ";
    }
    prev($flines);
    return $jour_string;
}

function parse_remark_genome_entrez(&$flines, $linestr)
{
    $linedata = trim(substr($linestr,12));
    $rem_string = $linedata . " ";
    while ( list($no, $linestr) = each($flines) )
    {
        $linelabel = trim(substr($linestr,0,12));
        if ( is_notmt($linelabel) ) break;
        $linedata = trim(substr($linestr,12));
        $rem_string .= $linedata . " ";
    }
    prev($flines);
    return $rem_string;
}

function parse_authors_genome_entrez(&$flines, $linestr)
{
    $linedata = trim(substr($linestr,12));
    $auth_string = $linedata . " ";
    while ( list($no, $linestr) = each($flines) )
    {
        $linelabel = trim(substr($linestr,0,12));
        if ( is_notmt($linelabel) ) break;
        $linedata = trim(substr($linestr,12));
        $auth_string .= $linedata . " ";
    }
    prev($flines);

    $auth_tokens = preg_split("/,\s/", $auth_string, -1, PREG_SPLIT_NO_EMPTY);

    // create an array for the last two author names.
    $lastindex = count($auth_tokens) - 1;
    $last_authors = preg_split("/\sand\s/", $auth_tokens[$lastindex], -1, PREG_SPLIT_NO_EMPTY);
    array_walk($last_authors, "trim_element");

    // remove the last element (author) from the array of authors.
    array_pop($auth_tokens);
    array_walk($auth_tokens, "trim_element");

    $aAuthors = array_merge($auth_tokens, $last_authors);
    return $aAuthors;
}

// =======================================================

// This function converts GENOME ENTREZ info from array into a GENOME ENTREZ object.	

function r2oGenome_Entrez($aTemp)
{
    $oGE = new Genome_Entrez();
    $oGE->entry_name = $aTemp["LOCUS"]["ENTRY_NAME"];
    $oGE->moltype = $aTemp["LOCUS"]["MOLTYPE"];
    $oGE->length = $aTemp["LOCUS"]["LENGTH"];
    $oGE->strands = $aTemp["LOCUS"]["STRANDS"];
    $oGE->topology = $aTemp["LOCUS"]["TOPOLOGY"];
    $oGE->division = $aTemp["LOCUS"]["DIVISION"];
    $oGE->entry_date = $aTemp["LOCUS"]["ENTRY_DATE"];
    $oGE->definition = $aTemp["DEFINITION"];
    $oGE->prim_acc = $aTemp["ACCESSION"][0];
    $oGE->accession = $aTemp["ACCESSION"];
    $oGE->version = $aTemp["VERSION"]["VERSION"];
    $oGE->ncbi_gi_id = $aTemp["VERSION"]["NCBI_GI_ID"];
    $oGE->keywords = $aTemp["KEYWORDS"];
    $oGE->source = $aTemp["SOURCE"];
    $oGE->organism = $aTemp["ORGANISM"];
    $oGE->taxonomy = $aTemp["TAXONOMY"];
    $oGE->reference = $aTemp["REFERENCE"];

    return $oGE;
}

/* NOTES:

If the data fields/sections in a flatfile record have a fixed order, i.e.
DEFINITION (if it appears at all) always comes after LOCUS, etc., then we
can still improve the efficiency of our current GenBank (and other parsers)
that use this code template:

   while ( list($no, $linestr) = each($flines) )
      {
      if ($linelabel == "something-1")
         {
         }

      if ($linelabel == "something-2")
         {
         }

      if ($linelabel == "something-n")
         {
         }
      }
      
The problem with the above is that for every line in the flatfile, the code
would check the $linelabel exactly N times.  Thus if we have a flatfile with
L number of lines, the code would make N * L IF-condition tests.

But if we are sure that DATA FIELD B always comes after DATA FIELD A, then
we can rewrite the code as follows:

   DATA FIELD A [MANDATORY]
   DATA FIELD B [MANDATORY]
   DATA FIELD C [OPTIONAL]
   DATA FIELD D [OPTIONAL]
   DATA FIELD E [MANDATORY]
*/
?>
