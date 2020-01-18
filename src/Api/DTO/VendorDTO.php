<?php
/**
 * Database of elements - TypeIIs Endonucleolases
 * Inspired by BioPHP's project biophp.org
 * Created 20 december 2019
 * Last modified 20 december 2019
 */
namespace App\Api\DTO;

/**
 * Enzymes - Vendors
 * @package App\Api\DTO
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class VendorDTO
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $vendor;

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
    public function getVendor(): string
    {
        return $this->vendor;
    }

    /**
     * @param string $vendor
     */
    public function setVendor(string $vendor): void
    {
        $this->vendor = $vendor;
    }
}