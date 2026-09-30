<?php
/**
 * Codon usage table construction Interface
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 30 September 2026
 */
namespace Amelaye\BioPHP\Domain\Tools\Interfaces;

use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;
use Amelaye\BioPHP\Domain\Tools\ValueObject\CodonUsageTable;

/**
 * Interface CodonUsageTableBuilderInterface - builds a CodonUsageTable by counting codon occurrences
 * across a set of real coding sequences (typically the highly expressed genes of a host organism).
 * @package Amelaye\BioPHP\Domain\Tools\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface CodonUsageTableBuilderInterface
{
    /**
     * @param   DnaSequence[]   $aCodingSequences
     * @return  CodonUsageTable
     */
    public function build(array $aCodingSequences): CodonUsageTable;
}
