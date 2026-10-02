<?php
/**
 * FASTQ file reading Interface
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Sequencing\Interfaces;

use Amelaye\BioPHP\Domain\Sequencing\Result\FastqImportResult;

/**
 * Interface FastqReaderInterface - reads a FASTQ file into FastqRecord instances.
 * @package Amelaye\BioPHP\Domain\Sequencing\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface FastqReaderInterface
{
    /**
     * @param   string[]    $aLines     The FASTQ file's lines, in order, newline included or not
     * @return  FastqImportResult
     */
    public function read(array $aLines): FastqImportResult;
}
