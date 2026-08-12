<?php
/**
 * Replaces the .idx file
 * Freely inspired by BioPHP's project biophp.org
 * Created 10 april 2019
 * Last modified 11 August 2026
 */
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
    private $id;

    /**
     * @var string
     */
    #[ORM\Column(type: "string", length: 50, nullable: false)]
    private $nomCollection;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getNomCollection()
    {
        return $this->nomCollection;
    }

    /**
     * @param string $nomCollection
     */
    public function setNomCollection($nomCollection)
    {
        $this->nomCollection = $nomCollection;
    }
}