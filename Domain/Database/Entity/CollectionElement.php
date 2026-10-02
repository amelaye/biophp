<?php
/**
 * Replaces the .dir file
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
#[ORM\Table(name: "collection_element")]
class CollectionElement
{
    /**
     * @var string
     */
    #[ORM\Id]
    #[ORM\Column(type: "string", length: 20)]
    private ?string $idElement = null;

    /**
     * @var string
     */
    #[ORM\Column(type: "string", length: 50, nullable: false)]
    private ?string $fileName = null;


    /**
     * @var string
     */
    #[ORM\Column(type: "string", length: 50, nullable: false)]
    private ?string $dbFormat = null;

    /**
     * @var int
     */
    #[ORM\Column(type: "integer", length: 5)]
    private ?int $lineNo = null;

    /**
     * @var int
     */
    #[ORM\Column(type: "integer", length: 5)]
    private ?int $seqCount = null;

    /**
     * @var Collection|null
     */
    #[ORM\ManyToOne(targetEntity: Collection::class)]
    #[ORM\JoinColumn(name: "id_collection", referencedColumnName: "id")]
    private ?Collection $collection = null;

    /**
     * @return string
     */
    public function getIdElement() : string {
        return $this->idElement;
    }

    /**
     * @param string $idElement
     */
    public function setIdElement(string $idElement) {
        $this->idElement = $idElement;
    }

    /**
     * @return string
     */
    public function getFileName() : string {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     */
    public function setFileName(string $fileName) {
        $this->fileName = $fileName;
    }

    /**
     * @return string
     */
    public function getDbFormat() : string {
        return $this->dbFormat;
    }

    /**
     * @param string $dbFormat
     */
    public function setDbFormat(string $dbFormat) {
        $this->dbFormat = $dbFormat;
    }

    /**
     * @return int
     */
    public function getLineNo() : int {
        return $this->lineNo;
    }

    /**
     * @param int $lineNo
     */
    public function setLineNo(int $lineNo) {
        $this->lineNo = $lineNo;
    }

    /**
     * @return int
     */
    public function getSeqCount() : int {
        return $this->seqCount;
    }

    /**
     * @param int $seqCount
     */
    public function setSeqCount(int $seqCount) {
        $this->seqCount = $seqCount;
    }

    /**
     * @return Collection|null
     */
    public function getCollection(): ?Collection
    {
        return $this->collection;
    }

    /**
     * @param Collection|null $collection
     */
    public function setCollection(?Collection $collection): void
    {
        $this->collection = $collection;
    }
}