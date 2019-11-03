<?php
/**
 * SeqMatch managing
 * Freely inspired by BioPHP's project biophp.org
 * Created 11 february 2019
 * Last modified 3 november 2019
 */
namespace AppBundle\Service;

use AppBundle\Entity\Sequence;

/**
 * Class SequenceMatchManager - This class represents the results of performing sequence analysis and matching
 * on two or more sequences.
 * @package AppBundle\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class SequenceMatchManager
{
    /**
     * @var
     */
    private $result;

    /**
     * @var SubMatrix
     */
    private $subMatrix;

    /**
     * SequenceMatchManager constructor.
     * @param SubMatrix $subMatrix
     */
    public function __construct(SubMatrix $subMatrix)
    {
        $this->subMatrix = $subMatrix;
    }

    /**
     * Compares two letters $let1 and $let2 and returns another letter
     * indicating if the two were exact matches, partial matches, or non-matches.
     * @param $let1
     * @param $let2
     * @param $matrix
     * @param $equal
     * @param string $partial
     * @param string $nomatch
     * @return string
     */
    public function compareLetter($let1, $let2, $matrix, $equal, $partial = "+", $nomatch = ".")
    {
        // if no custom substitution matrix was provided, use the default.
        if (!isset($matrix)) {
            $matrix = $this->subMatrix->getRules();
        }
        // if no symbol for exact matches was provided, use the residue symbol.
        if (!isset($equal)) {
            $equal = $let1;
        }
        if ($let1 == $let2) {
            return $equal;
        }
        elseif ($this->partialMatch($let1, $let2, $matrix)) {
            return $partial;
        }
        else {
            return $nomatch;
        }
    }

    /**
     * Computes the Hamming Distance between two strings or Seq objects
     * of equal length.  For more information, consult the technical reference.
     * @param Sequence $seq1
     * @param Sequence $seq2
     * @return int
     * @throws \Exception
     * @group Legacy
     */
    public function hamdist($seq1, $seq2)
    {
        // If $seq1 is a Seq object, we use its sequence property to compute Hamming Distance.
        if (gettype($seq1) == "object") {
            $string1 = $seq1->getSequence();
        } elseif (gettype($seq1) == "string") {
            $string1 = $seq1;
        }

        // If $seq2 is a Seq object, we use its sequence property to compute Hamming Distance.
        if (gettype($seq2) == "object") {
            $string2 = $seq2->getSequence();
        }
        elseif (gettype($seq2) == "string") {
            $string2 = $seq2;
        }

        // We terminate code execution if the two strings differ in length.
        if (strlen($string1) != strlen($string2)) {
            throw new \Exception("Both sequence must be of the same length ! ");
        }

        $len = strlen($string1);
        // Initialize the hamming distance to 0 (no difference between two strings).
        $distance = 0;

        // Match the two strings, character by character.  If they are NOT
        // identical, increment $distance by 1.
        for($i = 0; $i < $len; $i++) {
            $let1 = substr($string1, $i, 1);
            $let2 = substr($string2, $i, 1);
            if ($let1 != $let2) {
                $distance++;
            }
        }
        return $distance;
   }

    /**
     * levdist() computes the Levenshtein Distance between two strings or Seq objects
     * with equal/unequal lengths.  You can pass custom values for cost of insertion,
     * replacement, and deletion.  If you don't pass any, they are assumed to be 1.
     * For more information, see technical reference.
     * @param type $seq1
     * @param type $seq2
     * @param type $cost_ins
     * @param type $cost_rep
     * @param type $cost_del
     * @return type
     * @throws \Exception
     * @group Legacy
     */
    public function levdist($seq1, $seq2, $cost_ins = 1, $cost_rep = 1, $cost_del = 1)
    {
        // If $seq1 is a Seq object, we use its sequence property to compute Levenshtein Distance.
        if (gettype($seq1) == "object") {
            $string1 = $seq1->getSequence();
        } elseif (gettype($seq1) == "string") {
            $string1 = $seq1;
        }

        // If $seq2 is a Seq object, we use its sequence property to compute Levenshtein Distance.
        if (gettype($seq2) == "object") {
            $string2 = $seq2->getSequence();
        } elseif (gettype($seq2) == "string") {
            $string2 = $seq2;
        }

        // Check the lengths of the two strings.  If they exceed 255 characters, terminate code.
        if (strlen($string1) > 255) {
            throw new \Exception("String length must not exceed 255 characters!");
        }
        if (strlen($string2) > 255) {
            throw new \Exception("String length must not exceed 255 characters!");
        }
        // Compute and return the Levenshtein Distance using PHP's built-in levenshtein() function.
        return levenshtein($string1, $string2, $cost_ins, $cost_rep, $cost_del);
   }

    /**
     * xlevdist() is an extended version of levdist() which accepts strings with length
     * greater than 255 but not to exceed 1024 (which takes my CPU 18 seconds to compute).
     * The only drawback to xlevdist is that the cost of insertion, deletion, and replacement
     * is fixed to 1.  I have yet to find a way to allow custom values for these.
     * @param type $s
     * @param type $t
     * @return type
     * @throws \Exception
     * @group Legacy
     */
    public function xlevdist($s, $t)
    {
        $n = strlen($s);
        $m = strlen($t);

        if (($n > 1024) or ($m > 1024)) {
            throw new \Exception("String length must not exceed 1024 characters");
        }

        // initialize the array
        $values  = [];
        $temp    = [];
        $temp[0] = 0;

        for($j = 1; $j <= $m; $j++) {
            $temp[$j] = 0;
        }

        $values[0] = $temp;
        for($i = 1; $i <= $n; $i++) {
            $values[$i] = $temp;
        }

        for($i = 1; $i <= $n; $i++) {
            $lets = substr($s, $i-1, 1);
            for($j = 1; $j <= $m; $j++) {
                $lett = substr($t, $j-1, 1);
                $cost = ($lets == $lett) ? 0 : 1;

                // "normal" values of $up, $left, and $upleft
                $up     = ($j > 1) ? $values[$i][$j-1] : false;
                $left   = ($i > 1) ? $values[$i-1][$j] : false;
                $upleft = (($i > 1) && ($j > 1)) ? $values[$i-1][$j-1] : false;

                if ($i == 1) {
                    $value = ($j == 1 || $cost == 0) ? $cost : $up + 1;
                } else {
                    // if at the first or topmost row, there is no upleft and above.
                    if ($j == 1) {
                        $value = ($cost == 0) ? $cost : $left + 1;
                    } else {
                        $value = getmin($up + 1, $left + 1, $upleft + $cost);
                    }
                }
                $values[$i][$j] = $value;
            } 
        }
        return $values[$n][$m];
    }

    /**
     * The match() method accepts two sequence strings (not objects) of equal length,
     * and returns a sequence match result string, according to the following rules:
     * If there is an exact match, return the amino acid symbol.
     * If there is a partial match, return a plus sign.
     * If there is no match, return a whitespace character.
     * @global type $chemgrp_matrix
     * @param type $str1
     * @param type $str2
     * @param type $matrix
     * @param type $equal
     * @param type $partial
     * @param type $nomatch
     * @return string
     * @throws \Exception
     * @group Legacy
     */
    public function match($str1, $str2, $matrix, $equal, $partial = "+", $nomatch = ".")
    {
        // if the user chose not to use a custom submatrix, use the default one.
        if (!isset($matrix)) {
            $matrix = $this->subMatrix->getRules();
        }

        // if the strings differ in length, terminate code execution.
        if (strlen($str1) != strlen($str2)) {
            throw new \Exception("Cannot match sequences with unequal lengths !");
        }

        $resultstr = "";
        $seqlength = strlen($str1);

        // Match the two strings, character by character.  Each call to compare_letter()
        // function returns a "result character" which is appended to a "result string".
        for($i = 0; $i < $seqlength; $i++) {
            $let1 = substr($str1, $i, 1);
            $let2 = substr($str2, $i, 1);
            $resultstr = $resultstr . compare_letter($let1, $let2, $matrix, $equal, $partial, $nomatch);
        }

        // Assign "result string" to the result property of the calling SeqMatch object. 
        $this->result = $resultstr;

        // Return the result string.  While this line and the line above seems redundant, their
        // presense here actually permits programmers to write more compact code.
        return $resultstr;
    }

    /**
     * We abbreviate substitution matrix to "submatrix".  Each element in a submatrix is an array of
     * symbols that are considered "partial matches" of each other.
     * Default submatrix:
     * ( ('G','A','V','L','I'), ('S','T'), ('N','Q'), ('F','Y','W'), ('C', 'M'), ('P'), ('D','E'), ('K','R','H'),
     * ('*'), ('X') )
     * 1) Check if both $let1 and $let2 appear in the first element (G,A,V,L,I) of the substitution matrix.
     * 2) If they are, you've found a "hit", and $let1 and $let2 are partial matches.  Return a TRUE value.
     * If they are not, then go to the next element in the substitution matrix.
     * Repeat steps 1 and 2 until you reach a submatrix element where both $let1 and $let2 appear, or
     * until the last element in the submatrix has been checked.
     * 3) If you reach the last submatrix element without a "hit", return a FALSE value.
     * @param $let1
     * @param $let2
     * @param $matrix
     * @return bool
     */
    public function partialMatch($let1, $let2, $matrix)
    {
        if (!isset($matrix) == FALSE) {
            $matrix = $this->subMatrix->getRules();
        }
        foreach($matrix as $rule) {
            if ((in_array($let1, $rule)) && (in_array($let2, $rule))) {
                return true;
            }
        }
        return false;
    }
}
