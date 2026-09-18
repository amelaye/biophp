<?php
/**
 * Contract for one literature reference of an Entrez genome record
 * Freely inspired by BioPHP's project biophp.org
 * Created 18 September 2026
 * Last modified 18 September 2026
 */
namespace Amelaye\BioPHP\Domain\Parser\Interfaces;

/**
 * Interface EntrezReferenceInterface
 * @package Amelaye\BioPHP\Domain\Parser\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface EntrezReferenceInterface
{
    /**
     * @return string
     */
    public function getRefNo(): string;

    /**
     * @param string $refNo
     */
    public function setRefNo(string $refNo): void;

    /**
     * @return string
     */
    public function getBaseRange(): string;

    /**
     * @param string $baseRange
     */
    public function setBaseRange(string $baseRange): void;

    /**
     * @return array
     */
    public function getAuthors(): array;

    /**
     * @param array $authors
     */
    public function setAuthors(array $authors): void;

    /**
     * @return string
     */
    public function getTitle(): string;

    /**
     * @param string $title
     */
    public function setTitle(string $title): void;

    /**
     * @return string
     */
    public function getJournal(): string;

    /**
     * @param string $journal
     */
    public function setJournal(string $journal): void;

    /**
     * @return string
     */
    public function getMedline(): string;

    /**
     * @param string $medline
     */
    public function setMedline(string $medline): void;

    /**
     * @return string
     */
    public function getPubmed(): string;

    /**
     * @param string $pubmed
     */
    public function setPubmed(string $pubmed): void;

    /**
     * @return string
     */
    public function getRemark(): string;

    /**
     * @param string $remark
     */
    public function setRemark(string $remark): void;
}
