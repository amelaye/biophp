<?php
/**
 * Translates a codon under a specific NCBI genetic code table
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 9 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Tools\Service\Codon;

use Amelaye\BioPHP\Domain\Sequence\Interfaces\SequenceInterface;
use Amelaye\BioPHP\Domain\Tools\Interfaces\AlternateGeneticCodeTranslatorInterface;
use Amelaye\BioPHP\Domain\Tools\ValueObject\GeneticCodeTable;

/**
 * Every alternate table is stored as an OVERLAY of differences from the standard genetic code
 * (SequenceInterface::translateCodon()'s own, already-wired code, reused rather than duplicated -
 * the same reuse CodonAdaptationIndexCalculator, OrfFinder and CodonOptimizer already make), exactly
 * the way NCBI itself documents each alternate table : as a short list of codons that differ from
 * the standard, not a second full 64-entry table. A codon not present in a table's overlay falls
 * back to the standard translation.
 *
 * The overlays were generated from NCBI's gc.prt (version 4.6) and checked, codon by codon, against
 * Biopython's CodonTable. They cover every table whose stop codons do not depend on their context
 * (see GeneticCodeTable) ; adding a table means adding its overlay here and its id and name there.
 * Class AlternateGeneticCodeTranslator
 * @package Amelaye\BioPHP\Domain\Tools\Service\Codon
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class AlternateGeneticCodeTranslator implements AlternateGeneticCodeTranslatorInterface
{
    /**
     * @var string[]    The bases each IUPAC nucleotide code stands for
     */
    private const IUPAC = [
        "A" => "A", "C" => "C", "G" => "G", "T" => "T", "R" => "AG", "Y" => "CT", "S" => "CG",
        "W" => "AT", "K" => "GT", "M" => "AC", "B" => "CGT", "D" => "AGT", "H" => "ACT",
        "V" => "ACG", "N" => "ACGT",
    ];

    /**
     * @var     array<int,array<string,string>>   Per table, the codons it reads differently from the
     * standard code (gc.prt, version 4.6)
     */
    private const OVERLAYS = [
        2 => ["TGA" => "W", "ATA" => "M", "AGA" => "*", "AGG" => "*"],
        3 => ["TGA" => "W", "CTT" => "T", "CTC" => "T", "CTA" => "T", "CTG" => "T", "ATA" => "M"],
        4 => ["TGA" => "W"],
        5 => ["TGA" => "W", "ATA" => "M", "AGA" => "S", "AGG" => "S"],
        6 => ["TAA" => "Q", "TAG" => "Q"],
        9 => ["TGA" => "W", "AAA" => "N", "AGA" => "S", "AGG" => "S"],
        10 => ["TGA" => "C"],
        11 => [],
        12 => ["CTG" => "S"],
        13 => ["TGA" => "W", "ATA" => "M", "AGA" => "G", "AGG" => "G"],
        14 => ["TAA" => "Y", "TGA" => "W", "AAA" => "N", "AGA" => "S", "AGG" => "S"],
        15 => ["TAG" => "Q"],
        16 => ["TAG" => "L"],
        21 => ["TGA" => "W", "ATA" => "M", "AAA" => "N", "AGA" => "S", "AGG" => "S"],
        22 => ["TCA" => "*", "TAG" => "L"],
        23 => ["TTA" => "*"],
        24 => ["TGA" => "W", "AGA" => "S", "AGG" => "K"],
        25 => ["TGA" => "G"],
        26 => ["CTG" => "A"],
        29 => ["TAA" => "Y", "TAG" => "Y"],
        30 => ["TAA" => "E", "TAG" => "E"],
        32 => ["TAG" => "W"],
        33 => ["TAA" => "Y", "TGA" => "W", "AGA" => "S", "AGG" => "K"],
    ];

    /**
     * @var     SequenceInterface
     */
    private SequenceInterface $sequenceManager;

    /**
     * AlternateGeneticCodeTranslator constructor.
     * @param   SequenceInterface   $oSequenceManager
     */
    public function __construct(SequenceInterface $oSequenceManager)
    {
        $this->sequenceManager = $oSequenceManager;
    }

    /**
     * @param   string      $sCodon
     * @param   int         $iTableId
     * @return  string
     */
    public function translateCodon(string $sCodon, int $iTableId): string
    {
        if (!in_array($iTableId, GeneticCodeTable::SUPPORTED_TABLES, true)) {
            throw new \InvalidArgumentException(sprintf('Unsupported genetic code table %d.', $iTableId));
        }

        $aOverlay = self::OVERLAYS[$iTableId] ?? [];
        // The overlays are keyed on DNA codons : an RNA codon (AUA, UGA, AGA) must read the same.
        $sNormalizedCodon = str_replace("U", "T", strtoupper($sCodon));

        if (isset($aOverlay[$sNormalizedCodon])) {
            return $aOverlay[$sNormalizedCodon];
        }

        // An ambiguous codon (AGR, TGR, ATR) must be read under this table, not the standard one :
        // AGR is Arg in the standard code and a stop in the vertebrate mitochondrial one. It stands
        // for an amino acid when every codon it covers reads as that one, as the standard code does.
        if ($aOverlay !== [] && preg_match('/[^ACGT]/', $sNormalizedCodon) === 1) {
            return $this->translateAmbiguousCodon($sNormalizedCodon, $aOverlay);
        }

        return $this->sequenceManager->translateCodon($sCodon, 1);
    }

    /**
     * @param   string      $sCodon         DNA codon holding at least one IUPAC ambiguity code
     * @param   array       $aOverlay       The codons this table reads differently from the standard code
     * @return  string                      The amino acid, "X" when the covered codons disagree
     */
    private function translateAmbiguousCodon(string $sCodon, array $aOverlay): string
    {
        $aCodons = [""];
        foreach (str_split($sCodon) as $sBase) {
            if (!isset(self::IUPAC[$sBase])) {
                return "X";
            }
            $aNext = [];
            foreach ($aCodons as $sPrefix) {
                foreach (str_split(self::IUPAC[$sBase]) as $sConcrete) {
                    $aNext[] = $sPrefix . $sConcrete;
                }
            }
            $aCodons = $aNext;
        }

        $aAminoAcids = [];
        foreach ($aCodons as $sConcreteCodon) {
            $aAminoAcids[$aOverlay[$sConcreteCodon] ?? $this->sequenceManager->translateCodon($sConcreteCodon, 1)] = true;
        }

        return count($aAminoAcids) === 1 ? (string) array_key_first($aAminoAcids) : "X";
    }
}
