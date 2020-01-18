<?php
/**
 * Database of Triplets
 * Inspired by BioPHP's project biophp.org
 * Created 20 december 2019
 * Last modified 20 december 2019
 */
namespace App\Api\DTO;

/**
 * Database of Triplets
 * @package App\Api\DTO
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class TripletDTO
{
    /**
     * @var     int     The id (auto-increment)
     */
    private $id;

    /**
     * TTT, TTC ...
     * @var     string
     */
    private $triplet;


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
    public function getTriplet(): string
    {
        return $this->triplet;
    }

    /**
     * @param string $triplet
     */
    public function setTriplet(string $triplet): void
    {
        $this->triplet = $triplet;
    }
}