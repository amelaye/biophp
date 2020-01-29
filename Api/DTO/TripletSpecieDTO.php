<?php
/**
 * Database of Triplets
 * Inspired by BioPHP's project biophp.org
 * Created 20 december 2019
 * Last modified 20 december 2019
 */
namespace Amelaye\BioPHP\Api\DTO;

/**
 * Database of elements - Triplets ans Species
 * @package Amelaye\BioPHP\Api\DTO
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class TripletSpecieDTO
{
    /**
     * @var     int     The id (auto-increment)
     */
    private $id;

    /**
     * Standard, Vertebrate mitochondrial ...
     * @var     string
     */
    private $nature;

    /**
     * @var     array
     */
    private $triplets;

    /**
     * @var     array
     */
    private $tripletsGroups;

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
     * @return array
     */
    public function getTriplets(): array
    {
        return $this->triplets;
    }

    /**
     * @param array $triplets
     */
    public function setTriplets(array $triplets): void
    {
        $this->triplets = $triplets;
    }

    /**
     * @return array
     */
    public function getTripletsGroups(): array
    {
        return $this->tripletsGroups;
    }

    /**
     * @param array $tripletsGroups
     */
    public function setTripletsGroups(array $tripletsGroups): void
    {
        $this->tripletsGroups = $tripletsGroups;
    }
}