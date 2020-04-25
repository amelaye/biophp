<?php
/**
 * Doctrine Entity Authors
 * Freely inspired by BioPHP's project biophp.org
 * Created 23 march 2019
 * Last modified 18 january 2020
 */
namespace Amelaye\BioPHP\Domain\Sequence\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Authors
 * @package Amelaye\BioPHP\Domain\Sequence\Entity
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 * @ORM\Entity
 * @ORM\Table(name = "author")
 */
class Author
{

    /**
     * @var string
     * @ORM\Id
     * @ORM\ManyToOne(
     *     targetEntity = "Amelaye\BioPHP\Domain\Sequence\Entity\Reference",
     *     inversedBy="primAcc"
     * )
     * @ORM\JoinColumn(
     *     name = "prim_acc",
     *     referencedColumnName = "prim_acc"
     * )
     */
    private $primAcc;

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(
     *     type = "integer",
     *     length = 11,
     *     nullable = false,
     *     options = {"default":0}
     * )
     */
    private $refno;

    /**
     * @var string
     * @ORM\Column(
     *     type = "string",
     *     length = 50,
     *     nullable = false
     * )
     */
    private $author;

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