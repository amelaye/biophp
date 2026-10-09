<?php
/**
 * FASTQ serialization Interface
 * Freely inspired by BioPHP's project biophp.org
 * Created 9 October 2026
 * Last modified 9 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Sequencing\Interfaces;

use Amelaye\BioPHP\Domain\Sequencing\ValueObject\FastqRecord;

/**
 * Interface FastqWriterInterface - the counterpart of FastqReaderInterface : writes FastqRecord
 * instances as FASTQ text.
 * @package Amelaye\BioPHP\Domain\Sequencing\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface FastqWriterInterface
{
    /**
     * @param   FastqRecord     $oRecord
     * @return  string          Four lines, each ended by a newline
     */
    public function writeOne(FastqRecord $oRecord): string;

    /**
     * @param   FastqRecord[]   $aRecords
     * @return  string          Every record, in order
     * @throws  \InvalidArgumentException  When an entry is not a FastqRecord
     */
    public function writeMany(array $aRecords): string;
}
