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
 * Deliberately conservative in scope : only the Vertebrate Mitochondrial code (NCBI table 2) is
 * included, since its four differences from the standard code are extremely well established
 * (textbook material, not a niche or disputed table) - AGA/AGG becoming stop codons instead of
 * Arginine, and ATA/TGA being reassigned to Met/Trp, is the classic example of why vertebrate
 * mitochondrial DNA needs only 22 tRNAs instead of 32. Other NCBI tables were intentionally left out
 * rather than encoded from a less certain recollection ; adding one later only means adding its own
 * overlay entry to self::OVERLAYS and its constant to GeneticCodeTable.
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
     * @var     array<int,array<string,string>>
     */
    private const OVERLAYS = [
        GeneticCodeTable::VERTEBRATE_MITOCHONDRIAL => [
            "AGA" => "*",
            "AGG" => "*",
            "ATA" => "M",
            "TGA" => "W",
        ],
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
