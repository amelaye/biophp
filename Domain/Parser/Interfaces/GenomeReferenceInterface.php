<?php
/**
 * Contract for one reference set from a genome sequencing statistics record
 * Freely inspired by BioPHP's project biophp.org
 * Created 18 September 2026
 * Last modified 18 September 2026
 */
namespace Amelaye\BioPHP\Domain\Parser\Interfaces;

/**
 * Interface GenomeReferenceInterface
 * @package Amelaye\BioPHP\Domain\Parser\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface GenomeReferenceInterface
{
    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @param string $type
     */
    public function setType(string $type): void;

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
    public function getVolume(): string;

    /**
     * @param string $volume
     */
    public function setVolume(string $volume): void;

    /**
     * @return string
     */
    public function getPages(): string;

    /**
     * @param string $pages
     */
    public function setPages(string $pages): void;

    /**
     * @return string
     */
    public function getYear(): string;

    /**
     * @param string $year
     */
    public function setYear(string $year): void;
}
