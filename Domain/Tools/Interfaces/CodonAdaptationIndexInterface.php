<?php
/**
 * Codon Adaptation Index calculation Interface
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Tools\Interfaces;

use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;
use Amelaye\BioPHP\Domain\Tools\Result\CaiResult;
use Amelaye\BioPHP\Domain\Tools\ValueObject\CodonUsageTable;

/**
 * Interface CodonAdaptationIndexInterface - scores how well a coding sequence's own codon choices
 * match a reference organism's preferred codons (Sharp & Li, 1987), useful for predicting or
 * optimizing how well a cloned insert is likely to express in a given host.
 * @package Amelaye\BioPHP\Domain\Tools\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface CodonAdaptationIndexInterface
{
    /**
     * @param   DnaSequence         $oCodingSequence    A CDS, in frame from its first base ; only
     * complete trailing codons are considered
     * @param   CodonUsageTable     $oReferenceTable    The host organism's reference codon usage
     * @return  CaiResult
     */
    public function calculate(DnaSequence $oCodingSequence, CodonUsageTable $oReferenceTable): CaiResult;
}
