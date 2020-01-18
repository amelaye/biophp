<?php
/**
 * Database of elements - TypeIIs Endonucleolases
 * Inspired by BioPHP's project biophp.org
 * Created 20 december 2019
 * Last modified 20 december 2019
 */
namespace App\Api\DTO;

/**
 * Enzymes - Vendors Links
 * @package App\Api\DTO
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class VendorLinkDTO
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $link;

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
    public function getLink(): string
    {
        return $this->link;
    }

    /**
     * @param string $link
     */
    public function setLink(string $link): void
    {
        $this->link = $link;
    }
}