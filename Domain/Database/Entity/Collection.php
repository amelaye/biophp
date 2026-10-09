<?php
/**
 * Replaces the .idx file
 * Freely inspired by BioPHP's project biophp.org
 * Created 10 april 2019
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Database\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Database
 * @package Amelaye\BioPHP\Domain\Database\Entity
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
#[ORM\Entity]
#[ORM\Table(name: "collection")]
class Collection
{
    /**
     * @var int
     */
    #[ORM\Id]
    #[ORM\Column(type: "integer", length: 5, nullable: false, name: "id")]
    #[ORM\OneToMany(targetEntity: CollectionElement::class, mappedBy: "id_collection", cascade: ["persist"])]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    /**
     * @var string
     */
    #[ORM\Column(type: "string", length: 50, nullable: false)]
    private ?string $nomCollection = null;

    /**
     * @return int|null
     */
    public function getId() : ?int {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id) {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getNomCollection() : string {
        return $this->nomCollection;
    }

    /**
     * @param string $nomCollection
     */
    public function setNomCollection(string $nomCollection) {
        $this->nomCollection = $nomCollection;
    }
}