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
    private const BASES = ["A", "C", "G", "T"];

    /**
     * @var     SequenceInterface
     */
    private SequenceInterface $sequenceManager;

    /**
     * CodonAdaptationIndexCalculator constructor.
     * @param   SequenceInterface   $oSequenceManager
     */
    public function __construct(SequenceInterface $oSequenceManager)
    {
        $this->sequenceManager = $oSequenceManager;
    }

    /**
     * @param   DnaSequence         $oCodingSequence
     * @param   CodonUsageTable     $oReferenceTable
     * @return  CaiResult
     */
    public function calculate(DnaSequence $oCodingSequence, CodonUsageTable $oReferenceTable): CaiResult
    {
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

            $sAminoAcid = $this->sequenceManager->translateCodon($sCodon, 1);

            if ($sAminoAcid === "*" || $sAminoAcid === "X") {
                continue;
            }

            if ($this->countSynonyms($sAminoAcid) <= 1) {
                continue;
            }

            $iMax = $this->maxReferenceCount($sAminoAcid, $oReferenceTable);
            $iCount = $oReferenceTable->getCount($sCodon);

            if ($iMax === 0 || $iCount === 0) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Codon "%s" has no usable reference usage (relative adaptiveness would be zero); cannot compute CAI.',
                        $sCodon
                    )
                );
            }

            $fLogSum += log($iCount / $iMax);
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
     * @return  int
     */
    private function countSynonyms(string $sAminoAcid): int
    {
        $iCount = 0;

        foreach ($this->everyCodon() as $sCodon) {
            if ($this->sequenceManager->translateCodon($sCodon, 1) === $sAminoAcid) {
                $iCount++;
            }
        }

        return $iCount;
    }

    /**
     * @param   string              $sAminoAcid
     * @param   CodonUsageTable     $oReferenceTable
     * @return  int         The highest reference count among every codon translating to $sAminoAcid
     */
    private function maxReferenceCount(string $sAminoAcid, CodonUsageTable $oReferenceTable): int
    {
        $iMax = 0;

        foreach ($this->everyCodon() as $sCodon) {
            if ($this->sequenceManager->translateCodon($sCodon, 1) === $sAminoAcid) {
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
