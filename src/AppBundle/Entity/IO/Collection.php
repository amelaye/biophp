<?php
/**
 * Replaces the .idx file
 * Freely inspired by BioPHP's project biophp.org
 * Created 10 april 2019
 * Last modified 10 april 2019
 */
namespace SeqDatabaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Database
 * @package SeqDatabaseBundle\Entity
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 * @ORM\Entity
 * @ORM\Table(name = "collection")
 */
class Collection
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(
     *     type = "integer",
     *     length = 5,
     *     nullable = false,
     *     name = "id"
     * )
     * @ORM\OneToMany(
     *     targetEntity = "SeqDatabaseBundle\Entity\CollectionElement",
     *     mappedBy = "id_collection",
     *     cascade = {"persist"}
     * )
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(
     *     type = "string",
     *     length = 50,
     *     nullable = false
     * )
     */
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