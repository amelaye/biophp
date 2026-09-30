<?php
/**
 * Chooses the highest-usage synonymous codon for each residue of a target protein
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 30 September 2026
 */
namespace Amelaye\BioPHP\Domain\Tools\Service;

use Amelaye\BioPHP\Domain\Sequence\Interfaces\SequenceInterface;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\AminoAcidSequence;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;
use Amelaye\BioPHP\Domain\Tools\Interfaces\CodonOptimizerInterface;
use Amelaye\BioPHP\Domain\Tools\ValueObject\CodonUsageTable;

/**
 * Reuses SequenceInterface::translateCodon() to find every codon synonymous with a residue, the same
 * reuse CodonAdaptationIndexCalculator already makes, rather than hard-coding a second copy of the
 * genetic code. When every synonym of a residue is tied (most commonly because the reference table
 * has no observation for any of them at all), the tie is broken deterministically by codon
 * enumeration order (A<C<G<T, most to least significant base) - an arbitrary but reproducible choice,
 * the same kind of tie-break already used elsewhere in this project (e.g. NeighborJoiningTreeBuilder).
 * Class CodonOptimizer
 * @package Amelaye\BioPHP\Domain\Tools\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class CodonOptimizer implements CodonOptimizerInterface
{
    private const BASES = ["A", "C", "G", "T"];

    /**
     * @var     SequenceInterface
     */
    private $sequenceManager;

    /**
     * CodonOptimizer constructor.
     * @param   SequenceInterface   $oSequenceManager
     */
    public function __construct(SequenceInterface $oSequenceManager)
    {
        $this->sequenceManager = $oSequenceManager;
    }

    /**
     * @param   AminoAcidSequence   $oProtein
     * @param   CodonUsageTable     $oReferenceTable
     * @return  DnaSequence
     */
    public function optimize(AminoAcidSequence $oProtein, CodonUsageTable $oReferenceTable): DnaSequence
    {
        $sProtein = $oProtein->getValue();
        $sDna = "";
        $iLength = strlen($sProtein);

        for ($i = 0; $i < $iLength; $i++) {
            $sDna .= $this->bestCodonFor($sProtein[$i], $oReferenceTable);
        }

        return new DnaSequence($sDna);
    }

    /**
     * @param   string              $sResidue           A single-letter amino acid code, or "*"
     * @param   CodonUsageTable     $oReferenceTable
     * @return  string
     */
    private function bestCodonFor(string $sResidue, CodonUsageTable $oReferenceTable): string
    {
        $sBestCodon = null;
        $iBestCount = -1;

        foreach ($this->everyCodon() as $sCodon) {
            if ($this->sequenceManager->translateCodon($sCodon, 1) !== $sResidue) {
                continue;
            }

            $iCount = $oReferenceTable->getCount($sCodon);
            if ($iCount > $iBestCount) {
                $iBestCount = $iCount;
                $sBestCodon = $sCodon;
            }
        }

        if ($sBestCodon === null) {
            throw new \InvalidArgumentException(
                sprintf('No codon under the standard genetic code translates to "%s".', $sResidue)
            );
        }

        return $sBestCodon;
    }

    /**
     * @return  string[]    All 64 possible DNA codons
     */
    private function everyCodon(): array
    {
        $aCodons = [];

        foreach (self::BASES as $sFirst) {
            foreach (self::BASES as $sSecond) {
                foreach (self::BASES as $sThird) {
                    $aCodons[] = $sFirst . $sSecond . $sThird;
                }
            }
        }

        return $aCodons;
    }
}
