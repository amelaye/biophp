<?php
/**
 * Replaces the .dir file
 * Freely inspired by BioPHP's project biophp.org
 * Created 10 april 2019
 * Last modified 19 january 2020
 */
namespace Amelaye\BioPHP\Domain\Database\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Database
 * @package Amelaye\BioPHP\Domain\Database\Entity
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 * @ORM\Entity
 * @ORM\Table(name = "collection_element")
 */
class CollectionElement
{
    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(
     *     type = "string",
     *     length = 20
     * )
     */
    private $idElement;

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
     * @var string
     * @ORM\Column(
     *     type = "string",
     *     length = 50,
     *     nullable = false
     * )
     */
    private $dbFormat;

    /**
     * @var int
     * @ORM\Column(
     *     type = "integer",
     *     length = 5
     * )
     */
    private $lineNo;

    /**
     * @var int
     * @ORM\Column(
     *     type = "integer",
     *     length = 5
     * )
     */
    private $seqCount;

    /**
     * @var int
     * @ORM\ManyToOne(
     *     targetEntity = "Amelaye\BioPHP\Domain\Database\Entity\Collection"
     * ),
     * @ORM\JoinColumn(
     *     name = "id_collection",
     *     referencedColumnName = "id"
     * )
     */
    private $collection;

    /**
     * @return string
     */
    public function getIdElement()
    {
        return $this->idElement;
    }

    /**
     * @param string $idElement
     */
    public function setIdElement($idElement)
    {
        $this->idElement = $idElement;
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

    /**
     * @return string
     */
    public function getDbFormat()
    {
        return $this->dbFormat;
    }

    /**
     * @param string $dbFormat
     */
    public function setDbFormat($dbFormat)
    {
        $this->dbFormat = $dbFormat;
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

    /**
     * @return int
     */
    public function getSeqCount()
    {
        return $this->seqCount;
    }

    /**
     * @param int $seqCount
     */
    public function setSeqCount($seqCount)
    {
        $this->seqCount = $seqCount;
    }

    /**
     * @return int
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * @param int $collection
     */
    public function setCollection($collection)
    {
        $this->collection = $collection;
    }
}