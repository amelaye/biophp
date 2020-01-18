<?php

require_once("seqdb.inc");
require_once("etc.inc");

/*
lit.inc - include file containing the classes for Biomedical literature such as journals, etc.

Description: Contains the definition of the above classes and some helper functions.
Author: Serge Gregorio, Jr.
Date: May 11, 2003
License: General Public License 2.0

This code has been written as part of the GenePHP/BioPHP project, located at:

   http://genephp.sourceforge.net
*/

class Lit
{
}

/*
JrId: 1
JournalTitle: AADE editors' journal.
MedAbbr: AADE Ed J
ISSN: 0160-6999
ESSN:  ---
IsoAbbr:  ---
NlmId: 7708172
*/

class Journal
{
    var $id;
    var $title;
    var $med_abbr;
    var $issn;
    var $essn;
    var $iso_abbr;
    var $nlm_id;
}

function parseall_journal_ncbilit($fp)
{
    while( list($lineno, $linestr) = each($flines) )
    {
        $oJournal = new Journal;
        $oJournal = parse_journal_ncbi($flines);
    }
}

function parse_journal_ncbilit($flines)
{
    while( list($lineno, $linestr) = each($flines) )
    { // OPENS outermost while() loop

        // For now, assume that there are no multi-line data fields.
        if (left($linestr,10) == "----------")
        {
            // we detect the end of entry/record marker, start a new record.
            break;
        }
        else
        {
            $line_r = preg_split("/: /", $linestr, -1, PREG_SPLIT_NO_EMPTY);
            $label = trim($line_r[0]);
            $value = trim($line_r[1]);
            /*
            print $label . " : " . $value;
            print "<BR>";
            */
            $$label = $value;
        }
    }

    $oJournal = new Journal();

    $oJournal->id = $JrId;
    $oJournal->title = $JournalTitle;
    $oJournal->med_abbr = $MedAbbr;
    $oJournal->issn = $ISSN;
    $oJournal->essn = $ESSN;
    $oJournal->iso_abbr = $IsoAbbr;
    $oJournal->nlm_id = $NlmId;

    return $oJournal;
}

/*
function parse_compound_kegg($flines)
   {
   // Initialization of variables.

   $in_name_flag = FALSE;
   $aNames = array();
   $name_string = "";

   $in_path_flag = FALSE;
   $path_string = "";
   $aPaths = array();

   $in_react_flag = FALSE;
   $react_string = "";

   $in_enzyme_flag = FALSE;
   $enzyme_string = "";

   $in_dblink_flag = FALSE;
   $aDblinks = array();

   while( list($lineno, $linestr) = each($flines) )
      { // OPENS outermost while() loop

      $label = trim(left($linestr, 12));

      // Assume that ENTRY is always one line.
      if ($label == "ENTRY") $entry = trim(substr($linestr, 12));

      // NAME entry is made up of one or more names, the preferred name is at
      // the first line, other alternative names are in succeeding lines.  It
      // is possible for a long name to occupy two or more lines.  But for now,
      // let's assume one name in one line.
      if ($label == "NAME")
         {
         $aNames = array();
         $aNames[] = trim(substr($linestr,12));
         $in_name_flag = TRUE;
         }
      elseif ( (strlen($label) == 0) and ($in_name_flag) )
         {
         $aNames[] = trim(substr($linestr,12));
         }
      elseif ( (strlen($label) > 0) and ($in_name_flag) )
         {
         $in_name_flag = FALSE;
         }
*/
?>