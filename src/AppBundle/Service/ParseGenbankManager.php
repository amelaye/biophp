<?php

class ParseGenbankManager 
{
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
}