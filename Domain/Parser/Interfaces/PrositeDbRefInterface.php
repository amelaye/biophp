<?php
/**
 * Contract for one SWISS-PROT cross-reference (DR field) from a PROSITE motif entry
 * Freely inspired by BioPHP's project biophp.org
 * Created 18 September 2026
 * Last modified 18 September 2026
 */
namespace Amelaye\BioPHP\Domain\Parser\Interfaces;

/**
 * Interface PrositeDbRefInterface
 * @package Amelaye\BioPHP\Domain\Parser\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface PrositeDbRefInterface
{
    /**
     * @return string
     */
    public function getAccession(): string;

    /**
     * @param string $accession
     */
    public function setAccession(string $accession): void;

    /**
     * @return string
     */
    public function getEntryName(): string;

    /**
     * @param string $entryName
     */
    public function setEntryName(string $entryName): void;

    /**
     * @return bool
     */
    public function isTruePositive(): bool;

    /**
     * @param bool $truePositive
     */
    public function setTruePositive(bool $truePositive): void;
}
