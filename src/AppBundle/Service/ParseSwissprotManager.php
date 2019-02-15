<?php

class ParseSwissProtManager
{
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

}