<?php

require_once("etc.inc.php");
require_once("seq.inc.php");

class TFMatrix
{
    var $accession;
    var $id;
    var $date_created;
    var $date_updated;
    var $bnd_factor;
    var $desc;
    var $linked_factors;
    var $matrix;
    var $stat_basis;
    var $comments;

    var $ref_no;
    var $ref_author;
    var $ref_title;
    var $ref_data;
}

// parse_tfmatrix_transfac() parses MATRIX.DAT (Transfac) and returns a TFMATRIX object containing parsed data.
function parse_tfmatrix_transfac($flines)
{
    $cc_flag = FALSE;
    $cc_string = "";

    while ( list($no, $linestr) = each($flines) )
    { // OPENS 1st (outermost) while ( list($no, $linestr) = each($flines) )
        $linelabel = left($linestr, 2);
        $linedata = trim(substr($linestr, 4));
        $lineend = right($linedata, 1);

        // ID - IDENTIFICATION data field - one string in one line.
        if ($linelabel == "ID") $id = $linedata;

        // AC - ACCESSION NO data field - one string in one line.
        if ($linelabel == "AC") $accession = $linedata;

        /* DT - DATE data field - usually comes in two lines, the first is the
        date created, and the second, the date updated. Example:

           DT  20.06.90 (created); ewi.
           DT  24.08.95 (updated); hiwi.
        */
        if ($linelabel == "DT")
        {
            // assume "created", "updated" appear in lowercase at fixed position in DT line.
            $type = substr($linedata,10,7);
            if ($type == "created") $date_created = substr($linedata,0,8);
            if ($type == "updated") $date_updated = substr($linedata,0,8);
        }

        // DE - DESCRIPTION data field. From sample data, it appears to be one line only.
        if ($linelabel == "DE") $desc = $linedata;

        /* CC - COMMENTS data field - assume to be one or more lines to be concatenated
               by a whitespace character.  Example:

           CC  Group I in [903]; 5 sites selected in vitro for binding to E12N
           CC  (=N-terminally truncated E12); matrix corrected according to
           CC  the published sequences
        */
        if ($linelabel == "CC")
        {
            $cc_string .= $linedata . " ";
            $cc_flag = TRUE;
        }
        elseif ($cc_flag)
        {
            $comments = trim($cc_string);
            $cc_flag = FALSE;
        }

        if ($linelabel == "//") break;

    } // CLOSES 1st (outermost) while ( list($no, $linestr) = each($flines) )

    $oTFMatrix = new TFMatrix();
    $oTFMatrix->accession = $accession;
    $oTFMatrix->id = $id;
    $oTFMatrix->date_created = $date_created;
    $oTFMatrix->date_updated = $date_updated;
    $oTFMatrix->desc = $desc;
    $oTFMatrix->comments = $comments;

    return $oTFMatrix;

} // CLOSES parse_tfmatrix_transfac() function

class TFGene
{
    var $accession;
    var $id;
    var $date_created;
    var $date_updated;
    var $desc_short;
    var $desc_long;
    var $organism;
    var $species;

    var $tax_class;
    var $bucher_class;
    var $tfsite_pos;
    var $tfsite_accno;
    var $compel_accno;
    var $trrd_accno;
}

// parse_tfgene_transfac() parses GENE.DAT (Transfac) and returns a TFGENE object containing parsed data.
function parse_tfgene_transfac($flines)
{
    $tax_flag = FALSE;
    $tax_string = "";

    $aCompel = array();

    while ( list($no, $linestr) = each($flines) )
    { // OPENS 1st (outermost) while ( list($no, $linestr) = each($flines) )
        $linelabel = left($linestr, 2);
        $linedata = trim(substr($linestr, 4));
        $lineend = right($linedata, 1);

        // ID - IDENTIFICATION data field - one string in one line.
        if ($linelabel == "ID") $id = $linedata;

        // AC - ACCESSION NO data field - one string in one line.
        if ($linelabel == "AC") $accession = $linedata;

        /* DT - DATE data field - usually comes in two lines, the first is the
        date created, and the second, the date updated. Example:

           DT  20.06.90 (created); ewi.
           DT  24.08.95 (updated); hiwi.
        */
        if ($linelabel == "DT")
        {
            // assume "created", "updated" appear in lowercase at fixed position in DT line.
            $type = substr($linedata,10,7);
            if ($type == "created") $date_created = substr($linedata,0,8);
            if ($type == "updated") $date_updated = substr($linedata,0,8);
        }

        // SD - SHORT DESCRIPTION data field.  From sample data, it appears to be one line only.
        if ($linelabel == "SD") $desc_short = $linedata;

        // DE - LONG DESCRIPTION/GENE NAME data field. From sample data, it appears to be one line only.
        if ($linelabel == "DE") $desc_long = $linedata;

        // OS - ORGANISM SPECIES data field - assume to be always one line of this form (same as in class Factor):
        // Syntax: OS   common_name, scientific_name.
        // Example: OS    human, homo sapiens
        // Output: $organism = "human"
        //         $species = "homo sapiens"

        if ($linelabel == "OS")
        {
            $org_tokens = preg_split("/,/", $linedata, -1, PREG_SPLIT_NO_EMPTY);
            array_walk($org_tokens, "trim_element");
            $organism = $org_tokens[0];
            $species = $org_tokens[1];
        }

        // OC - ORGANISM CLASSIFICATION data field - assume to be always one line of this form (same as class Factor):
        // Syntax: OC   kingdom; phylum; class; ...;
        // Example:
        // OC  eukaryota; animalia; metazoa; chordata; vertebrata;
        // OC  tetrapoda; mammalia; eutheria; primates
        // Output: $tax_class = array("eukaryota", "mammalia", ...)
        // Later, convert this into an associative array. Same goes for GenBank, etc. - Serge

        if ($linelabel == "OC")
        {
            $tax_string .= $linedata . " ";
            $tax_flag = TRUE;
        }
        elseif ($tax_flag)
        {
            $tax_string = trim($tax_string);
            $tax_tokens = preg_split("/;/", $tax_string, -1, PREG_SPLIT_NO_EMPTY);
            array_walk($tax_tokens, "trim_element");
            $tax_flag = FALSE;
        }

        /* CO - COMPEL ACCESSION NO data field.  From data, one entry (word) in one line, multiple lines.
        Example:
              CO  C00001
              CO  C00005
              CO  C00006
        */
        if ($linelabel == "CO") $aCompel[] = $linedata;

        if ($linelabel == "//") break;

    } // CLOSES 1st (outermost) while ( list($no, $linestr) = each($flines) )

    $oTFGene = new TFGene();
    $oTFGene->accession = $accession;
    $oTFGene->id = $id;
    $oTFGene->date_created = $date_created;
    $oTFGene->date_updated = $date_updated;
    $oTFGene->desc_short = $desc_short;
    $oTFGene->desc_long = $desc_long;
    $oTFGene->organism = $organism;
    $oTFGene->species = $species;
    $oTFGene->tax_class = $tax_tokens;
    $oTFGene->compel_accno = $aCompel;

    return $oTFGene;

} // CLOSES parse_tfgene_transfac() function

class TFClass
{
    var $accession;
    var $id;
    var $date_created;
    var $date_updated;
    var $class;
    var $struct_desc;
    var $comments;
    var $member_factors;
    var $ref_no;
    var $ref_author;
    var $ref_title;
    var $ref_data;
    var $dbref;
}

// parse_tfclass_transfac() parses CLASS.DAT (Transfac) and returns a TFCLASS object containing parsed data.
function parse_tfclass_transfac($flines)
{
    $class_flag = FALSE;
    $class_string = "";

    while ( list($no, $linestr) = each($flines) )
    { // OPENS 1st (outermost) while ( list($no, $linestr) = each($flines) )
        $linelabel = left($linestr, 2);
        $linedata = trim(substr($linestr, 4));
        $lineend = right($linedata, 1);

        // ID - IDENTIFICATION data field - one string in one line.
        if ($linelabel == "ID") $id = $linedata;

        // AC - ACCESSION NO data field - one string in one line.
        if ($linelabel == "AC") $accession = $linedata;

        /* DT - DATE data field - usually comes in two lines, the first is the
        date created, and the second, the date updated. Example:

           DT  20.06.90 (created); ewi.
           DT  24.08.95 (updated); hiwi.
        */
        if ($linelabel == "DT")
        {
            // assume "created", "updated" appear in lowercase at fixed position in DT line.
            $type = substr($linedata,10,7);
            if ($type == "created") $date_created = substr($linedata,0,8);
            if ($type == "updated") $date_updated = substr($linedata,0,8);
        }

        // CL - CLASS data field - assume to be one or more lines, each entry separated by ;
        // Example: CL  zinc cluster; zinc-cysteine cluster; C6 zinc finger
        // Output: ( "zinc cluster", "zinc-cystein cluster", ... )
        if ($linelabel == "CL")
        {
            $class_string .= $linedata . " ";
            $class_flag = TRUE;
        }
        elseif ($class_flag)
        {
            $class_string = trim($class_string);
            if (strpos($class_string, ";") > 0)
            {
                $class_tokens = preg_split("/;/", $class_string);
                array_walk($class_tokens, "trim_element");
                // Later, look into possibility that some elements of $class_tokens array might
                // contain special characters like ', \, /, etc. - Serge
            }
            else $class_tokens = array($class_string);
            $class_flag = FALSE;
        }

        /* CC - COMMENTS data field - assume to be one or more lines to be concatenated
               by a whitespace character.  Example:

           CC  Zinc finger motif of GATA-type. Two such motifs are present
           CC  in each molecule. Each finger comprises 4 cysteine residues
           CC  presumably coordinating one zinc ion. However, metal chelators
           CC  do not suppress DNA-binding
        */

        if ($linelabel == "CC")
        {
            $cc_string .= $linedata . " ";
            $cc_flag = TRUE;
        }
        elseif ($cc_flag)
        {
            $comments = trim($cc_string);
            $cc_flag = FALSE;
        }

        if ($linelabel == "//") break;

    } // CLOSES 1st (outermost) while ( list($no, $linestr) = each($flines) )

    $oTFClass = new TFClass();
    $oTFClass->accession = $accession;
    $oTFClass->id = $id;
    $oTFClass->date_created = $date_created;
    $oTFClass->date_updated = $date_updated;
    $oTFClass->class = $class_tokens;
    $oTFClass->comments = $comments;

    return $oTFClass;

} // CLOSES parse_tfclass_transfac() function

class Cell
{
    var $accession;
    var $id;
    var $date_created;
    var $date_updated;
    var $author;
    var $organism;
    var $factor_src;
    var $desc;
}

// parse_cell_transfac() parses CELL.DAT (Transfac) and returns a CELL object containing parsed data.
function parse_cell_transfac($flines)
{
    $cd_flag = FALSE;
    $cd_string = "";

    while ( list($no, $linestr) = each($flines) )
    { // OPENS 1st (outermost) while ( list($no, $linestr) = each($flines) )
        $linelabel = left($linestr, 2);
        $linedata = trim(substr($linestr, 4));
        $lineend = right($linedata, 1);

        // ID - IDENTIFICATION data field - one string in one line.
        if ($linelabel == "ID") $id = $linedata;

        // AC - ACCESSION NO data field - one string in one line.
        if ($linelabel == "AC") $accession = $linedata;

        /* DT - DATE data field - usually comes in two lines, the first is the
        date created, and the second, the date updated. Example:

           DT  20.06.90 (created); ewi.
           DT  24.08.95 (updated); hiwi.
        */
        if ($linelabel == "DT")
        {
            // assume "created", "updated" appear in lowercase at fixed position in DT line.
            $type = substr($linedata,10,7);
            if ($type == "created") $date_created = substr($linedata,0,8);
            if ($type == "updated") $date_updated = substr($linedata,0,8);
        }

        // OS - ORGANISM SPECIES data field - assume to be always one line of this form:
        // Syntax: OS   common_name
        // Example: OS    human
        // Output: $organism = "human"
        // Note: This is like the OS field in the FACTOR class minus the SPECIES (sci name).

        if ($linelabel == "OS") $organism = $linedata;

        // SO - FACTOR SOURCE data field. Assume to be one line.

        if ($linelabel == "SO") $factor_src = $linedata;

        // CD - CELL DESCRIPTION data field - may be one or more lines, to be concatenated
        // with a whitespace between lines.

        if ($linelabel == "CD")
        {
            $cd_string .= $linedata . " ";
            $cd_flag = TRUE;
        }
        elseif ($cd_flag)
        {
            $cd_string = trim($cd_string);
            $cd_flag = FALSE;
        }

        if ($linelabel == "//") break;

    } // CLOSES 1st (outermost) while ( list($no, $linestr) = each($flines) )

    $oCell = new Cell();
    $oCell->accession = $accession;
    $oCell->id = $id;
    $oCell->date_created = $date_created;
    $oCell->date_updated = $date_updated;
    $oCell->organism = $organism;
    $oCell->factor_src = $factor_src;
    $oCell->desc = $cd_string;

    return $oCell;

} // CLOSES parse_cell_transfac() function

class Factor
{
    var $accession;
    var $id;
    var $date_created;
    var $date_updated;
    var $author;
    var $factor_name;
    var $synonyms;

    var $organism;                // "organism" here refers to the common name.
    var $species;                 // "species" is the scientific name of "organism".

    var $tax_class;
    var $homologs;
    var $class_accno;
    var $class_id;
    var $class_decno;

    var $length;
    var $molwt;

    var $sequence;
    var $seq_comment;
    var $features;
    var $feat_struct;

    var $cell_spec_pos;
    var $cell_spec_neg;
    var $feat_func;
    var $inter_fact;
    var $matrix;
    var $bndsite_accno;
    var $bndsite_id;
    var $bndsite_quality;
    var $bndsite_species;
    var $ref_no;
    var $ref_author;
    var $ref_title;
    var $ref_data;
    var $dbref;
}

// parse_factor_transfac() parses FACTOR.DAT (Transfac) and returns a Site object containing parsed data.
function parse_factor_transfac($flines)
{
    $desc_flag = FALSE;
    $desc_string = "";

    $region_flag = FALSE;
    $region_string = "";

    $syn_flag = FALSE;
    $syn_string = "";

    $homo_flag = FALSE;
    $homo_string = "";

    $tax_flag = FALSE;
    $tax_string = "";

    while ( list($no, $linestr) = each($flines) )
    { // OPENS 1st (outermost) while ( list($no, $linestr) = each($flines) )
        $linelabel = left($linestr, 2);
        $linedata = trim(substr($linestr, 4));
        $lineend = right($linedata, 1);

        // ID - IDENTIFICATION data field - one string in one line. (Same as SITE)
        if ($linelabel == "ID") $id = $linedata;

        // AC - ACCESSION NO data field - one string in one line. (Same as SITE)
        if ($linelabel == "AC") $accession = $linedata;

        /* DT - DATE data field - usually comes in two lines, the first is the
        date/time created, and the second, the date updated. Example:

           DT  20.06.90 11:00:03 (created); ewi.
           DT  24.08.95 (updated); hiwi.

           I've modified the code to allow a TIME entry after the DATE.  Later, update
           the code for the DT field of class SITE. - Serge
        */
        if ($linelabel == "DT")
        {
            // Assume "(created)", "(updated)" appear after the date/time, not in fixed position.
            $date_tokens = preg_split("/\s+/", $linedata, -1, PREG_SPLIT_NO_EMPTY);
            if (count($date_tokens) > 3)
            {
                // The line contains the TIME entry as its second token.
                $type = $date_tokens[2];
                if ($type == "(created);") $date_created = $date_tokens[0] . " " . $date_tokens[1];
                if ($type == "(updated);") $date_updated = $date_tokens[0] . " " . $date_tokens[1];
            }
            else
            {
                // The line does not contain the TIME entry as its second token.
                $type = $date_tokens[1];
                if ($type == "(created);") $date_created = $date_tokens[0];
                if ($type == "(updated);") $date_updated = $date_tokens[0];
            }
        }

        // FA - FACTOR NAME data field - assume one string in one line.
        if ($linelabel == "FA") $factor_name = $linedata;

        // SY - SYNONYMS data field - assume to be one or more lines, each entry separated by ;
        // Example: SY  AGP/EBP; ANF-2; CRP2; H-APF-2; IL-6DBP; LAP; LAP1; NF-IL6; NF-M;
        // Output: ( "AGP/EBP", "ANF-2", ... )
        if ($linelabel == "SY")
        {
            $syn_string .= $linedata . " ";
            $syn_flag = TRUE;
        }
        elseif ($syn_flag)
        {
            $syn_string = trim($syn_string);
            $syn_tokens = preg_split("/;/", $syn_string, -1, PREG_SPLIT_NO_EMPTY);
            array_walk($syn_tokens, "trim_element");
            // Later, look into possibility that some elements of $syn_tokens array might
            // contain special characters like ', \, /, etc. - Serge
            $syn_flag = FALSE;
        }

        // OS - ORGANISM SPECIES data field - assume to be always one line of this form:
        // Syntax: OS   common_name, scientific_name.
        // Example: OS    human, homo sapiens
        // Output: $organism = "human"
        //         $species = "homo sapiens"

        if ($linelabel == "OS")
        {
            $org_tokens = preg_split("/,/", $linedata, -1, PREG_SPLIT_NO_EMPTY);
            array_walk($org_tokens, "trim_element");
            $organism = $org_tokens[0];
            $species = $org_tokens[1];
        }

        // OC - ORGANISM CLASSIFICATION data field - assume to be always one line of this form:
        // Syntax: OC   kingdom; phylum; class; ...;
        // Example:
        // OC  eukaryota; animalia; metazoa; chordata; vertebrata;
        // OC  tetrapoda; mammalia; eutheria; primates
        // Output: $tax_class = array("eukaryota", "mammalia", ...)
        // Later, convert this into an associative array. Same goes for GenBank, etc. - Serge

        if ($linelabel == "OC")
        {
            $tax_string .= $linedata . " ";
            $tax_flag = TRUE;
        }
        elseif ($tax_flag)
        {
            $tax_string = trim($tax_string);
            $tax_tokens = preg_split("/;/", $tax_string, -1, PREG_SPLIT_NO_EMPTY);
            array_walk($tax_tokens, "trim_element");
            $tax_flag = FALSE;
        }

        // HO - HOMOLOGS data field - assume to be multiple entries separated by comma,
        // may span one or more lines to be concatenated by a whitespace.

        if ($linelabel == "HO")
        {
            $homo_string .= $linedata . " ";
            $homo_flag = TRUE;
        }
        elseif ($homo_flag)
        {
            $homo_tokens = preg_split("/,/", trim($homo_string), -1, PREG_SPLIT_NO_EMPTY);
            array_walk($homo_tokens, "trim_element");
            $homo_flag = FALSE;
        }

        // CL - CLASS data field. Always one line with 3 entries sep by a ;
        // Example: CL  C0001; CH; 2.3.3.0.1.
        // Output: $class_accno = "C0001", $class_id = "CH", $class_decno = "2.3.3.0.1."

        if ($linelabel == "CL")
        {
            $class_tokens = preg_split("/;/", $linedata, -1, PREG_SPLIT_NO_EMPTY);
            array_walk($class_tokens, "trim_element");
        }

        if ($linelabel == "//") break;

    } // CLOSES 1st (outermost) while ( list($no, $linestr) = each($flines) )

    $oFactor = new Factor();
    $oFactor->accession = $accession;
    $oFactor->id = $id;
    $oFactor->date_created = $date_created;
    $oFactor->date_updated = $date_updated;
    $oFactor->factor_name = $factor_name;
    $oFactor->synonyms = $syn_tokens;
    $oFactor->organism = $organism;
    $oFactor->species = $species;
    $oFactor->homolog = $homo_tokens;
    $oFactor->tax_class = $tax_tokens;
    $oFactor->class_accno = $class_tokens[0];
    $oFactor->class_id = $class_tokens[1];
    $oFactor->class_decno = $class_tokens[2];

    return $oFactor;
} // CLOSES parse_site_transfac() function

class Site
{
    var $accession;
    var $id;
    var $date_created;
    var $date_updated;
    var $author;
    var $seqtype;
    var $desc;
    var $gene_region;
    var $regel_seq;
    var $denom;

    var $firstpos;
    var $lastpos;
    var $firstpos_def;
    var $bind_factor;

    var $organism;
    var $tax_class;
    var $factor_src;
    var $method;
    var $comments;

    var $dbref;
    var $refno;
    var $ref_author;
    var $ref_title;
    var $ref_data;
} // closes CLASS SITE

// parse_site_transfac() parses SITE.DAT (Transfac) and returns a Site object containing parsed data.
function parse_site_transfac($flines)
{
    $desc_flag = FALSE;
    $desc_string = "";

    $region_flag = FALSE;
    $region_string = "";

    while ( list($no, $linestr) = each($flines) )
    { // OPENS 1st (outermost) while ( list($no, $linestr) = each($flines) )
        $linelabel = left($linestr, 2);
        $linedata = trim(substr($linestr, 4));
        $lineend = right($linedata, 1);

        // ID - IDENTIFICATION data field - one string in one line.
        if ($linelabel == "ID") $id = $linedata;

        // AC - ACCESSION NO data field - one string in one line.
        if ($linelabel == "AC") $accession = $linedata;

        /* DT - DATE data field - usually comes in two lines, the first is the
        date created, and the second, the date updated. Example:

           DT  20.06.90 (created); ewi.
           DT  24.08.95 (updated); hiwi.
        */
        if ($linelabel == "DT")
        {
            // assume "created", "updated" appear in lowercase at fixed position in DT line.
            $type = substr($linedata,10,7);
            if ($type == "created") $date_created = substr($linedata,0,8);
            if ($type == "updated") $date_updated = substr($linedata,0,8);
        }

        // TY - SEQUENCE TYPE data field - one string (one letter?) in one line.
        // Example: TY   D

        if ($linelabel == "TY") $seqtype = $linedata;

        // DE - DESCRIPTION data field - from sample data, it seems always one line.
        // Assume may be one or more lines concatenated with a whitespace char.

        if ($linelabel == "DE")
        {
            $desc_string .= $linedata . " ";
            $desc_flag = TRUE;
        }
        elseif ($desc_flag)
        {
            $desc_string = trim($desc_string);
            $desc_flag = FALSE;
        }

        // RE - GENE REGION data field - from sample data, it seems always one line.
        // Assume may be one or more lines concatenated with a whitespace char.
        // Example: RE   intron promoter

        if ($linelabel == "RE")
        {
            $region_string .= $linedata . " ";
            $region_flag = TRUE;
        }
        elseif ($region_flag)
        {
            $region_string = trim($region_string);
            $region_flag = FALSE;
        }

        // "//" - END OF RECORD MARKER
        if ($linelabel == "//") break;

    } // CLOSES 1st (outermost) while ( list($no, $linestr) = each($flines) )

    $oSite = new Site();
    $oSite->accession = $accession;
    $oSite->id = $id;
    $oSite->date_created = $date_created;
    $oSite->date_updated = $date_updated;
    $oSite->seqtype = $seqtype;
    $oSite->desc = $desc_string;
    $oSite->gene_region = $region_string;

    return $oSite;

} // CLOSES parse_site_transfac() function
?>
