<?php
/**
 * Doctrine Entity Reference
 * Freely inspired by BioPHP's project biophp.org
 * Created 23 march 2019
 * Last modified 7 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Sequence\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Reference
 * @package Amelaye\BioPHP\Domain\Sequence\Entity
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
#[ORM\Entity]
#[ORM\Table(name: "reference")]
#[ORM\UniqueConstraint(name: "uniq_reference", columns: ["prim_acc", "refno"])]
class Reference
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
     * @var string|null
     */
    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $baseRange = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: "text", nullable: true)]
    private ?string $title = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: "string", length: 20, nullable: true)]
    private ?string $medline = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: "string", length: 20, nullable: true)]
    private ?string $pubmed = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: "text", nullable: true)]
    private ?string $remark = null;

    /**
     * @var string
     */
    #[ORM\Column(type: "text")]
    private string $journal = "";

    /**
     * @var string|null
     */
    #[ORM\Column(type: "text", nullable: true)]
    private ?string $comments = null;

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