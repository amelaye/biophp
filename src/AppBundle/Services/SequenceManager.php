<?php

namespace AppBundle\Services;

class SequenceManager
{
    private $moltype;

    /**
     * Gets the genetic complement of a DNA or RNA sequence.
     * @param type $seq
     * @param type $moltype
     * @return string
     */
    function complement($seq, $moltype)
    {
        if (!isset($moltype)) {
            $moltype = (isset($this->moltype)) ? $this->moltype : "DNA";
        }

        $dna_complements = [
            "A" => "T",
            "T" => "A",
            "G" => "C",
            "C" => "G"
        ];

        $rna_complements = [
            "A" => "U",
            "U" => "A",
            "G" => "C",
            "C" => "G"
        ];

        $moltype = strtoupper($moltype);
        if ($moltype == "DNA") {
            $comp_r = $dna_complements;
        } elseif ($moltype == "RNA") {
            $comp_r = $rna_complements;
        }
        $seqlen = strlen($seq);
        $compseq = "";
        for($i = 0; $i < $seqlen; $i++) {
            $symbol = substr($seq, $i, 1);
            $compseq .= $comp_r[$symbol];
        }
        return $compseq;
    }


    /**
     * First gets the complement of a DNA or RNA sequence, and then returns it in reverse order.
     * @param type $seq
     * @param type $moltype
     * @return type
     */
    function revcomp($seq, $moltype)
    {
        return strrev($this->complement($seq, $moltype));
    }


    /**
     * Returns one of the two palindromic "halves" of a palindromic string. 
     * @param type $string
     * @param type $no
     * @return type
     */
    function halfstr($string, $no)
    {
        // for now, this holds for mirror repeats.
        if(strlen($string) % 2 != 0) { //odd
            $comp_len = (int)(strlen($string)/2);
            if ($no == 0) {
                return substr($string, 0, $comp_len);
            } else {
                return substr($string, $comp_len + 1);
            }
        } else {
            $comp_len = strlen($string)/2;
            if ($no == 0) {
                return substr($string, 0, $comp_len);
            } else {
                return substr($string, $comp_len);
            }
        }
    }


    /**
     * Returns the sequence located between two palindromic halves of a palindromic string.
     * Take note that the "bridge" as I call it, is not necessarily a genetic mirror or a palindrome.
     * @param type $string
     * @return string
     */
    function get_bridge($string)
    {
        if(strlen($string) % 2 != 0) { // odd
            $comp_len = (int) (strlen($string)/2);
            return substr($string, $comp_len, 1);
        } else {
            return "";
        }
    }


    /**
     * Returns the expansion of a nucleic acid sequence, replacing special wildcard symbols 
     * with the proper PERL regular expression. 
     * @param type $string
     * @return type
     */
    function expand_na($string)
    {
        $patterns = [
            "/N|X/", "/R/", "/Y/", "/S/", "/W/", "/M/", 
            "/K/", "/B/", "/D/", "/H/", "/R/"
        ];
        $replacements = [
            ".", "[AG]", "[CT]", "[GC]", "[AT]", "[AC]", 
            "[TG]", "[CGT]","[AGT]", "[ACT]", "[ACG]"
        ];
        $sExpansion = preg_replace($patterns, $replacements, $string);
        return $sExpansion;
    }


    /**
     * Calculates the molecular weight of a sequence.
     * @return boolean|real
     */
    function molwt()
    {
        // Check if characters outside our 20-letter amino alphabet is included in the sequence.
        if ($this->moltype == "DNA") {
            preg_match_all("/[^ACGTMRWSYKVHDBXN]/", $this->sequence, $match);
            // If there are unknown characters, then do not compute molwt and instead return FALSE.
            if (count($match[0]) > 0) {
                return FALSE;
            }
        } elseif ($this->moltype == "RNA") {
            preg_match_all("/[^ACGUMRWSYKVHDBXN]/", $this->sequence, $match);
            // If there are unknown characters, then do not compute molwt and instead return FALSE.
            if (count($match[0]) > 0) {
                return FALSE;
            }
        } elseif ($this->moltype == "PROTEIN") { // sequence is a protein, so invoke the Protein class' molwt() method.       
            $prot = new Protein();
            $prot->sequence = $this->sequence;
            return $prot->molwt();
        } else {
            return FALSE; // return FALSE when encountering unknown molecule types		
        }

        $lowerlimit = 0;
        $upperlimit = 1;

        $Car = 12.01;
        $Oxy = 16.00;
        $Nit = 14.01;
        $Hyd = 1.01;
        $Pho = 30.97;
        $water = 18.015;

        $adenine = (5 * $Car) + (5 * $Nit) + (5 * $Hyd);
        $guanine = (5 * $Car) + (5 * $Nit) + (1 * $Oxy) + (5 * $Hyd);
        $cytosine = (4 * $Car) + (3 * $Nit) + (1 * $Oxy) + (5 * $Hyd);
        $thymine = (5 * $Car) + (2 * $Nit) + (2 * $Oxy) + (6 * $Hyd);
        $uracil = (4 * $Car) + (2 * $Nit) + (2 * $Oxy) + (4 * $Hyd);

        // neutral (unionized) form
        $ribo_pho = (5 * $Car) + (7 * $Oxy) + (9 * $Hyd) + (1 * $Pho);
        $deoxy_pho = (5 * $Car) + (6 * $Oxy) + (9 * $Hyd) + (1 * $Pho);

        // the following are single strand molecular weights / base
        $rna_A_wt = $adenine + $ribo_pho - $water;
        $rna_C_wt = $cytosine + $ribo_pho - $water;
        $rna_G_wt = $guanine + $ribo_pho - $water;
        $rna_U_wt = $uracil + $ribo_pho - $water;

        $dna_A_wt = $adenine + $deoxy_pho - $water;
        $dna_C_wt = $cytosine + $deoxy_pho - $water;
        $dna_G_wt = $guanine + $deoxy_pho - $water;
        $dna_T_wt = $thymine + $deoxy_pho - $water;

        $dna_wts = [
            'A' => [$dna_A_wt, $dna_A_wt],          // Adenine
            'C' => [$dna_C_wt, $dna_C_wt],          // Cytosine
            'G' => [$dna_G_wt, $dna_G_wt],          // Guanine
            'T' => [$dna_T_wt, $dna_T_wt],          // Thymine
            'M' => [$dna_C_wt, $dna_A_wt],          // A or C
            'R' => [$dna_A_wt, $dna_G_wt],          // A or G
            'W' => [$dna_T_wt, $dna_A_wt],          // A or T
            'S' => [$dna_C_wt, $dna_G_wt],          // C or G
            'Y' => [$dna_C_wt, $dna_T_wt],          // C or T
            'K' => [$dna_T_wt, $dna_G_wt],          // G or T
            'V' => [$dna_C_wt, $dna_G_wt],          // A or C or G
            'H' => [$dna_C_wt, $dna_A_wt],          // A or C or T
            'D' => [$dna_T_wt, $dna_G_wt],          // A or G or T
            'B' => [$dna_C_wt, $dna_G_wt],          // C or G or T
            'X' => [$dna_C_wt, $dna_G_wt],          // G or A or T or C
            'N' => [$dna_C_wt, $dna_G_wt]           // G or A or T or C
        ];

        $rna_wts = [
            'A' => [$rna_A_wt, $rna_A_wt],      // Adenine
            'C' => [$rna_C_wt, $rna_C_wt],       // Cytosine
            'G' => [$rna_G_wt, $rna_G_wt],       // Guanine
            'U' => [$rna_U_wt, $rna_U_wt],       // Uracil
            'M' => [$rna_C_wt, $rna_A_wt],       // A or C
            'R' => [$rna_A_wt, $rna_G_wt],       // A or G
            'W' => [$rna_U_wt, $rna_A_wt],       // A or U
            'S' => [$rna_C_wt, $rna_G_wt],       // C or G
            'Y' => [$rna_C_wt, $rna_U_wt],       // C or U
            'K' => [$rna_U_wt, $rna_G_wt],       // G or U
            'V' => [$rna_C_wt, $rna_G_wt],       // A or C or G
            'H' => [$rna_C_wt, $rna_A_wt],       // A or C or U
            'D' => [$rna_U_wt, $rna_G_wt],       // A or G or U
            'B' => [$rna_C_wt, $rna_G_wt],       // C or G or U
            'X' => [$rna_C_wt, $rna_G_wt],       // G or A or U or C
            'N' => [$rna_C_wt, $rna_G_wt]        // G or A or U or C
        ];

        $all_na_wts = array("DNA" => $dna_wts, "RNA" => $rna_wts);
        $na_wts = $all_na_wts[$this->moltype];

        $weight_lower_bound += $water;
        $weight_upper_bound += $water;

        $mwt = array(0, 0);
        $NA_len = $this->seqlen();
        for($i = 0; $i < $NA_len; $i++) {
            $NA_base = substr($this->sequence, $i, 1);
            $mwt[$lowerlimit] += $na_wts[$NA_base][$lowerlimit];
            $mwt[$upperlimit] += $na_wts[$NA_base][$upperlimit];
        }
        $mwt_water = 18.015;
        $mwt[$lowerlimit] += $mwt_water;
        $mwt[$upperlimit] += $mwt_water;
        return $mwt;
    }


    /**
     * Counts the number of codons (trios of base-pairs) in a DNA/RNA sequence.
     * @return int
     */
    function count_codons()
    {
        $codstart = (isset($this->features["CDS"]["/codon_start"])) ? $this->features["CDS"]["/codon_start"] : 1;
        $codcount = (int) (($this->seqlen() - $codstart + 1)/3);
        return $codcount;
    }


    /**
     * @param type $start
     * @param type $count
     * @return \AppBundle\Entity\seq
     */
    function subseq($start, $count)
    {
        $newseq = new seq();
        $newseq->sequence = substr($this->sequence, $start, $count);
        return $newseq;
    }


    /**
     * Returns a two-dimensional associative array where each key is a substring matching a 
     * given pattern, and each value is an array of positional indexes which indicate the location of
     * each occurrence of the substring (needle) in the larger string (haystack). This DOES NOT allow 
     * for pattern overlaps.
     * @param type $pattern
     * @param type $options
     * @return array  - value example: ( "PAT1" => (0, 17), "PAT2" => (8, 29) )
     */
    function patpos($pattern, $options = "I")
    {
        $outer = array();
        $pf = $this->patfreq($pattern, $options);
        $haystack = $this->sequence;
        if (strtoupper($options) == "I") {
            $haystack = strtoupper($haystack);
        }

        foreach($pf as $key=>$value) {
            if ($options == "I") {
                $key = strtoupper($key);
            }
            $inner = array();
            $start = 0;
            for($i = 0; $i < $value; $i++) {
                $lastpos = strpos($haystack, $key, $start);
                array_push($inner, $lastpos);
                $start = $lastpos + strlen($key);
            }
            $outer[$key] = $inner;
        }
        return $outer;
    }


    /**
     * Similar to patpos() except that this allows for overlapping patterns.
     * Return value format: (index1, index2, ... )
     * Return value sample: ( 0, 8, 17, 29)
     * @param type $pattern
     * @param type $options
     * @param type $cutpos
     * @return type
     */
    function patposo($pattern, $options = "I", $cutpos = 1)
    {
        $outer = array();
        $haystack = $this->sequence;
        if (strtoupper($options) == "I") {
            $haystack = strtoupper($haystack);
        }
        $pf = $this->patfreq($pattern, $options);
        $relpos_r = array();
        $currentpos = -1 * $cutpos;
        $lastpos = -1 * $cutpos;
        $ctr = 0;
        $runsum_start = 0;
        while(strlen($haystack) >= strlen($pattern)) {
            $ctr++;
            if ($ctr == 1) {
                $start = 0;
            } else {
                $start = $lastpos + $cutpos;
            }
            $haystack = substr($haystack, $start);
            $runsum_start += $start;
            $minpos = 999999;
            $found_flag = FALSE;
            foreach($pf as $key=>$value) {
                $currentpos = strpos($haystack, $key);
                if (gettype($currentpos) == "integer") {
                    $found_flag = TRUE;
                    if ($currentpos < $minpos) $minpos = $currentpos;
                }
            }
            if (!$found_flag) {
                break;
            }
            $currentpos = $minpos;
            if ($ctr == 1) {
                $abspos[] = $currentpos;
            } else {
                $abspos[] = $runsum_start + $currentpos;
            }
            $lastpos = $currentpos;
        }
        return $abspos;
    }


    /**
     * Returns a one-dimensional associative array where each key is a substring matching the
     * given pattern, and  each value is the frequency count of the substring within the larger string.
     * Return value example: ( "GAATTC" => 3, "ATAT" => 4, ... )
     * @param type $pattern
     * @param type $options
     * @return type
     */
    function patfreq($pattern, $options = "I")
    {
        $match = $this->findpattern($pattern, $options);
        return array_count_values($match[0]);
    }


    /**
     * Findpattern returns: ( "GCG", "GCG", "GCG" ) if pattern is exactly "GCG".
     * @param type $pattern
     * @param type $options
     * @return type
     */
    function findpattern($pattern, $options = "I")
    {
        if (firstChar($pattern) == "_") {
            $pattern = getpattern($pattern);
        }

        if (strtoupper($options) == "I") {
            preg_match_all("/" . expand_na(strtoupper($pattern)) . "/", strtoupper($this->sequence), $match);
        } else {
            preg_match_all("/" . expand_na($pattern) . "/", $this->sequence, $match);
        }
        return $match;
    }


    /**
     * 
     * @return int
     */
    function seqlen()
    {
        return strlen($this->sequence);
    }


    /**
     * Note legacy
     *  // Apr 10, 2003 - This now returns 0 instead of NULL when
     *  // $symbol is not found.  0 is the preferred return value.
     * @param type $symbol
     * @return int
     */
    function symfreq($symbol)
    {
        $symtally = count_chars(strtoupper($this->sequence), 1);
        if ($symtally[ord($symbol)] == NULL) {
            return 0;
        } else {
            return $symtally[ord($symbol)];
        }
    }


    /**
     * 
     * @param type $index
     * @param type $readframe
     * @return type
     */
    function getcodon($index, $readframe = 0)
    {
        return strtoupper(substr($this->sequence, ($index * 3) + $readframe, 3));
    }


    /**
     * 
     * @param type $readframe
     * @param type $format
     * @return string
     */
    function translate($readframe = 0, $format = 3)
    {
        $codon_index = 0;
        $result = "";
        while(1) {
            $codon = $this->getcodon($codon_index, $readframe);
            if ($codon == "") {
                break;
            }
            if ($format == 1) {
                $result .= $this->translate_codon($codon, $format);
            } elseif ($format == 3) {
                $result .= " " . $this->translate_codon($codon, $format);
            } else {
                throw new \Exception("Invalid format parameter");
            }
            $codon_index++;
        }
        return $result;
    }


    /**
     * Function charge() accepts a string of amino acids in single-letter format and outputs
     * a string of charges in single-letter format also.  A for acidic, C for basic, and N
     * for neutral.
     * @param type $amino_seq
     * @return string
     */
    function charge($amino_seq)
    {
        $charge_seq = "";
        $ctr = 0;
        while(1) {
            $amino_letter = substr($amino_seq, $ctr, 1);
            switch($amino_letter) {
                case "":
                    break;
                case "D":
                case "E":
                    $charge_seq .= "A";
                    break;
                case "K":
                case "R":
                case "H":
                    $charge_seq .= "C";
                    break;
                case "*":
                    $charge_seq .= "*";
                    break;
                case "X":
                    $charge_seq .= "X";
                    break;
                default:
                    if (substr_count("GAVLISTNQFYWCMP", $amino_letter) >= 1) {
                        $charge_seq .= "N";
                    } else {
                        throw new \Exception("Invalid amino acid symbol in input sequence.");
                    }
                    break;
            }
            $ctr++;
        }
        return $charge_seq;
    }


    /**
     * Chemical groups: L - GAVLI, H - ST, M - NQ, R - FYW, S - CM, I - P, A - DE, C - KRH, * - *, X - X
     * @param type $amino_seq
     * @return string
     */
    function chemgrp($amino_seq)
    {
        $chemgrp_seq = "";
        $ctr = 0;
        while(1) {
        $amino_letter = substr($amino_seq, $ctr, 1);
            switch($amino_letter) {
                case "":
                    break;
                case "S":
                case "T":
                    $chemgrp_seq .= "H";
                    break;
                case "N":
                case "Q":
                    $chemgrp_seq .= "M";
                    break;
                case "C":
                case "M":
                    $chemgrp_seq .= "S";
                    break;
                case "P":
                    $chemgrp_seq .= "I";
                    break;
                case "D":
                case "E":
                    $chemgrp_seq .= "A";
                    break;
                case "K":
                case "R":
                case "H":
                    $chemgrp_seq .= "C";
                    break;
                case "*":
                    $chemgrp_seq .= "*";
                    break;
                case "X":
                    $chemgrp_seq .= "X";
                    break;
                default:
                    if (substr_count("GAVLI", $amino_letter) == 1) {
                        $charge_seq .= "L";
                    } elseif (substr_count("FYW", $amino_letter) == 1) {
                        $chemgrp_seq .= "R";
                    } else {
                        throw new \Exception("Invalid amino acid symbol in input sequence.");
                    }
                    break;
            }
            $ctr++;
        }
        return $chemgrp_seq;
    }


    /**
     * 
     * @param type $codon
     * @param type $format
     * @return string
     */
    function translate_codon($codon, $format = 3)
    {
        if (($format != 3) && ($format != 1)) {
            throw new \Exception("Invalid format parameter.");
        }
        if (strlen($codon) < 3) {
            if ($format == 3) {
                return "XXX";
            }
        } else {
            return "X";
        }

        $codon = strtoupper($codon);
        $codon = ereg_replace("T", "U", $codon);
        $letter1 = substr($codon, 0, 1);
        $letter2 = substr($codon, 1, 1);
        $letter3 = substr($codon, 2, 1);

        if ($format == 3) {
            if ($letter1 == "U") {
                switch($letter2) {
                    case "U":
                        switch($letter3) {
                            case "U":
                            case "C":
                                return "Phe";
                            case "A":
                            case "G":
                                return "Leu";
                        }
                    case "C":
                        return "Ser";
                    case "A":
                        switch($letter3) {
                            case "U":
                            case "C":
                                return "Tyr";
                            case "A":
                            case "G":
                                return "STP";
                        }
                    case "G":
                        switch($letter3) {
                            case "U":
                            case "C":
                                return "Cys";
                            case "A":
                                return "STP";
                            case "G":
                                return "Trp";
                        }
                }
            }

            if ($letter1 == "C") {
                switch($letter2) {
                    case "U":
                        return "Leu";
                    case "C":
                        return "Pro";
                    case "A":
                        switch($letter3) {
                            case "U":
                            case "C":
                                return "His";
                                break;
                            case "A":
                            case "G":
                                return "Gln";
                        }
                    case "G":
                        return "Arg";
                }
            }

            if ($letter1 == "A") {
                if ($letter2 == "U") {
                    if ($letter3 == "G") { 
                        return "Met"; 
                    } else { 
                        return "Ile"; 
                    }
                }
                if ($letter2 == "C") {
                    return "Thr";
                }
                if ($letter2 == "A") {
                    switch($letter3) {
                        case "U":
                        case "C":
                            return "Asn";
                        case "A":
                        case "G":
                            return "Lys";
                    }
                }
                if ($letter2 == "G") {
                    switch($letter3) {
                        case "U":
                        case "C":
                            return "Ser";
                        case "A":
                        case "G":
                            return "Arg";
                    }
                }
            }

            if ($letter1 == "G") {
                if ($letter2 == "U") {
                    return "Val";
                }
                if ($letter2 == "C") {
                    return "Ala";
                }
                if ($letter2 == "A") {
                    switch($letter3) {
                        case "U":
                        case "C":
                            return "Asp";
                        case "A":
                        case "G":
                            return "Glu";
                    }
                }
                if ($letter2 == "G") {
                    return "Gly";
                }
            }
        } elseif ($format == 1) { 
            if ($letter1 == "U") {
                if ($letter2 == "U") {
                    switch($letter3) {
                        case "U":
                        case "C":
                            return "F";
                        case "A":
                        case "G":
                            return "L";
                    }
                }
                if ($letter2 == "C") {
                    return "S";
                }
                if ($letter2 == "A") {
                    switch($letter3) {
                        case "U":
                        case "C":
                            return "Y";
                        case "A":
                        case "G":
                            return "*";
                    }
                }
                if ($letter2 == "G") {
                    switch($letter3) {
                        case "U":
                        case "C":
                            return "C";
                        case "A":
                            return "*";
                        case "G":
                            return "W";
                            
                    }
                }
            }
            if ($letter1 == "C") { 
                if ($letter2 == "U") {
                    return "L";
                }
                if ($letter2 == "C") {
                    return "P";
                }
                if ($letter2 == "A") {
                    switch($letter3) {
                        case "U":
                        case "C":
                            return "H";
                        case "A":
                        default:
                            return "Q";
                    }
                }
                if ($letter2 == "G") {
                    return "R";
                }
            }
            if ($letter1 == "A") {
                if ($letter2 == "U") {
                    if ($letter3 == "G") { 
                        return "M"; 
                    } else { 
                        return "I"; 
                    }
                }
                if ($letter2 == "C") {
                    return "T";
                }
                if ($letter2 == "A") {
                    switch($letter3) {
                        case "U":
                        case "C":
                            return "N";
                        case "A":
                        case "G":
                            return "K";
                    }
                }
                if ($letter2 == "G") {
                    switch($letter3) {
                        case "U":
                        case "C":
                            return "S";
                        case "A":
                        case "G":
                            return "R";
                    }
                }
            }

            if ($letter1 == "G") {
                if ($letter2 == "U") {
                    return "V";
                }
                if ($letter2 == "C") {
                    return "A";
                }
                if ($letter2 == "A") {
                    switch($letter3) {
                        case "U":
                        case "C":
                            return "D";
                        case "A":
                        case "G":
                            return "E";
                    }
                }
                if ($letter2 == "G") {
                    return "G";
                }
            }
        }
        return "X";
    }


    /**
     * 
     * @param type $start
     * @param type $count
     * @return type
     */
    function trunc($start, $count)
    {
        return substr($this->sequence, $start, $count);
    }


    /**
     * Definition of terms:
     * MIRROR: The equivalent of a string palindrome in programming terms.
     * Comes in two varieties -- ODD-LENGTH and EVEN-LENGTH.
     * The strict biological definition of mirrors are EVEN-LENGTH only.
     * MIRROR SEQUENCE: seq1-[X]-seq2, where X is an optional nucleotide base (A, G, C, or T).
     * Seq1 and Seq2 are called the complementary sequences or halves.
     * For our purposes, we shall call [X] as the "bridge".
     * @param type $string
     * @return boolean
     */
    function is_mirror($string = "")
    {
        if (strlen($string) == 0) {
            $string = $this->sequence;
        }
        if ($string == strrev($string)) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Returns 3D assoc array: ( [2] => ( ("AA", 3), ("GG", 7) ), [4] => ( ("GAAG", 16) ) )
     * @param type $haystack
     * @param type $pallen1
     * @param type $pallen2
     * @param type $options
     * @return boolean
     */
    function find_mirror($haystack, $pallen1, $pallen2 = "", $options = "E")
    {
        $haylen = strlen($haystack);
        if ($haylen == 0) {
            $haystack = $this->sequence;
            $haylen = strlen($haystack);
            if ($haylen == 0) {
                return false;
            }
        }
        if (!isset($pallen1)) {
            return false;
        }
        if ($pallen1 < 2) {
            return false;
        }
        if ($pallen1 > $haylen) {
            return false;
        }
        if (gettype($pallen1) != "integer") {
            return false;
        }
        // if third parameter (representing upper palindrome length) is missing
        if ((gettype($pallen2) == "string") && ($pallen2 == "")) {
            $pallen2 = $pallen1;
        } elseif (gettype($pallen2) != "integer") {
            return false;
        } elseif ($pallen2 < $pallen1) {
            return false;
        }
        $options = strtoupper($options);
        if (($options != "E") && ($options != "O") && ($options != "A")) {
            return false;
        }

        $outer_r = array();
        for($currlen = $pallen1; $currlen <= $pallen2; $currlen++) {
            if (($options == "E") && (is_odd($currlen) == TRUE)) {
                continue;
            }
            if (($options == "O") && (is_even($currlen) == TRUE)) {
                continue;
            }
            $string_count = $haylen - $currlen + 1;
            $middle_r = array();
            for($j = 0; $j < $string_count; $j++) {
                $string = substr($haystack, $j, $currlen);
                if ($this->is_mirror($string)) {
                    $inner_r = array($string, $j);
                    $middle_r[] = $inner_r;
                }
            }
            if (count($middle_r) > 0) {
                $outer_r[$currlen] = $middle_r;
            }
        }
        return $outer_r;
    }


    /**
     * For mirror repeats, we allow strings with both ODD and EVEN lengths.
     * @param type $string
     * @return boolean
     */
    function is_palindrome($string = "")
    {
        if (strlen($string) == 0) {
            $string = $this->sequence;
        }
        // By definition, odd-lengthed strings cannot be a palindrome.
        if (is_odd(strlen($string))) {
            return false;
        }
        $half1 = halfstr($string, 0);
        $half2 = halfstr($string, 1);
        if ($half1 == @revcomp($half2)) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Returns a two-dimensional array containing palindromic substrings found in a sequence,
     * and their location, in terms of zero-based indices.  E.g. ( ("ATGttCAT", 2), ("ATGccccccCAT", 18), ... )
     * CASES:
            1) seqlen is not set, pallen is not set. - return FALSE (function error)
            2) seqlen is set, pallen is set.
            3) seqlen is set, pallen is not set.
            4) seqlen is not set, pallen is set.
     * @param type $haystack
     * @param type $seqlen
     * @param type $pallen
     * @return boolean|array|int
     */
    function find_palindrome($haystack, $seqlen = "", $pallen = "")
    {
        // CASE 1) seqlen is not set, pallen is not set. - return FALSE (function error)
        if (is_blankstr($seqlen) and is_blankstr($pallen)) {
            return FALSE;
        }

        // CASE 2) seqlen is set, pallen is set.
        if (!(is_blankstr($seqlen)) and !(is_blankstr($pallen))) {
            $haylen = strlen($haystack);
            $string_count = $haylen - $seqlen + 1;
            $outer_r = array();
            for($j = 0; $j < $string_count; $j++) {
                $string = substr($haystack, $j, $seqlen);
                $halfstr_count = (int) (strlen($haystack)/2);
                $palstring1 = substr($string, 0, $pallen);
                $palstring2 = right($string, $pallen);
                if ($palstring1 == revcomp($palstring2, "DNA")) {
                    $outer_r[] = array($string, $j);
                }
            }
            return $outer_r;
        }

        // CASE 3) seqlen is set, pallen is not set.
        elseif (!(is_blankstr($seqlen)) and is_blankstr($pallen)) {
            $haylen = strlen($haystack);
            $string_count = $haylen - $seqlen + 1;
            $outer_r = array();
            for($j = 0; $j < $string_count; $j++) {
                $string = substr($haystack, $j, $seqlen);
                $halfstr_count = (int) (strlen($haystack)/2);
                $palstring = "";
                for($k = 0; $k < $halfstr_count; $k++) {
                    $let1 = substr($string, $k, 1);
                    $let2 = substr($string, strlen($string)-1-$k, 1);
                    if ($let1 == complement($let2, "DNA")) {
                        $palstring .= $let1;
                    } else {
                        break;
                    }
                }
                if (strlen($palstring) >= 3) {
                    $inner_r = array($string, $j);
                    $outer_r[] = $inner_r;
                }
            }
            return $outer_r;
        }

        // CASE 4) seqlen is not set, pallen is set.
        elseif (is_blankstr($seqlen) and !(is_blankstr($pallen))) { 
            $haylen = strlen($haystack);
            $string_count = ($haylen - $pallen + 1) - $pallen;
            $middle_r = array();
            $outer_r = array();
            $newseq = new seq();

            for($j = 0; $j < $string_count; $j++) {
                $whole = substr($haystack, $j);
                $head = substr($whole, 0, $pallen);
                $tail = substr($whole, $pallen);
                $tail_len = strlen($tail);
                $needle = complement(strrev($head), "DNA");
                $newseq->sequence = $tail;
                $pos_r = $newseq->patposo($needle, "I");
                if (count($pos_r) == 0) {
                    continue;
                }
                foreach($pos_r as $posidx) {
                    // Output: ( ("ATGttCAT", 2), ("ATGccccccCAT", 18), ... )
                    $seqstr = substr($whole, 0, $posidx + 2*$pallen);
                    $inner_r = array($seqstr, $j);
                    array_push($outer_r, $inner_r);
                }
            }
        }
        return $outer_r;
    }
}