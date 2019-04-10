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
 * @ORM\Table(name = "db_infos")
 */
class DbInfos
{
    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(
     *     type = "string",
     *     length = 50,
     *     nullable = false
     * )
     */
    private $id;

    /**
     * @var int
     * @ORM\ManyToOne(
     *     targetEntity = "SeqDatabaseBundle\Entity\Database",
     *     inversedBy="id"
     * )
     */
    private $database;

    /**
     * @var int
     * @ORM\Column(
     *     type = "integer",
     *     length = 5
     * )
     */
    private $lineNo;

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
     * @return int
     */
    public function getIdDatabase()
    {
        return $this->database;
    }

    /**
     * @param int $database
     */
    public function setIdDatabase($database)
    {
        $this->database = $database;
    }

    /**
     * @return int
     */
    public function getLineNo()
    {
        return $this->lineNo;
    }

    /**
     * @param int $lineNo
     */
    public function setLineNo($lineNo)
    {
        $this->lineNo = $lineNo;
    }
}