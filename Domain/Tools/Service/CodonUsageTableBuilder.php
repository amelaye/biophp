<?php
/**
 * Builds a CodonUsageTable by counting codons across real coding sequences
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 30 September 2026
 */
namespace Amelaye\BioPHP\Domain\Tools\Service;

use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;
use Amelaye\BioPHP\Domain\Tools\Interfaces\CodonUsageTableBuilderInterface;
use Amelaye\BioPHP\Domain\Tools\ValueObject\CodonUsageTable;

/**
 * Pure counting, no genetic code involved : which codons are synonymous with each other is
 * CodonAdaptationIndexCalculator's concern when the table is later used, not this builder's. Only
 * complete trailing codons are counted, the same convention CodonAdaptationIndexCalculator already
 * uses when scoring a coding sequence.
 * Class CodonUsageTableBuilder
 * @package Amelaye\BioPHP\Domain\Tools\Service
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
                $sCodon = substr($sValue, $i * 3, 3);
                $aCounts[$sCodon] = ($aCounts[$sCodon] ?? 0) + 1;
            }
        }

        return new CodonUsageTable($aCounts);
    }
}
