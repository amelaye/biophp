<?php
/**
 * Alternate genetic code table translation Interface
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Tools\Interfaces;

/**
 * Interface AlternateGeneticCodeTranslatorInterface - translates a codon under a specific NCBI
 * genetic code table (see GeneticCodeTable::SUPPORTED_TABLES). Only the single-letter amino acid
 * output SequenceInterface::translateCodon()'s format=1 already uses is supported here ; the
 * 3-letter format has no current caller and was left out to keep this service's scope tight.
 * @package Amelaye\BioPHP\Domain\Tools\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface AlternateGeneticCodeTranslatorInterface
{
    /**
     * @param   string      $sCodon
     * @param   int         $iTableId   One of GeneticCodeTable::SUPPORTED_TABLES
     * @return  string      A single-letter amino acid code, or "*" for a stop
     */
    public function translateCodon(string $sCodon, int $iTableId): string;
}
