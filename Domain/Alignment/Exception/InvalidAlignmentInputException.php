<?php
/**
 * Raised when an input to a pairwise alignment operation violates one of its invariants
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Alignment\Exception;

/**
 * Class InvalidAlignmentInputException
 * @package Amelaye\BioPHP\Domain\Alignment\Exception
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class InvalidAlignmentInputException extends \InvalidArgumentException
{
    /**
     * Builds the exception raised when a gap penalty is not strictly negative, which would make the
     * dynamic programming matrix prefer inserting gaps without bound instead of penalizing them.
     * @param   int         $iGapPenalty        The rejected gap penalty
     * @return  InvalidAlignmentInputException
     */
    public static function nonNegativeGapPenalty(int $iGapPenalty): self
    {
        return new self(
            sprintf('Gap penalty must be strictly negative, got %d.', $iGapPenalty)
        );
    }

    /**
     * Builds the exception raised when a PairwiseAlignmentResult is given two aligned sequences of
     * different lengths, which breaks the one-to-one column correspondence an alignment requires.
     * @param   int         $iFirstLength
     * @param   int         $iSecondLength
     * @return  InvalidAlignmentInputException
     */
    public static function mismatchedAlignedLength(int $iFirstLength, int $iSecondLength): self
    {
        return new self(
            sprintf(
                'Aligned sequences must have the same length, got %d and %d.',
                $iFirstLength,
                $iSecondLength
            )
        );
    }

    /**
     * Builds the exception raised when a PairwiseAlignmentResult is given an empty aligned sequence.
     * @return  InvalidAlignmentInputException
     */
    public static function emptyAlignedSequence(): self
    {
        return new self("Aligned sequences must not be empty.");
    }

    /**
     * Builds the exception raised when a substitution matrix (e.g. PAM250) has no entry for a pair of
     * symbols, typically an ambiguous or stop-codon symbol the matrix was never defined for.
     * @param   string      $sFirstSymbol
     * @param   string      $sSecondSymbol
     * @return  InvalidAlignmentInputException
     */
    public static function noScoreForSymbolPair(string $sFirstSymbol, string $sSecondSymbol): self
    {
        return new self(
            sprintf('No substitution score is defined for the symbol pair "%s"/"%s".', $sFirstSymbol, $sSecondSymbol)
        );
    }
}
