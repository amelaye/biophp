<?php
/**
 * Doctrine Entity GbFeatures
 * Freely inspired by BioPHP's project biophp.org
 * Created 23 march 2019
 * Last modified 7 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Sequence\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class GbFeatures
 * @package Amelaye\BioPHP\Domain\Sequence\Entity
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
#[ORM\Entity]
#[ORM\Table(name: "feature")]
#[ORM\Index(name: "feature_prim_acc", columns: ["prim_acc"])]
class Feature
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
    #[ORM\Column(type: "string", length: 20, nullable: false)]
    private string $ftKey = "";

    /**
     * @var int
     */
    #[ORM\Column(type: "integer", length: 11, nullable: true)]
    private ?int $ftFrom = null;

    /**
     * @var int
     */
    #[ORM\Column(type: "integer", length: 11, nullable: true)]
    private ?int $ftTo = null;

    /**
     * @var string
     */
    #[ORM\Column(type: "string", length: 60, nullable: false)]
    private string $ftQual = "";

    /**
     * @var string
     */
    #[ORM\Column(type: "text")]
    private string $ftValue = "";

    /**
     * @var string
     */
    #[ORM\Column(type: "text")]
    private string $ftDesc = "";

    /**
     * The strand the feature was read from : "+" (direct/sense) or "-" (the location was wrapped
     * in "complement(...)"). Null when the format has no strand concept (e.g. Swiss-Prot, whose
     * features are positions on a protein sequence) or none was recorded.
     * @var string|null
     */
    #[ORM\Column(type: "string", length: 1, nullable: true)]
    private ?string $strand = null;

    /**
     * The location exactly as the record wrote it (INSDC syntax, e.g. "join(94..300,401..1482)").
     * ftFrom and ftTo only keep its outer bounds : this keeps the exons of a spliced feature, and
     * any partial ("<", ">") mark. Null when the format has no such syntax or none was recorded.
     * @var string|null
     */
    #[ORM\Column(type: "text", nullable: true)]
    private ?string $ftLocation = null;

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
    public function getFtKey() : string
    {
        return $this->ftKey;
    }

    /**
     * @param string $ftKey
     */
    public function setFtKey(string $ftKey) : void
    {
        $this->ftKey = $ftKey;
    }

    /**
     * @return string
     */
    public function getFtQual() : string
    {
        return $this->ftQual;
    }

    /**
     * @param string $ftQual
     */
    public function setFtQual(string $ftQual) : void
    {
        $this->ftQual = $ftQual;
    }

    /**
     * @return string
     */
    public function getFtValue() : string
    {
        return $this->ftValue;
    }

    /**
     * @param string $ftValue
     */
    public function setFtValue(string $ftValue) : void
    {
        $this->ftValue = $ftValue;
    }

    /**
     * @return int|null
     */
    public function getFtFrom() : ?int
    {
        return $this->ftFrom;
    }

    /**
     * @param int|null $ftFrom
     */
    public function setFtFrom(?int $ftFrom) : void
    {
        $this->ftFrom = $ftFrom;
    }

    /**
     * @return int|null
     */
    public function getFtTo(): ?int
    {
        return $this->ftTo;
    }

    /**
     * @param int|null $ftTo
     */
    public function setFtTo(?int $ftTo) : void
    {
        $this->ftTo = $ftTo;
    }

    /**
     * @return string
     */
    public function getFtDesc() : string
    {
        return $this->ftDesc;
    }

    /**
     * @param string $ftDesc
     */
    public function setFtDesc(string $ftDesc) : void
    {
        $this->ftDesc = $ftDesc;
    }

    /**
     * @return string|null
     */
    public function getStrand() : ?string
    {
        return $this->strand;
    }

    /**
     * @param string|null $strand
     */
    public function setStrand(?string $strand) : void
    {
        $this->strand = $strand;
    }

    /**
     * @return string|null
     */
    public function getFtLocation() : ?string
    {
        return $this->ftLocation;
    }

    /**
     * @param string|null $ftLocation
     */
    public function setFtLocation(?string $ftLocation) : void
    {
        $this->ftLocation = $ftLocation;
    }

    /**
     * Tells whether the feature extends beyond the bases its location gives ("<1..206", the start
     * lies before base 1 ; "1..>888", the end lies after base 888), as the location was written.
     * @return bool     False as well when no location was recorded
     */
    public function isPartial() : bool
    {
        return $this->ftLocation !== null && strpbrk($this->ftLocation, "<>") !== false;
    }
}
