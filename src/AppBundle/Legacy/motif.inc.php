<?php

class Motif
{
    var $entry_name;
    var $entry_type;
    var $accession;
    var $date;
    var $desc;
    var $pattern;
    var $matrix;
    var $rule;
    var $num_results;
    var $comments;
    var $swp_xref;
    var $pdb_xref;
    var $doc_xref;
}

function parse_motif_prosite($flines)
{

    $in_desc_flag = FALSE;
    $desc_string = "";

    $in_dr_flag = FALSE;
    $dr_string = "";
    $aDBRefs = array();

    $in_pa_flag = FALSE;
    $pa_string = "";

    $in_ru_flag = FALSE;
    $ru_string = "";

    $in_3D_flag = FALSE;
    $three_d_string = "";
    $aPDB_names = array();

    $doc_string = "";

    $in_nr_flag = FALSE;
    $nr_string = "";
    $aNRs = array();

    $in_cc_flag = FALSE;
    $cc_string = "";
    $aCCs = array();

    $in_ma_flag = FALSE;
    $ma_string = "";
    $aInner = array();
    $aOuter = array();

    while ( list($no, $linestr) = each($flines) )
    { // opens outermost WHILE

        $label = left($linestr, 2);
        $data = trim(substr($linestr, 5));
        $lascar = right($linedata, 1);

        // ID data field

        if ($label == "ID")
        {
            $words = preg_split("/;/", substr($linestr, 5));
            $entry_name = trim($words[0]);
            $entry_type = trim($words[1]);
            $type_len = strlen($entry_type);
            $entry_type = substr($entry_type, 0, $type_len-1);
        }

        // AC - ACCESSION data field

        if ($label == "AC") $accession = substr($data, 0, strlen($data)-1);

        // DT - DATE data field (Note: Later, decide if you want to replace key "DATA UPDATE" with "DATA_UPDATE",
        // and "INFO UPDATE" with "INFO_UPDATE", or not.  Right now, I'm using the key with the inner whitespace.

        if ($label == "DT")
        {
            $date_array = preg_split("/;/", $data, -1, PREG_SPLIT_NO_EMPTY);
            $aDates = array();
            $counter = 1;
            foreach($date_array as $date_item)
            {
                $temp = preg_split("/\s\(/", $date_item, -1, PREG_SPLIT_NO_EMPTY);
                // May 20, 2003: Last key was "INFO UPDATE)".  I fixed this with an IF stmt.
                if ($counter == 3) $key = substr($temp[1], 0, strlen($temp[1])-2);
                else $key = substr($temp[1], 0, strlen($temp[1])-1);
                $val = $temp[0];
                $aDates[$key] = trim($val);
                $counter++;
            }
        }

        // DE - DESCRIPTION data field (assume that DE may be one or more lines to be connected by a whitespace).

        if ($label == "DE")
        {
            $desc_string .= $data . " ";
            $in_desc_flag = TRUE;
        }
        elseif ($in_desc_flag)
        {
            // we've encountered a line that is not DE (after one or more DE's). store accumulated string to a var.
            $description = trim($desc_string);
        }

        // PA - PATTERN data field - may be one or more lines connected to be each other without whitespaces.

        if ($label == "PA")
        {
            $pa_string .= $data;
            $in_pa_flag = TRUE;
        }
        elseif ($in_pa_flag)
        {
            $pattern = trim($pa_string);
        }

        // MA - MATRIX data field - skip this for now.

        /* SAMPLE ENTRY:
        MA   /GENERAL_SPEC: ALPHABET='ACDEFGHIKLMNPQRSTVWY'; LENGTH=97;
        MA   /DISJOINT: DEFINITION=PROTECT; N1=2; N2=96;
        MA   /NORMALIZATION: MODE=1; FUNCTION=GLE_ZSCORE;
        MA    R1=239.0; R2=-0.0036; R3=0.8341; R4=1.016; R5=0.169;
        */

        if ($label == "MA")
        {
            $ma_string .= $data . " ";
            $in_ma_flag = TRUE;
        }
        elseif ($in_ma_flag)
        {
            $ma = trim($ma_string);
            $ma_r = preg_split("/\//", $ma, -1, PREG_SPLIT_NO_EMPTY);
            $aOuter = array();
            foreach($ma_r as $ma_item)
            {
                $ma_qv = preg_split("/:/", $ma_item, -1,PREG_SPLIT_NO_EMPTY);
                array_walk($ma_qv, "trim_element");
                // $ma_qv = ( "GENERAL_SPEC", "X=1; Y=2;" )

                $qualifier = $ma_qv[0];
                $values_r = preg_split("/;/", $ma_qv[1], -1, PREG_SPLIT_NO_EMPTY);
                array_walk($values_r, "trim_element");
                // $values_r = ( "X=1", "Y=2" )

                $aInner = array();
                foreach($values_r as $value_item)
                {
                    $qv = preg_split("/=/", $value_item, -1, PREG_SPLIT_NO_EMPTY);
                    array_walk($qv, "trim_element");

                    $inner_qual = $qv[0];
                    $inner_value = $qv[1];
                    $aInner[$inner_qual] = $inner_value;
                }
                // $aInner = ( "X" => 1, "Y" => 2 )
                $aOuter[$qualifier] = $aInner;
                // $aOuter[] = ( "GENERAL_SPEC" => ( "X" => 1, "Y" => 2 ), "NORMALIZATION" => ( "A" => 1), ... )
            }
        }

        // NR - NUMERICAL RESULTS data field.

        if ($label == "NR")
        {
            $nr_string .= $data . " ";
            $in_nr_flag = TRUE;
        }
        elseif ($in_nr_flag)
        {
            $nr = trim($nr_string);
            $nr_array = preg_split("/;/", $nr, -1, PREG_SPLIT_NO_EMPTY);
            array_walk($nr_array, "trim_element");
            $aNRs = array();
            foreach($nr_array as $nr_item)
            {
                $nr_qv = preg_split("/=/", $nr_item, -1, PREG_SPLIT_NO_EMPTY);
                $qualifier = trim($nr_qv[0]);
                $value = trim($nr_qv[1]);
                $aNRs[$qualifier] = $value;
            }
        }

        // CC - COMMENT data field - may be one or more lines. contains qualifiers and values.

        if ($label == "CC")
        {
            $cc_string .= $data . " ";
            $in_cc_flag = TRUE;
        }
        elseif ($in_cc_flag)
        {
            $cc = trim($cc_string);
            $cc_array = preg_split("/;/", $cc, -1, PREG_SPLIT_NO_EMPTY);
            array_walk($cc_array, "trim_element");
            $aCCs = array();
            foreach($cc_array as $cc_item)
            {
                $cc_qv = preg_split("/=/", $cc_item, -1, PREG_SPLIT_NO_EMPTY);
                $qualifier = trim($cc_qv[0]);
                $value = trim($cc_qv[1]);
                $aCCs[$qualifier] = $value;
            }
        }

        // RU - RULES data field - may be one or more lines.  Free-format text, multiple lines to be
        // connected with a whitespace character.

        if ($label == "RU")
        {
            $ru_string .= $data . " ";
            $in_ru_flag = TRUE;
        }
        elseif ($in_ru_flag)
        {
            $rule = trim($ru_string);
        }

        // 3D - 3D STRUCTURE data field - may be one or more lines, to be connected by a whitespace.

        if ($label == "3D")
        {
            $three_d_string .= $data . " ";
            $in_3D_flag = TRUE;
        }
        elseif ($in_3D_flag)
        {
            $three_d = trim($three_d_string);
            $aPDB_names = preg_split("/;/", $three_d, -1, PREG_SPLIT_NO_EMPTY);
            array_walk($aPDB_names, "trim_element");
        }

        // DR - DATABASE REFERENCES data field

        if ($label == "DR")
        {
            $dr_string .= $data . " ";
            $in_dr_flag = TRUE;
        }
        elseif ($in_dr_flag)
        {
            // we've encountered a line that is not DE (after one or more DE's). store accumulated string to a var.
            $dr = trim($dr_string);

            $dr_array = preg_split("/;/", $dr, -1, PREG_SPLIT_NO_EMPTY);
            foreach($dr_array as $dr_item)
            {
                $temp = preg_split("/,/", $dr_item, -1, PREG_SPLIT_NO_EMPTY);
                array_walk($temp, "trim_element");
                $aDBRefs[] = $temp;
            }
            // May 20, 2003: You forgot this line which caused the array to triple in size (with duplicate entries).
            $in_dr_flag = FALSE;
        }

        // DO - DOCUMENTATION data field - exactly one entry in one line, terminated by a semi-colon (;).

        if ($label == "DO")
        {
            $doc_string = substr($data, 0, strlen($data)-1);
        }

        if ($label == "//") break;
    } // closes outermost WHILE

    $oMotif = new Motif();
    $oMotif->entry_name = $entry_name;
    $oMotif->entry_type = $entry_type;
    $oMotif->accession = $accession;
    $oMotif->date = $aDates;
    $oMotif->desc = $description;
    $oMotif->swp_xref = $aDBRefs;
    $oMotif->pattern = $pattern;
    $oMotif->rule = $rule;
    $oMotif->pdb_xref = $aPDB_names;
    $oMotif->doc_xref = $doc_string;
    $oMotif->num_results = $aNRs;
    $oMotif->comments = $aCCs;
    $oMotif->matrix = $aOuter;

    return $oMotif;
}
?>