<?php
/**
 * Doctrine Entity Keywords
 * Freely inspired by BioPHP's project biophp.org
 * Created 23 march 2019
 * Last modified 12 August 2026
 */
namespace Amelaye\BioPHP\Domain\Sequence\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Keywords
 * @package Amelaye\BioPHP\Domain\Sequence\Entity
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
#[ORM\Entity]
#[ORM\Table(name: "keyword")]
#[ORM\UniqueConstraint(name: "prim_acc", columns: ["prim_acc", "keywords"])]
class Keyword
{
    /**
     * @var string
     */
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Sequence::class)]
    #[ORM\JoinColumn(name: "prim_acc", referencedColumnName: "prim_acc")]
    private $primAcc = "";

    /**
     * @var string
     */
    #[ORM\Id]
    #[ORM\Column(type: "string", length: 80, nullable: false)]
    private $keywords = "";

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
    public function getKeywords() : string
    {
        return $this->keywords;
    }

    /**
     * @param string $keywords
     */
    public function setKeywords(string $keywords) : void
    {
        $this->keywords = $keywords;
    }
}