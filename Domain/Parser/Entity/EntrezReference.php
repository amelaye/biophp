<?php
/**
 * One literature reference of an Entrez genome record
 * Freely inspired by BioPHP's project biophp.org
 * Created 12 September 2026
 * Last modified 18 September 2026
 */
namespace Amelaye\BioPHP\Domain\Parser\Entity;

use Amelaye\BioPHP\Domain\Parser\Interfaces\EntrezReferenceInterface;

/**
 * Class EntrezReference
 * @package Amelaye\BioPHP\Domain\Parser\Entity
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class EntrezReference implements EntrezReferenceInterface
{
    /**
     * @var string
     */
    private $refNo = "";

    /**
     * The stretch of sequence the reference covers, e.g. "(bases 1 to 48502)".
     * @var string
     */
    private $baseRange = "";

    /**
     * @var array
     */
    private $authors = [];

    /**
     * @var string
     */
    private $title = "";

    /**
     * @var string
     */
    private $journal = "";

    /**
     * @var string
     */
    private $medline = "";

    /**
     * @var string
     */
    private $pubmed = "";

    /**
     * @var string
     */
    private $remark = "";

    /**
     * @return string
     */
    public function getRefNo(): string
    {
        return $this->refNo;
    }

    /**
     * @param string $refNo
     */
    public function setRefNo(string $refNo): void
    {
        $this->refNo = $refNo;
    }

    /**
     * @return string
     */
    public function getBaseRange(): string
    {
        return $this->baseRange;
    }

    /**
     * @param string $baseRange
     */
    public function setBaseRange(string $baseRange): void
    {
        $this->baseRange = $baseRange;
    }

    /**
     * @return array
     */
    public function getAuthors(): array
    {
        return $this->authors;
    }

    /**
     * @param array $authors
     */
    public function setAuthors(array $authors): void
    {
        $this->authors = $authors;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getJournal(): string
    {
        return $this->journal;
    }

    /**
     * @param string $journal
     */
    public function setJournal(string $journal): void
    {
        $this->journal = $journal;
    }

    /**
     * @return string
     */
    public function getMedline(): string
    {
        return $this->medline;
    }

    /**
     * @param string $medline
     */
    public function setMedline(string $medline): void
    {
        $this->medline = $medline;
    }

    /**
     * @return string
     */
    public function getPubmed(): string
    {
        return $this->pubmed;
    }

    /**
     * @param string $pubmed
     */
    public function setPubmed(string $pubmed): void
    {
        $this->pubmed = $pubmed;
    }

    /**
     * @return string
     */
    public function getRemark(): string
    {
        return $this->remark;
    }

    /**
     * @param string $remark
     */
    public function setRemark(string $remark): void
    {
        $this->remark = $remark;
    }
}
