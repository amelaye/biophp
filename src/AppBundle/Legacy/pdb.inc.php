// This code was written by Serge Gregorio, Jr. (serge525@hotmail.com)
// and is under the General Public License (GPL) 2.0 license.
// Last updated: June 2003

<?php
require_once("etc.inc.php");

function brk_tokval(&$value, $key)
{
    if (left($value,7) == "MOL_ID:")
    {
        $aTokval_strings = preg_split("/;/", $value, -1, PREG_SPLIT_NO_EMPTY);
        array_walk($aTokval_strings, "trim_element");
        $temp = array();
        foreach($aTokval_strings as $item)
        {
            $tokval_r = preg_split("/:/", $item, -1, PREG_SPLIT_NO_EMPTY);
            $key = $tokval_r[0];
            $val = trim($tokval_r[1]);
            $temp[$key] = $val;
        }
        $value = $temp;
    }
}

/*
DATA FIELDS FOR WHICH NO CODE HAS YET BEEN WRITTEN:

var $remark;
var $remark1;
var $remark2;
var $remark3;
var $remark4;

  ANISOU 
  SIGUIJ 
  TER 
  HETATM 
  ENDMDL 
10. Connectivity Section 
  CONECT 
11. Bookkeeping Section 
  MASTER 
  END 
*/

class Protein_PDB
{
    var $class;
    var $dep_date;
    var $id_code;

// OBSLTE group
    var $date_rep;         // short for 'date replaced'.
    var $new_id_code;      // array of id codes.

// TITLES group
    var $titles;           // array of titles.

// CAVEAT group
    var $caveats;          // array of caveats.

// COMPND group
    var $compounds;       // array of compound entries, each entry is
    // a string of this form:
    //  MOL_ID: 1; token1: value1; token2: value2;

// SOURCE group
    var $sources;        // array of info about biological sources of molecules.

// KEYWDS group
    var $keywords;        // array of keywords (strings).

// EXPDTA group
    var $expdta;          // array of experimental (technique?) data (strings).

// AUTHOR group
    var $authors;         // array of authors (strings)

// REVDAT group
    var $revdat;          // array of REVISION DATA (2D assoc. array).

    var $sprsde;          // array of SUPERSEDED ENTRIES (2D assoc array like REVDAT).
    var $journal;
    var $remark1;
    var $remark2;
    var $remark3;
    var $remark4;

// DBREF group 
    var $dbrefs;          // array of database references (in itself an associative array)

// SEQADV group
    var $seqadv;          // array of seqadv records (itself an associative array)

// SEQRES group
    var $seqres;          // array of SEQUENCE RESIDUE records (itself an associative array)

// MODRES group
    var $modres;			// array of MODIFICATION OF RESIDUE entries (itself an assoc array).

// HET group
    var $hets;           // array of (single-line) entries (itself an assoc array).

// HETNAM group 
    var $hetnams;        // array of HETEROGENOUS (ATOMS) NAMES (itself an assoc array).

// HETSYN group
    var $hetsyns;        // array of SYNONYMS for HETEROGENOUS ATOMS (itself an assoc array).

// FORMUL group
    var $het_formulas;       // array of (CHEMICAL) FORMULAS FOR HETEROGENOUS ATOMS

// HELIX group
    var $helix;         // array of HELICES (associative array).

// SHEET group  
    var $sheets;        // array of SHEETS (secondary structures) stored as assoc array.

// TURN group
    var $turns;         // array of TURNS (2ndary structures) stored as assoc array.

// SSBOND group
    var $ssbonds;        // array of disulfide bonds in protein and polypeptide structures.

// LINK group
    var $links;          // array of links (between residues).

// HYDBND group
    var $hydbnds;        // array of hydrogen bonds(?).

// SLTBRG group
    var $sltbrgs;        // array of salt bridges b/w residues.

// CISPEP group
    var $cispeps;        // array of Cis peptides (those with omega angles of 0°±30°.
    // Deviations larger than 30° are listed in REMARK 500.

// SITE group
    var $sites;          // array of significant sites in the macromolecule.

// CRYST1 group
    var $cryst1;         // array of CRYST1 unit cell parameters.

// ORIGX group
    var $origx;          // array of coordinates.

// SCALE group
    var $scale;          // array of scales;

// MTRIX group
    var $matrix;         // array of matrices.

// TVECT group
    var $tvect;         // array of translation vectors.

// MODEL group
    var $model;         // array of atomic models (skip for now).

// ATOM group
    var $atoms;         // array of ATOMs

// SIGATM group
    var $sigatms;       // array of STANDARD DEVIATIONS OF ATOMIC PARAMETERS
}

function parse_protein_pdb($flines)
{
    $outer = array();

    $in_title_flag = FALSE;
    $title_string = "";
    $aTitles = array();

    $in_caveat_flag = FALSE;
    $cav_string = "";
    $aCaveats = array();

    $in_compnd_flag = FALSE;
    $compnd_string = "";
    $aCompounds = array();

    $in_key_flag = FALSE;
    $key_string = "";
    $aKeywords = array();

    $in_expdta_flag = FALSE;
    $expdta_string = "";
    $aExpdta = array();

    $in_revdat_flag = FALSE;
    $aRevdats = array();

    $in_sprsde_flag = FALSE;
    $aSprsdes = array();

    $in_jrnl_flag = FALSE;
    $in_jauth_flag = FALSE;
    $in_jtitle_flag = FALSE;
    $in_jedit_flag = FALSE;
    $in_jref_flag = FALSE;
    $in_jpubl_flag = FALSE;
    $in_jrefn_flag = FALSE;

    $jauth_string = "";
    $jtitle_string = "";
    $jedit_string = "";
    $jpubl_string = "";

    $aJournals = array();
    $aJournal = array();
    $aJAuthors = array();
    $aJEditors = array();
    $aJRefns = array();

    $remark_ctr = 0;
    $in_remark1_flag = FALSE;

    $in_rref_flag = FALSE;
    $in_rjauth_flag = FALSE;
    $in_rjtitle_flag = FALSE;
    $in_rjedit_flag = FALSE;
    $in_rjref_flag = FALSE;
    $in_rjpubl_flag = FALSE;
    $in_rjrefn_flag = FALSE;

    $rjauth_string = "";
    $rjtitle_string = "";
    $rjedit_string = "";
    $rjpubl_string = "";

    $aRJournals = array();
    $aRJournal = array();
    $aRJAuthors = array();
    $aRJEditors = array();
    $aRJRefns = array();

    $in_author_flag = FALSE;
    $author_string = "";
    $aAuthors = array();

    $aDBRefs = array();
    $aSeqAdvs = array();

    $in_seqres_flag = FALSE;
    $aChain = array();
    $aSeqRes = array();
    $old_chain = "START";

    $aModRes = array();

    $in_hetnam_flag = FALSE;
    $hetnam_string = "";
    $het_id = "";
    $aHetNams = array();

    $in_hetsyn_flag = FALSE;
    $hetsyn_string = "";
    $het_id = "";
    $aHetSyns = array();

    $in_formul_flag = FALSE;
    $formul_string = "";
    $aFormuls = array();

    $aHelix = array();
    $aSheets = array();
    $aTurns = array();
    $aSSBonds = array();
    $aLinks = array();
    $aHydBnds = array();
    $aSltBrgs = array();
    $aCisPeps = array();
    $aSites = array();
    $aCryst1 = array();
    $aOrigxs = array();
    $aScales = array();
    $aMatrices = array();
    $aTvects = array();
    $aAtoms = array();
    $aSigAtms = array();
    $atom_ctr = 0;

    $in_source_flag = FALSE;
    $aSources = array();

    while ( list($no, $linestr) = each($flines) )
    { // opens outermost WHILE

        $label = trim(left($linestr, 6));
        $data = trim(substr($linestr, 9));
        $lascar = right($linedata, 1);
        $remark_sublabel = trim(substr($linestr,7,3));

        // Check for UNCLOSED items by inspecting the value of flag variables.

        // If current line/entry is not anymore of this form:
        // REMARK   1  REFN  ...
        /*
        if (!( ($label == "REMARK") and ($remark_sublabel == "1") and
                   (substr($linestr,22,4) == "REFN") ))
        */
        if ($in_remark1_flag)
        {
            if ( ($label == "REMARK") and ($remark_sublabel == "1") )
            {
                $rem_refnum = trim(substr($linestr,21,49));
                if ((substr($linestr,11,9) == "REFERENCE") and ($rem_refnum != "1") )
                {
                    // END OF A REMARK 1-REFERENCE entry.
                    if ($in_rjrefn_flag)
                    {
                        // if REMARK 1-REFN subentry is not yet "CLOSED", close it.
                        $aRJournal["REFN"] = $aRJRefns;
                        $aRJRefns = array();
                        $inner = array();
                        $in_rjrefn_flag = FALSE;
                    }
                    if (count($aRJournal) > 0)
                    {
                        // check if $aRJournal array is indeed non-empty.

                        $aRJournals[$rem_refnum-1] = $aRJournal;
                    }
                    $prev_rem_refnum = $rem_refnum;
                }
            }
            else
            {
                // END OF A REMARK 1-REFERENCE entry.
                if ($in_rjrefn_flag)
                {
                    // if REMARK 1-REFN subentry is not yet "CLOSED", close it.
                    $aRJournal["REFN"] = $aRJRefns;
                    $aRJRefns = array();
                    $inner = array();
                    $in_rjrefn_flag = FALSE;
                }
                if (count($aRJournal) > 0)
                {
                    // check if $aRJournal array is indeed non-empty.
                    $aRJournals[$prev_rem_refnum] = $aRJournal;
                }
            }
        }

        // ID data field

        if ($label == "HEADER")
        {
            $class = trim(substr($linestr,10,40));
            $dep_date = trim(substr($linestr,50,9));
            $id_code = trim(substr($linestr,62,4));
        }

        // OBSLTE - OBSOLETE data field

        if ($label == "OBSLTE")
        {
            $date_rep = substr($linestr,11,9);
            $id_code = substr($linestr,21,4);
            $aNew_ids = array();
            $id_ctr = 0;
            for($i = 0; $i < 8; $i++)
            {
                $id = substr($linestr,31+($i*5),4);
                if (strlen(trim($id)) > 0)
                {
                    $aNew_ids[$id_ctr] = trim($id);
                    $id_ctr++;
                }
            }
        }

        /*
        TITLE     RHIZOPUSPEPSIN COMPLEXED WITH REDUCED PEPTIDE INHIBITOR
        TITLE     BETA-GLUCOSYLTRANSFERASE, ALPHA CARBON COORDINATES ONLY

        TITLE     NMR STUDY OF OXIDIZED THIOREDOXIN MUTANT (C62A,C69A,C73A)
        TITLE    2 MINIMIZED AVERAGE STRUCTURE
        */

        if ($label == "TITLE")
        {
            $in_title_flag = TRUE;
            if (is_numeric(substr($linestr,8,2)))
                // current line is a continuation line.
                $title_string .= rtrim(substr($linestr,10,60));
            else
            {
                // we are at a new title, if a previous title exists,
                // add it to our title array.
                if (strlen($title_string) > 0)
                    $aTitles[] = $title_string;
                $title_string = rtrim(substr($linestr,10,60));
            }
        }
        elseif ($in_title_flag)
        {
            $aTitles[] = $title_string;
            $in_title_flag = FALSE;
        }

        // CAVEAT data field - assume that there can be more than one
        // caveat entries, each entry can span one or more lines.  As
        // far as processing is concerned, I'm treating this like the
        // TITLE data field.

        // NOTE: I've ignored the PDB ID CODE field in the CAVEAT as
        // it is merely a duplicate/repetition of $id_code property,
        // parsed earlier in the HEADER section/group of data fields.

        if ($label == "CAVEAT")
        {
            $in_caveat_flag = TRUE;
            if (is_numeric(trim(substr($linestr,8,2))))
                // current line is a continuation line.
                $cav_string .= rtrim(substr($linestr,19,51)) . " ";
            else
            {
                // we are at a new caveat, if a previous caveat exists,
                // add it to our caveat array.
                if (strlen(trim($cav_string)) > 0)
                    $aCaveats[] = $cav_string;
                $cav_string = rtrim(substr($linestr,19,51)) . " ";
            }
        }
        elseif ($in_caveat_flag)
        {
            $aCaveats[] = $cav_string;
            $in_caveat_flag = FALSE;
        }

        /*
        COMPND - COMPOUNDS data field - for now, we don't break down yet, the MOL_ID: 1, etc.
        into individual tokens.  We stop at the level of strings of compound entries.

        COMPND    BOVINE PANCREATIC TRYPSIN INHIBITOR (/BPTI$) MUTANT (TYR 23   1BPT   3
        COMPND   2 REPLACED BY ALA) (/Y23A$)                                    1BPT   4
        */

        if ($label == "COMPND")
        {
            $in_compnd_flag = TRUE;
            if (is_numeric(trim(substr($linestr,8,2))))
                // current line is a continuation line.
                $compnd_string .= rtrim(substr($linestr,10,60));
            else
            {
                // we are at a new COMPND entry, if a previous entry exists,
                // add it to our COMPND array.
                if (strlen(trim($source_string)) > 0)
                    $aCompounds[] = $compnd_string;
                $compnd_string = rtrim(substr($linestr,10,60));
            }
        }
        elseif ($in_compnd_flag)
        {
            $aCompounds[] = $compnd_string;

            // Process each item/element in the $aSources array.
            // If entry starts with "MOL_ID:", then convert into an assoc array,
            // if not, leave it as is (make no changes).
            array_walk($aCompounds, "brk_tokval");
            $in_compnd_flag = FALSE;
        }

        /*
        if ($label == "COMPND")
        {
           $in_compnd_flag = TRUE;
           if (is_numeric(trim(substr($linestr,8,2))))
           // current line is a continuation line.
              $compnd_string .= rtrim(substr($linestr,10,60));
           else
           {
              // we are at a new compound entry, if a previous compound entry exists,
              // add it to our compound array.
              if (strlen(trim($compnd_string)) > 0)
                 $aCompounds[] = $compnd_string;
              $compnd_string = rtrim(substr($linestr,10,60));
              }
           }
        elseif ($in_compnd_flag)
           {
           $aCompounds[] = $compnd_string;
           $in_compnd_flag = FALSE;
           }
        */

        // SOURCE data field - skip this for now.  Treatment: A single entry
        // may be one or more lines, it usually consists of MOL_ID token-value
        // pair followed by other token-value pairs (associated with the MOL_ID).
        // Concatenation stops when you encounter another SOURCE ENTRY with a
        // blank continuation entry or a different entry (e.g. KEYWD).
        /*
        Example:

        SOURCE    MOL_ID: 1;
        SOURCE   2 ORGANISM_SCIENTIFIC: AVIAN SARCOMA VIRUS;
        SOURCE   3 STRAIN: SCHMIDT-RUPPIN B;
        SOURCE   4 EXPRESSION_SYSTEM: ESCHERICHIA COLI;
        SOURCE   5 EXPRESSION_SYSTEM_PLASMID: PRC23IN
        */

        if ($label == "SOURCE")
        {
            $in_source_flag = TRUE;
            if (is_numeric(trim(substr($linestr,8,2))))
                // current line is a continuation line.
                $source_string .= rtrim(substr($linestr,10,60));
            else
            {
                // we are at a new SOURCE entry, if a previous SOURCE entry exists,
                // add it to our SOURCE array.
                if (strlen(trim($source_string)) > 0)
                    $aSources[] = $source_string;
                $source_string = rtrim(substr($linestr,10,60));
            }
        }
        elseif ($in_source_flag)
        {
            $aSources[] = $source_string;

            // Process each item/element in the $aSources array.
            // If entry starts with "MOL_ID:", then convert into an assoc array,
            // if not, leave it as is (make no changes).
            array_walk($aSources, "brk_tokval");
            $in_source_flag = FALSE;
        }

        /* KEYWDS - KEYWORDS data field.  For now, assume there is only ONE LIST of keywords,
           so all lines with label KEYWDS are to be concatenated (we ignore the contents of
           positions 9-10, the continuation chars).
           NOTE: When doing a PRE of the resulting array, the carriage return is apparent.

        Example:

        1234567890123456789012345678901234567890123456789012345678901234567890
        KEYWDS    LYASE, TRICARBOXYLIC ACID CYCLE, MITOCHONDRION, OXIDATIVE
        KEYWDS   2 METABOLISM
        */

        if ($label == "KEYWDS")
        {
            $key_string .= substr($linestr,10,60);
            $in_key_flag = TRUE;
        }
        elseif ($in_key_flag)
        {
            $aKeywords = preg_split("/,/", trim($key_string), -1, PREG_SPLIT_NO_EMPTY);
            array_walk($aKeywords, "trim_element");
            $in_key_flag = FALSE;
        }

        // EXPDTA - EXPERIMENTAL (TECHNIQUE?) DATA field - consists of one or more entries
        // to be placed in an array.  An entry may consist of one or more lines, with the
        // 2nd, 3rd, and succeeding lines indicated by the presence of numeric values in the
        // CONTINUATION field (positions 9-10).

        if ($label == "EXPDTA")
        {
            $in_expdta_flag = TRUE;
            if (is_numeric(substr($linestr,8,2)))
                // current line is a continuation line.
                $expdta_string .= rtrim(substr($linestr,10,60));
            else
            {
                // we are at a new expdta entry, if a previous expdta entry exists,
                // add it to our expdta array.
                if (strlen(trim($expdta_string)) > 0)
                    $aExpdta[] = $expdta_string;
                $expdta_string = rtrim(substr($linestr,10,60));
            }
        }
        elseif ($in_expdta_flag)
        {
            $aExpdta[] = $expdta_string;
            $in_expdta_flag = FALSE;
        }

        // AUTHOR -	AUTHORS (OF ENTRY) data field - assume to be similar to KEYWDS in format and handling.
        // Frankly, I don't see any reason why there should be two or more sets/lists of authors. Just one
        // set with each name separated by a comma would be.

        // MANUAL: Line breaks between multiple lines in the authorList occur only after a comma.

        if ($label == "AUTHOR")
        {
            $author_string .= substr($linestr,10,60);
            $in_author_flag = TRUE;
        }
        elseif ($in_author_flag)
        {
            $aAuthors = preg_split("/,/", trim($author_string), -1, PREG_SPLIT_NO_EMPTY);
            array_walk($aAuthors, "trim_element");
            $in_author_flag = FALSE;
        }

        /*
        Example
                 1         2         3         4         5         6         7
        1234567890123456789012345678901234567890123456789012345678901234567890
        REVDAT   3   15-OCT-89 1PRCB   1       REMARK
        REVDAT   2   19-APR-89 1PRCA   2       CONECT
        REVDAT   1   09-JAN-89 1PRC    0

        Output:

        OLD:

        ( (modnum, (moddate, modid, modtype, rec1, rec2, rec3, rec4), (moddate, ...), ... ),
           (modnum, (moddate, modid, modtype, rec1, rec2, rec3, rec4), (moddate, ...), ... ),
        ... )

        NEW (MAYBE? CHECK WITH PDB FIRST):
        ( modnum => ((moddate, modid, modtype, rec1, rec2, rec3, rec4), (moddate, ...), ... ),
           modnum => ((moddate, modid, modtype, rec1, rec2, rec3, rec4), (moddate, ...), ... ),
        ... )
        */

        if ($label == "REVDAT")
        {
            $in_revdat_flag = TRUE;
            if (is_numeric(trim(substr($linestr,10,2))))
            {
                // current line is a continuation line.
                // $compnd_string .= rtrim(substr($linestr,10,60));
                $inner = array();
                $inner["MOD_DATE"] = substr($linestr,13,9);
                $inner["MOD_ID"] = substr($linestr,23,5);
                $inner["MOD_TYPE"] = substr($linestr,31,1);
                $inner["REC1"] = substr($linestr,39,6);
                $inner["REC2"] = substr($linestr,46,6);
                $inner["REC3"] = substr($linestr,53,6);
                $inner["REC4"] = substr($linestr,60,6);
                $outer[] = $inner;
            }
            else
            {
                // we are at a new REVDAT entry, if a previous entry exists,
                // add it to our REVDAT array.
                if (count($outer) > 0)
                    $aRevdats[] = $outer;
                $outer = array();
                $outer["MOD_NUM"] = substr($linestr,7,3);
                $inner = array();
                $inner["MOD_DATE"] = substr($linestr,13,9);
                $inner["MOD_ID"] = substr($linestr,23,5);
                $inner["MOD_TYPE"] = substr($linestr,31,1);
                $inner["REC1"] = substr($linestr,39,6);
                $inner["REC2"] = substr($linestr,46,6);
                $inner["REC3"] = substr($linestr,53,6);
                $inner["REC4"] = substr($linestr,60,6);
                $outer[] = $inner;
            }
        }
        elseif ($in_revdat_flag)
        {
            $aRevdats[] = $outer;
            $in_revdat_flag = FALSE;
            // Re-initialize these array vars because they will be used by
            // IF statements for other DATA FIELDS like SPRSDE, etc.
            $inner = array();
            $outer = array();
        }

        /*
        SPRSDE - SUPERSEDED ENTRIES data field.  Treat like REVDAT with one difference:
        all entries in the OUTER array are arrays (no ATOMS like MODNUM in REVDAT).

        Example
        1         2         3         4         5         6         7
        1234567890123456789012345678901234567890123456789012345678901234567890
        SPRSDE     17-JUL-84 4HHB      1HHB
        SPRSDE     27-FEB-95 1GDJ      1LH4 2LH4

        Output:

        ( ( (sprsde_date, idcode, sidcode1, ..., sidcode8), (sprsde_date, ...), ... ),
          ( (sprsde_date, idcode, sidcode1, ..., sidcode8), (sprsde_date, ...), ... ),
        ... )
        */

        if ($label == "SPRSDE")
        {
            $in_sprsde_flag = TRUE;
            if (is_numeric(trim(substr($linestr,8,2))))
            {
                // current line is a continuation line.
                $inner = array();
                $inner["SPRSDE_DATE"] = substr($linestr,11,9);
                $inner["ID_CODE"] = substr($linestr,21,4);
                if (trim(substr($linestr,31,4)) != "")
                    $inner["SID_CODE1"] = substr($linestr,31,4);
                if (trim(substr($linestr,36,4)) != "")
                    $inner["SID_CODE2"] = substr($linestr,36,4);
                if (trim(substr($linestr,41,4)) != "")
                    $inner["SID_CODE3"] = substr($linestr,41,4);
                if (trim(substr($linestr,46,4)) != "")
                    $inner["SID_CODE4"] = substr($linestr,46,4);
                if (trim(substr($linestr,51,4)) != "")
                    $inner["SID_CODE5"] = substr($linestr,51,4);
                if (trim(substr($linestr,56,4)) != "")
                    $inner["SID_CODE6"] = substr($linestr,56,4);
                if (trim(substr($linestr,61,4)) != "")
                    $inner["SID_CODE7"] = substr($linestr,61,4);
                if (trim(substr($linestr,6,4)) != "")
                    $inner["SID_CODE8"] = substr($linestr,66,4);
                $outer[] = $inner;
            }
            else
            {
                // we are at a new REVDAT entry, if a previous entry exists,
                // add it to our REVDAT array.
                if (count($outer) > 0)
                    $aSprsdes[] = $outer;
                $outer = array();
                $inner = array();
                $inner["SPRSDE_DATE"] = substr($linestr,11,9);
                $inner["ID_CODE"] = substr($linestr,21,4);
                /*
                $inner["SID_CODE1"] = substr($linestr,31,4);
                $inner["SID_CODE2"] = substr($linestr,36,4);
                $inner["SID_CODE3"] = substr($linestr,41,4);
                $inner["SID_CODE4"] = substr($linestr,46,4);
                $inner["SID_CODE5"] = substr($linestr,51,4);
                $inner["SID_CODE6"] = substr($linestr,56,4);
                $inner["SID_CODE7"] = substr($linestr,61,4);
                $inner["SID_CODE8"] = substr($linestr,66,4);
                */
                if (trim(substr($linestr,31,4)) != "")
                    $inner["SID_CODE1"] = substr($linestr,31,4);
                if (trim(substr($linestr,36,4)) != "")
                    $inner["SID_CODE2"] = substr($linestr,36,4);
                if (trim(substr($linestr,41,4)) != "")
                    $inner["SID_CODE3"] = substr($linestr,41,4);
                if (trim(substr($linestr,46,4)) != "")
                    $inner["SID_CODE4"] = substr($linestr,46,4);
                if (trim(substr($linestr,51,4)) != "")
                    $inner["SID_CODE5"] = substr($linestr,51,4);
                if (trim(substr($linestr,56,4)) != "")
                    $inner["SID_CODE6"] = substr($linestr,56,4);
                if (trim(substr($linestr,61,4)) != "")
                    $inner["SID_CODE7"] = substr($linestr,61,4);
                if (trim(substr($linestr,6,4)) != "")
                    $inner["SID_CODE8"] = substr($linestr,66,4);
                $outer[] = $inner;
            }
        }
        elseif ($in_sprsde_flag)
        {
            $aSprsdes[] = $outer;
            $in_sprsde_flag = FALSE;
        }

        /*
        JRNL - JOURNAL data field.  Has the following sub-records:

        1. AUTH (AUTHOR)
        2. TITL (TITLE)
        3. EDIT (EDITORS)
        4. REF
        5. PUBL
        6. REFN
        7. REFN - ASTM

        Example
                 1         2         3         4         5         6         7
        1234567890123456789012345678901234567890123456789012345678901234567890
        JRNL        AUTH   N.THANKI,J.K.M.RAO,S.I.FOUNDLING,W.J.HOWE,
        JRNL        AUTH 2 A.G.TOMASSELLI,R.L.HEINRIKSON,S.THAISRIVONGS,
        JRNL        AUTH 3 A.WLODAWER
        JRNL        TITL   CRYSTAL STRUCTURE OF A COMPLEX OF HIV-1 PROTEASE
        JRNL        TITL 2 WITH A DIHYDROETHYLENE-CONTAINING INHIBITOR:
        JRNL        TITL 3 COMPARISONS WITH MOLECULAR MODELING
        JRNL        REF    TO BE PUBLISHED
        JRNL        REFN                                                  0353
        JRNL        AUTH   G.FERMI,M.F.PERUTZ,B.SHAANAN,R.FOURME
        JRNL        TITL   THE CRYSTAL STRUCTURE OF HUMAN DEOXYHAEMOGLOBIN AT
        JRNL        TITL 2 1.74 A RESOLUTION
        JRNL        REF    J.MOL.BIOL.                   V. 175   159 1984
        JRNL        REFN   ASTM JMOBAK  UK ISSN 0022-2836                 0070

        $aJournals array
        $aJournal array
        ( ( AUTH => (name1, name2, ...), TITL => "title", EDIT => (editor1, ...),
        REF => ( PUB_NAME => "pubname", ... ), PUBL => "publ", REFN => "refn", REFN_ASTM => "refn_astm" ),
          ( AUTH => (name1, name2, ...), TITL => "title", EDIT => (editor1, ...),
        REF => ( PUB_NAME => "pubname", ... ), PUBL => "publ", REFN => "refn", REFN_ASTM => "refn_astm" ),
     ( AUTH => ..... ) )
        */

        if ($label == "JRNL")
        {
            $in_jrnl_flag = TRUE;
            $sublabel = trim(substr($linestr,12,4));

            // JRNL-AUTH subrecord.
            if ($sublabel == "AUTH")
            {
                $contin = trim(substr($linestr,16,2));
                if (is_numeric($contin))
                {
                    // on the 2nd, 3rd, etc. line of JRNL-AUTH subrecord entry.
                    $jauth_string .= rtrim(substr($linestr,19,51));
                }
                else
                {
                    // on the 1st line of a JRNL-AUTH subrecord entry.  If a prior
                    // $aJournal entry exists, store this in $aJournals array.
                    if (count($aJournal) > 0)
                    {
                        if ($in_jrefn_flag)
                        {
                            // This means the previous JOURNAL's REFN entry has not
                            // yet been "CLOSED/COMMITTED/SAVED".
                            // NOTE: We may have to do this for the other ENTRIES,
                            // e.g. JRNL-PUBL, JRNL-REF, in case the JRNL-REFN is
                            // not mandatory and may not always be the last ENTRY
                            // within a JOURNAL.
                            $aJournal["REFN"] = $aJRefns;
                            $aJRefns = array();
                            $inner = array();
                            $in_jrefn_flag = FALSE;
                        }
                        $aJournals[] = $aJournal;
                        $aJournal = array();
                    }
                    $jauth_string = rtrim(substr($linestr,19,51));
                    $in_jauthor_flag = TRUE;
                }
            }
            elseif ($in_jauthor_flag)
            {
                // current line is JRNL but a different subrecord (not AUTH anymore).
                // start converting AUTH string into an array of AUTHOR NAMES.
                $aJAuthors = preg_split("/,/", trim($jauth_string), -1, PREG_SPLIT_NO_EMPTY);
                array_walk($aJAuthors, "trim_element");
                $aJournal["AUTH"] = $aJAuthors;
                $in_jauthor_flag = FALSE;
            }

            // JRNL-TITLE subrecord
            if ($sublabel == "TITL")
            {
                $contin = trim(substr($linestr,16,2));
                if (is_numeric($contin))
                    // on the 2nd, 3rd, etc. line of JRNL-TITL subrecord entry.
                    $jtitle_string .= rtrim(substr($linestr,19,51)) . " ";
                else
                {
                    // on the 1st line of a JRNL-TITL subrecord entry.
                    $jtitle_string = rtrim(substr($linestr,19,51)) . " ";
                    $in_jtitle_flag = TRUE;
                }
            }
            elseif ($in_jtitle_flag)
            {
                $aJournal["TITL"] = trim($jtitle_string);
                $in_jtitle_flag = FALSE;
            }

            // JRNL-EDIT subrecord.  Handle like JRNL-AUTH minus the end-of-journal
            // entry code that adds $aJournal to $aJournals array.

            if ($sublabel == "EDIT")
            {
                $contin = trim(substr($linestr,16,2));
                if (is_numeric($contin))
                {
                    // on the 2nd, 3rd, etc. line of JRNL-EDIT subrecord entry.
                    $jedit_string .= rtrim(substr($linestr,19,51));
                }
                else
                {
                    // on the 1st line of a JRNL-EDIT subrecord entry.
                    $jedit_string = rtrim(substr($linestr,19,51));
                    $in_jedit_flag = TRUE;
                }
            }
            elseif ($in_jedit_flag)
            {
                // current line is JRNL but a different subrecord (not EDIT anymore).
                // start converting EDIT string into an array of EDITOR NAMES.
                $aJEditors = preg_split("/,/", trim($jedit_string), -1, PREG_SPLIT_NO_EMPTY);
                array_walk($aJEditors, "trim_element");
                $aJournal["EDIT"] = $aJEditors;
                $in_jedit_flag = FALSE;
            }

            // JRNL-REF subrecord.  Like JRNL-EDIT or JRNL-AUTH, except each line
            // is to be treated as one assoc. array to be embedded inside a REF
            // array (to be embeded inside $aJournal, and then into $aJournals).
            // For now, assume that there is at most one REF entry for each
            // JOURNAL entry.  Later, clarify this with PDB authorities.

            if (($sublabel == "REF") and (strtoupper(substr($linestr,19,15)) == "TO BE PUBLISHED") )
            {
                // there is no more "elseif ($in_ref_flag)" statement here because
                // we assume that when entry is "TO BE PUBLISHED", there is exactly
                // ONE REF line (no 2nd, 3rd, etc. REF lines).
                $inner = array();
                $inner["PUB_NAME"] = "TO BE PUBLISHED";
                $aJournal["REF"] = $inner;
            }

            if (($sublabel == "REF") and (strtoupper(substr($linestr,19,15)) != "TO BE PUBLISHED") )
            {
                $contin = trim(substr($linestr,16,2));
                if (is_numeric($contin))
                {
                    // on the 2nd, 3rd, etc. line of JRNL-REF subrecord entry.
                    $inner["PUB_NAME"] .= " " . rtrim(substr($linestr,19,28));
                }
                else
                {
                    // on the 1st line of a JRNL-REF subrecord entry.
                    $inner = array();
                    $inner["PUB_NAME"] = rtrim(substr($linestr,19,28)) . " ";
                    $inner["VOLUME"] = trim(substr($linestr,51,4));
                    $inner["PAGE"] = trim(substr($linestr,56,5));
                    $inner["YEAR"] = (int) (trim(substr($linestr,62,4)));
                    $in_jref_flag = TRUE;
                }
            }
            elseif ($in_jref_flag)
            {
                // current line is JRNL but a different subrecord (not REF anymore).
                $inner["PUB_NAME"] = trim($inner["PUB_NAME"]);
                $aJournal["REF"] = $inner;
                $in_jref_flag = FALSE;
            }

            // JRNL-PUBL subrecord.  Treat like JRNL-TITL subrecord.

            if ($sublabel == "PUBL")
            {
                $contin = trim(substr($linestr,16,2));
                if (is_numeric($contin))
                    // on the 2nd, 3rd, etc. line of JRNL-PUBL subrecord entry.
                    $jpubl_string .= rtrim(substr($linestr,19,51)) . " ";
                else
                {
                    // on the 1st line of a JRNL-TITL subrecord entry.
                    $jpubl_string = rtrim(substr($linestr,19,51)) . " ";
                    $in_jpubl_flag = TRUE;
                }
            }
            elseif ($in_jpubl_flag)
            {
                $aJournal["PUBL"] = trim($jpubl_string);
                $in_jpubl_flag = FALSE;
            }

            // JRNL-REFN subrecord.

            // JRNL-REFN format A: When citation has not been published.
            if (($sublabel == "REFN") and (strtoupper(substr($linestr,66,4)) == "0353") )
            {
                $inner = array();
                $inner["CODEN"] = "0353";
                $aJournal["REFN"] = $inner;
                $inner = array();
            }

            // JRNL-REFN format B: When citation has been published.
            if (($sublabel == "REFN") and (strtoupper(substr($linestr,66,4)) != "0353") )
            {
                // print "INSIDE REFN and NOT 0353";
            $inner = array();
            $inner["ASTM"] = trim(substr($linestr,24,6));
            $inner["COUNTRY"] = substr($linestr,32,2);
            $inner["CODE_SYS"] = substr($linestr,35,4);
            $inner["CODE"] = trim(substr($linestr,40,25));
            $inner["CODEN"] = trim(substr($linestr,66,4));
            $aJRefns[] = $inner;
            $in_jrefn_flag = TRUE;
            }
         } // closes IF part of if ($label == "JRNL")
      elseif ($in_jrnl_flag)
      {
         if ($in_jrefn_flag)
            {
            $aJournal["REFN"] = $aJRefns;
            $aJRefns = array();
            $in_jrefn_flag = FALSE;
            }
         $aJournals[] = $aJournal;
         $aJournal = array();
         $inner = array();
         $in_jrnl_flag = FALSE;
         } // closes ELSE part of if ($label == "JRNL")
         
      // DBREF - DATABASE REFERENCES data field - stored as an array of associative arrays.
      // Each entry is made up of exactly one line (continuations not allowed).
      /*
      		   1         2         3         4         5         6         7
      1234567890123456789012345678901234567890123456789012345678901234567890
      DBREF  1ABC B    1B   36  PDB    1ABC     1ABC             1B    36
      DBREF  3AKY      3   220  SWS    P07170   KAD1_YEAST       5    222
      DBREF  1HAN      2   288  GB     397884   X66122           1    287
      DBREF  3HSV A    1    92  SWS    P22121   HSF_KLULA      193    284
      DBREF  3HSV B    1    92  SWS    P22121   HSF_KLULA      193    284
      */
      
      /*
      REMARK 1
      
      Example:
      REMARK   1                                                              1BPT  14
      REMARK   1 REFERENCE 1                                                  1BPT  15
      REMARK   1  AUTH   K.S.KIM,F.TAO,J.FUCHS,A.T.DANISHEFSKY,D.HOUSSET,     1BPT  16
      REMARK   1  AUTH 2 A.WLODAWER,C.WOODWARD                                1BPT  17
      REMARK   1  TITL   CREVICE-FORMING MUTANTS OF BPTI: STABILITY CHANGES   1BPT  18
      REMARK   1  TITL 2 AND NEW HYDROPHOBIC SURFACE                          1BPT  19
      REMARK   1  REF    TO BE PUBLISHED                                      1BPT  20
      REMARK   1  REFN                                                   353  1BPT  21
      REMARK   1 REFERENCE 2                                                  1BPT  22
      REMARK   1  AUTH   D.HOUSSET,K.-*S.KIM,J.FUCHS,C.WOODWARD,A.WLODAWER    1BPT  23
      REMARK   1  TITL   CRYSTAL STRUCTURE OF A /Y35G$ MUTANT OF BOVINE       1BPT  24
      REMARK   1  TITL 2 PANCREATIC TRYPSIN INHIBITOR                         1BPT  25
      REMARK   1  REF    J.MOL.BIOL.                   V. 220   757 1991      1BPT  26
      REMARK   1  REFN   ASTM JMOBAK  UK ISSN 0022-2836                  070  1BPT  27
      
      TARGET OUTPUT:
      Syntax:
         ( REFNUM => (one JOURNAL array), REFNUM2 => (one JOURNAL array) )
      Example:
  			( 1 => (AUTH => (..), ... ), 2 => (AUTH => (...), ... ) )				
      */

      if ( ($label == "REMARK") and ($remark_sublabel == "1") )
         {
         if ($remark_ctr == 0)
            {
            // we are in the first REMARK 1 line. skip this line and go to the next.
            $remark_ctr++;
            continue;
            }
         elseif (substr($linestr,11,9) == "REFERENCE")
            {
            // we are at the a REMARK  1 REFERENCE x line. get the NO after REFERENCE
            // KEYWORD and then go to the next line.
            $prev_remark_refno = $remark_refno;
            $remark_refno = (int) (trim(substr($linestr,21,49)));
            $remark_ctr++;
            continue;
            }
         // we are at the 3rd, 4th, etc. line of a REMARK 1 entry.  Parse like JOURNAL.

         $in_remark1_flag = TRUE;
         $sublabel = trim(substr($linestr,12,4));
         
         // REMARK-AUTH subrecord.
         if ($sublabel == "AUTH")
            {
            $contin = trim(substr($linestr,16,2));
            if (is_numeric($contin))
            {
               // on the 2nd, 3rd, etc. line of JRNL-AUTH subrecord entry.
               $rjauth_string .= rtrim(substr($linestr,19,51));
               }
            else
               {
               // on the 1st line of a REMARK1-AUTH subrecord entry.  If a prior
            // $aRJournal entry exists, store this in $aJournals array.
               $rjauth_string = rtrim(substr($linestr,19,51));
               $in_rjauthor_flag = TRUE;
               }
            } // closes if ($sublabel == "AUTH")
         elseif ($in_rjauthor_flag)
            {
            // current line is JRNL but a different subrecord (not AUTH anymore).
            // start converting AUTH string into an array of AUTHOR NAMES.
            $aRJAuthors = preg_split("/,/", trim($rjauth_string), -1, PREG_SPLIT_NO_EMPTY);
            array_walk($aRJAuthors, "trim_element");
            $aRJournal["AUTH"] = $aRJAuthors;
            $in_rjauthor_flag = FALSE;
            }
                     
         // REMARK 1-REFN subrecord.

         // REMARK 1-REFN format A: When citation has not been published.
         if (($sublabel == "REFN") and (strtoupper(substr($linestr,66,4)) == "0353") )
            {
            $inner = array();
            $inner["CODEN"] = "0353";
            $aRJournal["REFN"] = $inner;
            $inner = array();
            }

         // REMARK 1-REFN format B: When citation has been published.
         if (($sublabel == "REFN") and (strtoupper(substr($linestr,66,4)) != "0353") )
            {
            $inner = array();
            $inner["ASTM"] = trim(substr($linestr,24,6));
            $inner["COUNTRY"] = substr($linestr,32,2);
            $inner["CODE_SYS"] = substr($linestr,35,4);
            $inner["CODE"] = trim(substr($linestr,40,25));
            $inner["CODEN"] = trim(substr($linestr,66,4));
            $aRJRefns[] = $inner;
            $in_rjrefn_flag = TRUE;
            }
         }
      elseif ($in_remark1_flag)
         {
         $in_remark1_flag = FALSE;
         }
         
      if ($label == "DBREF")
      {
         $inner = array();
         $inner["ID_CODE"] = trim(substr($linestr,7,4));
         $inner["CHAIN_ID"] = trim(substr($linestr,12,1));
         $inner["SEQ_BEGIN"] = trim(substr($linestr,14,4));
         $inner["INSERT_BEGIN"] = trim(substr($linestr,18,1));
         $inner["SEQ_END"] = trim(substr($linestr,20,4));
         $inner["INSERT_END"] = trim(substr($linestr,24,1));
         $inner["DB_NAME"] = trim(substr($linestr,26,6));
         $inner["DB_ACCESSION"] = trim(substr($linestr,33,8));
         $inner["DB_ID_CODE"] = trim(substr($linestr,42,12));
         $inner["DB_SEQ_BEGIN"] = trim(substr($linestr,55,5));
         $inner["ID_BNS_BEG"] = trim(substr($linestr,60,1));
         $inner["DB_SEQ_END"] = trim(substr($linestr,62,5));
         $inner["DB_INS_END"] = trim(substr($linestr,67,1));
         $aDBRefs[] = $inner;
         }
         
      // SEQADV - SEQADV data field.  Each line is an array, to be added to a larger
      // $aSeqAdvs array.

      if ($label == "SEQADV")
         {
         $inner = array();
         $inner["ID_CODE"] = trim(substr($linestr,7,4));
         $inner["RES_NAME"] = trim(substr($linestr,12,3));
         $inner["CHAIN_ID"] = trim(substr($linestr,16,1));
         $inner["SEQ_NUM"] = trim(substr($linestr,18,4));
         $inner["ICODE"] = trim(substr($linestr,22,1));
         $inner["DATABASE"] = trim(substr($linestr,24,4));
         $inner["DB_ID_CODE"] = trim(substr($linestr,29,9));
         $inner["DB_RES"] = trim(substr($linestr,39,3));
         $inner["DB_SEQ"] = trim(substr($linestr,43,5));
         $inner["CONFLICT"] = trim(substr($linestr,49,21));
         $aSeqAdvs[] = $inner;
         }
         
      // SEQRES - SEQUENCE RESIDUE data fields
      /*
      1         2         3         4         5         6         7
      1234567890123456789012345678901234567890123456789012345678901234567890
      SEQRES   1 A   21  GLY ILE VAL GLU GLN CYS CYS THR SER ILE CYS SER LEU
      SEQRES   2 A   21  TYR GLN LEU GLU ASN TYR CYS ASN
      
      SEQRES
      CHAIN
      LINE
      ( ( (SER_NUM => 1, CHAIN_ID => A, NUM_RES => 21, RES => (ARG, GLY, ...)),
      	 (SER_NUM => 2, CHAIN_ID => A, NUM_RES => 21, RES => (GLY, VAL, ...)) ),
      ( (....),
      (....) ) )
      */
         
      if ($label == "SEQRES")
      {
         $in_seqres_flag = TRUE;
         if ($old_chain == "START")
            {
            $inner = array();
            $inner["SER_NUM"] = trim(substr($linestr,8,2));
            $inner["CHAIN_ID"] = trim(substr($linestr,11,1));
            $inner["NUM_RES"] = trim(substr($linestr,13,4));
            $temp = array();
            for($i = 0; $i < 13; $i++)
               {
               $res = substr($linestr,19+($i*4),3);
               if (strlen(trim($res)) > 0) $temp[] = substr($linestr,19+($i*4),3);
               else break;
               }
            $inner["RES_NAMES"] = $temp;
            $aChain[] = $inner;
            $old_chain = $inner["CHAIN_ID"];
            }
         elseif ($old_chain == substr($linestr,11,1))
         {
            // The current line belongs to the same chain as the previous line.
            // Ergo, simply add the current line to the (old) chain array.
            $inner = array();
            $inner["SER_NUM"] = substr($linestr,8,2);
            $inner["CHAIN_ID"] = substr($linestr,11,1);
            $inner["NUM_RES"] = substr($linestr,13,4);
            $temp = array();
            for($i = 0; $i < 13; $i++)
               {
               $res = substr($linestr,19+($i*4),3);
               if (strlen(trim($res)) > 0) $temp[] = substr($linestr,19+($i*4),3);
               else break;
               }
            $inner["RES_NAMES"] = $temp;
            $aChain[] = $inner;
            }
         elseif ($old_chain != substr($linestr,11,1))
            {
            // Current line belongs to a new chain different from the previous line.
            // Close old chain array, and store current line in a new chain array.
            $aSeqRes[] = $aChain;
            $aChain = array();
            $inner = array();
            $inner["SER_NUM"] = substr($linestr,8,2);
            $inner["CHAIN_ID"] = substr($linestr,11,1);
            $inner["NUM_RES"] = substr($linestr,13,4);
            $temp = array();
            for($i = 0; $i < 13; $i++)
               {
               $res = substr($linestr,19+($i*4),3);
               if (strlen(trim($res)) > 0) $temp[] = substr($linestr,19+($i*4),3);
               else break;
               }
            $inner["RES_NAMES"] = $temp;
            $aChain[] = $inner;
            $old_chain = $inner["CHAIN_ID"];
            }
         }
      elseif ($in_seqres_flag)
      {
         $aSeqRes[] = $aChain;
         $in_seqres_flag = FALSE;
         }
         
      // MODRES - MODIFICATION OF RESIDUE(S) data field.  Treated the same way as SEQADV.
      /*
      MODRES 1ABC ASN A   22A ASN  GLYCOSYLATION SITE
      MODRES 2ABC TTQ A   50A TRP  POST-TRANSLATIONAL MODIFICATION
      MODRES 3ABC DAL A   32  ALA  POST-TRANSLATIONAL MODIFICATION,D-ALANINE
      MODRES 3ABC DAL B   32  ALA  POST-TRANSLATIONAL MODIFICATION,D-ALANINE
      */
      
      if ($label == "MODRES")
         {
         $inner = array();
         $inner["ID_CODE"] = trim(substr($linestr,7,4));
         $inner["RES_NAME"] = trim(substr($linestr,12,3));
         $inner["CHAIN_ID"] = trim(substr($linestr,16,1));
         $inner["SEQ_NUM"] = trim(substr($linestr,18,4));
         $inner["ICODE"] = trim(substr($linestr,22,1));
         $inner["STD_RES"] = trim(substr($linestr,24,3));
         $inner["COMMENT"] = trim(substr($linestr,29,41));
         $aModRes[] = $inner;
         }
         
      /*
      HET - The heterogen section of a PDB file contains the complete description of
      non-standard residues in the entry. Because I'm in doubt about the best way to
      group these data, I will group them on a line-by-line basis. I leave it to the
      users to come up with a more meaningful grouping.
      
      Example:
               1         2         3         4         5         6         7
      1234567890123456789012345678901234567890123456789012345678901234567890
      HET    TRS    975       8
      HET    STA  I   4      25     PART_OF: HIV INHIBITOR;
      
      HET    FUC  Y   1      10     PART_OF: NONOATE COMPLEX; L-FUCOSE
      HET    GAL  Y   2      11     PART_OF: NONOATE COMPLEX
      HET    NAG  Y   3      15     PART_OF: NONOATE COMPLEX
      HET    FUC  Y   4      10     PART_OF: NONOATE COMPLEX
      HET    NON  Y   5      12     PART_OF: NONOATE COMPLEX
      
      HET    UNX  A 161       1     PSEUDO CARBON ATOM OF UNKNOWN LIGAND
      HET    UNX  A 162       1     PSEUDO CARBON ATOM OF UNKNOWN LIGAND
      HET    UNX  A 163       1     PSEUDO CARBON ATOM OF UNKNOWN LIGAND
      */
      
      if ($label == "HET")
         {
         $inner = array();
         $inner["HET_ID"] = trim(substr($linestr,7,3));
         $inner["CHAIN_ID"] = trim(substr($linestr,12,1));
         $inner["SEQ_NUM"] = trim(substr($linestr,13,4));
         $inner["ICODE"] = trim(substr($linestr,17,1));
         $inner["NUM_HET_ATOMS"] = trim(substr($linestr,20,5));
         $inner["TEXT"] = trim(substr($linestr,30,40));
         $aHets[] = $inner;
         }

      /*
      HETNAME - HETEROGENOUS NAME data field - This record gives the chemical
      name of the compound with the given hetID.
      
      Record Format
      		   1         2         3         4         5         6         7
      1234567890123456789012345678901234567890123456789012345678901234567890
      HETNAM     GLC GLUCOSE
      HETNAM     SAD BETA-METHYLENE SELENAZOLE-4-CARBOXAMIDE ADENINE
      HETNAM  2  SAD DINUCLEOTIDE
      
      HETNAM     UNX UNKNOWN ATOM OR ION
      */
      
      if ($label == "HETNAM")
      {
         $in_hetnam_flag = TRUE;
         if (is_numeric(trim(substr($linestr,8,2))))
         // current line is a continuation line.
            $hetnam_string .= rtrim(substr($linestr,15,55)) . " ";
         else
         {
            // we are at a new HETNAME entry, if a previous HETNAM entry exists,
            // add it to our $aHetNams array.
            if (strlen(trim($hetnam_string)) > 0)
               $aHetNams[$het_id] = $hetnam_string;
            $het_id = substr($linestr,11,3);
            $hetnam_string = rtrim(substr($linestr,15,55)) . " ";
            }
         }
      elseif ($in_hetnam_flag)
         {
         $aHetNams[$het_id] = $hetnam_string;
         $in_hetnam_flag = FALSE;
         }
         
      /*
      HETSYN - SYNONYMS for HETEROGENOUS ATOMS data field. Same treatment as HETNAM.
      
      HETSYN     NAD NICOTINAMIDE ADENINE DINUCLEOTIDE
      HETSYN     COA COA
      HETSYN     CMP CYCLIC AMP; CYCLIC ADENOSINE MONOPHOSPHATE

      HETSYN     TRS TRIS BUFFER; TRISAMINE;
      HETSYN   2 TRS TRIS(HYDROXYMETHYL)AMINOMETHANE; TRIMETHYLOL
      HETSYN   3 TRS AMINOMETHANE
      */
      
      if ($label == "HETSYN")
      {
         $in_hetsyn_flag = TRUE;
         if (is_numeric(trim(substr($linestr,8,2))))
         // current line is a continuation line.
            $hetsyn_string .= rtrim(substr($linestr,15,55)) . " ";
         else
         {
            // we are at a new HETSYN entry, if a previous HETSYN entry exists,
            // add it to our $aHetSyns array.
            if (strlen(trim($hetsyn_string)) > 0)
               $aHetSyns[$het_id] = $hetsyn_string;
            $het_id = substr($linestr,11,3);
            $hetsyn_string = rtrim(substr($linestr,15,55)) . " ";
            }
         }
      elseif ($in_hetsyn_flag)
         {
         $aHetSyns[$het_id] = $hetsyn_string;
         $in_hetsyn_flag = FALSE;
         }
         
      /*
      FORMUL - (CHEMICAL) FORMULA data field.  For now, treat this like HETS.
      Continuations are concatenated without an extra whitespace (so user must
      place the extra whitespace himself).

      1         2         3         4         5         6         7
      1234567890123456789012345678901234567890123456789012345678901234567890
      FORMUL   2  SO4    2(O4 S1 2-)
      FORMUL   3  GLC    C6 H12 O6
      FORMUL   3  FOL    2(C19 H17 N7 O6 2-)
      FORMUL   4   CL    2(CL1 1-)
      FORMUL   5   CA    CA1 2+
      FORMUL   6  HOH   *429(H2 O1)
      FORMUL   3  UNX   *3(X1)
      FORMUL   4  HOH   *256(H2 O1)
      FORMUL   1  ACE    C2 H3 O1
      FORMUL   2  ACE    C2 H3 O1
      
      ( (COMP_NUM => 2, HET_ID => SO4, EXCL_MW => *, FORMULA => "256(H2 O1)" ), (....) )
      */
      
      if ($label == "FORMUL")
      {
         $in_formul_flag = TRUE;
         if (is_numeric(trim(substr($linestr,16,2))))
         // current line is a continuation line.
            $formul_string .= rtrim(substr($linestr,19,51));
         else
         {
            // we are at a new FORMUL entry, if a previous FORMUL entry exists,
            // add it to our $aFormul array.
            if (strlen(trim($formul_string)) > 0)
               {
               $inner = array();
               $inner["COMP_NUM"] = $comp_num;
               $inner["HET_ID"] = $het_id;
               $inner["EXCL_MW"] = $excl_mw;
               $inner["FORMULA"] = $formul_string;
               $aFormuls[] = $inner;
               }
            $comp_num = trim(substr($linestr,8,2));
            $het_id = trim(substr($linestr,12,3));
            $excl_mw = trim(substr($linestr,18,1));
            $formul_string = rtrim(substr($linestr,19,51));
            }
         }
      elseif ($in_formul_flag)
         {
         $inner = array();
         $inner["COMP_NUM"] = $comp_num;
         $inner["HET_ID"] = $het_id;
         $inner["EXCL_MW"] = $excl_mw;
         $inner["FORMULA"] = $formul_string;
         $aFormuls[] = $inner;
         $in_formul_flag = FALSE;
         }
         
      if ($label == "HELIX")
         {
         $inner = array();
         $inner["SER_NUM"] = trim(substr($linestr,7,3));
         $inner["HELIX_ID"] = trim(substr($linestr,11,3));
         $inner["INIT_RES_NAME"] = trim(substr($linestr,15,3));
         $inner["INIT_CHAIN_ID"] = trim(substr($linestr,19,1));
         $inner["INIT_SEQ_NUM"] = trim(substr($linestr,21,4));
         $inner["INIT_ICODE"] = trim(substr($linestr,25,1));
         $inner["END_RES_NAME"] = trim(substr($linestr,27,3));
         $inner["END_CHAIN_ID"] = trim(substr($linestr,31,1));
         $inner["END_SEQ_NUM"] = trim(substr($linestr,33,4));
         $inner["END_ICODE"] = trim(substr($linestr,37,1));
         $inner["HELIX_CLASS"] = trim(substr($linestr,38,2));
         $inner["COMMENT"] = trim(substr($linestr,40,30));
         
         // In sample data, this is occupied by the PDB ID (e.g. "1BPT").
         // Clarify this with RGSC (maintainer of PDB).
         $inner["LENGTH"] = trim(substr($linestr,71,5));
         $aHelix[] = $inner;
         }
         
      /*
      SHEET - HELIX SECONDARY STRUCTURE SHEET data field. Treat like HET and HELIX.
      
      Example:
                     1         2         3         4         5         6         7
      1234567890123456789012345678901234567890123456789012345678901234567890
      SHEET    1   A 5 THR A 107  ARG A 110  0
      SHEET    2   A 5 ILE A  96  THR A  99 -1  N  LYS A  98   O  THR A 107
      SHEET    3   A 5 ARG A  87  SER A  91 -1  N  LEU A  89   O  TYR A  97
      SHEET    4   A 5 TRP A  71  ASP A  75 -1  N  ALA A  74   O  ILE A  88
      SHEET    5   A 5 GLY A  52  PHE A  56 -1  N  PHE A  56   O  TRP A  71
      SHEET    1   B 5 THR B 107  ARG B 110  0
      SHEET    2   B 5 ILE B  96  THR B  99 -1  N  LYS B  98   O  THR B 107
      SHEET    3   B 5 ARG B  87  SER B  91 -1  N  LEU B  89   O  TYR B  97
      SHEET    4   B 5 TRP B  71  ASP B  75 -1  N  ALA B  74   O  ILE B  88
      SHEET    5   B 5 GLY B  52  ILE B  55 -1  N  ASP B  54   O  GLU B  73
      */
      
      if ($label == "SHEET")
         {
         $inner = array();
         $inner["STRAND"] = trim(substr($linestr,7,3));
         $inner["SHEET_ID"] = trim(substr($linestr,11,4));
         $inner["NUM_STRANDS"] = trim(substr($linestr,14,2));
         $inner["INIT_RES_NAME"] = trim(substr($linestr,17,3));
         $inner["INIT_CHAIN_ID"] = trim(substr($linestr,21,1));
         $inner["INIT_SEQ_NUM"] = trim(substr($linestr,22,4));
         $inner["INIT_ICODE"] = trim(substr($linestr,26,1));
         $inner["END_RES_NAME"] = trim(substr($linestr,28,3));
         $inner["END_CHAIN_ID"] = trim(substr($linestr,32,1));
         $inner["END_SEQ_NUM"] = trim(substr($linestr,33,4));
         $inner["END_ICODE"] = trim(substr($linestr,37,1));
         $inner["SENSE"] = trim(substr($linestr,38,2));
         $inner["CUR_ATOM"] = trim(substr($linestr,41,4));
         $inner["CUR_RES_NAME"] = trim(substr($linestr,45,3));
         $inner["CUR_CHAIN_ID"] = trim(substr($linestr,49,1));
         $inner["CUR_RES_SEQ"] = trim(substr($linestr,50,4));
         $inner["CUR_ICODE"] = trim(substr($linestr,54,1));
         $inner["PREV_ATOM"] = trim(substr($linestr,56,4));
         $inner["PREV_RES_NAME"] = trim(substr($linestr,60,3));
         $inner["PREV_CHAIN_ID"] = trim(substr($linestr,64,1));
         $inner["PREV_RES_SEQ"] = trim(substr($linestr,65,4));
         $inner["PREV_ICODE"] = trim(substr($linestr,69,1));
         $aSheets[] = $inner;
         }
         
      // TURN data field  - treat like HET, one line = one assoc array in larger array.
         
      if ($label == "TURN")
         {
         $inner = array();
         $inner["SEQ"] = trim(substr($linestr,7,3));
         $inner["TURN_ID"] = trim(substr($linestr,11,3));
         $inner["INIT_RES_NAME"] = trim(substr($linestr,15,3));
         $inner["INIT_CHAIN_ID"] = trim(substr($linestr,19,1));
         $inner["INIT_SEQ_NUM"] = trim(substr($linestr,20,4));
         $inner["INIT_ICODE"] = trim(substr($linestr,24,1));
         $inner["END_RES_NAME"] = trim(substr($linestr,26,3));
         $inner["END_CHAIN_ID"] = trim(substr($linestr,30,1));
         $inner["END_SEQ_NUM"] = trim(substr($linestr,31,4));
         $inner["END_ICODE"] = trim(substr($linestr,35,1));
         $inner["COMMENT"] = trim(substr($linestr,40,30));
         $aTurns[] = $inner;
         }
         
      /*
      SSBOND - The SSBOND record identifies each disulfide bond in protein and
      polypeptide structures by identifying the two residues involved in the bond.
      Treat like HET, one line = one entry (no continuations) = one assoc array in
      a larger $aSSBonds array.
      
      Example:
      1         2         3         4         5         6         7
      123456789012345678901234567890123456789012345678901234567890123456789012
      SSBOND   1 CYS E   48    CYS E   51                          2555
      SSBOND   2 CYS E  252    CYS E  285
      */
      
      if ($label == "SSBOND")
         {
         $inner = array();
         $inner["SER_NUM"] = trim(substr($linestr,7,3));
         $inner["CYS1"] = trim(substr($linestr,11,3));
         $inner["CHAIN_ID1"] = trim(substr($linestr,15,1));
         $inner["SEQ_NUM1"] = trim(substr($linestr,17,4));
         $inner["ICODE1"] = trim(substr($linestr,21,1));
         
         $inner["CYS2"] = trim(substr($linestr,25,3));
         $inner["CHAIN_ID2"] = trim(substr($linestr,29,1));
         $inner["SEQ_NUM2"] = trim(substr($linestr,31,4));
         $inner["ICODE2"] = trim(substr($linestr,35,1));
         $inner["SYM1"] = trim(substr($linestr,59,6));
         $inner["SYM2"] = trim(substr($linestr,66,6));
         $aSSBonds[] = $inner;
         }
         
      /*
      LINK - link data field. Treat like HET.  One line = one entry (no conts) =
      one assoc array, to be added to a larger $aLinks array.
      1         2         3         4         5         6         7
      123456789012345678901234567890123456789012345678901234567890123456789012
      LINK         O1  DDA     1                 C3  DDL     2
      LINK        MN    MN   391                 OE2 GLU   217            2565
      */
         
      if ($label == "LINK")
      {
         $inner= array();
         $inner["ATOM_NAME1"] = trim(substr($linestr,12,4));
         $inner["ALT_LOC1"]   = trim(substr($linestr,16,1));
         $inner["RES_NAME1"]  = trim(substr($linestr,17,3));
         $inner["CHAIN_ID1"]  = trim(substr($linestr,21,1));
         $inner["RES_SEQ1"]   = trim(substr($linestr,22,4));
         $inner["ICODE1"]     = trim(substr($linestr,26,1));
         $inner["ATOM_NAME2"] = trim(substr($linestr,42,4));
         $inner["ALT_LOC2"]   = trim(substr($linestr,46,1));
         $inner["RES_NAME2"]  = trim(substr($linestr,47,3));
         $inner["CHAIN_ID2"]  = trim(substr($linestr,51,1));
         $inner["RES_SEQ2"]   = trim(substr($linestr,52,4));
         $inner["ICODE2"]     = trim(substr($linestr,56,1));
         $inner["SYM1"]       = trim(substr($linestr,59,6));
         $inner["SYM2"]       = trim(substr($linestr,66,6));
         $aLinks[] = $inner;
         }
         
      if ($label == "HYDBND")
      {
         $inner= array();
         $inner["ATOM_NAME1"] = trim(substr($linestr,12,4));
         $inner["ALT_LOC1"]   = trim(substr($linestr,16,1));
         $inner["RES_NAME1"]  = trim(substr($linestr,17,3));
         $inner["CHAIN1"]     = trim(substr($linestr,21,1));
         $inner["RES_SEQ1"]   = trim(substr($linestr,22,5));
         $inner["ICODE1"]     = trim(substr($linestr,27,1));
         $inner["NAME_H"]     = trim(substr($linestr,29,4));
         $inner["ALT_LOC_H"]  = trim(substr($linestr,33,1));
         $inner["CHAIN_H"]    = trim(substr($linestr,35,1));
         $inner["RES_SEQ_H"]  = trim(substr($linestr,36,5));
         $inner["ICODE_H"]    = trim(substr($linestr,41,1));
         $inner["ATOM_NAME2"] = trim(substr($linestr,43,4));
         $inner["ALT_LOC2"]   = trim(substr($linestr,47,1));
         $inner["RES_NAME2"]  = trim(substr($linestr,48,3));
         $inner["CHAIN_ID2"]  = trim(substr($linestr,52,1));
         $inner["RES_SEQ2"]   = trim(substr($linestr,53,5));
         $inner["ICODE2"]     = trim(substr($linestr,58,1));
         $inner["SYM1"]       = trim(substr($linestr,59,6));
         $inner["SYM2"]       = trim(substr($linestr,66,6));
         $aHydBnds[] = $inner;
         }
         
      // SLTBRG - Saltbridges between residues data field. Treat like HET.
         
      if ($label == "SLTBRG")
      {
         $inner = array();
         $inner["ATOM1"]     = trim(substr($linestr,12,4));
         $inner["ALT_LOC1"]  = trim(substr($linestr,16,1));
         $inner["RES_NAME1"] = trim(substr($linestr,17,3));
         $inner["CHAIN_ID1"] = trim(substr($linestr,21,1));
         $inner["RES_SEQ1"]  = trim(substr($linestr,22,4));
         $inner["ICODE1"]    = trim(substr($linestr,26,1));
         $inner["ATOM2"]     = trim(substr($linestr,42,4));
         $inner["ALT_LOC2"]  = trim(substr($linestr,46,1));
         $inner["RES_NAME2"] = trim(substr($linestr,47,3));
         $inner["CHAIN_ID2"] = trim(substr($linestr,51,1));
         $inner["RES_SEQ2"]  = trim(substr($linestr,52,4));
         $inner["ICODE2"]    = trim(substr($linestr,56,1));
         $inner["SYM1"]      = trim(substr($linestr,59,6));
         $inner["SYM2"]      = trim(substr($linestr,66,6));
         $aSltBrgs[] = $inner;
         }

      // CISPEP - CIS PEPTIDE data field.  Treat like HET.
   
      if ($label == "CISPEP")
      {
         $inner = array();
         $inner["SER_NUM"]   = trim(substr($linestr,7,3));
         $inner["PEP1"]      = trim(substr($linestr,11,3));
         $inner["CHAIN_ID1"] = trim(substr($linestr,15,1));
         $inner["SEQ_NUM1"]  = trim(substr($linestr,17,4));
         $inner["ICODE1"]    = trim(substr($linestr,21,1));
         $inner["PEP2"]      = trim(substr($linestr,25,3));
         $inner["CHAIN_ID2"] = trim(substr($linestr,29,1));
         $inner["SEQ_NUM2"]  = trim(substr($linestr,31,4));
         $inner["ICODE2"]    = trim(substr($linestr,35,1));
         $inner["MOD_NUM"]   = trim(substr($linestr,43,3));
         $inner["MEASURE"]   = (float) (substr($linestr,53,6));
         $aCisPeps[] = $inner;
         }
         
      if ($label == "SITE")
      {
         $inner = array();
         $inner["SEQ_NUM"]   = trim(substr($linestr,7,3));
         $inner["SITE_ID"]   = trim(substr($linestr,11,3));
         $inner["NUM_RES"]   = trim(substr($linestr,15,2));
         $inner["RES_NAME1"] = trim(substr($linestr,18,3));
         $inner["CHAIN_ID1"] = trim(substr($linestr,22,1));
         $inner["SEQ1"]      = trim(substr($linestr,23,4));
         $inner["ICODE1"]    = trim(substr($linestr,27,1));
         $inner["RES_NAME2"] = trim(substr($linestr,29,3));
         $inner["CHAIN_ID2"] = trim(substr($linestr,33,1));
         $inner["SEQ2"]      = trim(substr($linestr,34,4));
         $inner["ICODE2"]    = trim(substr($linestr,38,1));
         $inner["RES_NAME3"] = trim(substr($linestr,40,3));
         $inner["CHAIN_ID3"] = trim(substr($linestr,44,1));
         $inner["SEQ3"]      = trim(substr($linestr,45,4));
         $inner["ICODE3"]    = trim(substr($linestr,49,1));
         $inner["RES_NAME4"] = trim(substr($linestr,51,3));
         $inner["CHAIN_ID4"] = trim(substr($linestr,55,1));
         $inner["SEQ4"]      = trim(substr($linestr,56,4));
         $inner["ICODE4"]    = trim(substr($linestr,60,1));
         $aSites[] = $inner;
         }
         
      /* CRYST1 data field - treat like HET.

      Example:
      1         2         3         4         5         6         7
      1234567890123456789012345678901234567890123456789012345678901234567890
      CRYST1   52.000   58.600   61.900  90.00  90.00  90.00 P 21 21 21    8
      CRYST1    1.000    1.000    1.000  90.00  90.00  90.00 P 1           1
      CRYST1   42.544   69.085   50.950  90.00  95.55  90.00 P 1 21 1      2
      */
      
      if ($label == "CRYST1")
         {
         $inner = array();
         $inner["A"] = (float) (substr($linestr,6,9));
         $inner["B"] = (float) (substr($linestr,15,9));
         $inner["C"] = (float) (substr($linestr,24,9));
         $inner["ALPHA"] = (float) (substr($linestr,33,7));
         $inner["BETA"] = (float) (substr($linestr,40,7));
         $inner["GAMMA"] = (float) (substr($linestr,47,7));
         $inner["S_GROUP"] = trim(substr($linestr,55,11));
         $inner["Z"] = (int) (substr($linestr,66,4));
         $aCryst1[] = $inner;
         }
         
      // ORIGX data field - for now, assume that there is exactly one ORIGX1,
      // one ORIGX2, and one ORIGX3 entry appearing in that order in a PDB file.
         
      if (($label == "ORIGX1") or ($label == "ORIGX2") or ($label == "ORIGX3"))
      {
         $inner = array();
         $inner["ON1"] = (float) (substr($linestr,10,10));
         $inner["ON2"] = (float) (substr($linestr,20,10));
         $inner["ON3"] = (float) (substr($linestr,30,10));
         $inner["TN"] = (float) (substr($linestr,45,10));
         $aOrigxs[] = $inner;
         }
         
      // SCALE data field - treat like ORIGX data field. Same assumptions about
      // ordering of field labels ending with 1, 2, 3.
      
      if (($label == "SCALE1") or ($label == "SCALE2") or ($label == "SCALE3"))
      {
         $inner = array();
         $inner["SN1"] = (float) (substr($linestr,10,10));
         $inner["SN2"] = (float) (substr($linestr,20,10));
         $inner["SN3"] = (float) (substr($linestr,30,10));
         $inner["UN"] = (float) (substr($linestr,45,10));
         $aScales[] = $inner;
         }
         
      // MTRIX data field - treat like ORIGXn and SCALEn data fields.
      // QUESTION: Ask if the negative sign before a 0.00 entry should be retained.
      // As of now, it gets lost during the casting (conversion) to float.
      
      if (($label == "MTRIX1") or ($label == "MTRIX2") or ($label == "MTRIX3"))
      {
         $inner = array();
         $inner["SERIAL"] = (int) (substr($linestr,8,3));
         $inner["MN1"] = (float) (substr($linestr,10,10));
         $inner["MN2"] = (float) (substr($linestr,20,10));
         $inner["MN3"] = (float) (substr($linestr,30,10));
         $inner["VN"] = (float) (substr($linestr,45,10));
         $inner["I_GIVEN"] = (int) (substr($linestr,59,1));
         $aMatrices[] = $inner;
         }
         
      // TVECT - TRANSLATION VECTOR data fields. Treat like HET.
      
      if ($label == "TVECT")
         {
         $inner = array();
         $inner["SERIAL"] = (int) (substr($linestr,7,3));
         $inner["T1"] = (float) (substr($linestr,10,10));
         $inner["T2"] = (float) (substr($linestr,20,10));
         $inner["T3"] = (float) (substr($linestr,30,10));
         $inner["TEXT"] = substr($linestr,40,30);
         $aTvects[] = $inner;
         }
         
      // MODEL - (ATOMIC) MODEL data field.  Skip this for now.
      
      // ATOM - ATOM data field.  Treat like HET.  Data can be found in
      // positions 73 upwards (in pre-1996 files, these contain the PDB
      // ID, e.g. "1BPT 107").
      
      if ($label == "ATOM")
      {
         $inner = array();
         $inner["SERIAL"]      = (int) (substr($linestr,6,5));
         $inner["NAME"]        = trim(substr($linestr,12,4));
         $inner["ALT_LOC"]     = trim(substr($linestr,16,1));
         $inner["RES_NAME"]    = trim(substr($linestr,17,3));
         $inner["CHAIN_ID"]    = trim(substr($linestr,21,1));
         $inner["RES_SEQ"]     = (int) (substr($linestr,22,4));
         $inner["ICODE"]       = trim(substr($linestr,26,1));
         $inner["X"]           = (float) (substr($linestr,30,8));
         $inner["Y"]           = (float) (substr($linestr,38,8));
         $inner["Z"]           = (float) (substr($linestr,46,8));
         $inner["OCCUPANCY"]   = (float) (substr($linestr,54,6));
         $inner["TEMP_FACTOR"] = (float) (substr($linestr,60,6));
         $inner["SEG_ID"]      = trim(substr($linestr,72,4));
         $inner["ELEMENT"]     = trim(substr($linestr,76,2));
         $inner["CHARGE"]      = trim(substr($linestr,78,2));
         $aAtoms[] = $inner;
         $atom_ctr++;
         }
         
      // SIGATM - presents the STANDARD DEVIATION OF ATOMIC PARAMETERS.
      // Treat like HET.
      
      if ($label == "SIGATM")
      {
         $inner = array();
         $inner["SERIAL"]   = (int) (substr($linestr,6,5));
         $inner["NAME"]     = trim(substr($linestr,12,4));
         $inner["ALT_LOC"]  = trim(substr($linestr,16,1));
         $inner["RES_NAME"] = trim(substr($linestr,17,3));
         $inner["CHAIN_ID"] = trim(substr($linestr,21,1));
         $inner["RES_SEQ"]  = (int) (substr($linestr,22,4));
         $inner["ICODE"]    = trim(substr($linestr,26,1));
         $inner["SIG_X"]    = (float) (substr($linestr,30,8));
         $inner["SIG_Y"]    = (float) (substr($linestr,38,8));
         $inner["SIG_Z"]    = (float) (substr($linestr,46,8));
         $inner["SIG_OCC"]  = (float) (substr($linestr,54,6));
         $inner["SIG_TEMP"] = (float) (substr($linestr,60,6));
         $inner["SEG_ID"]   = trim(substr($linestr,72,4));
         $inner["ELEMENT"]  = trim(substr($linestr,76,2));
         $inner["CHARGE"]   = trim(substr($linestr,78,2));
         $aSigAtms[$atom_ctr-1] = $inner;
         }
                     
      if ($label == "END")
      {
         break;
         }
      }

   $oProt = new Protein_PDB();
   $oProt->class = $class;
   $oProt->dep_date = $dep_date;
   $oProt->id_code = $id_code;
   $oProt->date_rep = $date_rep;
   // I didn't make use of the $id_code data field obtained from
   // the OBSLTE field, on the assumption that it's the same as
   // the $id_code of the HEADER field.
   $oProt->new_id_code = $aNew_ids;
   $oProt->titles = $aTitles;
   $oProt->caveats = $aCaveats;
   $oProt->compounds = $aCompounds;	
   $oProt->sources = $aSources;
   $oProt->keywords = $aKeywords;
   $oProt->expdta = $aExpdta;
   $oProt->authors = $aAuthors;
   
   $oProt->revdat = $aRevdats;
   $oProt->sprsde = $aSprsdes;
   $oProt->journal = $aJournals;
   $oProt->remark1 = $aRJournals;

   /*
   $oProt->remark = $aRemarks;
   $oProt->remark2 = $aRemarks2;
   $oProt->remark3 = $aRemarks3;
   $oProt->remark4 = $aRemarks4;
   */

   $oProt->dbrefs = $aDBRefs;
   $oProt->seqadv = $aSeqAdvs;
   $oProt->seqres = $aSeqRes;
   $oProt->modres = $aModRes;
   $oProt->hets = $aHets;
   $oProt->hetnams = $aHetNams;
   $oProt->hetsyns = $aHetSyns;
   $oProt->het_formulas = $aFormuls;
   $oProt->helix = $aHelix;
   $oProt->sheets = $aSheets;
   $oProt->turns = $aTurns;
   $oProt->ssbonds = $aSSBonds;
   $oProt->links = $aLinks;
   $oProt->hydbnds = $aHydBnds;
   $oProt->sltbrgs = $aSltBrgs;
   $oProt->cispeps = $aCisPeps;
   $oProt->sites = $aSites;
   $oProt->cryst1 = $aCryst1;
   $oProt->origx = $aOrigxs;
   $oProt->scale = $aScales;
   $oProt->matrix = $aMatrices;
   $oProt->tvect = $aTvects;
   $oProt->atoms = $aAtoms;
   $oProt->sigatms = $aSigAtms;
   
   return $oProt;
   }
?>
