<?php
/**
 * Sequences Managing
 * @author Amélie DUVERNET akka Amelaye
 * Inspired by BioPHP's project biophp.org
 * Created 11 february 2019
 * Last modified 14 february 2019
 */
namespace AppBundle\Service;

use AppBundle\Entity\Sequence;
use AppBundle\Entity\Protein;

class SequenceManager
{
    private $aDnaComplements;
    private $aRnaComplements;
    private $aElements;
    private $aChemicalGroups;
    private $aCodons;
    private $sequence;
    
    /**
     * Constructor
     * @param array $aDnaComplements
     * @param array $aRnaComplements
     * @param array $aElements
     * @param array $aChemicalGroups
     * @param array $aCodons
     */
    public function __construct(
        array $aDnaComplements = [],
        array $aRnaComplements = [],
        array $aElements = [],
        array $aChemicalGroups = [],
        array $aCodons = []
    ) {
        $this->aDnaComplements  = $aDnaComplements;
        $this->aRnaComplements  = $aRnaComplements;
        $this->aElements        = $aElements;
        $this->aChemicalGroups  = $aChemicalGroups;
        $this->aCodons          = $aCodons;
    }
    
    /**
     * Injection Sequence
     * @param Sequence $oSequence
     */
    public function setSequence($oSequence)
    {
        $this->sequence = $oSequence;
    }
    /**
     * Gets the genetic complement of a DNA or RNA sequence.
     * @param   Sequence  $seq (or array ?)
     * @param   string    $sMoltypeUnfrmtd
     * @return  string
     */
    public function complement($seq, $sMoltypeUnfrmtd)
    {
        if (!isset($sMoltypeUnfrmtd)) {
            $sMoltypeUnfrmtd = (null !== $this->sequence->getMoltype()) ? $this->sequence->getMoltype() : "DNA";
        }

        $sMoltype = strtoupper($sMoltypeUnfrmtd);
        if ($sMoltype == "DNA") {
            $aComplements = $this->aDnaComplements;
        } elseif ($sMoltype == "RNA") {
            $aComplements = $this->aRnaComplements;
        }
        $seqlen = strlen($seq);
        $compseq = "";
        for($i = 0; $i < $seqlen; $i++) {
            $symbol = substr($seq, $i, 1);
            $compseq .= $aComplements[$symbol];
        }
        return $compseq;
    }


    /**
     * First gets the complement of a DNA or RNA sequence, and then returns it in reverse order.
     * @param type $seq
     * @param type $moltype
     * @return type
     */
    public function revcomp($seq, $moltype)
    {
        return strrev($this->complement($seq, $moltype));
    }


    /**
     * Returns one of the two palindromic "halves" of a palindromic string. 
     * @param type $string
     * @param type $no
     * @return type
     */
    public function halfstr($string, $no)
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
    public function get_bridge($string)
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
    public function expand_na($string)
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
    public function molwt()
    {
        // Check if characters outside our 20-letter amino alphabet is included in the sequence.
        if ($this->sequence->getMoltype() == "DNA") {
            preg_match_all("/[^ACGTMRWSYKVHDBXN]/", $this->sequence->getSequence(), $match);
            // If there are unknown characters, then do not compute molwt and instead return FALSE.
            if (count($match[0]) > 0) {
                return FALSE;
            }
        } elseif ($this->sequence->getMoltype() == "RNA") {
            preg_match_all("/[^ACGUMRWSYKVHDBXN]/", $this->sequence->getSequence(), $match);
            // If there are unknown characters, then do not compute molwt and instead return FALSE.
            if (count($match[0]) > 0) {
                return FALSE;
            }
        } elseif ($this->sequence->getMoltype() == "PROTEIN") { // sequence is a protein, so invoke the Protein class' molwt() method.       
            $prot = new Protein();
            $prot->setSequence($this->sequence->getSequence());
            return $prot->molwt();
        } else {
            return FALSE; // return FALSE when encountering unknown molecule types		
        }

        $lowerlimit = 0;
        $upperlimit = 1;

        $aMolecules = $this->getTotalmolecules(); // Array with ATGCU nb molecules
        $aPho       = $this->getPho();

        // the following are single strand molecular weights / base
        $rna_A_wt = $aMolecules["adenine"] + $aPho["ribo_pho"] - $this->aElements["water"];
        $rna_C_wt = $aMolecules["cytosine"] + $aPho["ribo_pho"] - $this->aElements["water"];
        $rna_G_wt = $aMolecules["guanine"] + $aPho["ribo_pho"] - $this->aElements["water"];
        $rna_U_wt = $aMolecules["uracil"] + $aPho["ribo_pho"] - $this->aElements["water"];

        $dna_A_wt = $aMolecules["adenine"] + $aPho["deoxy_pho"] - $this->aElements["water"];
        $dna_C_wt = $aMolecules["cytosine"] + $aPho["deoxy_pho"] - $this->aElements["water"];
        $dna_G_wt = $aMolecules["guanine"] + $aPho["deoxy_pho"] - $this->aElements["water"];
        $dna_T_wt = $aMolecules["thymine"] + $aPho["deoxy_pho"] - $this->aElements["water"];

        $dna_wts = $this->dnaWts($dna_A_wt, $dna_C_wt, $dna_G_wt, $dna_T_wt);
        $rna_wts = $this->rnaWts($rna_A_wt, $rna_C_wt, $rna_G_wt, $rna_U_wt);

        $all_na_wts = array("DNA" => $dna_wts, "RNA" => $rna_wts);
        $na_wts = $all_na_wts[$this->sequence->getMoltype()];

        $weight_lower_bound += $this->aElements["water"];
        $weight_upper_bound += $this->aElements["water"];

        $mwt = array(0, 0);
        $NA_len = $this->seqlen();
        for($i = 0; $i < $NA_len; $i++) {
            $NA_base = substr($this->sequence->getSequence(), $i, 1);
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
    public function count_codons()
    {
        $aFeatures = $this->sequence->getFeatures();
        $codstart = (isset($aFeatures["CDS"]["/codon_start"])) ? $aFeatures["CDS"]["/codon_start"] : 1;
        $codcount = (int) (($this->seqlen() - $codstart + 1)/3);
        return $codcount;
    }


    /**
     * @param int $start
     * @param int $count
     * @return Sequence
     */
    public function subseq($start, $count)
    {
        $newseq = new Sequence();
        $newseq->setSequence(substr($this->sequence->getSequence(), $start, $count));
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
    public function patpos($pattern, $options = "I")
    {
        $outer = array();
        $pf = $this->patfreq($pattern, $options);
        $haystack = $this->sequence->getSequence();
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
    public function patposo($pattern, $options = "I", $cutpos = 1)
    {
        $outer = array();
        $haystack = $this->sequence->getSequence();
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
    public function patfreq($pattern, $options = "I")
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
    public function findpattern($pattern, $options = "I")
    {
        if (firstChar($pattern) == "_") {
            $pattern = getpattern($pattern);
        }

        if (strtoupper($options) == "I") {
            preg_match_all("/" . expand_na(strtoupper($pattern)) . "/", strtoupper($this->sequence->getSequence()), $match);
        } else {
            preg_match_all("/" . expand_na($pattern) . "/", $this->sequence->getSequence(), $match);
        }
        return $match;
    }


    /**
     * 
     * @return int
     */
    public function seqlen()
    {
        return strlen($this->sequence->getSequence());
    }


    /**
     * Note legacy
     *  // Apr 10, 2003 - This now returns 0 instead of NULL when
     *  // $symbol is not found.  0 is the preferred return value.
     * @param   string $sSymbol
     * @return  int
     */
    public function symfreq($sSymbol)
    {
        $symtally = count_chars(strtoupper($this->sequence->getSequence()), 1);
        if (is_null($symtally[ord($sSymbol)])) {
            return 0;
        } else {
            return $symtally[ord($sSymbol)];
        }
    }


    /**
     * 
     * @param   int    $iIndex
     * @param   int    $iReadFrame
     * @return  string
     */
    public function getCodon($iIndex, $iReadFrame = 0)
    {
        return strtoupper(substr($this->sequence->getSequence(), ($iIndex * 3) + $iReadFrame, 3));
    }


    /**
     *
     * @param   int $iReadFrame
     * @param   int $iFormat
     * @return  string  $sResult
     * @throws \Exception
     */
    public function translate($iReadFrame = 0, $iFormat = 3)
    {
        $iCodonIndex = 0;
        $sResult = "";
        while(1) {
            $sCodon = $this->getCodon($iCodonIndex, $iReadFrame);
            if ($sCodon == "") {
                break;
            }
            if ($iFormat == 1) {
                $sResult .= $this->translateCodon($sCodon, $iFormat);
            } elseif ($iFormat == 3) {
                $sResult .= " " . $this->translateCodon($sCodon, $iFormat);
            } else {
                throw new \Exception("Invalid format parameter");
            }
            $iCodonIndex++;
        }
        return $sResult;
    }


    /**
     * Function charge() accepts a string of amino acids in single-letter format and outputs
     * a string of charges in single-letter format also.  A for acidic, C for basic, and N
     * for neutral.
     * @param   string $sAminoSeq
     * @return  string
     * @throws \Exception
     */
    public function charge($sAminoSeq)
    {
        $sChargedSequence = "";
        $ctr = 0;
        while(1) {
            $sAminoLetter = substr($sAminoSeq, $ctr, 1);
            switch($sAminoLetter) {
                case "":
                    break;
                case "D":
                case "E":
                    $sChargedSequence .= "A";
                    break;
                case "K":
                case "R":
                case "H":
                    $sChargedSequence .= "C";
                    break;
                case "*":
                    $sChargedSequence .= "*";
                    break;
                case "X":
                    $sChargedSequence .= "X";
                    break;
                default:
                    if (substr_count("GAVLISTNQFYWCMP", $sAminoLetter) >= 1) {
                        $sChargedSequence .= "N";
                    } else {
                        throw new \Exception("Invalid amino acid symbol in input sequence.");
                    }
                    break;
            }
            $ctr++;
        }
        return $sChargedSequence;
    }


    /**
     * Chemical groups: L - GAVLI, H - ST, M - NQ, R - FYW, S - CM, I - P, A - DE, C - KRH, * - *, X - X
     * @param   string $sAminoSeq
     * @return  string
     * @throws \Exception
     */
    public function chemicalGroup($sAminoSeq)
    {
        $sChemgrpSeq = "";
        $ctr = 0;
        while(1) {
            $sAminoLetter = substr($sAminoSeq, $ctr, 1);       
            if ($sAminoLetter != "") {
                if(isset($this->aChemicalGroups[$sAminoLetter])) {
                    $sChemgrpSeq .= $this->aChemicalGroups[$sAminoLetter];
                } elseif (substr_count("GAVLI", $sAminoLetter) == 1) {
                    $sChemgrpSeq .= "L";
                } elseif (substr_count("FYW", $sAminoLetter) == 1) {
                    $sChemgrpSeq .= "R";
                } else {
                     throw new \Exception("Invalid amino acid symbol in input sequence.");
                }
            }   
            $ctr++;
        }
        return $sChemgrpSeq;
    }


    /**
     * Tranlates string to RNA codon
     * @param   string $sCodon
     * @param   int $iFormat
     * @return  string
     * @throws \Exception
     * @group Legacy
     */
    public function translateCodon($sCodon, $iFormat = 3)
    {
        if (($iFormat != 3) && ($iFormat != 1)) {
            throw new \Exception("Invalid format parameter.");
        }
        if (strlen($sCodon) < 3) {
            if ($iFormat == 3) {
                return "XXX";
            }
        } else {
            return "X";
        }

        $sUpperCodon = strtoupper($sCodon);
        $sFormtdCodon = ereg_replace("T", "U", $sUpperCodon);
        $sLetter1 = substr($sFormtdCodon, 0, 1);
        $sLetter2 = substr($sFormtdCodon, 1, 1);
        $sLetter3 = substr($sFormtdCodon, 2, 1);

        switch($sLetter1) {
            case "U":
                $this->uracileLetters($sLetter2, $sLetter3, $iFormat);
                break;
            case "C":
                $this->cytosineLetters($sLetter2, $sLetter3, $iFormat);
                break;
            case "A":
                $this->adenineLetters($sLetter2, $sLetter3, $iFormat);
                break;
            case "G":
                $this->guanineLetters($sLetter2, $sLetter3, $iFormat);
                break;
        }        
        return "X";  
    }


    /**
     * 
     * @param   int     $iStart
     * @param   int     $iCount
     * @return  string
     * @group Legacy
     */
    public function trunc($iStart, $iCount)
    {
        return substr($this->sequence->getSequence(), $iStart, $iCount);
    }


    /**
     * Definition of terms:
     * MIRROR: The equivalent of a string palindrome in programming terms.
     * Comes in two varieties -- ODD-LENGTH and EVEN-LENGTH.
     * The strict biological definition of mirrors are EVEN-LENGTH only.
     * MIRROR SEQUENCE: seq1-[X]-seq2, where X is an optional nucleotide base (A, G, C, or T).
     * Seq1 and Seq2 are called the complementary sequences or halves.
     * For our purposes, we shall call [X] as the "bridge".
     * @param   string  $string
     * @return  boolean
     * @group Legacy
     */
    public function is_mirror($string = "")
    {
        if (strlen($string) == 0) {
            $string = $this->sequence->getSequence();
        }
        if ($string == strrev($string)) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Returns 3D assoc array: ( [2] => ( ("AA", 3), ("GG", 7) ), [4] => ( ("GAAG", 16) ) )
     * @param   type $haystack
     * @param   type $pallen1
     * @param   type $pallen2
     * @param   type $options
     * @return  boolean
     * @group Legacy
     */
    public function find_mirror($haystack, $pallen1, $pallen2 = "", $options = "E")
    {
        $haylen = strlen($haystack);
        if ($haylen == 0) {
            $haystack = $this->sequence->getSequence();
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
        if (!is_int($pallen1)) {
            return false;
        }
        // if third parameter (representing upper palindrome length) is missing
        if ((is_string($pallen2)) && ($pallen2 == "")) {
            $pallen2 = $pallen1;
        } elseif (!is_int($pallen2)) {
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
            if (($options == "E") && ($currlen % 2 != 0)) { // odd
                continue;
            }
            if (($options == "O") && ($currlen % 2 == 0)) { // even
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
     * @group Legacy
     */
    public function is_palindrome($string = "")
    {
        if (strlen($string) == 0) {
            $string = $this->sequence->getSequence();
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
     * @group Legacy
     */
    public function find_palindrome($haystack, $seqlen = "", $pallen = "")
    {
        // CASE 1) seqlen is not set, pallen is not set. - return FALSE (function error)
        if (is_blankstr($seqlen) && is_blankstr($pallen)) {
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
            $newseq = new Sequence();

            for($j = 0; $j < $string_count; $j++) {
                $whole = substr($haystack, $j);
                $head = substr($whole, 0, $pallen);
                $tail = substr($whole, $pallen);
                $tail_len = strlen($tail);
                $needle = $this->complement(strrev($head), "DNA");
                $newseq->setSequence($tail);
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


    /**
     * For each nucleotide, finds the number of molecules
     * @return array
     */
    private function getTotalmolecules()
    {
        $aMolecules = [];
        $aMolecules["adenine"] = (5 * $this->aElements["carbone"]) 
                + (5 * $this->aElements["nitrate"]) 
                + (5 * $this->aElements["hydrogene"]);
        $aMolecules["guanine"] = (5 * $this->aElements["carbone"]) 
                + (5 * $this->aElements["nitrate"]) 
                + (1 * $this->aElements["oxygene"]) 
                + (5 * $this->aElements["hydrogene"]);
        $aMolecules["cytosine"] = (4 * $this->aElements["carbone"]) 
                + (3 * $this->aElements["nitrate"]) 
                + (1 * $this->aElements["oxygene"]) 
                + (5 * $this->aElements["hydrogene"]);
        $aMolecules["thymine"] = (5 * $this->aElements["carbone"]) 
                + (2 * $this->aElements["nitrate"]) 
                + (2 * $this->aElements["oxygene"]) 
                + (6 * $this->aElements["hydrogene"]);
        $aMolecules["uracil"] = (4 * $this->aElements["carbone"]) 
                + (2 * $this->aElements["nitrate"]) 
                + (2 * $this->aElements["oxygene"]) 
                + (4 * $this->aElements["hydrogene"]);
        return $aMolecules;
    }


    /**
     * For each component, finds the number of molecules
     * @return array
     */
    private function getPho()
    {
        $aMolecules = [];
        
        $aMolecules["ribo_pho"] = 
                (5 * $this->aElements["carbone"]) 
                + (7 * $this->aElements["oxygene"]) 
                + (9 * $this->aElements["hydrogene"]) 
                + (1 * $this->aElements["phosphore"]);
        
        $aMolecules["deoxy_pho"] = 
                (5 * $this->aElements["carbone"]) 
                + (6 * $this->aElements["oxygene"]) 
                + (9 * $this->aElements["hydrogene"]) 
                + (1 * $this->aElements["phosphore"]);
        
        return $aMolecules;
    }


    /**
     * Codons beginning with G
     * @param   string  $letter2
     * @param   string  $letter3
     * @param   int     $format
     * @return  string
     */
    private function guanineLetters($letter2, $letter3, $format)
    {
       switch($letter2) {
            case "U":
                return $this->aCodons["Valine"][$format]; // GU*
            case "C":
                return $this->aCodons["Alanine"][$format]; // GC*
            case "A":
                switch($letter3) {
                    case "U":
                    case "C":
                        return $this->aCodons["Aspartic_acid"][$format]; // GAU or GAC
                    case "A":
                    case "G":
                        return $this->aCodons["Glutamic_acid"][$format]; // GAA or GAG
                }
            case "G":
                return $this->aCodons["Glycine"][$format]; // GG*
        }
    }


    /**
     * Codons beginning with A
     * @param   string  $letter2
     * @param   string  $letter3
     * @param   int     $format
     * @return  string
     */
    private function adenineLetters($letter2, $letter3, $format)
    {
        switch($letter2) {
            case "U":
                switch($letter3) {
                    case "G":
                        return $this->aCodons["Methionine"][$format]; // AUG
                    default:
                        return $this->aCodons["Isoleucine"][$format]; // AU* - G
                }
            case "C":
                return $this->aCodons["Threonine"][$format]; // AC*
            case "A":
                switch($letter3) {
                case "U":
                case "C":
                    return $this->aCodons["Asparagine"][$format]; // AAU / AAC
                case "A":
                case "G":
                    return $this->aCodons["Lysine"][$format]; // AAA / AAG
            }
            case "G":
                switch($letter3) {
                    case "U":
                    case "C":
                        return $this->aCodons["Serine"][$format]; // AGU / AGC
                    case "A":
                    case "G":
                        return $this->aCodons["Arginine"][$format]; // AGA / AGG
                }
        }
    }


    /**
     * Codons beginning with C
     * @param   string  $letter2
     * @param   string  $letter3
     * @param   int     $format
     * @return  string
     */
    private function cytosineLetters($letter2, $letter3, $format)
    {
        switch($letter2) {
            case "U":
                return $this->aCodons["Leucine"][$format]; // CU*
            case "C":
                return $this->aCodons["Proline"][$format]; // CC*
            case "A":
                switch($letter3) {
                    case "U":
                    case "C":
                        return $this->aCodons["Histidine"][$format]; // CAU / CAC
                    case "A":
                    case "G":
                        return $this->aCodons["Glutamine"][$format]; // CAA / CAG
                }
            case "G":
                return $this->aCodons["Arginine"][$format]; // CG*
        }
    }


    /**
     * Codons beginning with U
     * @param   string    $letter2
     * @param   string    $letter3
     * @param   int       $format
     * @return  string
     */
    private function uracileLetters($letter2, $letter3, $format)
    {
        switch($letter2) {
            case "U":
                switch($letter3) {
                    case "U":
                    case "C":
                        return $this->aCodons["Phenylalanine"][$format]; // UUU / UUC
                    case "A":
                    case "G":
                        return $this->aCodons["Leucine"][$format]; // UUA / UUG
                }
            case "C":
                return $this->aCodons["Serine"][$format]; // UC*
            case "A":
                switch($letter3) {
                    case "U":
                    case "C":
                        return $this->aCodons["Tyrosine"][$format]; // UAU / UAC
                    case "A":
                    case "G":
                        return $this->aCodons["STOP"][$format]; // UAA / UAG
                }
            case "G":
                switch($letter3) {
                    case "U":
                    case "C":
                        return $this->aCodons["Cysteine"][$format]; // UGU / UGC
                    case "A":
                        return $this->aCodons["STOP"][$format]; // UGA
                    case "G":
                        return $this->aCodons["Tryptophan"][$format]; // UGG
                }
        }
    }
    
    private function dnaWts($dna_A_wt, $dna_C_wt, $dna_G_wt, $dna_T_wt)
    {
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
        return $dna_wts;
    }
    
    private function rnaWts($rna_A_wt, $rna_C_wt, $rna_G_wt, $rna_U_wt)
    {
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
        
        return $rna_wts;
    }
}
