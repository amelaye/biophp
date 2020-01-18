<?php
/**
 * Database of elements - TypeIIs Endonucleolases
 * Inspired by BioPHP's project biophp.org
 * Created 20 december 2019
 * Last modified 20 december 2019
 */
namespace App\Api\DTO;

/**
 * Enzymes - TypeIIb Endonucleases
 * @package App\Api\DTO
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class TypeIIbEndonucleaseDTO
{
    /**
     * @var     string      First endonucleolase of the list
     */
    private $id;

    /**
     * @var     array       All endonucleolases recognizing the same pattern
     */
    private $samePattern;

    /**
     * @var     string      Recognition pattern
     */
    private $recognitionPattern;

    /**
     * @var     string      Recognition pattern for computing
     * @ORM\Column(type="text")
     */
    private $computingPattern;

    /**
     * @var     int         Length of all recognition pattern
     */
    private $lengthRecognitionPattern;

    /**
     * @var     int         Cleavage position in upper strand
     */
    private $cleavagePosUpper;

    /**
     * @var     int         Cleavage position in lower strand, relative to previous one
     */
    private $cleavagePosLower;

    /**
     * @var     int         Number of non-N bases within recognition pattern
     */
    private $nbNonNBases;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return array
     */
    public function getSamePattern(): array
    {
        return $this->samePattern;
    }

    /**
     * @param array $samePattern
     */
    public function setSamePattern(array $samePattern): void
    {
        $this->samePattern = $samePattern;
    }

    /**
     * @return string
     */
    public function getRecognitionPattern(): string
    {
        return $this->recognitionPattern;
    }

    /**
     * @param string $recognitionPattern
     */
    public function setRecognitionPattern(string $recognitionPattern): void
    {
        $this->recognitionPattern = $recognitionPattern;
    }

    /**
     * @return string
     */
    public function getComputingPattern(): string
    {
        return $this->computingPattern;
    }

    /**
     * @param string $computingPattern
     */
    public function setComputingPattern(string $computingPattern): void
    {
        $this->computingPattern = $computingPattern;
    }

    /**
     * @return int
     */
    public function getLengthRecognitionPattern(): int
    {
        return $this->lengthRecognitionPattern;
    }

    /**
     * @param int $lengthRecognitionPattern
     */
    public function setLengthRecognitionPattern(int $lengthRecognitionPattern): void
    {
        $this->lengthRecognitionPattern = $lengthRecognitionPattern;
    }

    /**
     * @return int
     */
    public function getCleavagePosUpper(): int
    {
        return $this->cleavagePosUpper;
    }

    /**
     * @param int $cleavagePosUpper
     */
    public function setCleavagePosUpper(int $cleavagePosUpper): void
    {
        $this->cleavagePosUpper = $cleavagePosUpper;
    }

    /**
     * @return int
     */
    public function getCleavagePosLower(): int
    {
        return $this->cleavagePosLower;
    }

    /**
     * @param int $cleavagePosLower
     */
    public function setCleavagePosLower(int $cleavagePosLower): void
    {
        $this->cleavagePosLower = $cleavagePosLower;
    }

    /**
     * @return int
     */
    public function getNbNonNBases(): int
    {
        return $this->nbNonNBases;
    }

    /**
     * @param int $nbNonNBases
     */
    public function setNbNonNBases(int $nbNonNBases): void
    {
        $this->nbNonNBases = $nbNonNBases;
    }
}