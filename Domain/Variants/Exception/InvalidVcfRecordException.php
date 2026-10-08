<?php
/**
 * Raised when a VCF data line cannot be turned into a VcfVariant
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 8 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Variants\Exception;

/**
 * Class InvalidVcfRecordException
 * @package Amelaye\BioPHP\Domain\Variants\Exception
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class InvalidVcfRecordException extends \InvalidArgumentException
{
    /**
     * @return  InvalidVcfRecordException
     */
    public static function emptyChrom(): self
    {
        return new self("A VCF record's CHROM must not be empty.");
    }

    /**
     * @param   int     $iPosition
     * @return  InvalidVcfRecordException
     */
    public static function negativePosition(int $iPosition): self
    {
        return new self(
            sprintf(
                'A VCF record\'s POS must be at least 0 (0 marks a telomere), got %d.',
                $iPosition
            )
        );
    }

    /**
     * @deprecated  POS 0 is valid VCF (a telomere) ; VcfVariant now throws negativePosition()
     * @param   int     $iPosition
     * @return  InvalidVcfRecordException
     */
    public static function nonPositivePosition(int $iPosition): self
    {
        return new self(
            sprintf('A VCF record\'s POS must be at least 1, got %d.', $iPosition)
        );
    }

    /**
     * @return  InvalidVcfRecordException
     */
    public static function emptyReference(): self
    {
        return new self("A VCF record's REF must not be empty.");
    }

    /**
     * @param   string      $sReference
     * @return  InvalidVcfRecordException
     */
    public static function invalidReference(string $sReference): self
    {
        return new self(sprintf('A VCF record\'s REF must be bases (A, C, G, T, N), "%s" given.', $sReference));
    }

    /**
     * @param   string      $sAlternate
     * @return  InvalidVcfRecordException
     */
    public static function invalidAlternate(string $sAlternate): self
    {
        return new self(sprintf(
            'A VCF record\'s ALT allele must be bases, "*", a symbolic allele (<DEL>) or a breakend'
            . ' (G]17:198982], .A), "%s" given.',
            $sAlternate
        ));
    }
}
