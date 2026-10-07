<?php
/**
 * Doctrine Entity Swissprot databank
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 november 2019
 * Last modified 7 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Sequence\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class SrcForm
 * @package Amelaye\BioPHP\Domain\Sequence\Entity
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
#[ORM\Entity]
#[ORM\Table(name: "sp_databank")]
#[ORM\Index(name: "sp_databank_prim_acc", columns: ["prim_acc"])]
class SpDatabank
{
    /**
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    /**
     * The primary accession of the sequence this row belongs to : a plain column, so that a
     * parsed record can be stored as the parser builds it, with its accession as a string.
     * @var string
     */
    #[ORM\Column(type: "string", length: 50, nullable: false)]
    private string $primAcc = "";

    /**
     * @var string|null
     */
    #[ORM\Column(type: "string", length: 50, nullable: true)]
    private ?string $dbName = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $pid1 = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: "string", length: 100, nullable: true)]
    private ?string $pid2 = null;

    /**
     * @return int|null     Null until the row is stored
     */
    public function getId() : ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getPrimAcc(): string
    {
        return $this->primAcc;
    }

    /**
     * @param string $primAcc
     */
    public function setPrimAcc(string $primAcc): void
    {
        $this->primAcc = $primAcc;
    }

    /**
     * @return string|null
     */
    public function getDbName(): ?string
    {
        return $this->dbName;
    }

    /**
     * @param string|null $dbName
     */
    public function setDbName(?string $dbName): void
    {
        $this->dbName = $dbName;
    }

    /**
     * @return string|null
     */
    public function getPid1(): ?string
    {
        return $this->pid1;
    }

    /**
     * @param string|null $pid1
     */
    public function setPid1(?string $pid1): void
    {
        $this->pid1 = $pid1;
    }

    /**
     * @return string|null
     */
    public function getPid2(): ?string
    {
        return $this->pid2;
    }

    /**
     * @param string|null $pid2
     */
    public function setPid2(?string $pid2): void
    {
        $this->pid2 = $pid2;
    }
}