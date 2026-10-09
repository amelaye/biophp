<?php
/**
 * One SWISS-PROT cross-reference (DR field) from a PROSITE motif entry
 * Freely inspired by BioPHP's project biophp.org
 * Created 12 August 2026
 * Last modified 7 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Parser\Entity;

use Amelaye\BioPHP\Domain\Parser\Interfaces\PrositeDbRefInterface;

/**
 * Class PrositeDbRef
 * @package Amelaye\BioPHP\Domain\Parser\Entity
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class PrositeDbRef implements PrositeDbRefInterface
{
    /**
     * @var string
     */
    private string $accession = "";

    /**
     * @var string
     */
    private string $entryName = "";

    /**
     * @var bool
     */
    private bool $truePositive = false;

    /**
     * The PROSITE code of the match : T true positive, N false negative (a member of the family
     * the motif misses), P potential (a member known from a fragment lacking the motif's region),
     * ? unknown, F false positive.
     * @var string
     */
    private string $category = "";

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

    /**
     * @return string   T, N, P, ? or F
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * @param string $category
     */
    public function setCategory(string $category): void
    {
        $this->category = $category;
    }

    /**
     * Tells whether the sequence belongs to the family the motif describes, detected (T) or not
     * (N, P) - unlike isTruePositive(), which a missed member fails too.
     * @return bool
     */
    public function isFamilyMember(): bool
    {
        return in_array($this->category, ["T", "N", "P"], true);
    }
}
