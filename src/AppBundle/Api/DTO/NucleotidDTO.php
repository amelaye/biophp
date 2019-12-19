<?php
/**
 * Database of nucleotids - weigths included
 * Inspired by BioPHP's project biophp.org
 * Created 19 december 2019
 * Last modified 19 december 2019
 */
namespace App\Entity;

/**
 * Database of nucleotids - weigths included
 * @package AppBundle\Api\DTO
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class Nucleotid
{
    /**
     * @var     int         Id of the nucleotid (auto-increment)
     */
    private $id;

    /**
     * @var     string      A, T, G or C for example
     */
    private $letter;

    /**
     * @var     string      T for A ...
     */
    private $complement;

    /**
     * @var     string      DNA or RNA
     */
    private $nature;

    /**
     * @var     float       Weight of the nucleotid
     */
    private $weigth;

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
    public function getLetter(): string
    {
        return $this->letter;
    }

    /**
     * @param string $letter
     */
    public function setLetter(string $letter): void
    {
        $this->letter = $letter;
    }

    /**
     * @return string
     */
    public function getComplement(): string
    {
        return $this->complement;
    }

    /**
     * @param string $complement
     */
    public function setComplement(string $complement): void
    {
        $this->complement = $complement;
    }

    /**
     * @return string
     */
    public function getNature(): string
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
     * @return float
     */
    public function getWeigth(): float
    {
        return $this->weigth;
    }

    /**
     * @param float $weigth
     */
    public function setWeigth(float $weigth): void
    {
        $this->weigth = $weigth;
    }
}