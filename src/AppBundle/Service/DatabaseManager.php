<?php
/**
 * Database Managing
 * @author Amélie DUVERNET
 * Inspired by BioPHP's project biophp.org
 * Created 11 february 2019
 * Last modified 14 february 2019
 */
namespace AppBundle\Service;

use AppBundle\Entity\Database;
use AppBundle\Entity\Sequence;
use AppBundle\Traits\FormatsTrait;

class DatabaseManager
{

    private $database;


    public function __construct(Database $database) {
        $this->database = $database;
    }
    /**
     * We need the functions bof() and eof() to determine if we've reached the end of
     * file or not.
     * Two ways of doing this: 1) examine value of seqptr, or 2) maintain boolean variables eof and bof
     * first() positions the sequence pointer (i.e. the seqptr property of a Seq object) to 
     * the first sequence in a database (SeqDB object).
     */
    public function first()
    {
        $this->database->setSeqptr(0);
    }


    /**
     * Positions the sequence pointer (i.e. the seqptr property of a Seq object) to 
     * the last sequence in a database (SeqDB object).
     */
    public function last()
    {
        $this->database->setSeqptr($this->database->getSeqcount() - 1);
    }


    /**
     * (short for previous) positions the sequence pointer (i.e. the seqptr property of
     * a Seq object) to the sequence that comes before the current sequence.  
     */
    public function prev()
    {
        if($this->database->getSeqptr() > 0) {
            $this->database->setSeqptr($this->database->getSeqptr()-1);
        } else {
            $this->database->setBof(true);
        }
    }

    /**
     * Positions the sequence pointer (i.e. the seqptr property of a Seq object) to the
     * sequence that comes after the current sequence.
     */
    public function next()
    {
        if($this->database->getSeqptr() < ($this->database->getSeqcount()-1)) {
            $this->database->setSeqptr($this->database->getSeqptr()+1);
        } else {
            $this->database->setEof(true);
        }
    }


    /**
     * Retrieves all data from the specified sequence record and returns them in the 
     * form of a Seq object.  This method invokes one of several parser methods.
     * @return      Sequence    $oMySequence
     * @todo Dependency injection for seqdb : bsrch_tabfile
     */
    public function fetch()
    {
        if ($this->database->getDataFn() == ""){
            throw new \Exception("Cannot invoke fetch() method from a closed object.");
        }
        @$seqid = func_get_arg(0);

        // IDX and DIR files remain open for the duration of the FETCH() method.
        $fp = fopen($this->database->getDataFn(), "r");
        $fpdir = fopen($this->database->getDirFn(), "r");

        if ($seqid) {
            $idx_r = bsrch_tabfile($fp, 0, $seqid);
            if (!$idx_r) {
                return false;
            } else {
                $this->database->setSeqptr($idx_r[3]);
            }
        } else {
            // For now, SEQPTR determines CURRENT SEQUENCE ID.  Alternative is to track curr line.
            fseekline($fp, $this->database->getSeqptr());
            $idx_r = preg_split("/\s+/", trim(fgets($fp, 81)));
        }
        $dir_r = bsrch_tabfile($fpdir, 0, $idx_r[1]);
        $fpseq = fopen($dir_r[1], "r");
        fseekline($fpseq, $idx_r[2]);
        $flines = line2r($fpseq);

        if ($this->databse->getDbformat() == "GENBANK") {
            $oMySequence = $this->parse_id($flines);
        } elseif ($this->databse->getDbformat() == "SWISSPROT") {
            $oMySequence = $this->parse_swissprot($flines);
        }

        fclose($fp);
        fclose($fpdir);
        fclose($fpseq);

        return $oMySequence;
    }


    /**
     * Parses a Swissprot data file and returns a Seq object containing parsed data.
     * @param   type        $flines
     * @return  Sequence    $oSequence
     */
    public function parse_swissprot($flines)
    {
        $accession  = [];
        $date_r     = [];
        $desc       = "";
        $desc_lnctr = 0;
        $gename_r   = [];
        $os_r       = [];
        $os_linectr = 0;
        $os_str     = "";
        $oc_linectr = 0;
        $oc_str     = "";
        $ref_r      = [];
        $ra_r       = [];
        $ra_ctr     = 0;
        $ra_str     = "";
        $rl_ctr     = 0;
        $rl_str     = "";
        $db_r       = [];
        $ft_r       = [];
        $kw_str     = "";
        $kw_r       = [];

        while (list($no, $linestr) = each($flines)) {
            $linelabel = left($linestr, 2);
            $linedata = trim(substr($linestr, 5));
            $lineend = right($linedata, 1);
            if (left($linestr, 2) == "ID") {
                $words          = preg_split("/;/", substr($linestr, 5));
                $endc           = preg_split("/\s/", $words[0]);
                $entry_name     = $endc[0];
                $namesrc        = preg_split("/_/", $entry_name);  
                $protein_name   = $namesrc[0];
                $protein_source = $namesrc[1]; 
                $data_class     = $endc[1]; 
                $moltype        = $words[1];
                $length         = (int) substr($words[2], 0, strlen($words[2])-4);
            }
            if (left($linestr, 2) == "AC") {
                $accstr = $linedata;
                $accstr = substr($accstr, 0, strlen($accstr)-1);
                $accline = preg_split("/;/", intrim($accstr));
                $accession = array_merge($accession, $accline);
            }
            if (left($linestr, 2) == "DT") {
                $datestr = $linedata;
                $datestr = substr($datestr, 0, strlen($datestr)-1);
                $words = preg_split("/\(/", $datestr);
                $firstcomma = strpos($words[1], ",");
                $comment = trim(substr($words[1], $firstcomma+1));

                switch($comment) {
                    case "CREATED":
                        // this DT line is a DATE CREATED line.
                        $create_date = substr($words[0], 0, 11); 
                        $create_rel = substr($words[1], 5, ($firstcomma-5));
                        $date_r[$comment] = array($create_date, $create_rel); 
                        break;
                    case "LAST SEQUENCE UPDATE":
                        $sequpd_date = substr($words[0], 0, 11);
                        $sequpd_rel = substr($words[1], 5, ($firstcomma-5));
                        $date_r[$comment] = array($sequpd_date, $sequpd_rel);
                        break;
                    case "LAST ANNOTATION UPDATE":
                        $notupd_date = substr($words[0], 0, 11);
                        $notupd_rel = substr($words[1], 5, ($firstcomma-5));
                        $date_r[$comment] = array($notupd_date, $notupd_rel);
                        break;
                    default:
                        // For now, we do not check vs. duplicate comments.
                        // We just overwrite the older comment with new one.
                        $other_comment = $comment; 
                        $other_date = substr($words[0], 0, 11);
                        $other_rel = substr($words[1], 5, ($firstcomma-5));
                        $date_r[$comment] = array($other_date, $other_rel);
                        break;
                }
            }  
            if (left($linestr, 2) == "DE") {
                $desc_lnctr++;
                $linestr = $linedata;
                if ($desc_lnctr == 1) {
                    $desc .= $linestr;
                } else {
                    $desc .= " " . $linestr;
                }

                // Checks if (FRAGMENT) or (FRAGMENTS) is found at the end
                // of the DE line to determine if sequence is complete.
                if (right($linestr, 1) == ".") {
                    if ((strtoupper(right($linestr, 11)) == "(FRAGMENT).") 
                            && (strtoupper(right($linestr, 12)) == "(FRAGMENTS).")) {
                        $is_fragment = true;
                    } else {
                        $is_fragment = false;
                    }
                }
            }
            if ($linelabel == "KW") {
                $kw_str .= $linedata;
                if ($lineend == ".") {
                    $kw_str = rem_right($kw_str);
                    $kw_r = preg_split("/;/", $kw_str);
                    array_walk($kw_r, "trim_element");
                    $kw_str = "";
                }
            }
            if ($linelabel == "OS") {
                $os_linectr++;
                if ($lineend != ".") {
                    if ($os_linectr == 1) {
                        $os_str .= $linedata;
                    } else {
                        $os_str .= " $linedata";
                    }
                } else {
                    $os_str .= " $linedata";
                    $os_str = rem_right($os_str);
                    $os_line = preg_split("/\, AND /", $os_str);
                }
            }
            if ($linelabel == "OG") {
                $organelle = rem_right($linedata);
            }
            if ($linelabel == "OC") {
                $oc_linectr++;
                if ($lineend != ".") {
                    if ($oc_linectr == 1) {
                        $oc_str .= $linedata;
                    } else {
                        $oc_str .= " $linedata";
                    }
                } else {
                    $oc_str .= " $linedata";
                    $oc_str = rem_right($oc_str);
                    $oc_line = preg_split("/;/", $oc_str);
                    array_walk($oc_line, "trim_element");
                }
            }
            if ($linelabel == "FT") {
                $ft_key = trim(substr($linestr, 5, 8));
                $ft_from = (int) trim(substr($linestr, 14, 6));
                $ft_to = (int) trim(substr($linestr, 21, 6));
                $ft_desc = rem_right(trim(substr($linestr, 34)));
                $ft_r[] = array($ft_key, $ft_from, $ft_to, $ft_desc);
            }
            // ( rn => ( "rp" => "my rp", "rc" => ("tok1" => "value", ...) ) )
            // ( 10 => ( "RP" => "my rp", "RC" => ("PLASMID" => "PLA_VAL", ... ) ) )
            // Example: DR AARHUS/GHENT-2DPAGE; 8006; IEF.
            if ($linelabel == "DR") {
                // DR DATA_BANK_IDENTIFIER; PRIMARY_IDENTIFIER; SECONDARY_IDENTIFIER
                // We assume that all three data items are mandatory/present in all DR entries.
                // ( refno => ( (dbname1, pid1, sid1), (dbname2, pid2, sid2), ... ), 1 => ( ... ) )
                // ( 0 => ( (REBASE, pid1, sid1), (WORPEP, pid2, sid2), ... ), 1 => ( ... ) )
                $linedata = rem_right($linedata);
                $dr_line = preg_split("/;/", $linedata);
                array_walk($dr_line, "trim_element");
                $db_name = $dr_line[0];
                $db_pid = $dr_line[1];
                $db_sid = $dr_line[2];
                $db_r[] = [$db_name, $db_pid, $db_sid];
            }
            if ($linelabel == "RN") {
                // Remove the [ and ] between the reference number.
                $refno = substr(rem_right($linedata), 1);

                $rc_ctr = 0;
                $rc_str = "";
                $rc_flag = false;
                $inner_r = [];
                
                while (list($no, $linestr) = each($flines)) {
                    $linelabel = left($linestr, 2);
                    $linedata = trim(substr($linestr, 5));
                    $lineend = right($linedata, 1);
                    if ($linelabel == "RP") {
                        $inner_r["RP"] = $linedata;
                    } elseif ($linelabel == "RC") {
                        $rc_str .= $linedata;
                        while (list($no, $linestr) = each($flines)) {
                            $linelabel = left($linestr, 2);
                            $linedata = trim(substr($linestr, 5));
                            $lineend = right($linedata, 1);
                            if ($linelabel == "RC") {
                                $rc_str .= " $linedata";  
                            } else {
                                prev($flines);
                                break;
                            }
                        }
                        // we remove the last character if it is ";"
                        $rc_str = trim($rc_str);
                        if (right($rc_str,1) == ";") {
                            $rc_str = rem_right($rc_str);
                        }
                        $rc_line = preg_split("/;/", trim($rc_str));
                        array_walk($rc_line, "trim_element");
                        $innermost = array();
                        foreach($rc_line as $tokval_str) {
                            // here we assume that there is no whitespace
                            // before or after (left or right of) the "=".
                            $tokval_r = preg_split("/=/", $tokval_str);
                            $token = $tokval_r[0];
                            $value = $tokval_r[1];
                            $innermost[$token] = $value;
                        }
                        $inner_r["RC"] = $innermost; 
                    } elseif ($linelabel == "RM") { // We have no idea what RM is about, so we assume it's a single-line entry.
                        // which may occur 0 to 1 times inside a SWISSPROT SEQUENCE RECORD.
                        $inner_r["RM"] = $linedata;
                    } elseif ($linelabel == "RX") {
                        $linedata = rem_right($linedata);
                        $rx_line = preg_split("/;/", intrim($linedata));
                        $inner_r["RX_BDN"] = $rx_line[0];
                        $inner_r["RX_ID"] = $rx_line[1];
                    } elseif ($linelabel == "RA") {
                        $ra_ctr++;
                        if ($ra_ctr == 1) {
                            $ra_str = $linedata;
                        } else {
                            $ra_str .= " $linedata";
                        }
                        if ($lineend == ";") {
                            $ra_str = rem_right($ra_str);
                            $ra_r = preg_split("/\,/", $ra_str);
                            array_walk($ra_r, "trim_element");
                            $inner_r["RA"] = $ra_r;
                        }
                    } elseif ($linelabel == "RL") {
                        $rl_ctr++;
                        if ($rl_ctr == 1) {
                            $rl_str = $linedata;
                        } else {
                            $rl_str .= " $linedata";
                        }
                    } else {
                        $inner_r["RL"] = $rl_str;
                        prev($flines);
                        break;
                    }
                } // CLOSES 2nd WHILE
                $ref_r[$refno-1] = $inner_r;
                $ra_str = "";
                $ra_ctr = 0;
                $rl_str = "";
                $rl_ctr = 0;
            } if (left($linestr, 2) == "GN") {
                // GN is always exactly one line.
                // GNAME1 OR GNAME2               ( (GNAME1, GNAME2) )
                // GNAME1 AND GNAME2              ( (GNAME1), (GNAME2) )
                // GNAME1 AND (GNAME2 OR GNAME3)  ( (GNAME1), (GNAME2, GNAME3) )
                // GNAME1 OR (GNAME2 AND GNAME3)  NOT POSSIBLE!!!

                /* ALGORITHM:
                1) Split expressions by " AND ".
                2) Test each "token" if in between parentheses or not.
                3) If not, then token is a singleton, else it's a multiple-ton.
                4) Singletons are translated into (GNAME1).
                   Multiple-tons are translated into (GNAME1, GNAME 2).
                5) Push gene name array into larger array. Go to next token.
                */

                // Remove "GN " at the beginning of our line.
                $linestr = trim(substr($linestr, 5));
                // Remove the last character which is always a period.
                $linestr = substr($linestr, 0, strlen($linestr)-1);

                // Go here if you detect at least one ( or ). 
                if (!(strpos($linestr, "("))) { // GN Line does not contain any parentheses.
                    // Ergo, it is made up of all OR's or AND's but not both.
                    if (strpos($linestr, " OR ")) {
                        // Case 1: GNAME1 OR GNAME2.
                        $temp = preg_split("/ OR /", $linestr);
                        $gename_r[] = $temp;
                    } elseif (strpos($linestr, " AND ")) {
                        // Case 2: GNAME1 AND GNAME2 AND GNAME3.
                        $temp = preg_split("/ AND /", $linestr);
                        foreach($temp as $gene) {
                            $gename_r[] = array($gene);
                        }
                    } else {
                        $gename_r[] = array($linestr);
                    }
                    // Case 0: GN GENENAME1. One gene name (no OR, AND).
                } else { 
                    // GN Line contains at least one pair of parentheses.
                    // Case 3: GNAME1 AND (GNAME2 OR GNAME3) => ( (GNAME1), (GNAME2, GNAME3) )
                    // COMMENTS # 1 below.
                    $temp = preg_split("/ AND /", $linestr);
                    foreach($temp as $gene) {
                        if (substr($gene, 0, 1) == "(") { // a list of 2 or more gene names OR'ed together
                            // remove the "(" and ")" at both ends of the string.
                            $gene = substr($gene, 1);
                            $gene = substr($gene, 0, strlen($gene)-1);
                            $genelist = preg_split("/ OR /", $gene);
                            $gename_r[] = $genelist;
                        } else { // singleton
                            $gename_r[] = array($gene);
                        }
                    }
                }
            }
            // 0123456789012345678901234567890123456789
            // SQ   SEQUENCE XXXX AA; XXXXX MW; XXXXX CN;
            if ($linelabel == "SQ") {
                $linedata = rem_right($linedata);
                // XXXX AA, XXXX MW, XXXX CN
                $words = preg_split("/;/", substr($linedata, 8));
                $aa = preg_split("/\s+/", trim($words[0]));
                $aa_count = (int) trim($aa[0]);
                $mw = preg_split("/\s+/", trim($words[1]));
                $mol_wt = (int) trim($mw[0]);
                $cn = preg_split("/\s+/", trim($words[2]));
                $chk_no = trim($cn[0]);
                $chk_method = trim($cn[1]); 
                $sequence = "";
                while (list($no, $linestr) = each($flines)) {
                    $linelabel = left($linestr, 2);
                    if ($linelabel == "//") {
                        break;
                    }
                    $linedata = intrim(trim($linestr));
                    $sequence .= $linedata;
                }
            }
        }

        $oSequence = new Sequence();
        $oSequence->setId($protein_name);
        $oSequence->setSeqlength($length);
        $oSequence->setMoltype($moltype);
        $oSequence->setDate($create_date);
        $oSequence->setAccession($accession[0]);
        array_shift($accession);
        $oSequence->setSecAccession($accession);
        $oSequence->setSource($os_line);
        $oSequence->setOrganism($oc_line);
        $oSequence->setSequence($sequence);
        $oSequence->setDefinition($desc);
        $oSequence->setKeywords($kw_r);

        $genbank_ref_r = [];
        $inner_r = [];
        foreach($ref_r as $key => $value) {
            $inner_r["REFNO"]   = $key;
            $db_id              = $value["RX_BDN"];
            $inner_r[$db_id]    = $value["RX_ID"];
            $inner_r["REMARKS"] = $value["RP"];
            $inner_r["COMMENT"] = $value["RC"];
            $inner_r["TITLE"]   = $value["RL"];
            $inner_r["JOURNAL"] = $value["RL"];
            $inner_r["AUTHORS"] = $value["RA"];
            $genbank_ref_r[]    = $inner_r;
        }
        $oSequence->setReference($genbank_ref_r);

        $aSwiss = [];
        $aSwiss["ID"]            = $protein_name;
        $aSwiss["PROT_NAME"]     = $protein_name;
        $aSwiss["MOL_TYPE"]      = $moltype;
        $aSwiss["PROT_SOURCE"]   = $protein_source;
        $aSwiss["DATA_CLASS"]    = $data_class;
        $aSwiss["LENGTH"]        = $length;
        $aSwiss["CREATE_DATE"]   = $create_date;
        $aSwiss["CREATE_REL"]    = $create_rel;
        $aSwiss["SEQUPD_DATE"]   = $sequpd_date;
        $aSwiss["SEQUPD_REL"]    = $sequpd_rel;
        $aSwiss["NOTUPD_DATE"]   = $notupd_date;
        $aSwiss["NOTUPD_REL"]    = $notupd_rel;
        // ACCESSION is an ARRAY.
        $aSwiss["ACCESSION"]     = $accession;
        $aSwiss["PRIM_AC"]       = $accession[0];
        $aSwiss["DESC"]          = $desc;
        $aSwiss["IS_FRAGMENT"]   = $is_fragment;
        // KEYWORDS is an ARRAY.
        $aSwiss["KEYWORDS"]      = $kw_r;
        // ORGANISM is an ARRAY.
        $aSwiss["ORGANISM"]      = $os_line;
        $aSwiss["ORGANELLE"]     = $organelle;
        // FT_<keyword> is an ARRAY.
        process_ft($aSwiss, $ft_r);

        $aSwiss["AMINO_COUNT"]   = $aa_count;
        $aSwiss["MOLWT"]         = $mol_wt;
        $aSwiss["CHK_NO"]        = $chk_no;
        $aSwiss["CHK_METHOD"]    = $chk_method;
        $aSwiss["SEQUENCE"]      = $sequence;
        // GENE_NAME is an ARRAY.
        $aSwiss["GENE_NAME"]     = $gename_r;
        // ORG_CLASS is an ARRAY.
        $aSwiss["ORG_CLASS"]     = $oc_line;
        // REFERENCE is an ARRAY.
        $aSwiss["REFERENCE"]     = $ref_r;

        $oSequence->setSwissprot($aSwiss);
        return $oSequence;
    }


    /**
     * Parses a GenBank data file and returns a Seq object containing parsed data.
     * @param   type        $flines
     * @return  Sequence    $oSequence
     */
    public function parse_id($flines)
    {
        $seqarr         = [];
        $inseq_flag     = false;
        $seqdata_flag   = false;
        $accession_flag = false;
        $ref_array      = [];
        $feature_array  = [];
        $entry_ctr      = 0;
        $ref_ctr        = 0;
        $maxlength      = 0;
        $minlength      = 999999;
        $tot_seqlength  = 0;

        while(list($lineno, $linestr) = each($flines)) {
            if (substr($linestr,0,5) == "LOCUS") {
                $entry_ctr++;
                $ref_ctr = 0;
                $ref_array = array();

                // This is the beginning of a SEQUENCE ENTRY.
                $seqdata = "";

                $oSequence = new Sequence();
                $oSequence->setId(trim(substr($linestr, 12, 16)));
                $oSequence->setSeqlength(trim(substr($linestr, 29, 11)) * 1);
                $tot_seqlength += $oSequence->getSeqlength();

                if ($oSequence->getSeqlength() > $maxlength) {
                    $maxlength = $oSequence->getSeqlength();
                }
                if ($oSequence->getSeqlength() < $minlength) {
                    $minlength = $oSequence->getSeqlength();
                }

                $oSequence->setMoltype(substr($linestr, 47, 6));
                if (substr($linestr, 44, 3) == "ss-") {
                    $oSequence->setStrands("SINGLE");
                }   elseif (substr($linestr, 44, 3) == "ds-") {
                    $oSequence->setStrands("DOUBLE");
                }  elseif (substr($linestr, 44, 3) == "ms-") {
                    $oSequence->setStrands("MIXED");
                }

                $oSequence->setTopology(strtoupper(substr($linestr, 55, 8)));
                $oSequence->setDivision(strtoupper(substr($linestr, 64, 3)));
                $oSequence->setDate(strtoupper(substr($linestr, 68, 11)));

                $inseq_flag = true;
            }

            if (trim(substr($linestr,0,10)) == "BASE COUNT") {
                if (count($feat_r) > 0) {
                    $oSequence->setFeatures($feat_r);
                }
            }

            if (trim(substr($linestr,0,12)) == "FEATURES") {
                // The REFERENCE section was present for this SEQUENCE ENTRY so we set REFERENCE attribute.
                if (count($ref_array) > 0) {
                    $oSequence->setReference($ref_array);
                }
                $lastsubkey = "";

                $feat_r = [];
                $qual_r = [];

                // Go to the next line.
                list($lineno, $linestr) = each($flines);

                // This loops through each line in the entire FEATURES SECTION.
                while(substr($linestr,0,10) != "BASE COUNT") {
                    $label = trim(substr($linestr,0,21));
                    $data = trim(substr($linestr,21));

                    if (strlen($label) != 0) {
                        // At the beginning of a new SUBKEY.
                        $subkey = $label;
                        // Add/save the qualifier array (qual_r) of the previous SUBKEY to our big feat_r array.
                        if (count($qual_r) > 0) {
                            $feat_r[$lastsubkey] = $qual_r;
                            $qual_r = array();
                        }

                        $qual = $subkey;
                        $qual_r[$qual] = "";
                        $qual_ctr = 0;
                        do {
                            $qual_ctr++;
                            $qual_r[$qual] .= " " . $data;

                            list($lineno, $linestr) = each($flines);
                            $label = trim(substr($linestr,0,21));
                            $data = trim(substr($linestr,21));
                        } while (is_blank($label) && !(isa_qualifier($data)));

                        if (!(is_blank($label))) {
                            $lastsubkey = $subkey;
                            $subkey = $label;
                        }
                    } else { // we are inside a subkey section but on the 2nd, 3rd, nth line which have blank LABELS.
                        if (isa_qualifier($data)) {
                            $wordarray = preg_split("/=/", $data);
                            $qual = $wordarray[0];
                            $data = $wordarray[1];
                            $qual_r[$qual] = "";
                            $qual_ctr = 0;

                            do {
                                $qual_ctr++;
                                $qual_r[$qual] .= " " . $data;
                                list($lineno, $linestr) = each($flines);
                                $label = trim(substr($linestr,0,21));
                                $data = trim(substr($linestr,21));
                            } while(is_blank($label) && !(isa_qualifier($data)));

                            if (!(is_blank($label))) {
                                $lastsubkey = $subkey;
                                $subkey = $label;
                            }
                        }
                    }
                }

                if (count($qual_r) > 0) {
                    $feat_r[$lastsubkey] = $qual_r;
                    $qual_r = [];
                }

                prev($flines);
            }

            if (substr($linestr,0,10) == "DEFINITION") {
                $wordarray = explode(" ", $linestr);
                array_shift($wordarray);
                $oSequence->setDefinition(implode(" ", $wordarray));
            }

            if ($inseq_flag) {
                if (trim(substr($linestr, 0, 12)) == "REFERENCE") {
                    // at this point, we are at the line with REFERENCE x (base y of z) in it.
                    $wordarray = preg_split("/\s+/", trim(substr($linestr,12)));

                    $ref_rec = array();
                    $ref_rec["REFNO"] = $wordarray[0];
                    array_shift($wordarray);
                    $ref_rec["BASERANGE"] = implode(" ", $wordarray);
                    $lastsubkey = "";
                    $subkey_lnctr = 0;

                    while(list($lineno, $linestr) = each($flines)) {
                        $subkey = trim(substr($linestr,0,12));

                        // If current subkey is blank string, then this is a continuation of the last subsection.
                        if (strlen($subkey) == 0) {
                            $subkey = $lastsubkey;
                        }

                        // If we are at the next subkey section (e.g. lastsubkey was AUTHORS, and current is TITLE).
                        if ($subkey != $lastsubkey) {
                            $subkey_lnctr = 0;
                        }
                        
                        switch ($subkey) {
                            case "AUTHORS":
                                $subkey_lnctr++;
                                $wordarray = preg_split("/\s+/", trim(substr($linestr,12)));
                                // we remove comma at the end of a name, and the element "and".
                                $newarray = array();
                                foreach($wordarray as $authname) {
                                    if (strtoupper($authname) != "AND") {
                                        if (substr($authname, strlen($authname)-1, 1) == ",") {
                                            $authname = substr($authname, 0, strlen($authname)-1);
                                        }
                                        $newarray[] = $authname;
                                    }
                                }
                                $ref_rec["AUTHORS"] = ($subkey_lnctr == 1) ? $newarray : array_merge($ref_rec["AUTHORS"], $newarray);
                                break;
                            case "TITLE":
                                $subkey_lnctr++;
                                if ($subkey_lnctr == 1) {
                                    $ref_rec["TITLE"] = trim(substr($linestr,12));
                                } else {
                                    $ref_rec["TITLE"] .= " " . trim(substr($linestr,12));
                                }
                                break;
                            case "JOURNAL":
                                $subkey_lnctr++;
                                if ($subkey_lnctr == 1) {
                                    $ref_rec["JOURNAL"] = trim(substr($linestr,12));
                                } else {
                                    $ref_rec["JOURNAL"] .= " " . trim(substr($linestr,12));
                                }
                                break;
                            case "MEDLINE":
                                $ref_rec["MEDLINE"] = substr($linestr, 12, 8);
                                break;
                            case "PUBMED":
                                $ref_rec["PUBMED"] = substr($linestr, 12, 8);
                                break;
                            case "REMARK":
                                $subkey_lnctr++;
                                if ($subkey_lnctr == 1) {
                                    $ref_rec["REMARK"] = trim(substr($linestr,12));
                                }
                                else $ref_rec["REMARK"] .= " " . trim(substr($linestr,12));
                                break;
                            case "COMMENT":
                                $subkey_lnctr++;
                                if ($subkey_lnctr == 1) {
                                    $ref_rec["COMMENT"] = trim(substr($linestr,12));
                                } else {
                                    $ref_rec["COMMENT"] .= " " . trim(substr($linestr,12));
                                }
                                break;
                        }
                        if ($subkey == "FEATURES") {
                            prev($flines);
                            break;
                        }
                        if ($subkey == "REFERENCE") {
                            $ref_ctr++;
                            prev($flines);
                            break;
                        }
                        $lastsubkey = $subkey;
                    }
                    array_push($ref_array, $ref_rec);
                }
                if (trim(substr($linestr, 0, 12)) == "SOURCE") {
                    // For now, assume a single-line SOURCE field.
                    $oSequence->setSource(substr($linestr, 12));
                }
                if (trim(substr($linestr, 0, 12)) == "SEGMENT") {
                    $oSequence->setSegment(substr($linestr, 12));
                    $wordarray = preg_split("/\s+/", trim(substr($linestr,12)));
                    $oSequence->setSegmentNo($wordarray[0]);
                    $oSequence->setSegmentCount($wordarray[2]);
                }

                // For now, assume that KEYWORDS field consists of exactly one line.
                if (trim(substr($linestr, 0, 12)) == "KEYWORDS") {
                    $wordarray = preg_split("/\s+/", trim($linestr));
                    array_shift($wordarray);
                    $wordarray = preg_split("/;+/", implode(" ", $wordarray));
                    if ($wordarray[0] != ".") {
                        $oSequence->setKeywords($wordarray);
                    }
                }
                if (substr($linestr, 0, 7) == "VERSION") {
                    // Assume that VERSION line is made up of exactly 2 or 3 tokens.
                    $wordarray = preg_split("/\s+/", trim($linestr));
                    $oSequence->setVersion($wordarray[1]);
                    if (count($wordarray) == 3) {
                        $oSequence->setNcbiGiId($wordarray[2]);
                    }
                    $accession_flag = false;
                }
                if ($accession_flag) {
                    // 2nd, 3rd, etc. line of ACCESSION field.
                    $wordarray = preg_split("/\s+/", trim($linestr));
                    //$this->sec_accession = array_merge($this->sec_accession, $wordarray); -> mistake ?
                    $oSequence->setSecAccession(array_merge($oSequence->getSecAccession(), $wordarray));
                }
                if (substr($linestr,0,9) == "ACCESSION") {
                    $wordarray = preg_split("/\s+/", trim($linestr));
                    $oSequence->setAccession($wordarray[1]);
                    array_shift($wordarray);
                    array_shift($wordarray);
                    $oSequence->setAccession($wordarray);
                    $accession_flag = true;
                }
                if (substr($linestr,0,10) == "  ORGANISM") {
                    $oSequence->setOrganism(substr($linestr,12));
                }
                if (($seqdata_flag == true) && (substr($linestr,0,2) != "//")) {
                    $wordarray = explode(" ", trim($linestr));
                    array_shift($wordarray);
                    $seqline = implode("", $wordarray);
                    $seqdata .= $seqline;
                }
                if (substr($linestr,0,6) == "ORIGIN") {
                    $seqdata_flag = true;
                }
                if (substr($linestr,0,2) == "//") {
                    $oSequence->setSequence($seqdata);
                    $seqarr[$this->database->getId()] = $this;
                    $seqdata_flag = false;
                    $inseq_flag = false;
                    break;
                }
            }
        }
        //$seqobj->seqarray = $seqarr; -> what for ? seqarray property does not exists
        return $oSequence;
    }


    /**
     * Opens or prepares the SeqDB for processing.  Opposite of close().
     * @param type $dbname
     */
    public function open($dbname)
    {
        if (!file_exists($dbname . ".idx")) {
            throw new \Exception("ERROR: Index file $dbname.IDX does not exist!");
        }

        if (!file_exists($dbname . ".dir")) {
            throw new \Exception("ERROR: Index file $dbname.DIR does not exist!");
        }

        $this->database->setDbname($dbname);
        $this->database->setDataFn($dbname . ".idx");
        $this->database->setDirFn($dbname . ".dir");
        $this->database->setSeqptr(0);
    }


    /**
     * Closes the SeqDB database after we're through using it.  Opposite of open() method.
     */
    public function close()
    { 
        // Close simply assigns null values to attributes of the seqdb() object.
        // Methods like fetch would not function properly if these values are null.
        $this->database->setDbname("");
        $this->database->setDataFn("");
        $this->database->setDirFn("");
        $this->database->setSeqptr(-1);
    }
    
    private function makeSwissProtArray()
    {
        
    }
} 