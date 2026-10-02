<?php
/**
 * Raised when a VCF data line cannot be turned into a VcfVariant
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 2 October 2026
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
}
