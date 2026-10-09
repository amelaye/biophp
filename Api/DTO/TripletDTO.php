<?php
/**
 * Database of Triplets
 * Inspired by BioPHP's project biophp.org
 * Created 20 december 2019
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Api\DTO;

/**
 * Database of Triplets
 * @package Amelaye\BioPHP\Api\DTO
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class TripletDTO
{
    /**
     * @var     int     The id (auto-increment)
     */
    private ?int $id = null;

    /**
     * TTT, TTC ...
     * @var     string
     */
    private ?string $triplet = null;


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