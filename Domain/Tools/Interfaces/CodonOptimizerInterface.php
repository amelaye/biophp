<?php
/**
 * Codon optimization Interface
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Tools\Interfaces;

use Amelaye\BioPHP\Domain\Sequence\ValueObject\AminoAcidSequence;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;
use Amelaye\BioPHP\Domain\Tools\ValueObject\CodonUsageTable;

/**
 * Interface CodonOptimizerInterface - the inverse of CodonAdaptationIndexCalculator : rather than
 * scoring an existing coding sequence against a reference codon usage table, this picks, for each
 * residue of a target protein, the synonymous codon with the highest count in that table - the
 * coding sequence a perfect (CAI = 1) translation of the protein would have.
 * @package Amelaye\BioPHP\Domain\Tools\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface CodonOptimizerInterface
{
    /**
     * @param   AminoAcidSequence   $oProtein           May include a trailing "*" for a stop
     * @param   CodonUsageTable     $oReferenceTable
     * @param   int                 $iGeneticCode       An NCBI genetic code table id, 1 (standard) by default
     * @return  DnaSequence
     */
    public function optimize(AminoAcidSequence $oProtein, CodonUsageTable $oReferenceTable, int $iGeneticCode = 1): DnaSequence;
}
