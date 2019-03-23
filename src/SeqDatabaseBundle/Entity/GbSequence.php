<?php
/**
 * Doctrine Entity GbSequence
 * Freely inspired by BioPHP's project biophp.org
 * Created 23 march 2019
 * Last modified 23 march 2019
 */
namespace SeqDatabaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class GbSequence
 * @package SeqDatabaseBundle\Entity
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 * @ORM\Entity
 * @ORM\Table(name="gb_sequence")
 */
class GbSequence
{
    /**
     * @ORM\Id
     * @ORM\Column(
     *     type = "string",
     *     length = 8,
     *     nullable = false,
     *     options = {"default":''}
     * )
     */
    private $primAcc;

    /**
     * @ORM\Column(
     *     type = "string",
     *     length = 2,
     *     nullable = true
     * )
     */
    private $strands;

    /**
     * @ORM\Column(
     *     type = "string",
     *     length = 1,
     *     nullable = true
     * )
     */
    private $topology;

    /**
     * @ORM\Column(
     *     type = "string",
     *     length = 3,
     *     nullable = true
     * )
     */
    private $division;

    /**
     * @ORM\Column(
     *     type = "integer",
     *     length = 11,
     *     nullable = true
     * )
     */
    private $segmentNo;

    /**
     * @ORM\Column(
     *     type = "integer",
     *     length = 11,
     *     nullable = true
     * )
     */
    private $segmentCount;

    /**
     * @ORM\Column(
     *     type = "string",
     *     length = 10,
     *     nullable = true
     * )
     */
    private $version;

    /**
     * @ORM\Column(
     *     type="string",
     *     length = 30,
     *     nullable = true
     * )
     */
    private $ncbiGiId;

    /**
     * @return mixed
     */
    public function getPrimAcc()
    {
        return $this->primAcc;
    }

    /**
     * @param mixed $primAcc
     */
    public function setPrimAcc($primAcc)
    {
        $this->primAcc = $primAcc;
    }

    /**
     * @return mixed
     */
    public function getStrands()
    {
        return $this->strands;
    }

    /**
     * @param mixed $strands
     */
    public function setStrands($strands)
    {
        $this->strands = $strands;
    }

    /**
     * @return mixed
     */
    public function getTopology()
    {
        return $this->topology;
    }

    /**
     * @param mixed $topology
     */
    public function setTopology($topology)
    {
        $this->topology = $topology;
    }

    /**
     * @return mixed
     */
    public function getDivision()
    {
        return $this->division;
    }

    /**
     * @param mixed $division
     */
    public function setDivision($division)
    {
        $this->division = $division;
    }

    /**
     * @return mixed
     */
    public function getSegmentNo()
    {
        return $this->segmentNo;
    }

    /**
     * @param mixed $segmentNo
     */
    public function setSegmentNo($segmentNo)
    {
        $this->segmentNo = $segmentNo;
    }

    /**
     * @return mixed
     */
    public function getSegmentCount()
    {
        return $this->segmentCount;
    }

    /**
     * @param mixed $segmentCount
     */
    public function setSegmentCount($segmentCount)
    {
        $this->segmentCount = $segmentCount;
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param mixed $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return mixed
     */
    public function getNcbiGiId()
    {
        return $this->ncbiGiId;
    }

    /**
     * @param mixed $ncbiGiId
     */
    public function setNcbiGiId($ncbiGiId)
    {
        $this->ncbiGiId = $ncbiGiId;
    }
}