<?php
/**
 * Doctrine Entity Accession
 * Freely inspired by BioPHP's project biophp.org
 * Created 23 march 2019
 * Last modified 7 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Sequence\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Accession
 * @package Amelaye\BioPHP\Domain\Sequence\Entity
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
#[ORM\Entity]
#[ORM\Table(name: "accession")]
#[ORM\UniqueConstraint(name: "uniq_accession", columns: ["prim_acc", "accession"])]
class Accession
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
     * @var string
     */
    #[ORM\Column(type: "string", length: 50, nullable: false)]
    private string $accession = "";

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
    public function getPrimAcc() : string
    {
        return $this->primAcc;
    }

    /**
     * @param string $primAcc
     */
    public function setPrimAcc(string $primAcc) : void
    {
        $this->primAcc = $primAcc;
    }

    /**
     * @return string
     */
    public function getAccession() : string
    {
        return $this->accession;
    }

    /**
     * @param string $accession
     */
    public function setAccession(string $accession) : void
    {
        $this->accession = $accession;
    }
}