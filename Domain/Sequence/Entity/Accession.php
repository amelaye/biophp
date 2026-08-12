<?php
/**
 * Doctrine Entity Accession
 * Freely inspired by BioPHP's project biophp.org
 * Created 23 march 2019
 * Last modified 11 August 2026
 */
namespace Amelaye\BioPHP\Domain\Sequence\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Accession
 * @package Amelaye\BioPHP\Domain\Sequence\Entity
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
#[ORM\Entity]
#[ORM\Table(name: "accession")]
#[ORM\UniqueConstraint(name: "prim_acc", columns: ["prim_acc", "accession"])]
class Accession
{

    /**
     * @var string
     */
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Sequence::class)]
    #[ORM\JoinColumn(name: "prim_acc", referencedColumnName: "prim_acc")]
    private $primAcc;

    /**
     * @var string
     */
    #[ORM\Id]
    #[ORM\Column(type: "string", length: 8, nullable: false)]
    private $accession;

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