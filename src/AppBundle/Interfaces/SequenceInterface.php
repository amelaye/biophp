<?php

namespace AppBundle\Interfaces;

use AppBundle\Entity\Sequence;


interface SequenceInterface
{
    /**
     * Injection Sequence
     * @param Sequence $oSequence
     */
    public function setSequence($oSequence);

    /**
     * Returns a string representing the genetic complement of a sequence.
     * @param string $sMoltypeUnfrmtd The type of molecule we are dealing with. If omitted,
     * we work with "DNA" by default.
     * @return  string                          A string which is the genetic complement of the input string.
     * @throws  \Exception
     */
    public function complement($sMoltypeUnfrmtd);

    /**
     * Returns one of the two palindromic "halves" of a palindromic string.
     * @param int $iIndex Pass 0 to get he first palindromic half, pass any other number (e.g. 1)
     * to get the second palindromic half.
     * @return  string              A string representing either the first or the second palindromic half of the string.
     * @throws  \Exception
     */
    public function halfSequence($iIndex);


    /**
     * Returns the sequence located between two palindromic halves of a palindromic string.
     * Take note that the "bridge" as I call it, is not necessarily a genetic mirror or a palindrome.
     * @param string $string A palindromic or mirror sequence containing the bridge.
     * @return  string
     * @todo : Correct it - does not seems to work :/
     */
    public function getBridge($string);


    /**
     * Returns the expansion of a nucleic acid sequence, replacing special wildcard symbols
     * with the proper regular expression.
     * @param string $sSequence The sequence
     * @return  string                      An "expanded" string where special metacharacters are replaced by the
     * appropriate regular expression.  For example, an N or X is replaced by the dot (.) meta-character, an R is
     * replaced by [AG], etc.
     * @throws  \Exception
     */
    public function expandNa($sSequence);


    /**
     * Computes the molecular weight of a particular sequence.
     * @param string $sLimit Upper or Lowerlimit
     * @return  float | bool                The molecular weight, upper or lower limit
     * @throws  \Exception
     */
    public function molwt($sLimit = "upperlimit");


    /**
     * Counts the number of codons (a trio of nucleotide base-pairs) in a sequence.
     * @return  int     The number of codons within a sequence, expressed as an non-negative integer.
     * @todo : test after
     */
    public function countCodons();

    /**
     * Creates a new sequence object with a sequence that is a substring of another.
     * @param int $iStart The position in the original sequence from which we will begin extracting
     * the subsequence; the position is expressed as a zero-based index.
     * @param int $iCount The number of "letters" to include in the subsequence, starting from the
     * position specified by the $start parameter.
     * @return  bool|string     String sequence.
     * @throws  \Exception
     */
    public function subSeq($iStart, $iCount);

    /**
     * Returns a two-dimensional associative array where each key is a substring matching a
     * given pattern, and each value is an array of positional indexes which indicate the location of
     * each occurrence of the substring (needle) in the larger string (haystack). This DOES NOT allow
     * for pattern overlaps.
     * @param string $sPattern The pattern to locate
     * @param string $sOptions If set to "I", pattern-matching will be case-insensitive.
     * @return      array                        Value example: ( "PAT1" => (0, 17), "PAT2" => (8, 29) )
     * @throws      \Exception
     */
    public function patPos($sPattern, $sOptions = "I");

    /**
     * Similar to patPos() except that this allows for overlapping patterns.
     * Return value format: (index1, index2, ... )
     * Return value sample: ( 0, 8, 17, 29)
     * @param string $sSequence The sequence to analyze
     * @param string $sPattern The pattern to locate
     * @param string $sOptions If set to "I", pattern-matching will be case-insensitive.
     * Passing anything else would cause it to be case-sensitive.
     * @param int $iCutPos A non-negative integer specifying where search for the
     * next pattern will resume, relative to the current matching substring.
     * @return      array                       One-dimensional array of the form:
     * ( position1, position2, position3, ... )
     * where position is a zero-based index indicating the location of the substring within the
     * larger sequence.  Thus, if substring is found at the very beginning of sequence, its
     * position is equal to zero (0).
     * @throws      \Exception
     */
    public function patPoso($sSequence, $sPattern, $sOptions = "I", $iCutPos = 1);

    /**
     * Returns a one-dimensional associative array where each key is a substring matching the
     * given pattern, and  each value is the frequency count of the substring within the larger string.
     * Return value example: ( "GAATTC" => 3, "ATAT" => 4, ... )
     * @param string $sPattern The pattern to search for and tally.
     * @param string $sOptions If set to "I", pattern-matching and tallying will be case-insensitive.
     * Passing anything else would cause it to be case-sensitive.
     * @return  array                     The function returns an array of the form:
     * ( substring1 => frequency1, substring2 => frequency2, ... )
     * @throws  \Exception
     */
    public function patFreq($sPattern, $sOptions = "I");

    /**
     * Returns a one-dimensional array enumerating each occurrence or instance of a given
     * pattern in a larger string or sequence.  This returns the actual substring (that
     * matches the pattern) itself.
     * @param string $sPattern The pattern to search for
     * @param string $sOptions If set to "I", pattern-matching will be case-insensitive. Passing
     * anything else would cause the pattern-matching to be case-sensitive.
     * @return  array                      A one-dimensional array
     * @throws  \Exception
     * @example Findpattern returns: ( "GCG", "GCG", "GCG" ) if pattern is exactly "GCG".
     */
    public function findPattern($sPattern, $sOptions = "I");

    /**
     * Returns the frequency of a given symbol in the sequence property string. Note that you
     * can pass this a symbol argument which may be not be part of the sequence's alphabet.
     * In this case, the method will simply return zero (0) value.
     * @param string $sSymbol The symbol whose frequency in a sequence we wish to determine.
     * @return  int                 The frequency (number of occurrences) of a particular symbol in a sequence string.
     * @throws  \Exception
     */
    public function symFreq($sSymbol);

    /**
     * Returns the n-th codon in a sequence, with numbering starting at 0.
     * @param int $iIndex The index number of the codon.
     * @param int $iReadFrame The reading frame, which may be 0, 1, or 2 only.  If omitted, this
     * is set to 0 by default.
     * @return  string                  The n-th codon in the sequence.
     */
    public function getCodon($iIndex, $iReadFrame = 0);

    /**
     * Translates a particular DNA sequence into its protein product sequence, using the given substitution matrix.
     * @param int $iReadFrame The reading frame (0, 1, or 2) to be used in translating a nucleic
     * sequence into a protein.
     * A value of 0 means that the first codon would start at the first "letter" in the sequence,
     * a value of 1 means that the second codon would start the second "letter" in the sequence,
     * and so on.  When omitted, this argument is set to reading frame 0 by default.
     * @param int $iFormat This may be passed the value 1 or 3 and determines the format of the
     * output string.  Passing 1 would cause translate() to output a string made up of single-letter amino acid
     * symbols strung together without any space in between. Passing 3 would output a string made up of three-letter
     * amino acid symbols separated by a space.
     * @return      string      $sResult
     * @throws \Exception
     * @example When $format is passed a value of 3, the function returns a string of this format:
     * Phe Leu Ser Tyr Cys STP
     * where each of Phe, Leu, and the other 3-letter "words" represent a single amino acid residue.
     * @example When $format is passed a value of 1, the function returns a string of this format:
     * GAVLISNFYW
     * where each of G, A, V, and the other letters represent a single amino acid residue.
     */
    public function translate($iReadFrame = 0, $iFormat = 1);

    /**
     * Translates an amino acid sequence into its equivalent "charge sequence".
     * Function charge() accepts a string of amino acids in single-letter format and outputs
     * a string of charges in single-letter format also.  A for acidic, C for basic, and N
     * for neutral.
     * @param string $sAminoSeq A string representing an amino acid sequence (e.g. GAVLIFYWKRH).
     * If omitted, this is set to the sequence property of the "calling" Seq object. If the
     * latter is not set either, the function returns the boolean value of FALSE.
     * @return  string                      A string where each amino acid "letter" is replaced by A
     * (if amino acid is acidic), C (if amino acid is basic), or N (if amino acid is neutral), e.g. ACNNCCNANCCNA.
     * @throws  \Exception
     */
    public function charge($sAminoSeq);

    /**
     * Returns a string of symbols from an 8-letter alphabet: A, L, M, R, C, H, I, S.
     * Chemical groups: L - GAVLI, H - ST, M - NQ, R - FYW, S - CM, I - P, A - DE, C - KRH, * - *, X - X
     * @param string $sAminoSeq A string representing an amino acid chain (e.g. GAVLI).
     * If omitted, this is set to the sequence property of the "calling" Seq object. If the
     * latter is not set either, the function returns the boolean value of FALSE.
     * @return  string                      A string where each amino acid "letter" is replaced by one of the
     * following: A (acidic group), L (aliphatic group), M (amide group), R (aromatic group),
     * C (basic group), H (hydroxyl), I (iminio group), S (sulfur group).
     * @throws  \Exception
     */
    public function chemicalGroup($sAminoSeq);

    /**
     * Translates a single codon into an amino acid.
     * @param string $sCodon A three-letter nucleic acid sequence (each letter can be A, U, G,
     * or C) which translates into a single amino acid residue.
     * @param int $iFormat This may be passed the value 1 or 3 and determines the format of
     * the output string. When omitted, $format is set to 3 by default.
     * @return  string                  When $format is passed a value of 1, the function returns a single letter.
     * When $format is passed a value of 3, the function returns a string of three letters. The return value
     * represents a single amino acid residue.
     * @throws  \Exception
     */
    public function translateCodon($sCodon, $iFormat = 3);

    /**
     * Returns TRUE if the given sequence or string is a "genetic mirror" which is the same
     * as a "string palindrome", i.e., a sequence that "looks" the same when read backwards.
     * Definition of terms:
     * MIRROR: The equivalent of a string palindrome in programming terms.
     * Comes in two varieties -- ODD-LENGTH and EVEN-LENGTH.
     * The strict biological definition of mirrors are EVEN-LENGTH only.
     * MIRROR SEQUENCE: seq1-[X]-seq2, where X is an optional nucleotide base (A, G, C, or T).
     * Seq1 and Seq2 are called the complementary sequences or halves.
     * For our purposes, we shall call [X] as the "bridge".
     * @param string $sSequence A sequence which we want to test if it is a mirror or not.
     * @return  boolean
     */
    public function isMirror($sSequence = null);

    /**
     * Returns a three-dimensional associative array listing all mirror substrings contained
     * within a given sequence, and their location (expressed as a zero-based index number).
     * @param string $sSequence The sequence which will be searched by the method for any occurrences
     * of mirrors. If omitted, this is set to the sequence property of the current Seq object.
     * @param int $iPallen1 The length of the shortest mirror to look for.
     * @param int $iPallen2 The length of the longest mirror to look for.
     * @param string $sOptions May be "E" or "O" or "A". If "E" is passed, then the method only looks
     * for mirrors with even lengths. If "O" is passed, the method only looks for mirrors with odd
     * lengths.  If "A" is passed, then method looks for all mirrors (odd and even lengths). If
     * omitted, this is set to "E" by default.
     * @return  array | bool            3D assoc array: ( [2] => ( ("AA", 3), ("GG", 7) ), [4] => ( ("GAAG", 16) ) )
     */
    public function findMirror($sSequence, $iPallen1, $iPallen2 = null, $sOptions = "E");

    /**
     * Tests if a given sequence is a "genetic palindrome" (as opposed to a "string
     * palindrome"). A "genetic palindrome" is one where the ends of a sequence are
     * reverse complements of each other.
     * For mirror repeats, we allow strings with both ODD and EVEN lengths.
     * @param string $sSequence A sequence which we want to test if it is a genetic palindrome or not.
     * @return  boolean                  TRUE if the given string is a genetic palindrome, FALSE otherwise.
     */
    public function isPalindrome($sSequence = "");

    /**
     * Returns a two-dimensional array containing palindromic substrings found in a sequence,
     * and their location, in terms of zero-based indices.  E.g. ( ("ATGttCAT", 2), ("ATGccccccCAT", 18), ... )
     * CASES:
     * 1) seqlen is not set, pallen is not set. - return FALSE (function error)
     * 2) seqlen is set, pallen is set.
     * 3) seqlen is set, pallen is not set.
     * 4) seqlen is not set, pallen is set.
     * @param string $sSequence The sequence to be searched by the method for any genetic palindromes.
     * If omitted, this is set to the sequence property of the current Seq object.
     * @param int $iSeqLen The length of the palindromic substring within $sSequence. If omitted,
     * the method searches for palindromes of whatever length.
     * @param string $iPalLen The length of one of two palindromic edges in a palindromic substring
     * within $haystack.
     * @return  boolean|array   A two-dimensional array of the form:
     * ((palindrome1, position1), (palindrome2, position2), ...)
     * @throws  \Exception
     */
    public function findPalindrome($sSequence, $iSeqLen = null, $iPalLen = null);
}
