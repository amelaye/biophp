<?php
/**
 * FASTA sequence writing Interface
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Sequence\Interfaces;

/**
 * Interface FastaWriterInterface - serializes one or several sequences into FASTA text. Purely a
 * string formatter : it does not validate that $sSequence belongs to any particular alphabet, since
 * that validation already happened wherever the sequence itself was built (e.g. a DnaSequence value
 * object).
 * @package Amelaye\BioPHP\Domain\Sequence\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface FastaWriterInterface
{
    /**
     * @param   string      $sHeader        Everything that goes after ">", e.g. "id description"
     * @param   string      $sSequence
     * @return  string      A single FASTA record, header line included, terminated with a newline
     */
    public function writeOne(string $sHeader, string $sSequence): string;

    /**
     * @param   array       $aRecords       Each entry : ["header" => string, "sequence" => string]
     * @return  string      Every record concatenated, in order
     */
    public function writeMany(array $aRecords): string;
}
