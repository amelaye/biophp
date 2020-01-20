<?php
require_once("etc.inc.php");
require_once("seqdb.inc.php");
require_once("seq.inc.php");

class Protein_PMD
{
    var $entry_type;
    var $entry_no;
    var $mutation_type;
    var $article_no;

    var $authors;
    var $journal;
    var $medline_no;
    var $title;

    var $dbref;				// structure unclear, implement later.
    var $protein;
    var $sequence;
    var $source;
    var $n_terminal;
    var $express_sys;
    var $change;			// structure unclear, implement later.
    var $disease;			// structure unclear, implement later.
    var $comment;
}

function parse_protein_pmd($flines)
{
    // initialize variables here.
    $auth_flag = FALSE;
    $auth_string = "";
    $aAuthors = array();

    $jour_flag = FALSE;
    $jour_string = "";

    $title_flag = FALSE;
    $title_string = "";

    while ( list($no, $linestr) = each($flines) )
    {
        $linelabel = trim(left($linestr, 16));
        $linedata = trim(substr($linestr, 16));

        /* ENTRY data field.
           Example:
           ENTRY           A000300 - Artificial                    2607383

           Assume that ENTRY data field is always one line.
           Assume that ENTRY_TYPE and ENTRY_NO can be found at fixed positions in the line.
           Assume that all data items are mandatory (always appear in the ENTRY line).
        */

        if ($linelabel == "ENTRY")
        {
            $entry_type = substr($linedata,0,1);
            $entry_no = substr($linedata,1,6);
            $entry_tokens = preg_split("/\s+/", substr($linedata,10), -1, PREG_SPLIT_NO_EMPTY);
            $mutation_type = trim($entry_tokens[0]);
            $article_no = trim($entry_tokens[1]);
        }

        /* AUTHORS data field
           Example:
           AUTHORS         Shoshani I., Bianchi G., Desaubry L., Dessauer C.W. &
        Johnson R.A.
        */

        if ($linelabel == "AUTHORS")
        {
            $auth_string = $linedata . " ";
            $auth_flag = TRUE;
        }
        elseif ( (strlen(trim($linelabel)) == 0) and ($auth_flag) )
            $auth_string .= $linedata . " ";
        elseif ( (strlen(trim($linelabel)) > 0) and ($auth_flag) )
        {
            $aAuthors = preg_split("/[\,\&]/", $auth_string, -1, PREG_SPLIT_NO_EMPTY);
            array_walk($aAuthors, "trim_element");
            $auth_string = "";
            $auth_flag = FALSE;
        }

        /* MEDLINE data field
           Example: MEDLINE         10666322
           For now, assume that it's always exactly one entry (word).
        */

        if ($linelabel == "MEDLINE") $medline_no = $linedata;

        /* JOURNAL data field
        Example: JOURNAL         Arch.Biochem.Biophys. (2000) 374(2), 389-394
           For now, let's just concatenate all the lines with space.  We don't extract
           individual data items like journal title, publication year, etc.
        */

        if ($linelabel == "JOURNAL")
        {
            $jour_string = $linedata . " ";
            $jour_flag = TRUE;
        }
        elseif ( (strlen(trim($linelabel)) == 0) and ($jour_flag) )
            $jour_string .= $linedata . " ";
        elseif ( (strlen(trim($linelabel)) > 0) and ($jour_flag) )
        {
            $journal = trim($jour_string);
            $jour_string = "";
            $jour_flag = FALSE;
        }

        /* TITLE data field - handle the same way as JOURNAL.
           Example:
           TITLE           Lys-Ala mutations of type I adenylyl cyclase result in altered
                                  susceptibility to inhibition by adenine nucleoside
           3'-polyphosphates.
        */

        if ($linelabel == "TITLE")
        {
            $title_string = $linedata . " ";
            $title_flag = TRUE;
        }
        elseif ( (strlen(trim($linelabel)) == 0) and ($title_flag) )
            $title_string .= $linedata . " ";
        elseif ( (strlen(trim($linelabel)) > 0) and ($title_flag) )
        {
            $title = trim($title_string);
            $title_string = "";
            $title_flag = FALSE;
        }

        if ($linelabel == "///") break;
    }

    $oProtein = new Protein_PMD();
    $oProtein->entry_type = $entry_type;
    $oProtein->entry_no = $entry_no;
    $oProtein->mutation_type = $mutation_type;
    $oProtein->article_no = $article_no;

    $oProtein->authors = $aAuthors;
    $oProtein->medline_no = $medline_no;
    $oProtein->journal = $journal;
    $oProtein->title = $title;

    return $oProtein;
}

?>