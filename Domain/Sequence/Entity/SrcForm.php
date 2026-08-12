<?php
/**
 * Doctrine Entity SrcForm
 * Freely inspired by BioPHP's project biophp.org
 * Created 23 march 2019
 * Last modified 12 August 2026
 */
namespace Amelaye\BioPHP\Domain\Sequence\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class SrcForm
 * @package Amelaye\BioPHP\Domain\Sequence\Entity
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
#[ORM\Entity]
#[ORM\Table(name: "src_form")]
#[ORM\UniqueConstraint(name: "prim_acc", columns: ["prim_acc"])]
class SrcForm
{
    /**
     * @var string
     */
    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: Sequence::class)]
    #[ORM\JoinColumn(name: "prim_acc", referencedColumnName: "prim_acc")]
    private $primAcc = "";

    /**
     * @var string
     */
    #[ORM\Column(type: "text")]
    private $entry = "";

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
    public function getEntry() : string
    {
        return $this->entry;
    }

    /**
     * @param string $entry
     */
    public function setEntry(string $entry) : void
    {
        $this->entry = $entry;
    }
}