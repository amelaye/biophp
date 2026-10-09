<?php
/**
 * Translation of a codon under the genetic code the caller chose
 * Freely inspired by BioPHP's project biophp.org
 * Created 9 October 2026
 * Last modified 9 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Tools\Service\Codon;

use Amelaye\BioPHP\Domain\Sequence\Interfaces\SequenceInterface;
use Amelaye\BioPHP\Domain\Tools\ValueObject\GeneticCodeTable;

/**
 * Shared by the services that read codons (ORF search, CAI, codon optimization) : the standard code
 * (table 1) goes through SequenceInterface::translateCodon(), as they always did, and any other
 * NCBI table through AlternateGeneticCodeTranslator. The class using it holds the
 * SequenceInterface in $this->sequenceManager.
 * @package Amelaye\BioPHP\Domain\Tools\Service\Codon
 */
trait TranslatesUnderGeneticCode
{
    /**
     * @var     AlternateGeneticCodeTranslator|null
     */
    private ?AlternateGeneticCodeTranslator $geneticCodeTranslator = null;

    /**
     * @param   int     $iGeneticCode   An NCBI table id (see GeneticCodeTable::SUPPORTED_TABLES)
     * @throws  \InvalidArgumentException  When the table is not supported
     */
    private function assertSupportedGeneticCode(int $iGeneticCode): void
    {
        if (!in_array($iGeneticCode, GeneticCodeTable::SUPPORTED_TABLES, true)) {
            throw new \InvalidArgumentException(sprintf('Unsupported genetic code table %d.', $iGeneticCode));
        }
    }

    /**
     * @param   string      $sCodon
     * @param   int         $iGeneticCode
     * @return  string      The amino acid, "*" for a stop, "X" for an ambiguous codon
     */
    private function translateUnder(string $sCodon, int $iGeneticCode): string
    {
        if ($iGeneticCode === GeneticCodeTable::STANDARD) {
            return $this->sequenceManager->translateCodon($sCodon, 1);
        }
        $this->geneticCodeTranslator ??= new AlternateGeneticCodeTranslator($this->sequenceManager);

        return $this->geneticCodeTranslator->translateCodon($sCodon, $iGeneticCode);
    }
}
