<?php
require_once("seqdb.inc.php");
require_once("etc.inc.php");

/*
hgbase.inc.php - include file containing the Mutation class (based on HGBase).

Description: Contains the definition of the above classes and some helper functions.
Author: Serge Gregorio, Jr.
Date: May 27, 2003
License: General Public License 2.0

This code has been written as part of the GenePHP/BioPHP project, located at:

   http://genephp.sourceforge.net
*/

class Mutation
{
    var $haplotype_id;
    var $allele;
    var $is_in_block;

    var $population_id;
    var $pop_name;
    var $pop_indiv;
    var $freq_perc;
    var $freq_indiv;

    var $source_id;
    var $citation;
    var $submitter_name;
    var $submission_id;
    var $source_comment;

    var $mesh;
}

function parse_mutation_hgbase($flines)
{
    $citation = "";

    while( list($lineno, $linestr) = each($flines) )
    { // OPENS outermost while() loop

        // removes any \t after the label and before the data.
        // Original string:
        //    haplotypeid\s\s\s\tdata_is_here
        // Result:
        //    haplotypeid\s\s\sdata_is_here or
        //    haplotypeiddata_is_here

        $linestr = stripcslashes($linestr);

        // $label = strtoupper(trim($line_array[0]));
        // strtoupper(trim(left($linestr, 16)));
        // $data = substr($linestr,16);
        // $data = $line_array[1];

        // Assume that ENTRY is always one string in one line.
        if (strtoupper(left($linestr,11)) == "HAPLOTYPEID") $haplotype_id = trim(substr($linestr,11));

        // ALLELE data field.
        if (strtoupper(left($linestr,6)) == "ALLELE") {}

        // ISINBLOCK data field
        if (strtoupper(left($linestr,9)) == "ISINBLOCK") {}

        // POPULATIONID data field
        if (strtoupper(left($linestr,12)) == "POPULATIONID") $population_id = trim(substr($linestr,12));

        if (strtoupper(left($linestr,10)) == "POPULATION")
        {
            $data = trim(substr($linestr,10));
            // Caucasian (USA) (216 individuals)
            $pop_array = preg_split("/\(/", trim($data), -1, PREG_SPLIT_NO_EMPTY);
            // Caucasian, USA), 216 individuals)
            $pop_str = "";
            $ctr = 0;
            foreach($pop_array as $item)
            {
                $ctr++;
                if ($ctr == count($pop_array)) break;
                if (right($item,1) == ")") $item = "(" . $item;
                $pop_str .= $item;
            }
            $pop_name = $pop_str;

            $ind_array = preg_split("/\s+/", array_pop($pop_array), -1, PREG_SPLIT_NO_EMPTY);
            if (strtoupper(left($ind_array[1],10)) == "INDIVIDUAL")
                $pop_indiv = (int) ($ind_array[0]);
        }

        if (strtoupper(left($linestr,9)) == "FREQUENCY")
        {
            $data = trim(substr($linestr,9));
            $perc_array = preg_split("/%/", trim($data), -1, PREG_SPLIT_NO_EMPTY);
            // 2, (1039 individuals)
            $freq_perc = (float) $perc_array[0];
            array_shift($perc_array);
            // " (1039 individuals) (more nonsense) "
            array_walk($perc_array, "trim_element");
            // "(1039 individuals) (more nonsense)"

            $freq_array = preg_split("/\s+/", $perc_array[0], -1, PREG_SPLIT_NO_EMPTY);
            // "(1039", "individuals)", ...
            if (left($freq_array[0],1) == "(")
                $freq_indiv = substr($freq_array[0],1);
        }

        // SOURCEID data field. Assume to be one entry in one line.
        if (strtoupper(left($linestr,8)) == "SOURCEID") $source_id = trim(substr($linestr,8));

        // CITATION data field. Assume to be one entry in one line.
        if (strtoupper(left($linestr,8)) == "CITATION")
        {
            $linestr = ereg_replace("\t", ' ', $linestr);
            $citation .= trim(substr($linestr,8)) . " ";
        }

        // SUBMITTER data field. Assume to be one line only.
        if (strtoupper(left($linestr,9)) == "SUBMITTER")
        {
            $data = trim(substr($linestr,9));
            // Jan. W. Koper, SUB0001234)
            $submit_array = preg_split("/\(/", trim($data), -1, PREG_SPLIT_NO_EMPTY);
            $submitter_name = trim($submit_array[0]);
            $submission_id = trim($submit_array[1]);
            $submission_id = left($submission_id, strlen($submission_id)-1);
        }

        // SOURCECOMMENT data field. Assume to be one string in one line.
        if (strtoupper(left($linestr, 13)) == "SOURCECOMMENT")
            $source_comment = trim(substr($linestr,13));

        // Exit while loop when an end-of-entry marker is found.
        if (left($linestr,2) == "//") break;
    }

    $oMutation = new Mutation();
    $oMutation->haplotype_id = $haplotype_id;
    // $oMutation->allele = $allele;
    // $oMutation->is_in_block = $is_in_block;
    $oMutation->population_id = $population_id;
    $oMutation->pop_name = $pop_name;
    $oMutation->pop_indiv = $pop_indiv;
    $oMutation->freq_perc = $freq_perc;
    $oMutation->freq_indiv = $freq_indiv;
    $oMutation->source_id = $source_id;
    $oMutation->citation = $citation;
    $oMutation->submitter_name = $submitter_name;
    $oMutation->submission_id = $submission_id;
    $oMutation->source_comment = $source_comment;
    // $oMutation->mesh = $aMesh;

    return $oMutation;
}
?>

