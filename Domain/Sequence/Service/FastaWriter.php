<?php
/**
 * Serializes sequences into FASTA text
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 30 September 2026
 */
namespace Amelaye\BioPHP\Domain\Sequence\Service;

use Amelaye\BioPHP\Domain\Sequence\Interfaces\FastaWriterInterface;

/**
 * The sequence body is wrapped at 70 symbols per line, the width NCBI's own FASTA output and most
 * downstream tools (samtools faidx, BioPerl's default) already expect ; a header containing a
 * newline would break the one-line-per-header format FASTA relies on and is rejected rather than
 * silently truncated or escaped.
 * Class FastaWriter
 * @package Amelaye\BioPHP\Domain\Sequence\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class FastaWriter implements FastaWriterInterface
{
    private const LINE_WIDTH = 70;

    /**
     * @param   string      $sHeader
     * @param   string      $sSequence
     * @return  string
     */
    public function writeOne(string $sHeader, string $sSequence): string
    {
        if ($sHeader === "") {
            throw new \InvalidArgumentException("A FASTA header must not be empty.");
        }

        if (strpos($sHeader, "\n") !== false || strpos($sHeader, "\r") !== false) {
            throw new \InvalidArgumentException("A FASTA header must not contain a newline.");
        }

        $sRecord = ">" . $sHeader . "\n";

        if ($sSequence !== "") {
            $sRecord .= implode("\n", str_split($sSequence, self::LINE_WIDTH)) . "\n";
        }

        return $sRecord;
    }

    /**
     * @param   array       $aRecords
     * @return  string
     */
    public function writeMany(array $aRecords): string
    {
        $sOutput = "";

        foreach ($aRecords as $aRecord) {
            $sOutput .= $this->writeOne($aRecord["header"] ?? "", $aRecord["sequence"] ?? "");
        }

        return $sOutput;
    }
}
