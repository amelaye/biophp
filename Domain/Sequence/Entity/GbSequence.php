<?php
/**
 * Doctrine Entity GbSequence
 * Freely inspired by BioPHP's project biophp.org
 * Created 23 march 2019
 * Last modified 7 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Sequence\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class GbSequence
 * @package Amelaye\BioPHP\Domain\Sequence\Entity
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
#[ORM\Entity]
#[ORM\Table(name: "gb_sequence")]
class GbSequence
{
    /**
     * @var string
     */
    #[ORM\Id]
    #[ORM\Column(type: "string", length: 50, nullable: false)]
    private string $primAcc = "";

    /**
     * @var string|null
     */
    #[ORM\Column(type: "string", length: 10, nullable: true)]
    private ?string $strands = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: "string", length: 10, nullable: true)]
    private ?string $topology = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: "string", length: 10, nullable: true)]
    private ?string $division = null;

    /**
     * @var int|null
     */
    #[ORM\Column(type: "integer", length: 11, nullable: true)]
    private ?int $segmentNo = null;

    /**
     * @var int|null
     */
    #[ORM\Column(type: "integer", length: 11, nullable: true)]
    private ?int $segmentCount = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: "string", length: 50, nullable: true)]
    private ?string $version = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: "string", length: 30, nullable: true)]
    private ?string $ncbiGiId = null;

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
     * @return string|null
     */
    public function getStrands() : ?string
    {
        return $this->strands;
    }

    /**
     * @param string|null $strands
     */
    public function setStrands(?string $strands) : void
    {
        $this->strands = $strands;
    }

    /**
     * @return string|null
     */
    public function getTopology() : ?string
    {
        return $this->topology;
    }

    /**
     * @param string|null $topology
     */
    public function setTopology(?string $topology) : void
    {
        $this->topology = $topology;
    }

    /**
     * @return string|null
     */
    public function getDivision() : ?string
    {
        return $this->division;
    }

    /**
     * @param string|null $division
     */
    public function setDivision(?string $division) : void
    {
        $this->division = $division;
    }

    /**
     * @return int|null
     */
    public function getSegmentNo() : ?int
    {
        return $this->segmentNo;
    }

    /**
     * @param int|null $segmentNo
     */
    public function setSegmentNo(?int $segmentNo) : void
    {
        $this->segmentNo = $segmentNo;
    }

    /**
     * @return int|null
     */
    public function getSegmentCount() : ?int
    {
        return $this->segmentCount;
    }

    /**
     * @param int|null $segmentCount
     */
    public function setSegmentCount(?int $segmentCount) : void
    {
        $this->segmentCount = $segmentCount;
    }

    /**
     * @return string|null
     */
    public function getVersion() : ?string
    {
        return $this->version;
    }

    /**
     * @param string|null $version
     */
    public function setVersion(?string $version) : void
    {
        $this->version = $version;
    }

    /**
     * @return string|null
     */
    public function getNcbiGiId() : ?string
    {
        return $this->ncbiGiId;
    }

    /**
     * @param string|null $ncbiGiId
     */
    public function setNcbiGiId(?string $ncbiGiId) : void
    {
        $this->ncbiGiId = $ncbiGiId;
    }
}