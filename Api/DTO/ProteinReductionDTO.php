<?php
/**
 * Database of elements - PK Values
 * Inspired by BioPHP's project biophp.org
 * Created 20 december 2019
 * Last modified 20 december 2019
 */
namespace Amelaye\BioPHP\Api\DTO;

/**
 * Database of elements - PK Values
 * @package Amelaye\BioPHP\Api\DTO
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class ProteinReductionDTO
{
    /**
     * @var     int       Id of the row
     */
    private $id;

    /**
     * @var     string  Name of the Alphabet (Murphy etc ...)
     */
    private $alphabet;

    /**
     * @var     string  Letters of the alphabet
     */
    private $letters;

    /**
     * @var     string  Patterns of reduction
     */
    private $pattern;

    /**
     * @var     string  Nature of the pattern (Aliphatic, Aromatic ...)
     */
    private $nature;

    /**
     * @var     string  Corresponding letter
     */
    private $reduction;

    /**
     * @var     string  Description of the pattern (original alphabet)
     */
    private $description;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getAlphabet(): string
    {
        return $this->alphabet;
    }

    /**
     * @param string $alphabet
     */
    public function setAlphabet(string $alphabet): void
    {
        $this->alphabet = $alphabet;
    }

    /**
     * @return string
     */
    public function getLetters(): string
    {
        return $this->letters;
    }

    /**
     * @param string $letters
     */
    public function setLetters(string $letters): void
    {
        $this->letters = $letters;
    }

    /**
     * @return string
     */
    public function getPattern(): string
    {
        return $this->pattern;
    }

    /**
     * @param string $pattern
     */
    public function setPattern(string $pattern): void
    {
        $this->pattern = $pattern;
    }

    /**
     * @return string
     */
    public function getNature(): ?string
    {
        return $this->nature;
    }

    /**
     * @param string $nature
     */
    public function setNature(string $nature): void
    {
        $this->nature = $nature;
    }

    /**
     * @return string
     */
    public function getReduction(): string
    {
        return $this->reduction;
    }

    /**
     * @param string $reduction
     */
    public function setReduction(string $reduction): void
    {
        $this->reduction = $reduction;
    }

    /**
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }
}