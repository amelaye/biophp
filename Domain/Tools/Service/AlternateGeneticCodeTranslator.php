<?php
/**
 * Translates a codon under a specific NCBI genetic code table
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 30 September 2026
 */
namespace Amelaye\BioPHP\Domain\Tools\Service;

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
 * @package Amelaye\BioPHP\Domain\Tools\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class AlternateGeneticCodeTranslator implements AlternateGeneticCodeTranslatorInterface
{
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
    private $sequenceManager;

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
        $sNormalizedCodon = strtoupper($sCodon);

        return $aOverlay[$sNormalizedCodon] ?? $this->sequenceManager->translateCodon($sCodon, 1);
    }
}
