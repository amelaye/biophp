<?php
/**
 * One parsed database record, kept as a JSON document
 * Freely inspired by BioPHP's project biophp.org
 * Created 20 September 2026
 * Last modified 20 September 2026
 */
namespace Amelaye\BioPHP\Domain\Database\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class ParsedRecord
 * Every parser exposes a different set of fields, so what a parser read is stored as one document
 * rather than one table per format. A record is identified by its format and its entry identifier.
 * @package Amelaye\BioPHP\Domain\Database\Entity
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
#[ORM\Entity]
#[ORM\Table(name: "parsed_record")]
#[ORM\UniqueConstraint(name: "uniq_parsed_record", columns: ["db_format", "entry_id"])]
class ParsedRecord
{
    /**
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer", name: "id")]
    private $id;

    /**
     * @var string
     */
    #[ORM\Column(type: "string", length: 30, nullable: false, name: "db_format")]
    private $dbFormat = "";

    /**
     * @var string
     */
    #[ORM\Column(type: "string", length: 100, nullable: false, name: "entry_id")]
    private $entryId = "";

    /**
     * @var array
     */
    #[ORM\Column(type: "json", nullable: false, name: "data")]
    private $data = [];

    /**
     * @var Collection|null
     */
    #[ORM\ManyToOne(targetEntity: Collection::class)]
    #[ORM\JoinColumn(name: "id_collection", referencedColumnName: "id", nullable: true, onDelete: "SET NULL")]
    private $collection;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getDbFormat(): string
    {
        return $this->dbFormat;
    }

    /**
     * @param string $dbFormat
     */
    public function setDbFormat(string $dbFormat): void
    {
        $this->dbFormat = $dbFormat;
    }

    /**
     * @return string
     */
    public function getEntryId(): string
    {
        return $this->entryId;
    }

    /**
     * @param string $entryId
     */
    public function setEntryId(string $entryId): void
    {
        $this->entryId = $entryId;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
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
