<?php
/**
 * Doctrine Entity Authors
 * Freely inspired by BioPHP's project biophp.org
 * Created 23 march 2019
 * Last modified 7 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Sequence\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Authors
 * @package Amelaye\BioPHP\Domain\Sequence\Entity
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
#[ORM\Entity]
#[ORM\Table(name: "author")]
#[ORM\Index(name: "author_prim_acc", columns: ["prim_acc", "refno"])]
class Author
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
     * @var int
     */
    #[ORM\Column(type: "integer", nullable: false, options: ["default" => 0])]
    private int $refno = 0;

    /**
     * @var string
     */
    #[ORM\Column(type: "string", length: 255, nullable: false)]
    private string $author = "";

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
     * @return int
     */
    public function getRefno() : int
    {
        return $this->refno;
    }

    /**
     * @param int $refno
     */
    public function setRefno(int $refno) : void
    {
        $this->refno = $refno;
    }

    /**
     * @return string
     */
    public function getAuthor() : string
    {
        return $this->author;
    }

    /**
     * @param string $author
     */
    public function setAuthor(string $author) : void
    {
        $this->author = $author;
    }
}