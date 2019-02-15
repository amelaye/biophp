<?php
/**
 * Swissprot database parsing
 * @author Amélie DUVERNET akka Amelaye
 * Freely inspired by BioPHP's project biophp.org
 * Created 15 february 2019
 * Last modified 15 february 2019
 */
namespace AppBundle\Service;

class ParseSwissProtManager
{
    private $accession;
    private $date_r;
    private $desc;
    private $desc_lnctr;
    private $gename_r;
    private $os_linectr;
    private $os_str;
    private $oc_linectr;
    private $oc_str;
    private $ref_r;
    private $ra_r;
    private $ra_ctr;
    private $ra_str;
    private $rl_ctr;
    private $rl_str;
    private $db_r;
    private $ft_r;
    private $kw_str;
    private $kw_r;
        
    public function __construct()
    {
        $this->accession  = [];
        $this->date_r     = [];
        $this->desc       = "";
        $this->desc_lnctr = 0;
        $this->gename_r   = [];
        $this->os_linectr = 0;
        $this->os_str     = "";
        $this->oc_linectr = 0;
        $this->oc_str     = "";
        $this->ref_r      = [];
        $this->ra_r       = [];
        $this->ra_ctr     = 0;
        $this->ra_str     = "";
        $this->rl_ctr     = 0;
        $this->rl_str     = "";
        $this->db_r       = [];
        $this->ft_r       = [];
        $this->kw_str     = "";
        $this->kw_r       = [];
    }
    
    /**
     * Parses a Swissprot data file and returns a Seq object containing parsed data.
     * Parses the Feature Table lines (those that begin with FT) in a Swissprot
     * data file, extracts the feature key name, from endpoint, to endpoint, and description, and
     * stores them in a (simple) array.
     * @param   type        $flines
     * @return  Sequence    $oSequence
     */
    public function parse_swissprot($flines)
    {
        

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
                //$accstr = $linedata;
                $accstr = substr($linedata, 0, strlen($linedata)-1);
                $accline = preg_split("/;/", intrim($accstr));
                $this->accession = array_merge($this->accession, $accline);
            }
            if (left($linestr, 2) == "DT") {
                //$datestr = $linedata;
                $datestr = substr($$linedata, 0, strlen($linedata)-1);
                $words = preg_split("/\(/", $datestr);
                $firstcomma = strpos($words[1], ",");
                $comment = trim(substr($words[1], $firstcomma+1));

                switch($comment) {
                    case "CREATED":
                        // this DT line is a DATE CREATED line.
                        $create_date = substr($words[0], 0, 11); 
                        $create_rel = substr($words[1], 5, ($firstcomma-5));
                        $this->date_r[$comment] = array($create_date, $create_rel); 
                        break;
                    case "LAST SEQUENCE UPDATE":
                        $sequpd_date = substr($words[0], 0, 11);
                        $sequpd_rel = substr($words[1], 5, ($firstcomma-5));
                        $this->date_r[$comment] = array($sequpd_date, $sequpd_rel);
                        break;
                    case "LAST ANNOTATION UPDATE":
                        $notupd_date = substr($words[0], 0, 11);
                        $notupd_rel = substr($words[1], 5, ($firstcomma-5));
                        $this->date_r[$comment] = array($notupd_date, $notupd_rel);
                        break;
                    default:
                        // For now, we do not check vs. duplicate comments.
                        // We just overwrite the older comment with new one.
                        $other_comment = $comment; 
                        $other_date = substr($words[0], 0, 11);
                        $other_rel = substr($words[1], 5, ($firstcomma-5));
                        $this->date_r[$comment] = array($other_date, $other_rel);
                        break;
                }
            }  
            if (left($linestr, 2) == "DE") {
                $this->desc_lnctr++;
                $linestr = $linedata;
                if ($this->desc_lnctr == 1) {
                    $this->desc .= $linestr;
                } else {
                    $this->desc .= " " . $linestr;
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
                $this->kw_str .= $linedata;
                if ($lineend == ".") {
                    $this->kw_str = rem_right($this->kw_str);
                    $this->kw_r = preg_split("/;/", $this->kw_str);
                    array_walk($this->kw_r, "trim_element");
                    $this->kw_str = "";
                }
            }
            if ($linelabel == "OS") {
                $this->os_linectr++;
                if ($lineend != ".") {
                    if ($this->os_linectr == 1) {
                        $this->os_str .= $linedata;
                    } else {
                        $this->os_str .= " $linedata";
                    }
                } else {
                    $this->os_str .= " $linedata";
                    $this->os_str = rem_right($this->os_str);
                    $os_line = preg_split("/\, AND /", $this->os_str);
                }
            }
            if ($linelabel == "OG") {
                $organelle = rem_right($linedata);
            }
            if ($linelabel == "OC") {
                $this->oc_linectr++;
                if ($lineend != ".") {
                    if ($this->oc_linectr == 1) {
                        $this->oc_str .= $linedata;
                    } else {
                        $this->oc_str .= " $linedata";
                    }
                } else {
                    $this->oc_str .= " $linedata";
                    $this->oc_str = rem_right($this->oc_str);
                    $oc_line = preg_split("/;/", $this->oc_str);
                    array_walk($oc_line, "trim_element");
                }
            }
            if ($linelabel == "FT") {
                $ft_key = trim(substr($linestr, 5, 8));
                $ft_from = (int) trim(substr($linestr, 14, 6));
                $ft_to = (int) trim(substr($linestr, 21, 6));
                $ft_desc = rem_right(trim(substr($linestr, 34)));
                $this->ft_r[] = array($ft_key, $ft_from, $ft_to, $ft_desc);
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
                $this->db_r[] = [$db_name, $db_pid, $db_sid];
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
                        $this->ra_ctr++;
                        if ($this->ra_ctr == 1) {
                            $this->ra_str = $linedata;
                        } else {
                            $this->ra_str .= " $linedata";
                        }
                        if ($lineend == ";") {
                            $this->ra_str = rem_right($this->ra_str);
                            $this->ra_r = preg_split("/\,/", $this->ra_str);
                            array_walk($this->ra_r, "trim_element");
                            $inner_r["RA"] = $this->ra_r;
                        }
                    } elseif ($linelabel == "RL") {
                        $this->rl_ctr++;
                        if ($this->rl_ctr == 1) {
                            $this->rl_str = $linedata;
                        } else {
                            $this->rl_str .= " $linedata";
                        }
                    } else {
                        $inner_r["RL"] = $this->rl_str;
                        prev($flines);
                        break;
                    }
                } // CLOSES 2nd WHILE
                $this->ref_r[$refno-1] = $inner_r;
                $this->ra_str = "";
                $this->ra_ctr = 0;
                $this->rl_str = "";
                $this->rl_ctr = 0;
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
                        $this->gename_r[] = $temp;
                    } elseif (strpos($linestr, " AND ")) {
                        // Case 2: GNAME1 AND GNAME2 AND GNAME3.
                        $temp = preg_split("/ AND /", $linestr);
                        foreach($temp as $gene) {
                            $this->gename_r[] = array($gene);
                        }
                    } else {
                        $this->gename_r[] = array($linestr);
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
                            $this->gename_r[] = $genelist;
                        } else { // singleton
                            $this->gename_r[] = array($gene);
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
        $oSequence->setAccession($this->accession[0]);
        array_shift($this->accession);
        $oSequence->setSecAccession($this->accession);
        $oSequence->setSource($os_line);
        $oSequence->setOrganism($oc_line);
        $oSequence->setSequence($sequence);
        $oSequence->setDefinition($this->desc);
        $oSequence->setKeywords($this->kw_r);

        $genbank_ref_r = $this->makeRefArray();
        
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
        $aSwiss["ACCESSION"]     = $this->accession;
        $aSwiss["PRIM_AC"]       = $this->accession[0];
        $aSwiss["DESC"]          = $this->desc;
        $aSwiss["IS_FRAGMENT"]   = $is_fragment;
        // KEYWORDS is an ARRAY.
        $aSwiss["KEYWORDS"]      = $this->kw_r;
        // ORGANISM is an ARRAY.
        $aSwiss["ORGANISM"]      = $os_line;
        $aSwiss["ORGANELLE"]     = $organelle;
        // FT_<keyword> is an ARRAY.
        $this->process_ft($aSwiss, $this->ft_r);

        $aSwiss["AMINO_COUNT"]   = $aa_count;
        $aSwiss["MOLWT"]         = $mol_wt;
        $aSwiss["CHK_NO"]        = $chk_no;
        $aSwiss["CHK_METHOD"]    = $chk_method;
        $aSwiss["SEQUENCE"]      = $sequence;
        // GENE_NAME is an ARRAY.
        $aSwiss["GENE_NAME"]     = $this->gename_r;
        // ORG_CLASS is an ARRAY.
        $aSwiss["ORG_CLASS"]     = $oc_line;
        // REFERENCE is an ARRAY.
        $aSwiss["REFERENCE"]     = $this->ref_r;

        $oSequence->setSwissprot($aSwiss);
        return $oSequence;
    }

    /**
     * Then pushes this array into a larger associative array, called $swiss, which is
     * also an attribute of the Seq object. It is assigned a key of the form: FT_<feature_key_name>.
     * Examples are: FT_PEPTIDE, FT_DISULFID.
     * @param array $swiss
     */
    private function process_ft(&$swiss)
    {
	foreach($this->ft_r as $element) {
            $index = "FT_" . $element[0];
            array_shift($element);					
            if (count($swiss[$index]) == 0) {
		$swiss[$index] = array();
		array_push($swiss[$index], $element);
            } else {
                array_push($swiss[$index], $element);
            }
	}
    }
    
    private function makeRefArray()
    {
        $genbank_ref_r = [];
        $inner_r = [];
        foreach($this->ref_r as $key => $value) {
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
        return $genbank_ref_r;
    }
}