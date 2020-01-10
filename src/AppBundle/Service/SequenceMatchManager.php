<?php
/**
 * SeqMatch managing
 * Freely inspired by BioPHP's project biophp.org
 * Created 11 february 2019
 * Last modified 21 december 2019
 */
namespace AppBundle\Service;

use AppBundle\Entity\SubMatrix;
use AppBundle\Interfaces\SequenceMatchInterface;
use AppBundle\Traits\FormatsTrait;

/**
 * Class SequenceMatchManager - This class represents the results of performing sequence analysis and matching
 * on two or more sequences.
 * @package AppBundle\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class SequenceMatchManager implements SequenceMatchInterface
{
    use FormatsTrait;

    /**
     * @var SubMatrix
     */
    private $subMatrix;

    /**
     * @param SubMatrix $subMatrix
     */
    public function setSubMatrix(SubMatrix $subMatrix)
    {
        $this->subMatrix = $subMatrix;
    }

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
        string $sLetter1, string $sLetter2, array $aMatrix = null, string $sEqual = null,
        string $sPartial = "+", string $sNomatch = ".") : string
    {
        try {
            if (!isset($aMatrix)) { // if no custom substitution matrix was provided, use the default.
                $aMatrix = $this->subMatrix->getRules();
            }
            if (!isset($sEqual)) { // if no symbol for exact matches was provided, use the residue symbol.
                $sEqual = $sLetter1;
            }
            if ($sLetter1 == $sLetter2) {
                return $sEqual;
            }
            elseif ($this->partialMatch($sLetter1, $sLetter2, $aMatrix)) {
                return $sPartial;
            }
            else {
                return $sNomatch;
            }
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }

    /**
     * Computes the Hamming Distance between two strings or Seq objects
     * of equal length.  For more information, consult the technical reference.
     * @param   string      $sSequence1     The first sequence
     * @param   string      $sSequence2     The second sequence
     * @return  int         The hamming distance between the two strings or sequences, defined to be the number of
     * "mismatching" characters found in corresponding positions in the two strings.
     * @throws  \Exception
     */
    public function hamdist(string $sSequence1, string $sSequence2) : int
    {
        try {
            // We terminate code execution if the two strings differ in length.
            if (strlen($sSequence1) != strlen($sSequence2)) {
                throw new \Exception("Both sequence must be of the same length ! ");
            }

            $iLength = strlen($sSequence1);
            // Initialize the hamming distance to 0 (no difference between two strings).
            $iDistance = 0;

            // Match the two strings, character by character.  If they are NOT
            // identical, increment $iDistance by 1.
            for($i = 0; $i < $iLength; $i++) {
                $sLet1 = substr($sSequence1, $i, 1);
                $sLet2 = substr($sSequence2, $i, 1);
                if ($sLet1 != $sLet2) {
                    $iDistance++;
                }
            }
            return $iDistance;
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
   }

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
    public function levdist(string $sSequence1, string $sSequence2,
                            int $iCostInser = 1, int $iCostRepl = 1, int $iCostDel = 1) : int
    {
        try {
            // Check the lengths of the two strings.  If they exceed 255 characters, terminate code.
            if (strlen($sSequence1) > 255) {
                throw new \Exception("String length must not exceed 255 characters!");
            }
            if (strlen($sSequence2) > 255) {
                throw new \Exception("String length must not exceed 255 characters!");
            }
            // Compute and return the Levenshtein Distance using PHP's built-in levenshtein() function.
            return levenshtein($sSequence1, $sSequence2, $iCostInser, $iCostRepl, $iCostDel);
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
   }

    /**
     * Extended version of levdist() which accepts strings with length greater than 255 but not to exceed 1024 .
     * The only drawback to xlevdist is that the cost of insertion, deletion, and replacement
     * is fixed to 1.  I have yet to find a way to allow custom values for these.
     * @param   string      $sSequence1     The first sequence, with a length not exceeding 1024 symbols.
     * @param   string      $sSequence2     The second string or sequence, with a length not exceeding 1024 symbols.
     * @return  int         The Levenshtein Distance between two strings, as defined in levdist().
     * @throws  \Exception
     */
    public function xlevdist(string $sSequence1, string $sSequence2) : int
    {
        $iSeqLen1 = strlen($sSequence1);
        $iSeqLen2 = strlen($sSequence2);

        if (($iSeqLen1 > 1024) or ($iSeqLen2 > 1024)) {
            throw new \Exception("String length must not exceed 1024 characters");
        }

        // initialize the array
        $aValues  = [];
        $aTemp    = [];
        $aTemp[0] = 0;

        for($j = 1; $j <= $iSeqLen2; $j++) {
            $aTemp[$j] = 0;
        }

        $aValues[0] = $aTemp;
        for($i = 1; $i <= $iSeqLen1; $i++) {
            $aValues[$i] = $aTemp;
        }

        for($i = 1; $i <= $iSeqLen1; $i++) {
            $sLets = substr($sSequence1, $i-1, 1);
            for($j = 1; $j <= $iSeqLen2; $j++) {
                $sLett = substr($sSequence2, $j-1, 1);
                $iCost = ($sLets == $sLett) ? 0 : 1;

                // "normal" values of $up, $left, and $upleft
                $iUp     = ($j > 1) ? $aValues[$i][$j-1] : 0;
                $iLeft   = ($i > 1) ? $aValues[$i-1][$j] : 0;
                $iUpLeft = (($i > 1) && ($j > 1)) ? $aValues[$i-1][$j-1] : 0;

                if ($i == 1) {
                    $iValue = ($j == 1 || $iCost == 0) ? $iCost : $iUp + 1;
                } else {
                    // if at the first or topmost row, there is no upleft and above.
                    if ($j == 1) {
                        $iValue = ($iCost == 0) ? $iCost : $iLeft + 1;
                    } else {
                        $iValue = $this->getmin($iUp + 1, $iLeft + 1, $iUpLeft + $iCost);
                    }
                }
                $aValues[$i][$j] = $iValue;
            } 
        }
        return $aValues[$iSeqLen1][$iSeqLen2];
    }

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
    public function match(string $sSequence1, string $sSequence2,
                          array $aMatrix = null, string $sEqual = null,
                          string $sPartial = "+", string $sNonmatch = ".") : string
    {
        // if the user chose not to use a custom submatrix, use the default one.
        if (!isset($aMatrix)) {
            $aMatrix = $this->subMatrix->getRules();
        }

        // if the strings differ in length, terminate code execution.
        if (strlen($sSequence1) != strlen($sSequence2)) {
            throw new \Exception("Cannot match sequences with unequal lengths !");
        }

        $sResult = "";
        $iSeqLength = strlen($sSequence1);

        // Match the two strings, character by character.  Each call to compare_letter()
        // function returns a "result character" which is appended to a "result string".
        for($i = 0; $i < $iSeqLength; $i++) {
            $sLet1 = substr($sSequence1, $i, 1);
            $sLet2 = substr($sSequence2, $i, 1);
            $sResult = $sResult . $this->compareLetter($sLet1, $sLet2, $aMatrix, $sEqual, $sPartial, $sNonmatch);
        }

        // Return the result string.  While this line and the line above seems redundant, their
        // presense here actually permits programmers to write more compact code.
        return $sResult;
    }

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
     * @param   string      $sLet1       The first amino acid residue.
     * @param   string      $sLet2       The second amino acid residue.
     * @param   array       $aMatrix     The substitution matrix to use for determining partial matches.
     * @return  bool        TRUE if the two symbols belong to the same chemical group, FALSE otherwise.
     */
    public function partialMatch(string $sLet1, string $sLet2, array $aMatrix) : bool
    {
        if (!isset($aMatrix) == FALSE) {
            $aMatrix = $this->subMatrix->getRules();
        }
        foreach($aMatrix as $aRule) {
            if ((in_array($sLet1, $aRule)) && (in_array($sLet2, $aRule))) {
                return true;
            }
        }
        return false;
    }
}
