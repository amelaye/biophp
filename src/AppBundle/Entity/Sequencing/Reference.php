<?php
/**
 * Doctrine Entity Reference
 * Freely inspired by BioPHP's project biophp.org
 * Created 23 march 2019
 * Last modified 13 november 2019
 */
namespace AppBundle\Entity\Sequencing;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Reference
 * @package SeqDatabaseBundle\Entity
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
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
     * @ORM\ManyToOne(targetEntity = "SeqDatabaseBundle\Entity\Sequence")
     * @ORM\JoinColumn(
     *     name = "prim_acc",
     *     referencedColumnName = "prim_acc"
     * )
     * @ORM\OneToMany(
     *     targetEntity = "SeqDatabaseBundle\Entity\Authors",
     *     mappedBy = "primAcc"
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
    private $journal;

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
     * @return int
     */
    public function getRefno()
    {
        return $this->refno;
    }

    /**
     * @param int $refno
     */
    public function setRefno($refno)
    {
        $this->refno = $refno;
    }

    /**
     * @return string
     */
    public function getBaseRange()
    {
        return $this->baseRange;
    }

    /**
     * @param string $baseRange
     */
    public function setBaseRange($baseRange)
    {
        $this->baseRange = $baseRange;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getJournal()
    {
        return $this->journal;
    }

    /**
     * @param string $journal
     */
    public function setJournal($journal)
    {
        $this->journal = $journal;
    }

    /**
     * @return string
     */
    public function getMedline()
    {
        return $this->medline;
    }

    /**
     * @param string $medline
     */
    public function setMedline($medline)
    {
        $this->medline = $medline;
    }

    /**
     * @return string
     */
    public function getPubmed()
    {
        return $this->pubmed;
    }

    /**
     * @param string $pubmed
     */
    public function setPubmed($pubmed)
    {
        $this->pubmed = $pubmed;
    }

    /**
     * @return string
     */
    public function getRemark()
    {
        return $this->remark;
    }

    /**
     * @param string $remark
     */
    public function setRemark($remark)
    {
        $this->remark = $remark;
    }
}