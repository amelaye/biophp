<?php
/**
 * One SWISS-PROT cross-reference (DR field) from a PROSITE motif entry
 * Freely inspired by BioPHP's project biophp.org
 * Created 12 August 2026
 * Last modified 12 August 2026
 */
namespace Amelaye\BioPHP\Domain\Model;

/**
 * Class PrositeDbRef
 * @package Amelaye\BioPHP\Domain\Model
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class PrositeDbRef
{
    /**
     * @var string
     */
    private $accession;

    /**
     * @var string
     */
    private $entryName;

    /**
     * @var bool
     */
    private $truePositive;

    /**
     * @return string
     */
    public function getAccession(): string
    {
        return $this->accession;
    }

    /**
     * @param string $accession
     */
    public function setAccession(string $accession): void
    {
        $this->accession = $accession;
    }

    /**
     * @return string
     */
    public function getEntryName(): string
    {
        return $this->entryName;
    }

    /**
     * @param string $entryName
     */
    public function setEntryName(string $entryName): void
    {
        $this->entryName = $entryName;
    }

    /**
     * @return bool
     */
    public function isTruePositive(): bool
    {
        return $this->truePositive;
    }

    /**
     * @param bool $truePositive
     */
    public function setTruePositive(bool $truePositive): void
    {
        $this->truePositive = $truePositive;
    }
}
