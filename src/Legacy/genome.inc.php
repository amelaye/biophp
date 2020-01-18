<?php

require_once("seqdb.inc");
require_once("etc.inc");

/*
genome.inc - include file containing the Genome class which has one parser method
Description: Contains the definition of the Genome class plus some helper functions.
Author: Serge Gregorio, Jr.
Date: May 4, 2003
License: General Public License 2.0

This code has been written as part of the GenePHP/BioPHP project, located at:

   http://genephp.sourceforge.net
*/

class genome
{
    var $organism;       // scientific name of source organism
    var $common_name;    // common name of source organism
    var $tax_class;      // taxonomic classification of source organism (excl. scientific name)

    var $is_complete;   // YES/NO indicating if genome has been completely sequenced
    var $gb_release;     // GenBank release used for stats below
    var $gb_entries;     // number of entries in GenBank
    var $gb_basepairs;   // number of basepairs in Genbank
    var $size;           // haploid size in basepairs

    var $reference;      /* an array of REFERENCE SETs (which is itself an array); a single
                       GENOME record/object may contain one or more REFERENCE ENTRIES.
                       A reference set may hold the ff. info: address, title, journal,
                       volume, pages, year. */

} // closes GENOME class

// $files is an array of name of all the files to be parsed.
function parseall_genome_dogs($files)
{
    $aoGenomes = array();
    foreach($files as $fname)
    {
        $fp = fopen($fname, "r");
        if ($fp == FALSE) die("Cannot open $fname!");
        $flines = array();
        while(1)
        {
            $linestr = fgets($fp, 101);
            if (feof($fp) == TRUE) break;
            $flines[] = $linestr;
            if (left($linestr,2) == '//')
            {
                $aoGenomes[] = parse_genome_dogs($flines);
                $flines = array();
            }
        }
        fclose($fp);
    }
    return $aoGenomes;
    // return FALSE;
}

function parse_genome_dogs($flines)
{ // opens function parse_genome_dogs()
    $entry_ctr = 0;
    $in_record_flag = FALSE;

    // Initialize values for the CLASSIFICATION field.
    $in_tax_flag = FALSE;
    $tax_string = "";
    $tax_class = array();

    // Initialize values for REFERENCE section.
    $in_ref_flag = FALSE;
    $reference = array();
    $curr_ref = array();
    $ref_types = array();

    // Initialize values for AUTHOR subsection within REFERENCE section.
    $author_string = "";
    $author_array = array();

    // Initialize values for TITLE subsection within REFERENCE section.
    $title_string = "";
    $title_array = array();

    while( list($lineno, $linestr) = each($flines) )
    { // OPENS outermost while() loop

        // if record hasn't begun and we haven't found "ORGANISM" tag yet, skip to next line.
        if ( ($in_record_flag == FALSE) and (left($linestr,8) != "ORGANISM") )
            continue;
        else $in_record_flag = TRUE;

//      print $lineno . " :  " . $linestr;
//      print "<BR>";

        if (left($linestr,8) == "ORGANISM")
        {
            $entry_ctr++;
            $organism = trim(substr($linestr,8));
//         print "ORGANISM (INSIDE PARSE): ";
//         print $organism;
//         print "<BR>";
        }
        if (left($linestr,11) == "COMMON_NAME")
        {
            $common_name = trim(substr($linestr,11));
        }
        if (left($linestr,14) == "CLASSIFICATION")
        {
            // Start (first line) of CLASSIFICATION field.
            $tax_string .= trim(substr($linestr,14)) . " ";
            $in_tax_flag = TRUE;
        }
        elseif ( (left($linestr,9) != "COMPLETED") and ($in_tax_flag) )
        {
            // at the 2nd, 3rd, etc. line of the CLASSIFICATION field.
            $tax_string .= trim($linestr) . " ";
        }
        if (left($linestr,9) == "COMPLETED")
        {
            // print $tax_string;
            // print "<BR>";
            // assume that COMPLETED field ALWAYS follows CLASSIFICATION field.
            // tax class field has ended, convert $tax_string into an array.
            $tax_class = preg_split("/;/", $tax_string, -1, PREG_SPLIT_NO_EMPTY);
            array_walk($tax_class, "trim_element");
            $tax_string = "";
            $in_tax_flag = FALSE;

            // assume anything other than "yes" is a "no".
            $completed = strtoupper(trim(substr($linestr,9)));
            $is_complete = ($completed == "YES" ? TRUE : FALSE);
        }
        if (left($linestr,10) == "GB_RELEASE")
        {
            $gb_release = trim(substr($linestr,10));
        }
        if (left($linestr,10) == "GB_ENTRIES")
        {
            $gb_entries = (int) (trim(substr($linestr,10)));
        }
        if (left($linestr,12) == "GB_BASEPAIRS")
        {
            // check if need to convert to (int) or (float)
            $gb_bps = trim(substr($linestr,12));
        }
        if (left($linestr,11) == "GENOME_SIZE")
        {
            // check if need to convert to (int) or (float)
            $size = trim(substr($linestr,11));
        }
        if (left($linestr,8) == "REF_TYPE")
        {
            // if (count($curr_ref) > 0)
            if ($in_ref_flag == TRUE)
            {
                // Add the previous reference set ($curr_ref array) to the big $references array.
                $reference[] = $curr_ref;
                $in_ref_flag == TRUE;

                // Create $ref_type array and add to the $curr_ref array.
                $ref_type = trim(substr($linestr,8));
                $ref_type = preg_split("/\s/", $ref_type, -1, PREG_SPLIT_NO_EMPTY);
                // initialize $reference array to contain one element, the $ref_type array.
                $curr_ref = array();
                $curr_ref["TYPE"] = $ref_type;
                continue;
            }
            else
            {
                $ref_type = trim(substr($linestr,8));
                $ref_type = preg_split("/\s/", $ref_type, -1, PREG_SPLIT_NO_EMPTY);
                // initialize $reference array to contain one element, the $ref_type array.
                $curr_ref = array();
                $curr_ref["TYPE"] = $ref_type;
                $in_ref_flag = TRUE;
                continue;
            }
        }
        if ($in_ref_flag)
        { // opens if ($in_ref_flag)

            // Set of IF statements to handle REF_AUTHOR section.

            // print "(" . trim(left($linestr,11)) . ")";
            // print "<BR>";
            if (left($linestr,10) == "REF_AUTHOR")
            {
                $author_string .= trim(substr($linestr,10)) . " ";
                $in_author_flag = TRUE;
            }
            elseif ( (left($linestr,1) == "\t") and ($in_author_flag) )
            {
                // we are at the 2nd, 3rd, etc. lines of the REF_AUTHOR section.
                $author_string .= trim($linestr) . " ";
            }
            elseif ( (left($linestr,1) != "\t") and ($in_author_flag) )
            {
                // author section has ended, make author string into array.
                $author_array = preg_split("/,/", $author_string, -1, PREG_SPLIT_NO_EMPTY);
                // The next two lines can be replaced with an array_walk().
                // $author_trimmed = array();
                // foreach($author_array as $author) $author_trimmed[] = trim($author);
                array_walk($author_array, "trim_element");
                $curr_ref["AUTHOR"] = $author_array;
                $author_string = "";
                $in_author_flag = FALSE;
            }

            // Set of IF statements to handle REF_TITLE section.
            if (left($linestr,9) == "REF_TITLE")
            {
                // assume there is only one title in one or more lines.
                $title_string .= trim(substr($linestr,9)) . " ";
                $in_title_flag = TRUE;
            }
            elseif ( (left($linestr,1) == "\t") and ($in_title_flag) )
            {
                // we are at the 2nd, 3rd, etc. lines of the REF_TITLE section.
                $title_string .= trim($linestr) . " ";
            }
            elseif ( (left($linestr,1) != "\t") and ($in_title_flag) )
            {
                // title section has ended, add the string accumulated so far to the array with key "TITLE".
                $curr_ref["TITLE"] = $title_string;
                $title_string = "";
                $in_title_flag = FALSE;
            }

            // Handles REF_JOURNAL field (assume to be single line)

            if (left($linestr,11) == "REF_JOURNAL")
            {
                $journal = trim(substr($linestr,11));
                $curr_ref["JOURNAL"] = $journal;
            }

            // Handles REF_VOLUME field (assume to be single line)
            if (left($linestr,10) == "REF_VOLUME")
            {
                $volume = trim(substr($linestr,10));
                $curr_ref["VOLUME"] = $volume;
            }

            // Handles REF_PAGES field (assume to be single line)
            if (left($linestr,9) == "REF_PAGES")
            {
                $pages = trim(substr($linestr,9));
                $curr_ref["PAGES"] = $pages;
            }

            // Handles REF_YEAR field (assume to be single line)
            if (left($linestr,8) == "REF_YEAR")
            {
                $year = trim(substr($linestr,8));
                $curr_ref["YEAR"] = $year;
            }
            if (left($linestr,2) == "//")
            {
                // end of this REFERENCE set within REF section, AND end of record as well.

                // Add the previous reference set ($curr_ref array) to the big $references array.
                $reference[] = $curr_ref;
                // initialize author_string and title_string. placed it here in case we use
                // this function to parse more than one record at a time.
                $curr_ref = array();
                $author_string = "";
                $title_string = "";

                $in_ref_flag = FALSE;
                $in_record_flag = FALSE;
                break;
            }
        } // closes if ($in_ref_flag)
    } // closes outermost WHILE loop.

    // Code that causes this function to return a GENOME object, with values from parsed file.
    // Placed here so it can be easily modified should the GENOME class change, or should we
    // we want to return something else (e.g. array, string, another kind of object, etc.)

    $oGenome = new Genome();
    $oGenome->organism = $organism;
    $oGenome->common_name = $common_name;
    $oGenome->tax_class = $tax_class;
    $oGenome->is_complete = $is_complete;
    $oGenome->gb_release = $gb_release;
    $oGenome->gb_entries = $gb_entries;
    $oGenome->gb_bps = $gb_bps;
    $oGenome->size = $size;
    $oGenome->reference = $reference;
    return $oGenome;
} // closes function parse_genome_dogs()
?>