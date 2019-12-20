<?php
/**
 * Database of elements - weigths included
 * Inspired by BioPHP's project biophp.org
 * Created 20 december 2019
 * Last modified 20 december 2019
 */
namespace AppBundle\Api\DTO;

/**
 * Database of elements - weights included
 * @package AppBundle\Api\DTO
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class ElementDTO
{
    /**
     * @var     int     The id (auto-increment)
     */
    private $id;

    /**
     * @var     string  Water, carbone, for example
     */
    private $name;

    /**
     * @var     float   The weight of the nucleotid
     */
    private $weight;

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
     * @return float
     */
    public function getWeight(): float
    {
        return $this->weight;
    }

    /**
     * @param float $weight
     */
    public function setWeight(float $weight): void
    {
        $this->weight = $weight;
    }
}