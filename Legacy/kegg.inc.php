// This code was written by Serge Gregorio, Jr. (serge525@hotmail.com)
// and is under the General Public License (GPL) 2.0 license.

<?php

require_once("seqdb.inc.php");
require_once("etc.inc.php");

/*
kegg.inc - include file containing the classes for the Kegg Ligand 
Database objects - Enzyme, Reaction, Molecule, Ligand, and Compound. 

Description: Contains the definition of the above classes and some helper functions.
Author: Serge Gregorio, Jr.
Date: May 4, 2003
License: General Public License 2.0

This code has been written as part of the GenePHP/BioPHP project, located at:

   http://genephp.sourceforge.net
*/

class Compound
{
    var $entry;
    var $name;
    var $formula;
    var $reaction;

    var $pathway;
    var $enzyme;
    var $dblink;
// var $structures; - sample compound file I've obtained from Kegg does not seem to 
//                    contain any STRUCTURE/S entry.
}

class Enzyme
{
    var $entry;
    var $name;
    var $class;
    var $sysname;
    var $reaction;
    var $substrate;
    var $product;
    var $comment;
    var $reference;
    var $pathway;
    var $ortholog;
    var $genes;
    var $disease;
    var $motif;
    var $structures;
    var $dblinks;
}

class Reaction
{
    var $entry;
    var $name;
    var $definition;
    var $pathway;     // array e.g. ( ('MAP00220', 'Urea cycle'), ('MAP00221', 'Citric cycle') )
    var $equation;
    var $enzyme;      // array e.g. ( '3.6.3.50', '3.5.3.49', '3.6.1.8', ... )
}

class Ortholog
{
    var $entry;
    var $name;
    var $definition;
    var $class;
    var $dblinks;
    var $genes;
}

class Ligand
{
// Still empty.  Anyone interested in writing this? 
}

class Mol
{
// Still empty.  Anyone interested in writing this?
}

class ECRel
{
    var $enzyme1;
    var $enzyme2;
    var $pathway;
}

// Example line:
// ec:1.1.1.1	ec:1.2.1.3	ECrel		C00577 map00561 C00071 map00071 C05445 map00120

function parse_ecrel_kegg($fline)
{
    $words = preg_split("/[\s]+/", $fline, -1, PREG_SPLIT_NO_EMPTY);
    $enzyme1 = $words[0];
    $enzyme2 = $words[1];
    $wno = 3;
    $outer = array();
    while(1)
    {
        if ($wno < count($words))
        {
            $inner = array();
            $inner[] = $words[$wno];
            $inner[] = $words[$wno+1];
            $outer[] = $inner;
            $wno = $wno + 2;
        }
        else break;
    }
    $pathway = $outer;

    $oECRel = new ECRel();

    $oECRel->enzyme1 = $enzyme1;
    $oECRel->enzyme2 = $enzyme2;
    $oECRel->pathway = $pathway;

    return $oECRel;
}

class Genome
{
    var $entry;
    var $name;
    var $definition;
    var $taxonomy;

// Parser for these data fields not yet implemented
    var $lineage;
    var $reference;
    var $authors;
    var $title;
    var $journal;
    var $chromosome;
    var $chrom_seq;
    var $chrom_len;
    var $scaffold;
    var $scaf_seq;
    var $scaf_len;
    var $plasmid;
    var $plasm_seq;
    var $plasm_len;
    var $statistics;
    var $genome_map;
}

function parse_genome_kegg($flines)
{
    // Initialize variables used inside the WHILE parser loop.
    $in_def_flag = FALSE;
    $def_string = "";

    $tax_string = "";

    while( list($lineno, $linestr) = each($flines) )
    { // OPENS outermost while() loop

        $label = trim(left($linestr, 12));

        // Assume that ENTRY is always one string in one line.
        if ($label == "ENTRY") $entry = trim(substr($linestr,12));

        // Assume that NAME is always one string in one line.
        if ($label == "NAME") $name = trim(substr($linestr,12));

        // DEFINITION field may be one or more lines - follows CONTINUATION ($) convention
        if ($label == "DEFINITION")
        {
            $def_string = trim(substr($linestr,12));
            $in_def_flag = TRUE;
        }
        elseif ( (strlen($label) == 0) and ($in_def_flag) )
        {
            if (left(trim(substr($linestr,12)),1) == '$')
                $def_string .= rtrim(substr($linestr,13));
            else
                $def_string .= " " . trim(substr($linestr,12));
        }
        elseif ( (strlen($label) > 0) and ($in_def_flag) )
        {
            $def_string = trim($def_string);
            $in_def_flag = FALSE;
        }

        // Assume that TAXONOMY is always one string in one line of the form:
        //   TAXONOMY  TAX:1234
        if ($label == "TAXONOMY")
        {
            $tax_string = trim(substr($linestr,12));
            $tax_array = preg_split("/:/", $tax_string, -1, PREG_SPLIT_NO_EMPTY);
            $taxonomy = trim($tax_array[1]);
        }

        if ($label == "///") break;
    }

    $oGenome = new Genome();
    $oGenome->entry = $entry;
    $oGenome->name = $name;
    $oGenome->definition = $def_string;
    $oGenome->taxonomy = $taxonomy;

    /*
    This section is still unfinished.  Here, we are supposed to set the values of
    other attributes of the oGenome class like "lineage", "authors", "plasmid", etc.
    */

    return $oGenome;
}

function parse_ortho_kegg($flines)
{
    // Initialization of variables.
    $def_string = "";
    $in_def_flag = FALSE;

    $class_string = "";
    $in_class_flag = FALSE;
    $aClasses = array();

    $in_dblink_flag = FALSE;
    $aDblinks = array();

    $in_genes_flag = FALSE;
    $genes_string = "";
    $aGenes = array();

    $in_dblink_flag = FALSE;
    $dblink_string = "";
    $aDblinks = array();

    while( list($lineno, $linestr) = each($flines) )
    { // OPENS outermost while() loop

        $label = trim(left($linestr, 12));

        // Assume that ENTRY is always one line.
        if ($label == "ENTRY")
        {
            $entry = trim(substr($linestr,12,28));
            $entry_type = trim(substr($linestr,40,2));
        }

        // Assume that NAME is always one line.
        if ($label == "NAME") $name = trim(substr($linestr, 12));

        // DEFINITION field may be one or more lines - follows CONTINUATION ($) convention
        if ($label == "DEFINITION")
        {
            $def_string = trim(substr($linestr,12));
            $in_def_flag = TRUE;
        }
        elseif ( (strlen($label) == 0) and ($in_def_flag) )
        {
            if (left(trim(substr($linestr,12)),1) == '$')
                $def_string .= rtrim(substr($linestr,13));
            else
                $def_string .= " " . trim(substr($linestr,12));
        }
        elseif ( (strlen($label) > 0) and ($in_def_flag) )
        {
            $def_string = trim($def_string);
            $in_def_flag = FALSE;
        }

        /* CLASS field - first line is the class, 2nd is subclass, 3rd is
        the sub-subclass.  Store as an array.
        Example:

        CLASS       Metabolism; Carbohydrate Metabolism; Glycolysis / Gluconeogenesis
                      [PATH:ot00010]
                          Metabolism; Lipid Metabolism; Fatty acid metabolism [PATH:ot00071]
                      Metabolism; Lipid Metabolism; Bile acid biosynthesis [PATH:ot00120]
                      Metabolism; Amino Acid Metabolism; Tyrosine metabolism
                      [PATH:ot00350]
                      Metabolism; Metabolism of Complex Lipids; Glycerolipid metabolism
                      [PATH:ot00561]
        Syntax:

        CLASS			class1; subclass1; sub-subclass1 [PATH:path_id1]
                    class2; subclass2; sub-subclass2 [PATH:path_id2]

        Algorithm:
           1) Concatenate all lines, adding a whitespace char in between lines.
           2) Separate/split by the ] character, e.g.
              Metabolism; Carbo meta; Glycolysis [PATH:0t0001
           3) Further split each token by the [ character.
           Metabolism; Carbo meta; Glycosis
              PATH:ot0001
           4) Further split the first token by ;
           5) Further split the second token by :
           6) We now have:
           class => Metabolism
              subclass => Carbo meta
              subsubclass => Glycolysis
              path_id => ot0001
        */

        if ($label == "CLASS")
        {
            $aClasses = array();
            // $aClasses[] = trim(substr($linestr,12));
            $class_string .= trim(substr($linestr,12)) . " ";
            $in_class_flag = TRUE;
        }
        elseif ( (strlen($label) == 0) and ($in_class_flag) )
        {
            $class_string .= trim(substr($linestr,12)) . " ";
        }
        elseif ( (strlen($label) > 0) and ($in_class_flag) )
        {
            $class_string = trim($class_string);
            $temp = preg_split("/\]/", $class_string, -1, PREG_SPLIT_NO_EMPTY);
            foreach($temp as $temp2)
            {
                $inner = array();
                // Example of $temp2: Metabolism; Carbo meta; Glycolysis [PATH:0t0001
                $temp3 = preg_split("/[;\[\:]/", $temp2, -1, PREG_SPLIT_NO_EMPTY);
                // Example of $temp3: Metabolism, Carbo meta, Glycolysis, PATH, ot0001
                $inner["CLASS"] = trim($temp3[0]);
                $inner["SUBCLASS"] = trim($temp3[1]);
                $inner["SUBSUBCLASS"] = trim($temp3[2]);
                $inner["PATH_ID"] = trim($temp3[4]);
                $aClasses[] = $inner;
            }
            $in_class_flag = FALSE;
        }

        // Like the GENES section of the ENZYME class, we assume here one dbsource : one or more dblinks : one line

        if ($label == "DBLINKS")
        {
            $aDblinks = array();
            $dblink_string = trim(substr($linestr,12));
            $dblink_array = preg_split("/: /", $dblink_string, -1, PREG_SPLIT_NO_EMPTY);
            $db_code = trim($dblink_array[0]);
            $dblinks = trim($dblink_array[1]);
            $links_array = preg_split("/\s+/", $dblinks, -1, PREG_SPLIT_NO_EMPTY);
            array_walk($links_array, "trim_element");
            $aDblinks[$db_code] = $links_array;
            $in_dblink_flag = TRUE;
        }
        elseif ( (strlen($label) == 0) and ($in_dblink_flag) )
        {
            // at the 2nd, 3rd, etc. line of the DBLINKS data field.
            $dblink_string = trim(substr($linestr,12));
            $dblink_array = preg_split("/: /", $dblink_string, -1, PREG_SPLIT_NO_EMPTY);
            $db_code = trim($dblink_array[0]);
            $dblinks = trim($dblink_array[1]);
            $links_array = preg_split("/\s+/", $dblinks, -1, PREG_SPLIT_NO_EMPTY);
            array_walk($links_array, "trim_element");
            $aDblinks[$db_code] = $links_array;
        }
        elseif ( (strlen($label) > 0) and ($in_dblink_flag) )
        {
            $in_dblink_flag = FALSE;
        }

        /*
        MLO: mll6011 mll6012 mll7727 mll7731
        SME: SMb21122(mccA) SMb21124(mccB)
        ATU: Atu3478(mccB) Atu3479(mccA)
        ATC: AGR_L_2704 AGR_L_2706

        Unlike in the enzyme parser, here we do not assume 1 organism : one line : one or more gene names
        separated by whitespace(s).  The code here supports multiple lines per organism, e.g.

           MLO: gene1 gene2 gene3
           gene4 gene5 gene6
        SME: gene1 gene2
        */

        // Let me try a different approach.

        if ($label == "GENES")
        {
            $genes_string .= trim(substr($linestr,12)) . " ";
            $in_genes_flag = TRUE;
        }
        elseif ( (strlen($label) == 0) and ($in_genes_flag) )
            // at the 2nd, 3rd, etc. line of the GENES data field.
            $genes_string .= trim(substr($linestr,12)) . " ";
        elseif ( (strlen($label) > 0) and ($in_genes_flag) )
        {
            $genes_string = trim($genes_string);
            $genes_array = preg_split("/: /", $genes_string, -1, PREG_SPLIT_NO_EMPTY);
            // ABC, "A123 B234 C345 BCD", "X123 Y234 Z345 CDE", ..., "D123 E234 F345"
            // The last token in each string (except for the first and last) represents
            // a 3-letter organism code.
            // Desired output format: ( "ABC" => (A123, ...), "BCD" => (B123, ...) )
            $ctr = 0;
            $item_count = count($genes_array);
            foreach($genes_array as $gene)
            {
                $ctr++;
                if ($ctr == 1) $prev_label = $gene;
                if ( ($ctr > 1) and ($ctr < $item_count) )
                {
                    $gene_names = preg_split("/\s+/", $gene, -1, PREG_SPLIT_NO_EMPTY);
                    $name_count = count($gene_names);
                    $last_item = array_pop($gene_names);
                    $gene_names = array_slice($gene_names, 0, $name_count-1);
                    array_walk($gene_names, "trim_element");
                    $aGenes[$prev_label] = $gene_names;
                    $prev_label = $last_item;
                }
                if ($ctr == $item_count)
                {
                    $gene_names = preg_split("/\s+/", $gene, -1, PREG_SPLIT_NO_EMPTY);
                    array_walk($gene_names, "trim_element");
                    $aGenes[$prev_label] = $gene_names;
                }
            }
            $in_genes_flag = FALSE;
        }

        if ($label == '///') break;
    } // CLOSES outermost while() loop

    // Save parsed values to a new Reaction object

    $oOrtholog = new Ortholog();
    $oOrtholog->entry = $entry;
    $oOrtholog->entry_type = $entry_type;
    $oOrtholog->name = $name;
    $oOrtholog->definition = $def_string;
    $oOrtholog->class = $aClasses;
    $oOrtholog->dblinks = $aDblinks;
    $oOrtholog->genes = $aGenes;

    return $oOrtholog;
}

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

        // Assume FORMULA is only one line.
        if ($label == "FORMULA")
        {
            $formula = trim(substr($linestr,12));
        }

        if ($label == "PATHWAY")
        {
            // at the first line of PATHWAY data field.
            $path_string = trim(substr($linestr,12)) . " ";
            $in_path_flag = TRUE;
        }
        elseif ( (strlen($label) == 0) and ($in_path_flag) )
        {
            // at the 2nd, 3rd, etc. line of the PATHWAY data field.
            $path_string .= trim(substr($linestr,12)) . " ";
        }
        elseif ( (strlen($label) > 0) and ($in_path_flag) )
        {
            // at the first line of the next data section (or end of entry).
            $pathway = trim($path_string);

            $paths = preg_split("/PATH:/", $pathway, -1, PREG_SPLIT_NO_EMPTY);
            array_walk($paths, "trim_element");
            $aPaths = array();
            foreach($paths as $path)
            {
                $path_id = trim(left($path,10));
                $path_desc = trim(substr($path,10));
                $path_array = array($path_id, $path_desc);
                $aPaths[] = $path_array;
            }
            // set the flag to indicate end of PATHWAY data field.
            $in_path_flag = FALSE;
        }

        // REACTION field is like ENZYME, it's made up of one or more lines
        // containing REACTION IDs (of fixed length) separated by spaces.
        if ($label == "REACTION")
        {
            $react_string = trim(substr($linestr,12)) . " ";
            $in_react_flag = TRUE;
        }
        elseif ( (strlen($label) == 0) and ($in_react_flag) )
        {
            $react_string .= trim(substr($linestr,12)) . " ";
        }
        elseif ( (strlen($label) > 0) and ($in_react_flag) )
        {
            $react = trim($react_string);
            $aReactions = preg_split("/\s/", $react, -1, PREG_SPLIT_NO_EMPTY);
            array_walk($aReactions, "trim_element");
            $in_react_flag = FALSE;
        }

        // Assume that ENZYME data field is always one line.
        if ($label == "ENZYME")
        {
            $enzyme_string = trim(substr($linestr,12)) . " ";
            $in_enzyme_flag = TRUE;
        }
        elseif ( (strlen($label) == 0) and ($in_enzyme_flag) )
        {
            $enzyme_string .= trim(substr($linestr,12)) . " ";
        }
        elseif ( (strlen($label) > 0) and ($in_enzyme_flag) )
        {
            $enzyme = trim($enzyme_string);
            $aEnzymes = preg_split("/\s/", $enzyme, -1, PREG_SPLIT_NO_EMPTY);
            array_walk($aEnzymes, "trim_element");
            $in_enzyme_flag = FALSE;
        }

        // It appears there are no STRUCTURE/S entries in the 'COMPOUND' file.

        if ($label == "DBLINKS")
        {
            $aDblinks = array();
            $dblink = preg_split("/: /", trim(substr($linestr,12)), -1, PREG_SPLIT_NO_EMPTY);
            array_walk($dblink, "trim_element");
            $aDblinks[] = $dblink;

            $in_dblink_flag = TRUE;
        }
        elseif ( (strlen($label) == 0) and ($in_dblink_flag) )
        {
            // at the 2nd, 3rd, etc. line of the DBLINK data field.
            $dblink = preg_split("/: /", trim(substr($linestr,12)), -1, PREG_SPLIT_NO_EMPTY);
            array_walk($dblink, "trim_element");
            $aDblinks[] = $dblink;
        }
        elseif ( (strlen($label) > 0) and ($in_dblink_flag) )
        {
            $in_dblink_flag = FALSE;
        }

        if ($label == '///') break;

    } // CLOSES outermost while() loop

    // Save parsed values to a new Reaction object

    $oCompound = new Compound();
    $oCompound->entry = $entry;
    $oCompound->name = $aNames;
    $oCompound->formula = $formula;
    $oCompound->pathway = $aPaths;
    $oCompound->reaction = $aReactions;
    $oCompound->enzyme = $aEnzymes;
    $oCompound->dblink = $aDblinks;

    return $oCompound;
}

// Each enzyme file should end with a blank line after the last record terminator ('///'),
// otherwise, the parser will skip over the last entry of the file which does not follow 
// this format/rule.

function parse_enzyme_kegg($flines)
{
    // Initialization of variables.

    $in_name_flag = FALSE;
    $aNames = array();
    $name_string = "";

    $in_sysname_flag = FALSE;
    $sysname_string = "";

    $in_react_flag = FALSE;
    $react_string = "";
    $aReactions = array();

    $in_sub_flag = FALSE;
    $sub_string = "";

    $in_prod_flag = FALSE;
    $prod_string = "";
    $aProducts = array();

    $in_comm_flag = FALSE;
    $comm_string = "";

    $in_ref_flag = FALSE;
    $ref_string = "";
    $aRefs = array();
    $inner = array();

    $in_path_flag = FALSE;
    $path_string = "";
    $aPaths = array();

    $in_dblink_flag = FALSE;
    $aDblinks = array();

    $in_struct_flag = FALSE;
    $struct_string = "";
    $aStructs = array();

    $in_ortho_flag = FALSE;
    $ortho_string = "";
    $aOrthologs = array();

    $in_genes_flag = FALSE;
    $genes_string = "";
    $aGenes = array();

    $in_dis_flag = FALSE;
    $dis_string = "";
    $aDiseases = array();

    $in_motif_flag = FALSE;
    $motif_string = "";
    $aMotifs = array();

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

        // CLASS field - first line is the class, 2nd is subclass, 3rd is
        // the sub-subclass.  Store as an array.
        if ($label == "CLASS")
        {
            $aClasses = array();
            $aClasses[] = trim(substr($linestr,12));
            $in_class_flag = TRUE;
        }
        elseif ( (strlen($label) == 0) and ($in_class_flag) )
        {
            $aClasses[] = trim(substr($linestr,12));
        }
        elseif ( (strlen($label) > 0) and ($in_class_flag) )
        {
            $in_class_flag = FALSE;
        }

        // SYSNAME field may be one or more lines.  SYSNAME follows conventions
        // for multiple lines as stated in the Ligand Manual (e.g. uses $ sign).
        if ($label == "SYSNAME")
        {
            $sysname_string = trim(substr($linestr,12));
            $in_sysname_flag = TRUE;
        }
        elseif ( (strlen($label) == 0) and ($in_sysname_flag) )
        {
            if (left(trim(substr($linestr,12)),1) == '$')
                $sysname_string .= trim(substr($linestr,13));
            else
                $sysname_string .= " " . trim(substr($linestr,12));
        }
        elseif ( (strlen($label) > 0) and ($in_sysname_flag) )
        {
            $sysname = trim($sysname_string);
            $in_sysname_flag = FALSE;
        }

        // REACTION field may be one or more lines.  Format is different from
        // the REACTION field in 'COMPOUNDS' file.  Follows the '$' convention
        // for multiple lines.  When doing a scan(?) of $aReactions array, it
        // splits each string into one or more lines (\n) but the value is not
        // affected (it's still one string).
        if ($label == "REACTION")
        {
            $react_string = substr($linestr,12);
            $in_react_flag = TRUE;
        }
        elseif ( (strlen($label) == 0) and ($in_react_flag) )
        {
            if (left(trim(substr($linestr,12)),1) == '$')
                $react_string .= substr($linestr,13);
            else
                $react_string .= " " . substr($linestr,12);
        }
        elseif ( (strlen($label) > 0) and ($in_react_flag) )
        {
            $reaction = trim($react_string);
            $aReactions = preg_split("/;/", $reaction, -1, PREG_SPLIT_NO_EMPTY);
            array_walk($aReactions, "trim_element");
            $in_react_flag = FALSE;
        }

        // SUBSTRATE field - contains entries found in the LEFT SIDE of the
        // REACTION equation (before the '=' sign).  Items are to be stored
        // in an array.  Parsing technique will be a combination of those
        // used for CLASS and REACTION (array of possibly multi-line entries,
        // following the "$ convention").

        /* Get one line.  Store in tempstring.
           Get next line.  Check if first char is '$'.  If it is, concatenate
           to tempstring.  If not, then it's a new entry, store tempstring
              to array of substrate entries.
        */
        if ($label == "SUBSTRATE")
        {
            $aSubstrates = array();
            $sub_string = substr($linestr,12);
            $in_sub_flag = TRUE;
        }
        elseif ( (strlen($label) == 0) and ($in_sub_flag) )
        {
            if (left(trim(substr($linestr,12)),1) == '$')
            {
                // current line is a continuation of the previous line(s).
                $sub_string .= substr($linestr,13);
            }
            else
            {
                // $aSubstrates[] = trim($sub_string);
                $sub_string = substr($linestr,12);
            }
        }
        elseif ( (strlen($label) > 0) and ($in_sub_flag) )
        {
            $aSubstrates[] = trim($sub_string);
            $in_sub_flag = FALSE;
        }

        // PRODUCT field - contains entries found in the RIGHT SIDE of the
        // REACTION field.  Parsed the same way as SUBSTRATE.
        if ($label == "PRODUCT")
        {
            $aProducts = array();
            $prod_string = substr($linestr,12);
            $in_prod_flag = TRUE;
        }
        elseif ( (strlen($label) == 0) and ($in_prod_flag) )
        {
            if (left(trim(substr($linestr,12)),1) == '$')
            {
                // current line is a continuation of the previous line(s).
                $prod_string .= substr($linestr,13);
            }
            else
            {
                // $aProducts[] = trim($prod_string);
                $prod_string = substr($linestr,12);
            }
        }
        elseif ( (strlen($label) > 0) and ($in_prod_flag) )
        {
            $aProducts[] = trim($prod_string);
            $in_prod_flag = FALSE;
        }

        // DEFINITION field may be one or more lines
        if ($label == "COMMENT")
        {
            $comm_string .= trim(substr($linestr,12)) . " ";
            $in_comm_flag = TRUE;
        }
        elseif ( (strlen($label) == 0) and ($in_comm_flag) )
        {
            // at the 2nd, 3rd, etc. line of the DEFINITION data field.
            $comm_string .= trim(substr($linestr,12)) . " ";
        }
        elseif ( (strlen($label) > 0) and ($in_comm_flag) )
        {
            $comment = trim($comm_string);
            $in_comm_flag = FALSE;
        }

        // REFERENCE field - skip this for now.

        /*
     REFERENCE   1
                      Branden, G.-I., Jornvall, H., Eklund, H. and Furugren, B. Alcohol
        dehydrogenase. In: Boyer, P.D. (Ed.), The Enzymes, 3rd ed., vol.
        11, Academic Press, New York, 1975, p. 103-190.
        2  [UI:77115786]
        Jornvall, H. Differences between alcohol dehydrogenases. Structural
        properties and evolutionary aspects. Eur. J. Biochem. 72 (1977)
        443-452.
        3
        Negelein, E. and Wulff, H.-J. Diphosphopyridinproteid ackohol,
        acetaldehyd. Biochem. Z. 293 (1937) 351-389.
        4
        Sund, H. and Theorell, H. Alcohol dehydrogenase. In: Boyer, P.D.,
        Lardy, H. and Myrback, K. (Eds.), The Enzymes, 2nd ed., vol. 7,
        Academic Press, New York, 1963, p. 25-83.
        5
        Theorell, H. Kinetics and equilibria in the liver alcohol
        dehydrogenase system. Adv. Enzymol. Relat. Subj. Biochem. 20 (1958)
        31-49.

        Sample output:
        ( 0 => (ID => [UI:xxx], AUTHORS => (Branden, G.I., Jornvall, H., Eklund, H., Furugren, B.),
        TITLE => Alcohol dehydrogenase, ... ),
        1 => (ID => [UI:xxx], AUTHORS => (Branden, G.I., Jornvall, H., Eklund, H., Furugren, B.),
        TITLE => Alcohol dehydrogenase, ... ) )
        */

        if ($label == "REFERENCE")
        {
            $ref_ctr = 0;
            $aRefs = array();
            $inner = array();
            // we are at the start of a reference set and the first reference entry.
            $ref_string = trim(substr($linestr,12));
            $ref_array = preg_split("/[\s]+/", $ref_string);
            $inner["ID"] = $ref_array[1];
            // $aRefs[] = $inner;

            $ref_string = "";
            $in_ref_flag = TRUE;
        }
        elseif ( (strlen($label) == 0) and ($in_ref_flag) )
        {
            $current_string = trim(substr($linestr,12));
            $ref_array = preg_split("/[\s]+/", $current_string);
            $first_token = $ref_array[0];
            if (is_numeric($first_token) == TRUE)
            {
                // we are at the start of a reference (entry) within a reference set.
                $ref_ctr++;
                $inner["REFSTR"] = $ref_string;
                $aRefs[] = $inner;
                $inner = array();
                $ref_string = "";
            }
            else $ref_string .= $current_string . " ";
        }
        elseif ( (strlen($label) > 0) and ($in_ref_flag) )
        {
            $ref_ctr++;
            $inner["REFSTR"] = $ref_string;
            $aRefs[] = $inner;
            $ref_string = "";

            $in_ref_flag = FALSE;

            $tempo = array();
            foreach($aRefs as $refno => $ref_array)
            { // opens FOREACH
                $ref_string = $ref_array["REFSTR"];
                $ref_string_len = strlen($ref_string);
                $period_flag = FALSE;
                $space_flag = FALSE;
                $author_string = "";

                $refstr = preg_split("/\.\s[^a]/", $ref_string, -1, PREG_SPLIT_NO_EMPTY);
                $pos = strpos($ref_string, $refstr[1]);
                $rest_ref = substr($ref_string, $pos-1);

                $authors = $refstr[0] . ".";
                $authors_array = preg_split("/\.\,/", $authors, -1, PREG_SPLIT_NO_EMPTY);
                array_walk($authors_array, "trim_element");
                $auth_len = count($authors_array);
                $ctr = 0;
                $temp = array();
                foreach($authors_array as $auth)
                {
                    $ctr++;
                    if ($ctr == $auth_len)
                    {
                        $last_auth = preg_split("/\sand\s/", $auth, -1, PREG_SPLIT_NO_EMPTY);
                        $temp[] = $last_auth[0];
                        $temp[] = $last_auth[1];
                    }
                    else
                    {
                        $temp[] = $auth . ".";
                    }
                }

                $tempo["ID"] = $ref_array["ID"];
                $tempo["AUTHORS"] = $temp;
                $tempo["REST_REF"] = $rest_ref;
                $outer[] = $tempo;
            } // closes FOREACH

        }

        // PATHWAY field - may be one or more lines.

        if ($label == "PATHWAY")
        {
            // at the first line of PATHWAY data field.
            $path_string = trim(substr($linestr,12)) . " ";
            $in_path_flag = TRUE;
        }
        elseif ( (strlen($label) == 0) and ($in_path_flag) )
        {
            // at the 2nd, 3rd, etc. line of the PATHWAY data field.
            $path_string .= trim(substr($linestr,12)) . " ";
        }
        elseif ( (strlen($label) > 0) and ($in_path_flag) )
        {
            // at the first line of the next data section (or end of entry).
            $pathway = trim($path_string);

            $paths = preg_split("/PATH:/", $pathway, -1, PREG_SPLIT_NO_EMPTY);
            array_walk($paths, "trim_element");
            $aPaths = array();
            foreach($paths as $path)
            {
                $path_id = trim(left($path,10));
                $path_desc = trim(substr($path,10));
                $path_array = array($path_id, $path_desc);
                $aPaths[] = $path_array;
            }
            // set the flag to indicate end of PATHWAY data field.
            $in_path_flag = FALSE;
        }

        if ($label == "STRUCTURES")
        {
            $struct_string = trim(substr($linestr, 12));
            $aStructs = preg_split("/: /", $struct_string, -1, PREG_SPLIT_NO_EMPTY);
            $struct_db = $aStructs[0];
            $struct_string = $aStructs[1];
            $in_struct_flag = TRUE;
        }
        elseif ( (strlen($label) == 0) and ($in_struct_flag) )
        {
            $struct_string .= " " . trim(substr($linestr,12));
        }
        elseif ( (strlen($label) > 0) and ($in_struct_flag) )
        {
            $aStructs = preg_split("/\s/", $struct_string, -1, PREG_SPLIT_NO_EMPTY);
            $in_struct_flag = FALSE;
        }

        if ($label == "DBLINKS")
        {
            $aDblinks = array();
            $dblink = preg_split("/: /", trim(substr($linestr,12)), -1, PREG_SPLIT_NO_EMPTY);
            array_walk($dblink, "trim_element");
            $aDblinks[] = $dblink;

            $in_dblink_flag = TRUE;
        }
        elseif ( (strlen($label) == 0) and ($in_dblink_flag) )
        {
            // at the 2nd, 3rd, etc. line of the DBLINK data field.
            $dblink = preg_split("/: /", trim(substr($linestr,12)), -1, PREG_SPLIT_NO_EMPTY);
            array_walk($dblink, "trim_element");
            $aDblinks[] = $dblink;
        }
        elseif ( (strlen($label) > 0) and ($in_dblink_flag) )
        {
            $in_dblink_flag = FALSE;
        }

        /*
        ORTHOLOG    KO: K01967  3-methylcrotonyl-CoA carboxylase
                    KO: K01968  3-methylcrotonyl-CoA carboxylase alpha subunit
                    KO: K01969  3-methylcrotonyl-CoA carboxylase beta subunit

        PATHWAY     PATH: MAP00010  Glycolysis / Gluconeogenesis
                    PATH: MAP00071  Fatty acid metabolism
        */

        if ($label == "ORTHOLOG")
        {
            // at the first line of ORTHOLOG data field.
            $ortho_string = trim(substr($linestr,12)) . " ";
            $in_ortho_flag = TRUE;
        }
        elseif ( (strlen($label) == 0) and ($in_ortho_flag) )
        {
            // at the 2nd, 3rd, etc. line of the ORTHOLOG data field.
            $ortho_string .= trim(substr($linestr,12)) . " ";
        }
        elseif ( (strlen($label) > 0) and ($in_ortho_flag) )
        {
            // at the first line of the next data section (or end of entry).
            $ortholog = trim($ortho_string);

            $orthos = preg_split("/KO:/", $ortholog, -1, PREG_SPLIT_NO_EMPTY);
            array_walk($orthos, "trim_element");
            $aOrthologs = array();
            foreach($orthos as $ortho)
            {
                $ortho_id = trim(left($ortho,8));
                $ortho_desc = trim(substr($ortho,8));
                $ortho_array = array($ortho_id, $ortho_desc);
                $aOrthologs[] = $ortho_array;
            }
            // set the flag to indicate end of ORTHOLOG data field.
            $in_ortho_flag = FALSE;
        }

        /*
        MLO: mll6011 mll6012 mll7727 mll7731
        SME: SMb21122(mccA) SMb21124(mccB)
        ATU: Atu3478(mccB) Atu3479(mccA)
        ATC: AGR_L_2704 AGR_L_2706

        For now, assume one organism (and its 3-letter code) : one line : one or more genes separated by whitespace(s).
        */
        if ($label == "GENES")
        {
            $aGenes = array();
            $genes_string = trim(substr($linestr,12));
            $genes_array = preg_split("/: /", $genes_string, -1, PREG_SPLIT_NO_EMPTY);
            $org_code = $genes_array[0];
            $genes_codes = $genes_array[1];
            $genes_array = preg_split("/\s/", $genes_codes, -1, PREG_SPLIT_NO_EMPTY);
            $aGenes[] = array($org_code, $genes_array);
            $in_genes_flag = TRUE;
        }
        elseif ( (strlen($label) == 0) and ($in_genes_flag) )
        {
            // at the 2nd, 3rd, etc. line of the GENES data field.
            $genes_string = trim(substr($linestr,12));
            $genes_array = preg_split("/: /", $genes_string, -1, PREG_SPLIT_NO_EMPTY);
            $org_code = $genes_array[0];
            $genes_codes = $genes_array[1];
            $genes_array = preg_split("/\s/", $genes_codes, -1, PREG_SPLIT_NO_EMPTY);
            $aGenes[] = array($org_code, $genes_array);
        }
        elseif ( (strlen($label) > 0) and ($in_genes_flag) )
        {
            $in_genes_flag = FALSE;
        }

        // DISEASE data field - code will work even if one entry (beginning with MIM:) occupies 2
        // or more lines before the next MIM: is found.

        if ($label == "DISEASE")
        {
            $aDiseases = array();
            // at the first line of DISEASE data field.
            $dis_string = trim(substr($linestr,12)) . " ";
            $in_dis_flag = TRUE;
        }
        elseif ( (strlen($label) == 0) and ($in_dis_flag) )
        {
            // at the 2nd, 3rd, etc. line of the DISEASE data field.
            $dis_string .= trim(substr($linestr,12)) . " ";
        }
        elseif ( (strlen($label) > 0) and ($in_dis_flag) )
        {
            // at the first line of the next data section (or end of entry).
            $disease = trim($dis_string);

            $diseases = preg_split("/MIM:/", $disease, -1, PREG_SPLIT_NO_EMPTY);
            array_walk($diseases, "trim_element");
            $aDiseases = array();
            foreach($diseases as $dis)
            {
                $dis_id = trim(left($dis,8));
                $dis_desc = trim(substr($dis,8));
                $dis_array = array($dis_id, $dis_desc);
                $aDiseases[] = $dis_array;
            }
            // set the flag to indicate end of DISEASE data field.
            $in_dis_flag = FALSE;
        }

        /*
        Assumption: When broken into two or more lines, a motif is to be reconstructed by concatenating
        the different lines WITHOUT a whitespace in between lines.  Other multi-line data fields are
           connected with whitespaces in between lines.

        MOTIF       PS: PS01055  K-[LIVM]-D-G-[LIVM]-[SA]-x(4)-Y-x(2)-G-x-[LF]-x(4)-
                           [ST]-R-G-[DN]-G-x(2)-G-[DE]-[DENL]
                      PS: PS01056  [IV]-G-[KR]-[ST]-G-x-[LIVM]-[STNK]-x-[VT]-x(2)-L-x-
                           [PS]-[IV]
                      PS: PS50172  BRCT domain profile
                      PS: PS00061  [LIVSPADNK]-x(12)-Y-[PSTAGNCV]-[STAGNQCIVM]-[STAGC]-K-
                           {PC}-[SAGFYR]-[LIVMSTAGD]-x(2)-[LIVMFYW]-x(3)-
                           [LIVMFYWGAPTHQ]-[GSACQRHM]
        */

        if ($label == "MOTIF")
        {
            $motif_string = trim(substr($linestr,12));
            $in_motif_flag = TRUE;
        }
        elseif ( (strlen($label) == 0) and ($in_motif_flag) )
        {
            $motif_string .= trim(substr($linestr,12));
        }
        elseif ( (strlen($label) > 0) and ($in_motif_flag) )
        {
            $motif = trim($motif_string);

            $motifs = preg_split("/PS:/", $motif, -1, PREG_SPLIT_NO_EMPTY);
            array_walk($motifs, "trim_element");
            $aMotifs = array();
            foreach($motifs as $motif)
            {
                $motif_id = trim(left($motif,9));
                $motif_desc = trim(substr($motif,9));
                $motif_array = array($motif_id, $motif_desc);
                $aMotifs[] = $motif_array;
            }
            $in_motif_flag = FALSE;
        }

        if ($label == '///') break;
    } // CLOSES outermost while() loop

    $oEnzyme = new Enzyme();
    $oEnzyme->entry = $entry;
    $oEnzyme->name = $aNames;
    $oEnzyme->class = $aClasses;
    $oEnzyme->sysname = $sysname;
    $oEnzyme->reaction = $aReactions;
    $oEnzyme->substrate = $aSubstrates;
    $oEnzyme->product = $aProducts;
    $oEnzyme->comment = $comment;
    $oEnzyme->reference = $outer;
    $oEnzyme->pathway = $aPaths;
    $oEnzyme->structures = $aStructs;
    $oEnzyme->dblinks = $aDblinks;
    $oEnzyme->ortholog = $aOrthologs;
    $oEnzyme->genes = $aGenes;
    $oEnzyme->disease = $aDiseases;
    $oEnzyme->motif = $aMotifs;

    return $oEnzyme;
}

function parse_ligand_kegg()
{
}

function parse_mol_kegg()
{
}

// This has the weird behavior of not reading the last entry/record if the
// file doesn't end with a blank line (below the END OF RECORD line).  Fix
// this later.

function parseall($files, $object, $format)
{
    $aoObj = array();
    foreach($files as $fname)
    {
        $fp = fopen($fname, "r");
        if ($fp == FALSE) die("Cannot open $fname!");

        $flines = array();
        $eor = get_eor($format);
        $funcname = get_parsefunc($object, $format);
        while(1)
        {
            $linestr = fgets($fp, 141);
            if (feof($fp) == TRUE) break;
            $flines[] = $linestr;

            if (trim($linestr) == $eor)
            {
                $evalstr = "\$aoObj[] = " . $funcname . "(\$flines)" . ";";
                eval($evalstr);
                $flines = array();
            }
        }
        fclose($fp);
    }
    return $aoObj;
}

function get_eor($format)
{
    $format = strtoupper($format);
    switch($format)
    {
        case "GENBANK": return '//';
        case "GB": return '//';
        case "SWISSPROT": return '//';
        case "SWP": return '//';
        case "KEGG": return '///';
        case "NCBILIT": return '--------------------------------------------------------';
    }
}

// $files is an array of name of all the files to be parsed.

function parseall_reaction_kegg($files)
{
    $aoReactions = array();
    foreach($files as $fname)
    {
        $fp = fopen($fname, "r");
        if ($fp == FALSE) die("Cannot open $fname!");

        $flines = array();
        while(1)
        {
            $linestr = fgets($fp, 141);
            if (feof($fp) == TRUE) break;
            $flines[] = $linestr;
            if (trim($linestr) == '///')
            {
                $aoReactions[] = parse_reaction_kegg($flines);
                $flines = array();
            }
        }
        fclose($fp);
    }
    return $aoReactions;
}


// We will require the webpage(s) to pass this function an assoc array of
// values, where the keys correspond to the name of fields in a REACTION
// record in UPPERCASE.  Example keys: ENTRY, DEFINITION, EQUATION, etc.
// Format: $fields = array("ENTRY" => value, "NAME" => value, ...)

function write_reaction_kegg($fields)
{
    $result = str_pad("ENTRY", 12) . $fields["ENTRY"];
    $result .= "\n";

    if ( strlen(trim($fields["NAME"])) > 0 )
        $result .= str_pad("NAME",12) . linechop(rtrim($fields["NAME"]), 68, str_pad("$", 13, " ", STR_PAD_LEFT));
    // no need to put a "\n" because linechop puts one at the end already

    $result .= str_pad("DEFINITION",12) . linechop(rtrim($fields["DEFINITION"]), 68, str_pad("$", 13, " ", STR_PAD_LEFT));
    // no need to put a "\n" because linechop puts one at the end already
    $result .= str_pad("EQUATION",12) . linechopw($fields["EQUATION"], 68, "            ");

    if ( (count($fields["PATHWAY"]) > 0) or (strlen($fields["PATHWAY_ADD"]) > 0) )
    {
        $pway_add_tokens = preg_split("/::/", $fields["PATHWAY_ADD"], -1, PREG_SPLIT_NO_EMPTY);

        if ( (count($pway_add_tokens) == 0) and (strlen($fields["PATHWAY_ADD"]) > 0) )
            // there is exactly one PATHWAY entry in the text box
            $pway_add_tokens = array($fields["PATHWAY_ADD"]);

        $new_pway = array_merge($fields["PATHWAY"], $pway_add_tokens);
        $result .= str_pad("PATHWAY",12) . fmt_pway($new_pway, "            ");
    }

    if ( (count($fields["ENZYME"]) > 0) or (strlen($fields["ENZYME"]) > 0) )
    {
        // Example: $fields["ENZYME_ADD"] = "1.1.1.1 2.2.2.2       3.3.3.3"
        // $enz_add = array("1.1.1.1", "2.2.2.2", ... )
        $enz_add_tokens = preg_split("/\s+/", $fields["ENZYME_ADD"], -1, PREG_SPLIT_NO_EMPTY);
        $new_enzyme = array_merge($fields["ENZYME"], $enz_add_tokens);
        $result .= str_pad("ENZYME",12) . multientry_pad($new_enzyme, " ", 16, STR_PAD_RIGHT, 3, "            ");
        $result .= "\n";
    }

    $result .= "///";
    return $result;
}

/*	
PATHWAY     PATH: MAP00290  Valine, leucine and isoleucine biosynthesis
            PATH: MAP00650  Butanoate metabolism
            PATH: MAP00010  Glycolysis / Gluconeogenesis
*/

function fmt_pway($aPways, $prefix = "")
{
    $result = "";
    $line_ctr = 0;
    foreach($aPways as $pway)
    {
        $line_ctr++;
        $pway_tokens = preg_split("/\s+/", $pway, -1, PREG_SPLIT_NO_EMPTY);
        array_walk($pway_tokens, "trim_element");

        $pway_line = "PATH: " . $pway_tokens[0] . "  " . $pway_tokens[1] . "\n";
        if ($line_ctr == 1) $result .= $pway_line;
        else $result .= $prefix . $pway_line;
    }
    return $result;
}

function parse_reaction_kegg($flines)
{
    // Initialization of variables.
    $entry_ctr = 0;

    $in_name_flag = FALSE;
    $in_def_flag = FALSE;
    $in_eq_flag = FALSE;
    $in_path_flag = FALSE;
    $in_enzyme_flag = FALSE;

    // Note: Should this function be modified to parse more than one record,
    // each of these lines should be placed whenever the end of a multi-line
    // data field is detected.
    $name_string = "";
    $def_string = "";
    $eq_string = "";
    $path_string = "";

    while( list($lineno, $linestr) = each($flines) )
    { // OPENS outermost while() loop

        $entry_ctr++;

        $label = trim(left($linestr, 12));

        // Assume that ENTRY is always one line.
        if ($label == "ENTRY") $entry = trim(substr($linestr, 12));

        // NAME field may be one or more lines. Uses the $ sign notation.
        // June 5, 2003: Inside this IF stmt, changed all trim() to rtrim().
        if ($label == "NAME")
        {
            $name_string = rtrim(substr($linestr,12));
            $in_name_flag = TRUE;
        }
        elseif ( (strlen($label) == 0) and ($in_name_flag) )
        {
            if (left(trim(substr($linestr,12)),1) == '$')
                $name_string .= rtrim(substr($linestr,13));
            else
                $name_string .= " " . rtrim(substr($linestr,12));
        }
        elseif ( (strlen($label) > 0) and ($in_name_flag) )
        {
            $name = rtrim($name_string);
            $in_name_flag = FALSE;
        }

        /* DEFINITION data field - multi-word, multi-line, single entry.
        Follows the $ convention for line continuation.

           Example:
           DEFINITION  sn-Glycerol 3-phosphate + H2O <=> Glycerol + Orthophosphate

           Syntax:
           DEFINITION  subs1 + subs2 + ... <=> prod1 + prod2 + ...
        */

        if ($label == "DEFINITION")
        {
            $def_string .= rtrim(substr($linestr,12));
            $in_def_flag = TRUE;
        }
        elseif ( (strlen($label) == 0) and ($in_def_flag) )
        {
            if (left(trim(substr($linestr,12)),1) == '$')
                $def_string .= rtrim(substr($linestr,13));
            else
                $def_string .= " " . rtrim(substr($linestr,12));
        }
        elseif ( (strlen($label) > 0) and ($in_def_flag) )
        {
            $def_string = rtrim($def_string);
            $in_def_flag = FALSE;
        }

        /* The ligand user manual says DEFINITION and EQUATION are mandatory and
           that the latter immediately follows the former. EQUATION label signals
           end of DEFINITION field. */

        if ($label == "EQUATION")
        {
            // trim "accumulated string" and store in $definition variable.
            $definition = trim($def_string);
            // set flag to FALSE to indicate we've exited the DEFINITION data field.
            $in_def_flag = FALSE;

            $eq_string = trim(substr($linestr,12)) . " ";
            $in_eq_flag = TRUE;
        }
        elseif ( (strlen($label) == 0) and ($in_eq_flag) )
        {
            // at the 2nd, 3rd, etc. line of the EQUATION data field.
            $eq_string .= trim(substr($linestr,12)) . " ";
        }
        elseif ( (strlen($label) > 0) and ($in_eq_flag) )
        {
            // at the first line of the next data section (or end of entry).
            $equation = trim($eq_string);
            $in_eq_flag = FALSE;
        }

        // PATHWAY data field may have one or more lines.

        if ($label == "PATHWAY")
        {
            // at the first line of PATHWAY data field.
            $path_string = trim(substr($linestr,12)) . " ";
            $in_path_flag = TRUE;
        }
        elseif ( (strlen($label) == 0) and ($in_path_flag) )
        {
            // at the 2nd, 3rd, etc. line of the PATHWAY data field.
            $path_string .= trim(substr($linestr,12)) . " ";
        }
        elseif ( (strlen($label) > 0) and ($in_path_flag) )
        {
            // at the first line of the next data section (or end of entry).
            $pathway = trim($path_string);

            $paths = preg_split("/PATH:/", $pathway, -1, PREG_SPLIT_NO_EMPTY);
            array_walk($paths, "trim_element");
            $aPaths = array();
            foreach($paths as $path)
            {
                $path_id = trim(left($path,10));
                $path_desc = trim(substr($path,10));
                $path_array = array($path_id, $path_desc);
                $aPaths[] = $path_array;
            }
            // set the flag to indicate end of PATHWAY data field.
            $in_path_flag = FALSE;
        }

        // ENZYME data field may be one or more lines.
        if ($label == "ENZYME")
        {
            $enzyme_string = trim(substr($linestr,12)) . " ";
            $in_enzyme_flag = TRUE;
        }
        elseif ( (strlen($label) == 0) and ($in_enzyme_flag) )
        {
            $enzyme_string .= trim(substr($linestr,12)) . " ";
        }
        elseif ( (strlen($label) > 0) and ($in_enzyme_flag) )
        {
            $enzyme = trim($enzyme_string);
            $aEnzymes = preg_split("/\s/", $enzyme, -1, PREG_SPLIT_NO_EMPTY);
            array_walk($aEnzymes, "trim_element");
            $in_enzyme_flag = FALSE;
        }

        if ($label == '///') break;

    } // CLOSES outermost while() loop

    // Save parsed values to a new Reaction object

    $oReaction = new Reaction();
    $oReaction->entry = $entry;
    $oReaction->name = $name;
    $oReaction->definition = $definition;
    $oReaction->pathway = $aPaths;
    $oReaction->equation = $equation;
    $oReaction->enzyme = $aEnzymes;

    return $oReaction;
}

/*
RULES USED IN GENERATING KEGG REACTION FILES FROM WEB INPUT

EXAMPLE ENTRY:

ENTRY       R00841
NAME        Glycerol-3-phosphate phosphohydrolase
DEFINITION  sn-Glycerol 3-phosphate + H2O <=> Glycerol + Orthophosphate
EQUATION    C00093 + C00001 <=> C00116 + C00009
PATHWAY     PATH: MAP00561  Glycerolipid metabolism
ENZYME      3.1.3.21        
///

RULES FOR CONTINUATION LINES FOR SELECTED DATA FIELDS/ENTRIES:

1) The name of an enzyme or a chemical compound is sometimes too long to fit
in one line, in which case continuation lines are used.  The continuation
line is indicated by the dollar sign ($) on column 13.  Note that a long
name is simply separated into two lines without any hyphenation.  This
rule applies to the following data items: NAME, SYSNAME, REACTION,
SUBSTRATE, PRODUCT, and DEFINITION.

RULES FOR FORMING VALID REACTION RECORDS:

1) A Kegg reaction record consists of six (6) fields:

   a) ENTRY
   b) NAME
   c) DEFINITION
   d) EQUATION
   e) PATHWAY
   f) ENZYME
   
2) Each field has an entry label and entry data.  The field
   labels are the same as above.
   
3) All field labels occupy positions 1 to 12 of the entry's
   first line.  Succeeding lines do not have field labels,
   and are instead left blank.

4) All field data occupy positions 13 to 80.
   
The following describes each data field in a REACTION record:	

5) ENTRY

   Example: ENTRY       R00841

   5.1. ENTRY data consists of a SINGLE WORD on a SINGLE LINE
   representing the reaction accession number and is mandatory
      for all REACTION records.
      
   5.2. OBS: The reaction accession number is a 6-character
   code.  The first character is the letter "R", the
      2nd to 6th are numeric digits (0-9).
      
   5.3. OBS: The reaction accession number is incremented by
   1 in successive records (although a few exceptions
      have been observed.)

6) NAME

   Example: NAME        Glycerol-3-phosphate phosphohydrolase

   6.1. The NAME data item contains MULTIPLE WORDS on MULTIPLE LINES
   containing the recommended name.  The $ convention for line
      continuations is followed for this entry.
      
      Note: MULTIPLE = one or more
 	  		     WORD = a string of one or more non-whitespace characters.

   6.2. This field is optional.

7) DEFINITION

   Example:
   DEFINITION  sn-Glycerol 3-phosphate + H2O <=> Glycerol + Orthophosphate

   Syntax:
   DEFINITION  subs1 + subs2 + ... <=> prod1 + prod2 + ...

      where subs is a substrate compound name, prod is a product compound name.
   
   7.1. MAN: The DEFINITION data item contains the chemical reaction in
   the form of an equation; substrates and products are separated by
      '<=>', and each compound in substrates and products is separated by
      ' + '. There may be a coefficient before the compound name.  This
      item is mandatory for all entries.
      
   7.2. From 7.1, we can conclude that this is MULTI-WORD and MULTI-LINE.

   7.3. The DEFINITION entry follows the $ convention for line continuations.
   
   7.4. From 7.1, this entry contains the ff. special WORDS: +, <=>

8. EQUATION

   Example: EQUATION    C00093 + C00001 <=> C00116 + C00009
   Syntax:  EQUATION    compid1 + compid2 + ... <=> compid1' + compid2' + ...
   
   8.1. MAN: The EQUATION data item also contains the chemical reaction in 
   the form of an equation. This item represent the chemical compounds
      by their compound IDs, whereas the name of the compounds are used in
      the DEFINITION data item.  This item is mandatory for all entries.
      
   8.2. From 8.1, this entry is MULTI-WORD.
   
   8.3. OBS: This entry is a SPACED MULTI-LINE.
   
   8.4. From 8.1, this entry contains the ff. special WORDS: +, <=>
   
9. PATHWAY

   Example:
   PATHWAY     PATH: MAP00220  Urea cycle and metabolism of amino groups
   PATH: MAP00910  Nitrogen metabolism
   	         PATH: MAP00791  Atrazine degradation

   Syntax:  PATHWAY     PATH:  
                     [ PATH:   ... ]

   9.1. MAN: The PATHWAY data item contains the link information to the
      KEGG (Kyoto Encyclopedia of Genes and Genomes) pathway database:
      the pathway map accession number followed by the description.
      By clicking on this number in the WWW version of DBGET, the
      pathway diagram is displayed.
      
   9.2. OBS: The Pathway map accession number is of the form: MAP99999,
   where each represents a digit from 0-9.

   9.3. From 9.1, one PATHWAY entry the ff. special WORDS: "PATH:"
      
   9.4. This is a MULTI-WORD, MULTI-LINE and MULTI-ENTRY field.
      It follows ONE LINE-ONE ENTRY rule.
      
   9.5. This field is OPTIONAL.
      
10. ENZYME

   Examples: ENZYME      3.1.3.21
            ENZYME      1.2.3.-         1.2.3.13
   Syntax:   ENZYME       [   ... ]
            [   ... ]
   
   10.1. MAN: The ENZYME data item contains the link information to the
         ENZYME section.  The enzyme entries that catalyse the corresponding
         reaction, are listed by their EC numbers.
   
   10.2. From 10.1, this is MULTI-WORD.

   10.3. OBS: From the parser code (which is based on OBS), it appears that
   this is MULTI-ENTRY, MULTI-LINE.  It follows ONE-LINE-MULTI-ENTRY
         rule, with ONE OR MORE SPACES as delimiter.  The number of entries
         in a single line is indeterminate at this time.
   
   10.4. This field is OPTIONAL.
         
11. The END-OF-RECORD (EOR) marker 

   11.1. MAN: The end-of-entry data item marks the end of the entry.  It is
   denoted by the identifier consisting of three consecutive slashes,
         '///'.  This item is mandatory for all entries.
         
   11.2. Corollary to 11.1, the /// is mandatory even of the last entry.  Also,
   it is a good idea to place an extra blank line after the last ///.
*/
?>
