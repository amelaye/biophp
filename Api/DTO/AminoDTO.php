<?php
/**
 * Database of elements - Amino acids
 * Inspired by BioPHP's project biophp.org
 * Created 1st December 2019
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Api\DTO;

/**
 * Database of elements - Amino acids
 * @package Amelaye\BioPHP\Api\DTO
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class AminoDTO
{
    /**
     * @var     string      Id of the amino acid (auto-increment)
     */
    private ?string $id = null;

    /**
     * @var     string      Name of the amino
     */
    private ?string $name = null;

    /**
     * @var     string      Name in 1 letter (A, L, S ...)
     */
    private ?string $name1Letter = null;

    /**
     * @var string          Name in 3 letters (Ser, Leu ...)
     */
    private ?string $name3Letters = null;

    /**
     * @var float
     */
    private ?float $weight1 = null;

    /**
     * @var float
     */
    private ?float $weight2 = null;

    /**
     * @var float
     */
    private ?float $residueMolWeight = null;

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
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName1Letter(): string
    {
        return $this->name1Letter;
    }

    /**
     * @param string $name1Letter
     */
    public function setName1Letter(string $name1Letter): void
    {
        $this->name1Letter = $name1Letter;
    }

    /**
     * @return string
     */
    public function getName3Letters(): string
    {
        return $this->name3Letters;
    }

    /**
     * @param string $name3Letters
     */
    public function setName3Letters(string $name3Letters): void
    {
        $this->name3Letters = $name3Letters;
    }

    /**
     * @return float
     */
    public function getWeight1(): float
    {
        return $this->weight1;
    }

    /**
     * @param float $weight1
     */
    public function setWeight1(float $weight1): void
    {
        $this->weight1 = $weight1;
    }

    /**
     * @return float
     */
    public function getWeight2(): float
    {
        return $this->weight2;
    }

    /**
     * @param float $weight2
     */
    public function setWeight2(float $weight2): void
    {
        $this->weight2 = $weight2;
    }

    /**
     * @return float
     */
    public function getResidueMolWeight(): ?float
    {
        return $this->residueMolWeight;
    }

    /**
     * @param float $residueMolWeight
     */
    public function setResidueMolWeight(float $residueMolWeight): void
    {
        $this->residueMolWeight = $residueMolWeight;
    }
}