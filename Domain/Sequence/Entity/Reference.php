<?php
/**
 * Doctrine Entity Reference
 * Freely inspired by BioPHP's project biophp.org
 * Created 23 march 2019
 * Last modified 25 july 2026
 */
namespace Amelaye\BioPHP\Domain\Sequence\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Reference
 * @package Amelaye\BioPHP\Domain\Sequence\Entity
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 * @ORM\Entity
 * @ORM\Table(name="reference")
 */
class Reference
{
    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(
     *     type = "string",
     *     length = 8,
     *     options = {"default":0},
     *     nullable = false
     * )
     * @ORM\ManyToOne(targetEntity = "Amelaye\BioPHP\Domain\Sequence\Entity\Sequence")
     * @ORM\JoinColumn(
     *     name = "prim_acc",
     *     referencedColumnName = "prim_acc"
     * )
     * @ORM\OneToMany(
     *     targetEntity = "Amelaye\BioPHP\Domain\Sequence\Entity\Author",
     *     mappedBy = "primAcc"
     * )
     */
    private $primAcc = "";

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
    private $refno = 0;

    /**
     * @var string
     * @ORM\Column(
     *     type = "string",
     *     length = 80,
     *     nullable = true
     * )
     */
    private $baseRange;

    /**
     * @var string
     * @ORM\Column(
     *     type = "string",
     *     length = 255,
     *     nullable = true
     * )
     */
    private $title;

    /**
     * @var string
     * @ORM\Column(
     *     type = "string",
     *     length = 8,
     *     nullable = true
     * )
     */
    private $medline;

    /**
     * @var string
     * @ORM\Column(
     *     type = "string",
     *     length = 20,
     *     nullable = true
     * )
     */
    private $pubmed;

    /**
     * @var string
     * @ORM\Column(
     *     type = "string",
     *     length = 255,
     *     nullable = true
     * )
     */
    private $remark;

    /**
     * @var string
     * @ORM\Column(
     *     type = "text"
     * )
     */
    private $journal = "";

    /**
     * @var string
     * @ORM\Column(
     *     type = "string",
     *     length = 255,
     *     nullable = true
     * )
     */
    private $comments;

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
     * @return string|null
     */
    public function getBaseRange() : ?string
    {
        return $this->baseRange;
    }

    /**
     * @param string|null $baseRange
     */
    public function setBaseRange(?string $baseRange) : void
    {
        $this->baseRange = $baseRange;
    }

    /**
     * @return string|null
     */
    public function getTitle() : ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     */
    public function setTitle(?string $title) : void
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getJournal() : string
    {
        return $this->journal;
    }

    /**
     * @param string $journal
     */
    public function setJournal(string $journal) : void
    {
        $this->journal = $journal;
    }

    /**
     * @return string|null
     */
    public function getMedline() : ?string
    {
        return $this->medline;
    }

    /**
     * @param string|null $medline
     */
    public function setMedline(?string $medline) : void
    {
        $this->medline = $medline;
    }

    /**
     * @return string|null
     */
    public function getPubmed() : ?string
    {
        return $this->pubmed;
    }

    /**
     * @param string|null $pubmed
     */
    public function setPubmed(?string $pubmed) : void
    {
        $this->pubmed = $pubmed;
    }

    /**
     * @return string|null
     */
    public function getRemark() : ?string
    {
        return $this->remark;
    }

    /**
     * @param string|null $remark
     */
    public function setRemark(?string $remark) : void
    {
        $this->remark = $remark;
    }

    /**
     * @return string|null
     */
    public function getComments(): ?string
    {
        return $this->comments;
    }

    /**
     * @param string|null $comments
     */
    public function setComments(?string $comments): void
    {
        $this->comments = $comments;
    }
}