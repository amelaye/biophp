<?php
/**
 * VCF variant-file reading Interface
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Variants\Interfaces;

use Amelaye\BioPHP\Domain\Variants\Result\VcfImportResult;

/**
 * Interface VcfReaderInterface - reads a VCF file into VcfVariant instances. FORMAT and sample
 * genotype columns (9th column onward) are not modeled ; only the 8 mandatory fixed fields (CHROM
 * through INFO) are read.
 * @package Amelaye\BioPHP\Domain\Variants\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface VcfReaderInterface
{
    /**
     * @param   string[]    $aLines     The VCF file's lines, in order, newline included or not
     * @return  VcfImportResult
     */
    public function read(array $aLines): VcfImportResult;
}
