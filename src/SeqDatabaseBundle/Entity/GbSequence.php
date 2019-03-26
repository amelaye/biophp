<?php
/**
 * Doctrine Entity GbSequence
 * Freely inspired by BioPHP's project biophp.org
 * Created 23 march 2019
 * Last modified 26 march 2019
 */
namespace SeqDatabaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class GbSequence
 * @package SeqDatabaseBundle\Entity
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
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
     * @ORM\OneToOne(targetEntity = "SeqDatabaseBundle\Entity\Sequence")
     * @ORM\JoinColumn(
     *     name = "prim_acc",
     *     referencedColumnName = "prim_acc"
     * )
     */
    private $primAcc;

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
    public function getPrimAcc()
    {
        return $this->primAcc;
    }

    /**
     * @param string $primAcc
     */
    public function setPrimAcc($primAcc)
    {
        $this->primAcc = $primAcc;
    }

    /**
     * @return string
     */
    public function getStrands()
    {
        return $this->strands;
    }

    /**
     * @param string $strands
     */
    public function setStrands($strands)
    {
        $this->strands = $strands;
    }

    /**
     * @return string
     */
    public function getTopology()
    {
        return $this->topology;
    }

    /**
     * @param string $topology
     */
    public function setTopology($topology)
    {
        $this->topology = $topology;
    }

    /**
     * @return string
     */
    public function getDivision()
    {
        return $this->division;
    }

    /**
     * @param string $division
     */
    public function setDivision($division)
    {
        $this->division = $division;
    }

    /**
     * @return int
     */
    public function getSegmentNo()
    {
        return $this->segmentNo;
    }

    /**
     * @param int $segmentNo
     */
    public function setSegmentNo($segmentNo)
    {
        $this->segmentNo = $segmentNo;
    }

    /**
     * @return int
     */
    public function getSegmentCount()
    {
        return $this->segmentCount;
    }

    /**
     * @param int $segmentCount
     */
    public function setSegmentCount($segmentCount)
    {
        $this->segmentCount = $segmentCount;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param string $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getNcbiGiId()
    {
        return $this->ncbiGiId;
    }

    /**
     * @param string $ncbiGiId
     */
    public function setNcbiGiId($ncbiGiId)
    {
        $this->ncbiGiId = $ncbiGiId;
    }
}