<?php
//pdbstr.inc.php

require_once("etc.inc.php");
require_once("seqdb.inc.php");
require_once("seq.inc.php");

class Gene_Unigene
{
// MEMBER section
    var $entry_id;
    var $title;
    var $seq_count;
}

function parse_gene_unigene($flines)
{
    // initialize variables here
    $title_flag = FALSE;
    $title_string = "";

    while ( list($no, $linestr) = each($flines) )
    {
        $linelabel = trim(substr($linestr,0,12));
        $linedata = trim(substr($linestr,12));

        // ID data field - from observation, one entry (word) in one line.
        //	Example: ID          Sbi.1
        if ($linelabel == "ID") $entry_id = $linedata;

        /* TITLE data field - assume to be multiline.
           Example:
           TITLE       ESTs, Moderately similar to  putative pyrophosphate-fructose-6-phosphate 1-phosphotransferase [Arabidopsis thaliana] [A.thaliana]
        */

        if ($linelabel == "TITLE")
        {
            $title_string .= $linedata . " ";
            $title_flag = TRUE;
        }
        elseif ($title_flag)
        {
            $title = trim($title_string);
            $title_string = "";
            $title_flag = FALSE;
        }

        /* EXPRESS data field
           Example:
           EXPRESS     Embryos germinated for 24 hr ; 10- to 14-day-old light-grown (greenhouse) seedlings ; Mix of ovaries of varying immature stages from 8-week-old plants ; Developing preanthesis pannicles ; Leaves
        */
        if ($linelabel == "EXPRESS") {}


        /* PROTSIM data field
           Example:
           PROTSIM     ORG=Arabidopsis thaliana; PROTGI=15221156; PROTID=ref:NP_172664.1; PCT=79.41; ALN=68
        */
        if ($linelabel == "PROTSIM") {}

        /* SCOUNT - SEQUENCE COUNT data field
           Example:
           SCOUNT      12
        */

        if ($linelabel == "SCOUNT") $seq_count = (int) $linedata;

        if ($linelabel == "//") break;
    }

    $oGene = new Gene_Unigene();
    $oGene->entry_id = $entry_id;
    $oGene->title = $title;
    $oGene->seq_count = $seq_count;

    return $oGene;
}
?>
