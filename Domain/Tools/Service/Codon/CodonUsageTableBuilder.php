<?php
/**
 * Builds a CodonUsageTable by counting codons across real coding sequences
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 8 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Tools\Service\Codon;

use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;
use Amelaye\BioPHP\Domain\Tools\Interfaces\CodonUsageTableBuilderInterface;
use Amelaye\BioPHP\Domain\Tools\ValueObject\CodonUsageTable;

/**
 * Pure counting, no genetic code involved : which codons are synonymous with each other is
 * CodonAdaptationIndexCalculator's concern when the table is later used, not this builder's. Only
 * complete trailing codons are counted, the same convention CodonAdaptationIndexCalculator already
 * uses when scoring a coding sequence.
 * Class CodonUsageTableBuilder
 * @package Amelaye\BioPHP\Domain\Tools\Service\Codon
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class CodonUsageTableBuilder implements CodonUsageTableBuilderInterface
{
    /**
     * @param   DnaSequence[]   $aCodingSequences
     * @return  CodonUsageTable
     */
    public function build(array $aCodingSequences): CodonUsageTable
    {
        $aCounts = [];

        foreach ($aCodingSequences as $oSequence) {
            if (!$oSequence instanceof DnaSequence) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'CodonUsageTableBuilder coding sequences must be DnaSequence instances, got %s.',
                        is_object($oSequence) ? get_class($oSequence) : gettype($oSequence)
                    )
                );
            }

            $sValue = $oSequence->getValue();
            $iCodonCount = intdiv(strlen($sValue), 3);

            for ($i = 0; $i < $iCodonCount; $i++) {
                $sCodon = strtoupper(substr($sValue, $i * 3, 3));
                // A codon holding an ambiguous base (NNN, ATR) is no codon of the table : it is
                // left out, as the CAI leaves out what translates to X.
                if (preg_match('/^[ACGT]{3}$/', $sCodon) !== 1) {
                    continue;
                }
                $aCounts[$sCodon] = ($aCounts[$sCodon] ?? 0) + 1;
            }
        }

        return new CodonUsageTable($aCounts);
    }
}
