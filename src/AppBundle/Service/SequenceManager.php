<?php
/**
 * @author Amélie DUVERNET akka Amelaye
 * Inspired by BioPHP's project biophp.org
 * Created 11 february 2019
 * Last modified 2nd October 2019
 */
namespace AppBundle\Service;

use AppBundle\Bioapi\Bioapi;
use AppBundle\Entity\Sequence;
use AppBundle\Traits\SequenceTrait;

/**
 * Class SequenceManager - Functions for sequences
 * We use this class to manipulate Sequence() elements, most of the time taken from a database instance.
 * @package AppBundle\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class SequenceManager
{
    use SequenceTrait;

    /**
     * @var Sequence
     */
    private $sequence;

    /**
     * @var Bioapi
     */
    private $bioapi;
    
    /**
     * Constructor
     * @param   array   $aChemicalGroups
     * @param   Bioapi  $bioapi
     */
    public function __construct($aChemicalGroups, Bioapi $bioapi) {
        $this->aChemicalGroups  = $aChemicalGroups;
        $this->bioapi           = $bioapi;
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
     * Returns a string representing the genetic complement of a sequence.
     * @param   string    $sMoltypeUnfrmtd      The type of molecule we are dealing with. If omitted,
     * we work with "DNA" by default.
     * @return  string                          A string which is the genetic complement of the input string.
     * @throws  \Exception
     */
    public function complement($sMoltypeUnfrmtd)
    {
        try {
            $sComplement = "";
            $sSequence = $this->sequence->getSequence();

            if (!isset($sMoltypeUnfrmtd)) {
                $sMoltypeUnfrmtd = (null !== $this->sequence->getMoltype()) ? $this->sequence->getMoltype() : "DNA";
            }

            if (strtoupper($sMoltypeUnfrmtd) == "DNA") {
                $aComplements = $this->bioapi->getDNAComplement();
            } elseif (strtoupper($sMoltypeUnfrmtd) == "RNA") {
                $aComplements = $this->bioapi->getRNAComplement();
            }

            $iSeqLength = strlen($sSequence);
            for($i = 0; $i < $iSeqLength; $i++) {
                $sAmino = substr($sSequence, $i, 1);
                $sComplement .= $aComplements[$sAmino];
            }
            return $sComplement;
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }

    /**
     * Returns one of the two palindromic "halves" of a palindromic string.
     * @param   int     $iIndex     Pass 0 to get he first palindromic half, pass any other number (e.g. 1)
     * to get the second palindromic half.
     * @return  string              A string representing either the first or the second palindromic half of the string.
     * @throws  \Exception
     */
    public function halfSequence($iIndex)
    {
        try {
            $sSequence = $this->sequence->getSequence();
            if(strlen($sSequence) % 2 != 0) {
                $iCompLength = (int)(strlen($sSequence)/2);
                if ($iIndex == 0) {
                    return substr($sSequence, 0, $iCompLength);
                } else {
                    return substr($sSequence, $iCompLength + 1);
                }
            } else {
                $iCompLength = strlen($sSequence)/2;
                if ($iIndex == 0) {
                    return substr($sSequence, 0, $iCompLength);
                } else {
                    return substr($sSequence, $iCompLength);
                }
            }
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }


    /**
     * Returns the sequence located between two palindromic halves of a palindromic string.
     * Take note that the "bridge" as I call it, is not necessarily a genetic mirror or a palindrome.
     * @param   string    $string     A palindromic or mirror sequence containing the bridge.
     * @return  string
     * @todo : Correct it - does not seems to work :/
     */
    public function getBridge($string)
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
     * with the proper regular expression.
     * @param   string         $sSequence   The sequence
     * @return  string                      An "expanded" string where special metacharacters are replaced by the
     * appropriate regular expression.  For example, an N or X is replaced by the dot (.) meta-character, an R is
     * replaced by [AG], etc.
     * @throws  \Exception
     */
    public function expandNa($sSequence)
    {
        try {
            $aPattern = [
                "/N|X/", "/R/", "/Y/", "/S/", "/W/", "/M/", "/K/", "/B/", "/D/", "/H/", "/R/"
            ];
            $aReplacement = [
                ".", "[AG]", "[CT]", "[GC]", "[AT]", "[AC]", "[TG]", "[CGT]","[AGT]", "[ACT]", "[ACG]"
            ];
            $sExpansion = preg_replace($aPattern, $aReplacement, $sSequence);
            return $sExpansion;
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }


    /**
     * Computes the molecular weight of a particular sequence.
     * @param   string        $sLimit       Upper or Lowerlimit
     * @return  float | bool                The molecular weight, upper or lower limit
     * @throws  \Exception
     */
    public function molwt($sLimit = "upperlimit")
    {
        try {
            $sSequence = $this->sequence->getSequence();
            $sMolType  = $this->sequence->getMoltype();
            $this->cleanSequence($sSequence, $sMolType);

            $iLowLimit   = 0;
            $iUppLimit   = 1;
            $aMwt        = [0, 0];

            $dna_wts = $this->dnaWts($this->bioapi->getDNAWeight());
            $rna_wts = $this->rnaWts($this->bioapi->getRNAWeight());

            $aAllNaWts = ["DNA" => $dna_wts, "RNA" => $rna_wts];
            $na_wts = $aAllNaWts[$sMolType];

            $NA_len = $this->sequence->getSeqlength();

            for($i = 0; $i < $NA_len; $i++) {
                $sNABase = substr($sSequence, $i, 1);
                $aMwt[$iLowLimit] += $na_wts[$sNABase][$iLowLimit];
                $aMwt[$iUppLimit] += $na_wts[$sNABase][$iUppLimit];
            }

            $aWater = $this->bioapi->getWater();

            $aMwt[$iLowLimit] += $aWater["weight"];
            $aMwt[$iUppLimit] += $aWater["weight"];

            if($sLimit == "lowerlimit") {
                $iWlimit = 1;
            }
            else if($sLimit == "upperlimit") {
                $iWlimit = 0;
            }

            return $aMwt[$iWlimit];
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }


    /**
     * Counts the number of codons (a trio of nucleotide base-pairs) in a sequence.
     * @return  int     The number of codons within a sequence, expressed as an non-negative integer.
     * @todo : test after
     */
    public function countCodons()
    {
        $aFeatures = $this->sequence->getFeatures();
        $codstart = (isset($aFeatures["CDS"]["/codon_start"])) ? $aFeatures["CDS"]["/codon_start"] : 1;
        $codcount = (int) (($this->sequence->getSeqlength() - $codstart + 1)/3);
        return $codcount;
    }

    /**
     * Creates a new sequence object with a sequence that is a substring of another.
     * @param   int         $iStart         The position in the original sequence from which we will begin extracting
     * the subsequence; the position is expressed as a zero-based index.
     * @param   int         $iCount         The number of "letters" to include in the subsequence, starting from the
     * position specified by the $start parameter.
     * @return  bool|string     String sequence.
     * @throws  \Exception
     */
    public function subSeq($iStart, $iCount)
    {
        try {
            $sSequence = $this->sequence->getSequence();
            $newSeq = substr($sSequence, $iStart, $iCount);
            return $newSeq;
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }

    /**
     * Returns a two-dimensional associative array where each key is a substring matching a 
     * given pattern, and each value is an array of positional indexes which indicate the location of
     * each occurrence of the substring (needle) in the larger string (haystack). This DOES NOT allow 
     * for pattern overlaps.
     * @param       string      $sPattern        The pattern to locate
     * @param       string      $sOptions        If set to "I", pattern-matching will be case-insensitive.
     * @return      array                        Value example: ( "PAT1" => (0, 17), "PAT2" => (8, 29) )
     * @throws      \Exception
     */
    public function patPos($sPattern, $sOptions = "I")
    {
        try {
            $aOuter = [];
            $aPatFreq = $this->patFreq($sPattern, $sOptions);

            $sSequence = $this->sequence->getSequence();
            if (strtoupper($sOptions) == "I") {
                $sSequence = strtoupper($sSequence);
            }

            foreach($aPatFreq as $skey => $iValue) {
                if ($sOptions == "I") {
                    $skey = strtoupper($skey);
                }
                $aInner = [];
                $iStart = 0;
                for($i = 0; $i < $iValue; $i++) {
                    $iLastPos = strpos($sSequence, $skey, $iStart);
                    array_push($aInner, $iLastPos);
                    $iStart = $iLastPos + strlen($skey);
                }
                $aOuter[$skey] = $aInner;
            }
            return $aOuter;
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }

    /**
     * Similar to patPos() except that this allows for overlapping patterns.
     * Return value format: (index1, index2, ... )
     * Return value sample: ( 0, 8, 17, 29)
     * @param       string     $sPattern        The pattern to locate
     * @param       string     $sOptions        If set to "I", pattern-matching will be case-insensitive.
     * Passing anything else would cause it to be case-sensitive.
     * @param       int        $iCutPos         A non-negative integer specifying where search for the
     * next pattern will resume, relative to the current matching substring.
     * @return      array                       One-dimensional array of the form:
     * ( position1, position2, position3, ... )
     * where position is a zero-based index indicating the location of the substring within the
     * larger sequence.  Thus, if substring is found at the very beginning of sequence, its
     * position is equal to zero (0).
     * @throws      \Exception
     */
    public function patPoso($sPattern, $sOptions = "I", $iCutPos = 1)
    {
        try {
            $aAbsPos = [];
            $sSequence = $this->sequence->getSequence();
            if (strtoupper($sOptions) == "I") {
                $sSequence = strtoupper($sSequence);
            }
            $aPatFreq = $this->patFreq($sPattern, $sOptions);
            $iLastPos = -1 * $iCutPos;
            $iCtr = 0;
            $iRunSumStart = 0;
            while(strlen($sSequence) >= strlen($sPattern)) {
                $iCtr++;
                if ($iCtr == 1) {
                    $iStart = 0;
                } else {
                    $iStart = $iLastPos + $iCutPos;
                }
                $sSequence = substr($sSequence, $iStart);
                $iRunSumStart += $iStart;
                $iMinPos = 999999;
                $bFoundFlag = false;
                foreach($aPatFreq as $key => $value) {
                    $iCurrentPos = strpos($sSequence, $key);
                    if (gettype($iCurrentPos) == "integer") {
                        $bFoundFlag = true;
                        if ($iCurrentPos < $iMinPos) $iMinPos = $iCurrentPos;
                    }
                }
                if (!$bFoundFlag) {
                    break;
                }
                $iCurrentPos = $iMinPos;
                if ($iCtr == 1) {
                    $aAbsPos[] = $iCurrentPos;
                } else {
                    $aAbsPos[] = $iRunSumStart + $iCurrentPos;
                }
                $iLastPos = $iCurrentPos;
            }
            return $aAbsPos;
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }

    /**
     * Returns a one-dimensional associative array where each key is a substring matching the
     * given pattern, and  each value is the frequency count of the substring within the larger string.
     * Return value example: ( "GAATTC" => 3, "ATAT" => 4, ... )
     * @param   string      $sPattern     The pattern to search for and tally.
     * @param   string      $sOptions     If set to "I", pattern-matching and tallying will be case-insensitive.
     * Passing anything else would cause it to be case-sensitive.
     * @return  array                     The function returns an array of the form:
     * ( substring1 => frequency1, substring2 => frequency2, ... )
     * @throws  \Exception
     */
    public function patFreq($sPattern, $sOptions = "I")
    {
        $sMatch = $this->findpattern($sPattern, $sOptions);
        return array_count_values($sMatch[0]);
    }

    /**
     * Returns a one-dimensional array enumerating each occurrence or instance of a given
     * pattern in a larger string or sequence.  This returns the actual substring (that
     * matches the pattern) itself.
     * @example Findpattern returns: ( "GCG", "GCG", "GCG" ) if pattern is exactly "GCG".
     * @param   string      $sPattern      The pattern to search for
     * @param   string      $sOptions      If set to "I", pattern-matching will be case-insensitive. Passing
     * anything else would cause the pattern-matching to be case-sensitive.
     * @return  array                      A one-dimensional array
     * @throws  \Exception
     */
    public function findPattern($sPattern, $sOptions = "I")
    {
        try {
            if (strtoupper($sOptions) == "I") {
                preg_match_all(
                    "/" . $this->expandNa(strtoupper($sPattern)) . "/",
                    strtoupper($this->sequence->getSequence()),
                    $sMatch);
            } else {
                preg_match_all(
                    "/" . $this->expandNa($sPattern) . "/",
                    $this->sequence->getSequence(),
                    $sMatch);
            }
            return $sMatch;
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }

    /**
     * Returns the frequency of a given symbol in the sequence property string. Note that you
     * can pass this a symbol argument which may be not be part of the sequence's alphabet.
     * In this case, the method will simply return zero (0) value.
     * @param   string  $sSymbol    The symbol whose frequency in a sequence we wish to determine.
     * @return  int                 The frequency (number of occurrences) of a particular symbol in a sequence string.
     * @throws  \Exception
     */
    public function symFreq($sSymbol)
    {
        try {
            $iSymTally = count_chars(strtoupper($this->sequence->getSequence()), 1);
            if (!isset($iSymTally[ord($sSymbol)])) {
                return 0;
            } else {
                return $iSymTally[ord($sSymbol)];
            }
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }

    /**
     * Returns the n-th codon in a sequence, with numbering starting at 0.
     * @param   int    $iIndex          The index number of the codon.
     * @param   int    $iReadFrame      The reading frame, which may be 0, 1, or 2 only.  If omitted, this
     * is set to 0 by default.
     * @return  string                  The n-th codon in the sequence.
     */
    public function getCodon($iIndex, $iReadFrame = 0)
    {
        return strtoupper(substr($this->sequence->getSequence(), ($iIndex * 3) + $iReadFrame, 3));
    }

    /**
     * Translates a particular DNA sequence into its protein product sequence, using the given substitution matrix.
     * @param       int         $iReadFrame     The reading frame (0, 1, or 2) to be used in translating a nucleic
     * sequence into a protein.
     * A value of 0 means that the first codon would start at the first "letter" in the sequence,
     * a value of 1 means that the second codon would start the second "letter" in the sequence,
     * and so on.  When omitted, this argument is set to reading frame 0 by default.
     * @param       int         $iFormat        This may be passed the value 1 or 3 and determines the format of the
     * output string.  Passing 1 would cause translate() to output a string made up of single-letter amino acid
     * symbols strung together without any space in between. Passing 3 would output a string made up of three-letter
     * amino acid symbols separated by a space.
     * @return      string      $sResult
     * @example When $format is passed a value of 1, the function returns a string of this format:
     * GAVLISNFYW
     * where each of G, A, V, and the other letters represent a single amino acid residue.
     * @example When $format is passed a value of 3, the function returns a string of this format:
     * Phe Leu Ser Tyr Cys STP
     * where each of Phe, Leu, and the other 3-letter "words" represent a single amino acid residue.
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
            } else {
                return "X";
            }
        }

        $sUpperCodon = strtoupper($sCodon);
        $sFormtdCodon = str_replace("T", "U", $sUpperCodon);
        $sLetter1 = substr($sFormtdCodon, 0, 1);
        $sLetter2 = substr($sFormtdCodon, 1, 1);
        $sLetter3 = substr($sFormtdCodon, 2, 1);

        switch($sLetter1) {
            case "U":
                $sTranslation = $this->uracileLetters($sLetter2, $sLetter3, $iFormat);
                break;
            case "C":
                $sTranslation = $this->cytosineLetters($sLetter2, $sLetter3, $iFormat);
                break;
            case "A":
                $sTranslation = $this->adenineLetters($sLetter2, $sLetter3, $iFormat);
                break;
            case "G":
                $sTranslation = $this->guanineLetters($sLetter2, $sLetter3, $iFormat);
                break;
            default:
                $sTranslation =  "X";
        }
        return $sTranslation;
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
        $aMolecules["adenine"] = (5 * $this->bioapi->getElements()["carbone"])
                + (5 * $this->bioapi->getElements()["nitrate"])
                + (5 * $this->bioapi->getElements()["hydrogene"]);
        $aMolecules["guanine"] = (5 * $this->bioapi->getElements()["carbone"])
                + (5 * $this->bioapi->getElements()["nitrate"])
                + (1 * $this->bioapi->getElements()["oxygene"])
                + (5 * $this->bioapi->getElements()["hydrogene"]);
        $aMolecules["cytosine"] = (4 * $this->bioapi->getElements()["carbone"])
                + (3 * $this->bioapi->getElements()["nitrate"])
                + (1 * $this->bioapi->getElements()["oxygene"])
                + (5 * $this->bioapi->getElements()["hydrogene"]);
        $aMolecules["thymine"] = (5 * $this->bioapi->getElements()["carbone"])
                + (2 * $this->bioapi->getElements()["nitrate"])
                + (2 * $this->bioapi->getElements()["oxygene"])
                + (6 * $this->bioapi->getElements()["hydrogene"]);
        $aMolecules["uracil"] = (4 * $this->bioapi->getElements()["carbone"])
                + (2 * $this->bioapi->getElements()["nitrate"])
                + (2 * $this->bioapi->getElements()["oxygene"])
                + (4 * $this->bioapi->getElements()["hydrogene"]);
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
                (5 * $this->bioapi->getElements()["carbone"])
                + (7 * $this->bioapi->getElements()["oxygene"])
                + (9 * $this->bioapi->getElements()["hydrogene"])
                + (1 * $this->bioapi->getElements()["phosphore"]);
        
        $aMolecules["deoxy_pho"] = 
                (5 * $this->bioapi->getElements()["carbone"])
                + (6 * $this->bioapi->getElements()["oxygene"])
                + (9 * $this->bioapi->getElements()["hydrogene"])
                + (1 * $this->bioapi->getElements()["phosphore"]);
        
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
        $aAminos = $this->bioapi->getAminosOnlyLetters();
        switch($letter2) {
            case "U":
                return $aAminos["Valine"][$format]; // GU*
                break;
            case "C":
                return $aAminos["Alanine"][$format]; // GC*
                break;
            case "A":
                switch($letter3) {
                    case "U":
                    case "C":
                        return $aAminos["Aspartic acid"][$format]; // GAU or GAC
                        break;
                    case "A":
                    case "G":
                        return $aAminos["Glutamic acid"][$format]; // GAA or GAG
                        break;
                }
                break;
            case "G":
                return $aAminos["Glycine"][$format]; // GG*
                break;
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
        $aAminos = $this->bioapi->getAminosOnlyLetters();
        switch($letter2) {
            case "U":
                switch($letter3) {
                    case "G":
                        return $aAminos["Methionine"][$format]; // AUG
                        break;
                    default:
                        return $aAminos["Isoleucine"][$format]; // AU* - G
                        break;
                }
                break;
            case "C":
                return $aAminos["Threonine"][$format]; // AC*
                break;
            case "A":
                switch($letter3) {
                    case "U":
                    case "C":
                        return $aAminos["Asparagine"][$format]; // AAU / AAC
                        break;
                    case "A":
                    case "G":
                        return $aAminos["Lysine"][$format]; // AAA / AAG
                        break;
                }
            break;
            case "G":
                switch($letter3) {
                    case "U":
                    case "C":
                        return $aAminos["Serine"][$format]; // AGU / AGC
                        break;
                    case "A":
                    case "G":
                        return $aAminos["Arginine"][$format]; // AGA / AGG
                        break;
                }
                break;
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
        $aAminos = $this->bioapi->getAminosOnlyLetters();
        switch($letter2) {
            case "U":
                return $aAminos["Leucine"][$format]; // CU*
                break;
            case "C":
                return $aAminos["Proline"][$format]; // CC*
                break;
            case "A":
                switch($letter3) {
                    case "U":
                    case "C":
                        return $aAminos["Histidine"][$format]; // CAU / CAC
                        break;
                    case "A":
                    case "G":
                        return $aAminos["Glutamine"][$format]; // CAA / CAG
                        break;
                }
                break;
            case "G":
                return $aAminos["Arginine"][$format]; // CG*
                break;
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
        $aAminos = $this->bioapi->getAminosOnlyLetters();
        switch($letter2) {
            case "U":
                switch($letter3) {
                    case "U":
                    case "C":
                        return $aAminos["Phenylalanine"][$format]; // UUU / UUC
                        break;
                    case "A":
                    case "G":
                        return $aAminos["Leucine"][$format]; // UUA / UUG
                        break;
                }
                break;
            case "C":
                return $aAminos["Serine"][$format]; // UC*
                break;
            case "A":
                switch($letter3) {
                    case "U":
                    case "C":
                        return $aAminos["Tyrosine"][$format]; // UAU / UAC
                        break;
                    case "A":
                    case "G":
                        return $aAminos["STOP"][$format]; // UAA / UAG
                        break;
                }
                break;
            case "G":
                switch($letter3) {
                    case "U":
                    case "C":
                        return $aAminos["Cysteine"][$format]; // UGU / UGC
                        break;
                    case "A":
                        return $aAminos["STOP"][$format]; // UGA
                        break;
                    case "G":
                        return $aAminos["Tryptophan"][$format]; // UGG
                        break;
                }
                break;
        }
    }

    /**
     * @param $aDnaWeightsTemp
     * @return array
     */
    private function dnaWts($aDnaWeightsTemp)
    {
        $aDnaWeights = [
            'A' => [$aDnaWeightsTemp["A"], $aDnaWeightsTemp["A"]],  // Adenine
            'C' => [$aDnaWeightsTemp["C"], $aDnaWeightsTemp["C"]],  // Cytosine
            'G' => [$aDnaWeightsTemp["G"], $aDnaWeightsTemp["G"]],  // Guanine
            'T' => [$aDnaWeightsTemp["T"], $aDnaWeightsTemp["T"]],  // Thymine
            'M' => [$aDnaWeightsTemp["C"], $aDnaWeightsTemp["A"]],  // A or C
            'R' => [$aDnaWeightsTemp["A"], $aDnaWeightsTemp["G"]],  // A or G
            'W' => [$aDnaWeightsTemp["T"], $aDnaWeightsTemp["A"]],  // A or T
            'S' => [$aDnaWeightsTemp["C"], $aDnaWeightsTemp["G"]],  // C or G
            'Y' => [$aDnaWeightsTemp["C"], $aDnaWeightsTemp["T"]],  // C or T
            'K' => [$aDnaWeightsTemp["T"], $aDnaWeightsTemp["G"]],  // G or T
            'V' => [$aDnaWeightsTemp["C"], $aDnaWeightsTemp["G"]],  // A or C or G
            'H' => [$aDnaWeightsTemp["C"], $aDnaWeightsTemp["A"]],  // A or C or T
            'D' => [$aDnaWeightsTemp["T"], $aDnaWeightsTemp["G"]],  // A or G or T
            'B' => [$aDnaWeightsTemp["C"], $aDnaWeightsTemp["G"]],  // C or G or T
            'X' => [$aDnaWeightsTemp["C"], $aDnaWeightsTemp["G"]],  // G, A, T or C
            'N' => [$aDnaWeightsTemp["C"], $aDnaWeightsTemp["G"]]   // G, A, T or C
        ];
        return $aDnaWeights;
    }

    /**
     * @param $aRnaWeightsTemp
     * @return array
     */
    private function rnaWts($aRnaWeightsTemp)
    {
        $aRnaWeights = [
            'A' => [$aRnaWeightsTemp["A"], $aRnaWeightsTemp["A"]],  // Adenine
            'C' => [$aRnaWeightsTemp["C"], $aRnaWeightsTemp["C"]],  // Cytosine
            'G' => [$aRnaWeightsTemp["G"], $aRnaWeightsTemp["G"]],  // Guanine
            'U' => [$aRnaWeightsTemp["U"], $aRnaWeightsTemp["U"]],  // Uracil
            'M' => [$aRnaWeightsTemp["C"], $aRnaWeightsTemp["A"]],  // A or C
            'R' => [$aRnaWeightsTemp["A"], $aRnaWeightsTemp["G"]],  // A or G
            'W' => [$aRnaWeightsTemp["U"], $aRnaWeightsTemp["A"]],  // A or U
            'S' => [$aRnaWeightsTemp["C"], $aRnaWeightsTemp["G"]],  // C or G
            'Y' => [$aRnaWeightsTemp["C"], $aRnaWeightsTemp["U"]],  // C or U
            'K' => [$aRnaWeightsTemp["U"], $aRnaWeightsTemp["G"]],  // G or U
            'V' => [$aRnaWeightsTemp["C"], $aRnaWeightsTemp["G"]],  // A or C or G
            'H' => [$aRnaWeightsTemp["C"], $aRnaWeightsTemp["A"]],  // A or C or U
            'D' => [$aRnaWeightsTemp["U"], $aRnaWeightsTemp["G"]],  // A or G or U
            'B' => [$aRnaWeightsTemp["C"], $aRnaWeightsTemp["G"]],  // C or G or U
            'X' => [$aRnaWeightsTemp["C"], $aRnaWeightsTemp["G"]],  // G, A, U or C
            'N' => [$aRnaWeightsTemp["C"], $aRnaWeightsTemp["G"]]   // G, A, U or C
        ];
        
        return $aRnaWeights;
    }
}
