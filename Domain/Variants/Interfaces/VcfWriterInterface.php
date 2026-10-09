<?php
/**
 * VCF serialization Interface
 * Freely inspired by BioPHP's project biophp.org
 * Created 9 October 2026
 * Last modified 9 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Variants\Interfaces;

use Amelaye\BioPHP\Domain\Variants\ValueObject\VcfVariant;

/**
 * Interface VcfWriterInterface - the counterpart of VcfReaderInterface : writes VcfVariant instances
 * as a VCF file with its eight fixed columns (no FORMAT, no sample genotype).
 * @package Amelaye\BioPHP\Domain\Variants\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface VcfWriterInterface
{
    /**
     * @param   VcfVariant[]    $aVariants
     * @param   string[]        $aMetaLines     Extra "##..." header lines (contig, INFO, FILTER definitions...)
     * written after the file format line, with or without their leading "##"
     * @return  string          The file : the meta lines, the column header line, one line per variant
     * @throws  \InvalidArgumentException  When an entry is not a VcfVariant, or a field cannot be written
     */
    public function write(array $aVariants, array $aMetaLines = []): string;
}
