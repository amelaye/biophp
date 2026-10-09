<?php
/**
 * One reference set from a genome sequencing statistics record
 * Freely inspired by BioPHP's project biophp.org
 * Created 25 August 2026
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Parser\Entity;

use Amelaye\BioPHP\Domain\Parser\Interfaces\GenomeReferenceInterface;

/**
 * Class GenomeReference
 * @package Amelaye\BioPHP\Domain\Parser\Entity
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class GenomeReference implements GenomeReferenceInterface
{
    /**
     * @var string
     */
    private string $type = "";

    /**
     * @var array
     */
    private array $authors = [];

    /**
     * @var string
     */
    private string $title = "";

    /**
     * @var string
     */
    private string $journal = "";

    /**
     * @var string
     */
    private string $volume = "";

    /**
     * @var string
     */
    private string $pages = "";

    /**
     * @var string
     */
    private string $year = "";

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
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
    public function getVolume(): string
    {
        return $this->volume;
    }

    /**
     * @param string $volume
     */
    public function setVolume(string $volume): void
    {
        $this->volume = $volume;
    }

    /**
     * @return string
     */
    public function getPages(): string
    {
        return $this->pages;
    }

    /**
     * @param string $pages
     */
    public function setPages(string $pages): void
    {
        $this->pages = $pages;
    }

    /**
     * @return string
     */
    public function getYear(): string
    {
        return $this->year;
    }

    /**
     * @param string $year
     */
    public function setYear(string $year): void
    {
        $this->year = $year;
    }
}
