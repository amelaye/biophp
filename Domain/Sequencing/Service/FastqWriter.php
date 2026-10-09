<?php
/**
 * Serializes sequencing reads into FASTQ text
 * Freely inspired by BioPHP's project biophp.org
 * Created 9 October 2026
 * Last modified 9 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Sequencing\Service;

use Amelaye\BioPHP\Domain\Sequencing\Interfaces\FastqWriterInterface;
use Amelaye\BioPHP\Domain\Sequencing\ValueObject\FastqRecord;

/**
 * One record is four lines - "@identifier", the sequence, a bare "+", the quality - the sequence and
 * the quality on a single line each : the wrapped layout some old tools wrote is ambiguous (a quality
 * line may start with "@" or "+") and no current tool expects it. The "+" line does not repeat the
 * identifier, which the format allows and nobody needs. The quality is Phred+33, as FastqRecord
 * holds it. FastqReader reads back what this writes.
 * Class FastqWriter
 * @package Amelaye\BioPHP\Domain\Sequencing\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class FastqWriter implements FastqWriterInterface
{
    /**
     * @param   FastqRecord     $oRecord
     * @return  string
     * @throws  \InvalidArgumentException  When the identifier holds a newline, which would cut the header in two
     */
    public function writeOne(FastqRecord $oRecord): string
    {
        $sIdentifier = $oRecord->getIdentifier();
        if (strpos($sIdentifier, "\n") !== false || strpos($sIdentifier, "\r") !== false) {
            throw new \InvalidArgumentException("A FASTQ identifier must not contain a newline.");
        }

        return "@" . $sIdentifier . "\n"
            . $oRecord->getSequence()->getValue() . "\n"
            . "+\n"
            . $oRecord->getQuality() . "\n";
    }

    /**
     * @param   FastqRecord[]   $aRecords
     * @return  string
     */
    public function writeMany(array $aRecords): string
    {
        $sOutput = "";
        foreach ($aRecords as $oRecord) {
            if (!$oRecord instanceof FastqRecord) {
                throw new \InvalidArgumentException("FastqWriter writes FastqRecord instances only.");
            }
            $sOutput .= $this->writeOne($oRecord);
        }

        return $sOutput;
    }
}
