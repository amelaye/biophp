<?php
/**
 * Codon Adaptation Index calculation
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 9 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Tools\Service\Codon;

use Amelaye\BioPHP\Domain\Sequence\Interfaces\SequenceInterface;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;
use Amelaye\BioPHP\Domain\Tools\Interfaces\CodonAdaptationIndexInterface;
use Amelaye\BioPHP\Domain\Tools\Result\CaiResult;
use Amelaye\BioPHP\Domain\Tools\ValueObject\CodonUsageTable;

/**
 * Reuses SequenceInterface::translateCodon() (the library's own, already-DI-wired genetic code) to
 * decide which codons are synonymous with each other, rather than hard-coding a second copy of the
 * genetic code here. Following Sharp & Li's original 1987 definition : a stop codon never counts
 * towards the score, and neither does a codon for an amino acid that has no synonym under the standard
 * genetic code (Met, Trp) - not because those two codons are special-cased by name, but because ANY
 * amino acid with exactly one codon trivially has relative adaptiveness 1 for it, and folding those
 * guaranteed-1 terms into the geometric mean would systematically inflate the score rather than
 * reflect a real codon CHOICE. The remaining score is the geometric mean, across every other codon of
 * the coding sequence, of that codon's count in the reference table divided by the highest count among
 * its synonyms in that same table.
 * Class CodonAdaptationIndexCalculator
 * @package Amelaye\BioPHP\Domain\Tools\Service\Codon
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class CodonAdaptationIndexCalculator implements CodonAdaptationIndexInterface
{
    use TranslatesUnderGeneticCode;

    private const BASES = ["A", "C", "G", "T"];

    /**
     * @var     SequenceInterface
     */
    private SequenceInterface $sequenceManager;

    /**
     * @var     float|null
     */
    private ?float $missingCodonWeight;

    /**
     * CodonAdaptationIndexCalculator constructor.
     * @param   SequenceInterface   $oSequenceManager
     * @param   float|null          $fMissingCodonWeight    The relative adaptiveness given to a codon the
     * reference table never uses, the usual remedy for a table built from few genes (0.5 is a common
     * choice, CodonW's). Null, the default, keeps the strict behaviour : such a codon is an error.
     * @throws  \InvalidArgumentException  When the weight is not in ]0, 1]
     */
    public function __construct(SequenceInterface $oSequenceManager, ?float $fMissingCodonWeight = null)
    {
        if ($fMissingCodonWeight !== null && ($fMissingCodonWeight <= 0.0 || $fMissingCodonWeight > 1.0)) {
            throw new \InvalidArgumentException(
                sprintf('The weight of a missing codon must be in ]0, 1], %s given.', $fMissingCodonWeight)
            );
        }
        $this->sequenceManager = $oSequenceManager;
        $this->missingCodonWeight = $fMissingCodonWeight;
    }

    /**
     * @param   DnaSequence         $oCodingSequence
     * @param   CodonUsageTable     $oReferenceTable
     * @param   int                 $iGeneticCode       The NCBI table that says which codons are synonyms
     * @return  CaiResult
     * @throws  \InvalidArgumentException  When the genetic code is not supported
     */
    public function calculate(DnaSequence $oCodingSequence, CodonUsageTable $oReferenceTable, int $iGeneticCode = 1): CaiResult
    {
        $this->assertSupportedGeneticCode($iGeneticCode);
        $sValue = $oCodingSequence->getValue();
        $iCodonCount = intdiv(strlen($sValue), 3);

        $fLogSum = 0.0;
        $iScored = 0;

        for ($i = 0; $i < $iCodonCount; $i++) {
            $sCodon = substr($sValue, $i * 3, 3);

            // A codon holding an ambiguity code (a sequencing N) has no usage in the table, which
            // counts A, C, G and T codons only : it is left out, as the builder leaves it out
            if (!preg_match('/^[ACGT]{3}$/', $sCodon)) {
                continue;
            }

            $sAminoAcid = $this->translateUnder($sCodon, $iGeneticCode);

            if ($sAminoAcid === "*" || $sAminoAcid === "X") {
                continue;
            }

            if ($this->countSynonyms($sAminoAcid, $iGeneticCode) <= 1) {
                continue;
            }

            $iMax = $this->maxReferenceCount($sAminoAcid, $oReferenceTable, $iGeneticCode);
            $iCount = $oReferenceTable->getCount($sCodon);

            // No synonym of the amino acid in the table : nothing to be relative to, whatever the weight
            if ($iMax === 0 || ($iCount === 0 && $this->missingCodonWeight === null)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Codon "%s" has no usable reference usage (relative adaptiveness would be zero); cannot compute CAI.',
                        $sCodon
                    )
                );
            }

            $fLogSum += $iCount === 0 ? log($this->missingCodonWeight) : log($iCount / $iMax);
            $iScored++;
        }

        if ($iScored === 0) {
            throw new \InvalidArgumentException(
                "No scorable codon (excluding stop codons and single-codon amino acids) was found in the coding sequence."
            );
        }

        return new CaiResult(exp($fLogSum / $iScored), $iScored);
    }

    /**
     * How many of the 64 possible codons translate to $sAminoAcid, under the standard genetic code -
     * not how many of them happen to appear in a reference table, which could be incomplete.
     * @param   string  $sAminoAcid     A single-letter amino acid code, as returned by translateCodon()
     * @param   int     $iGeneticCode
     * @return  int
     */
    private function countSynonyms(string $sAminoAcid, int $iGeneticCode): int
    {
        $iCount = 0;

        foreach ($this->everyCodon() as $sCodon) {
            if ($this->translateUnder($sCodon, $iGeneticCode) === $sAminoAcid) {
                $iCount++;
            }
        }

        return $iCount;
    }

    /**
     * @param   string              $sAminoAcid
     * @param   CodonUsageTable     $oReferenceTable
     * @param   int                 $iGeneticCode
     * @return  int         The highest reference count among every codon translating to $sAminoAcid
     */
    private function maxReferenceCount(string $sAminoAcid, CodonUsageTable $oReferenceTable, int $iGeneticCode): int
    {
        $iMax = 0;

        foreach ($this->everyCodon() as $sCodon) {
            if ($this->translateUnder($sCodon, $iGeneticCode) === $sAminoAcid) {
                $iMax = max($iMax, $oReferenceTable->getCount($sCodon));
            }
        }

        return $iMax;
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
