<?php
/**
 * SeqMatch managing
 * Freely inspired by BioPHP's project biophp.org
 * Created 20 january 2020
 * Last modified 18 january 2020
 */
namespace AppBundle\Domain\Sequence\Interfaces;

use AppBundle\Domain\Sequence\Entity\SubMatrix;

/**
 * Class SequenceMatchManager - This class represents the results of performing sequence analysis and matching
 * on two or more sequences.
 * @package AppBundle\Domain\Sequence\Interfaces
 */
interface SequenceMatchInterface
{
    /**
     * @param SubMatrix $subMatrix
     */
    public function setSubMatrix(SubMatrix $subMatrix);

    /**
     * Compares two letters $let1 and $let2 and returns another letter
     * indicating if the two were exact matches, partial matches, or non-matches.
     * @param       string      $sLetter1     The first amino acid residue symbol to match.
     * @param       string      $sLetter2     The second amino acid residue symbol to match.
     * @param       array       $aMatrix      The substitution matrix to use in matching.
     * @param       string      $sEqual       The character symbol to return if $let1 and $let2 are exact matches.
     * @param       string      $sPartial     The character symbol to return if $let1 and $let2 are partial matches.
     * @param       string      $sNomatch     The character symbol to return if $let1 and $let2 are totally mismatched.
     * @return      string      A character symbol which indicates if the two residues are exact, partial, or
     * negative matches.
     * @throws      \Exception
     */
    public function compareLetter(
        string $sLetter1, string $sLetter2, array $aMatrix = null,
        string $sEqual = null, string $sPartial = "+", string $sNomatch = ".") : string;

    /**
     * Computes the Hamming Distance between two strings or Seq objects
     * of equal length.  For more information, consult the technical reference.
     * @param   string      $sSequence1     The first sequence
     * @param   string      $sSequence2     The second sequence
     * @return  int         The hamming distance between the two strings or sequences, defined to be the number of
     * "mismatching" characters found in corresponding positions in the two strings.
     * @throws  \Exception
     */
    public function hamdist(string $sSequence1, string $sSequence2) : int;

    /**
     * Computes the Levenshtein Distance between two sequences
     * with equal/unequal lengths.  You can pass custom values for cost of insertion,
     * replacement, and deletion.  If you don't pass any, they are assumed to be 1.
     * For more information, see technical reference.
     * @param   string  $sSequence1     The first sequence, with a length not exceeding 255 symbols.
     * @param   string  $sSequence2     The second string or sequence, with a length not exceeding 255 symbols.
     * @param   int     $iCostInser     The cost or weight of an insertion operation.
     * @param   int     $iCostRepl      The cost or weight of a replacement operation.
     * @param   int     $iCostDel       The cost or weight of a deletion operation.
     * @return  int     The Levenshtein Distance between two strings, defined to be the number of insertion,
     * deletion, or replacement operations that must be performed on the strings before they
     * can become identical.
     * @throws  \Exception
     */
    public function levdist(
        string $sSequence1, string $sSequence2,
        int $iCostInser = 1, int $iCostRepl = 1, int $iCostDel = 1) : int;

    /**
     * Extended version of levdist() which accepts strings with length greater than 255 but not to exceed 1024 .
     * The only drawback to xlevdist is that the cost of insertion, deletion, and replacement
     * is fixed to 1.  I have yet to find a way to allow custom values for these.
     * @param   string      $sSequence1     The first sequence, with a length not exceeding 1024 symbols.
     * @param   string      $sSequence2     The second string or sequence, with a length not exceeding 1024 symbols.
     * @return  int         The Levenshtein Distance between two strings, as defined in levdist().
     * @throws  \Exception
     */
    public function xlevdist(string $sSequence1, string $sSequence2) : int;

    /**
     * This method accepts two sequence strings (not objects) of equal length,
     * and returns a sequence match result string, according to the following rules:
     * If there is an exact match, return the amino acid symbol.
     * If there is a partial match, return a plus sign.
     * If there is no match, return a whitespace character.
     * @param   string      $sSequence1     The first of two sequences being compared.
     * @param   string      $sSequence2     The second of two sequences being compared.
     * @param   array       $aMatrix        An array specifying valid symbol substitution and equivalence rules.
     * @param   string      $sEqual         The symbol to output if the symbol in the first sequence is
     * exactly the same as the corresponding symbol in the second sequence.
     * @param   string      $sPartial       The symbol to output if the symbol in the first sequence is
     * equivalent but not identical to the corresponding symbol in the second sequence.
     * @param   string      $sNonmatch      The symbol to output if the symbol in the first sequence is
     * neither identical nor equivalent to the corresponding symbol in the second sequence.
     * @return  string      A string which indicates where exact, partial and no matches occur between the first
     * and second sequences being compared.
     * @throws  \Exception
     */
    public function match(
        string $sSequence1, string $sSequence2, array $aMatrix = null,
        string $sEqual = null, string $sPartial = "+", string $sNonmatch = ".") : string;

    /**
     * We abbreviate substitution matrix to "submatrix".  Each element in a submatrix is an array of
     * symbols that are considered "partial matches" of each other.
     * Default submatrix:
     * ( ('G','A','V','L','I'), ('S','T'), ('N','Q'), ('F','Y','W'), ('C', 'M'), ('P'), ('D','E'), ('K','R','H'),
     * ('*'), ('X') )
     * 1) Check if both $iLet1 and $let2 appear in the first element (G,A,V,L,I) of the substitution matrix.
     * 2) If they are, you've found a "hit", and $let1 and $let2 are partial matches.  Return a TRUE value.
     * If they are not, then go to the next element in the substitution matrix.
     * Repeat steps 1 and 2 until you reach a submatrix element where both $let1 and $let2 appear, or
     * until the last element in the submatrix has been checked.
     * 3) If you reach the last submatrix element without a "hit", return a FALSE value.
     * @param   string      $aLet1       The first amino acid residue.
     * @param   string      $aLet2       The second amino acid residue.
     * @param   array       $aMatrix     The substitution matrix to use for determining partial matches.
     * @return  bool        TRUE if the two symbols belong to the same chemical group, FALSE otherwise.
     */
    public function partialMatch(string $aLet1, string $aLet2, array $aMatrix) : bool;
}