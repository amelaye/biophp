<?php
/**
 * Doctrine Entity GbSequence
 * Freely inspired by BioPHP's project biophp.org
 * Created 23 march 2019
 * Last modified 25 july 2026
 */
namespace Amelaye\BioPHP\Domain\Sequence\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class GbSequence
 * @package Amelaye\BioPHP\Domain\Sequence\Entity
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 * @ORM\Entity
 * @ORM\Table(
 *     name = "gb_sequence",
 *     uniqueConstraints = {
 *        @ORM\UniqueConstraint(
 *            name = "prim_acc",
 *            columns = {"prim_acc"})
 *     }
 * )
 */
class GbSequence
{
    /**
     * @var string
     * @ORM\Id
     * @ORM\OneToOne(targetEntity = "Amelaye\BioPHP\Domain\Sequence\Entity\Sequence")
     * @ORM\JoinColumn(
     *     name = "prim_acc",
     *     referencedColumnName = "prim_acc"
     * )
     */
    private $primAcc = "";

    /**
     * @var string
     * @ORM\Column(
     *     type = "string",
     *     length = 2,
     *     nullable = true
     * )
     */
    private $strands;

    /**
     * @var string
     * @ORM\Column(
     *     type = "string",
     *     length = 1,
     *     nullable = true
     * )
     */
    private $topology;

    /**
     * @var string
     * @ORM\Column(
     *     type = "string",
     *     length = 3,
     *     nullable = true
     * )
     */
    private $division;

    /**
     * @var int
     * @ORM\Column(
     *     type = "integer",
     *     length = 11,
     *     nullable = true
     * )
     */
    private $segmentNo;

    /**
     * @var int
     * @ORM\Column(
     *     type = "integer",
     *     length = 11,
     *     nullable = true
     * )
     */
    private $segmentCount;

    /**
     * @var string
     * @ORM\Column(
     *     type = "string",
     *     length = 10,
     *     nullable = true
     * )
     */
    private $version;

    /**
     * @var string
     * @ORM\Column(
     *     type="string",
     *     length = 30,
     *     nullable = true
     * )
     */
    private $ncbiGiId;

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