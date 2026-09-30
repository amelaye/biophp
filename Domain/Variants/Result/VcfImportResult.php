<?php
/**
 * Immutable value object holding the outcome of reading a VCF file
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 30 September 2026
 */
namespace Amelaye\BioPHP\Domain\Variants\Result;

use Amelaye\BioPHP\Domain\Variants\ValueObject\VcfVariant;

/**
 * Class VcfImportResult
 * @package Amelaye\BioPHP\Domain\Variants\Result
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class VcfImportResult
{
    /**
     * @var     VcfVariant[]
     */
    private $variants;

    /**
     * @var     string[]
     */
    private $warnings;

    /**
     * VcfImportResult constructor.
     * @param   VcfVariant[]    $aVariants
     * @param   string[]        $aWarnings
     */
    public function __construct(array $aVariants, array $aWarnings = [])
    {
        foreach ($aVariants as $oVariant) {
            if (!$oVariant instanceof VcfVariant) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'VcfImportResult variants must be VcfVariant instances, got %s.',
                        is_object($oVariant) ? get_class($oVariant) : gettype($oVariant)
                    )
                );
            }
        }

        $this->variants = array_values($aVariants);
        $this->warnings = array_values($aWarnings);
    }

    /**
     * @return  VcfVariant[]
     */
    public function getVariants(): array
    {
        return $this->variants;
    }

    /**
     * @return  string[]
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }
}
