<?php
/**
 * Replaces the .dir file
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
 * @ORM\Table(name = "database")
 */
class Database
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(
     *     type = "integer",
     *     length = 5
     * )
     * @ORM\GeneratedValue
     * @ORM\OneToMany(
     *     targetEntity = "SeqDatabaseBundle\Entity\DbInfos",
     *     mappedBy = "database"
     * )
     * @ORM\JoinColumn(
     *     name = "database",
     *     referencedColumnName = "database"
     * )
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
    private $fileName;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }
}