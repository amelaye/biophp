<?php
/**
 * Raised when a string cannot be parsed as a Newick tree
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Phylogenetics\Exception;

/**
 * Class InvalidNewickException
 * @package Amelaye\BioPHP\Domain\Phylogenetics\Exception
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class InvalidNewickException extends \InvalidArgumentException
{
    /**
     * Builds the exception raised when the input string is empty or made of whitespace only.
     * @return  InvalidNewickException
     */
    public static function emptyString(): self
    {
        return new self("A Newick string must not be empty.");
    }

    /**
     * Builds the exception raised when the input does not end with the ";" every Newick tree
     * description must be terminated with.
     * @return  InvalidNewickException
     */
    public static function missingTerminator(): self
    {
        return new self('A Newick string must end with ";".');
    }

    /**
     * Builds the exception raised when a "(" opened for a subtree is never matched by a ")", or a
     * ")" is encountered with no matching "(" still open.
     * @param   int         $iPosition      Zero-based character position where parsing failed
     * @return  InvalidNewickException
     */
    public static function unbalancedParentheses(int $iPosition): self
    {
        return new self(
            sprintf('Unbalanced parentheses at position %d.', $iPosition)
        );
    }

    /**
     * Builds the exception raised when characters remain after the subtree that starts right after
     * the opening of the string has been fully parsed, i.e. the string describes more than one tree.
     * @param   int         $iPosition
     * @return  InvalidNewickException
     */
    public static function trailingContent(int $iPosition): self
    {
        return new self(
            sprintf('Unexpected trailing content at position %d ; only one tree per call is supported.', $iPosition)
        );
    }

    /**
     * Builds the exception raised when a branch length (the text following ":") is not a valid
     * number.
     * @param   string      $sValue
     * @param   int         $iPosition
     * @return  InvalidNewickException
     */
    public static function invalidBranchLength(string $sValue, int $iPosition): self
    {
        return new self(
            sprintf('Invalid branch length "%s" at position %d.', $sValue, $iPosition)
        );
    }
}
