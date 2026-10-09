<?php
/**
 * Raised when an input to a FASTQ record violates one of its invariants
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Sequencing\Exception;

/**
 * Class InvalidFastqRecordException
 * @package Amelaye\BioPHP\Domain\Sequencing\Exception
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class InvalidFastqRecordException extends \InvalidArgumentException
{
    /**
     * Builds the exception raised when a FASTQ record's identifier line ("@...") carries no read ID.
     * @return  InvalidFastqRecordException
     */
    public static function emptyIdentifier(): self
    {
        return new self("A FASTQ record's identifier must not be empty.");
    }

    /**
     * Builds the exception raised when the quality string does not carry exactly one symbol per base,
     * which breaks the one-to-one, per-position correspondence a FASTQ record requires.
     * @param   int         $iSequenceLength
     * @param   int         $iQualityLength
     * @return  InvalidFastqRecordException
     */
    public static function mismatchedLength(int $iSequenceLength, int $iQualityLength): self
    {
        return new self(
            sprintf(
                'Quality string length (%d) must match sequence length (%d).',
                $iQualityLength,
                $iSequenceLength
            )
        );
    }

    /**
     * Builds the exception raised when a quality symbol falls outside the printable ASCII range a
     * Phred+33 (Sanger / Illumina 1.8+) encoding uses, "!" (Phred 0) through "~" (Phred 93).
     * @param   string      $sSymbol
     * @param   int         $iPosition      Zero-based position of the offending symbol
     * @return  InvalidFastqRecordException
     */
    public static function invalidQualitySymbol(string $sSymbol, int $iPosition): self
    {
        return new self(
            sprintf(
                'Invalid Phred+33 quality symbol "%s" (ASCII %d) at position %d.',
                $sSymbol,
                ord($sSymbol),
                $iPosition
            )
        );
    }
}
