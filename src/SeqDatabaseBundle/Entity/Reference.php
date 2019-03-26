<?php
/**
 * Doctrine Entity Reference
 * Freely inspired by BioPHP's project biophp.org
 * Created 23 march 2019
 * Last modified 23 march 2019
 */
namespace SeqDatabaseBundle\Entity;

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
     * @ORM\Id
     * @ORM\Column(
     *     type = "string",
     *     length = 8,
     *     nullable = false
     * )
     */
    private $primAcc;

    /**
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
     * @ORM\Column(
     *     type = "string",
     *     length = 80,
     *     nullable = true
     * )
     */
    private $baseRange;

    /**
     * @ORM\Column(
     *     type = "string",
     *     length = 255,
     *     nullable = true
     * )
     */
    private $title;

    /**
     * @ORM\Column(
     *     type = "string",
     *     length = 8,
     *     nullable = true
     * )
     */
    private $medline;

    /**
     * @ORM\Column(
     *     type = "string",
     *     length = 20,
     *     nullable = true
     * )
     */
    private $pubmed;

    /**
     * @ORM\Column(
     *     type = "string",
     *     length = 255,
     *     nullable = true
     * )
     */
    private $remark;

    /**
     * @ORM\Column(
     *     type = "text"
     * )
     */
    private $journal;

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
    public function getRefno()
    {
        return $this->refno;
    }

    /**
     * @param mixed $refno
     */
    public function setRefno($refno)
    {
        $this->refno = $refno;
    }

    /**
     * @return mixed
     */
    public function getBaseRange()
    {
        return $this->baseRange;
    }

    /**
     * @param mixed $baseRange
     */
    public function setBaseRange($baseRange)
    {
        $this->baseRange = $baseRange;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getJournal()
    {
        return $this->journal;
    }

    /**
     * @param mixed $journal
     */
    public function setJournal($journal)
    {
        $this->journal = $journal;
    }

    /**
     * @return mixed
     */
    public function getMedline()
    {
        return $this->medline;
    }

    /**
     * @param mixed $medline
     */
    public function setMedline($medline)
    {
        $this->medline = $medline;
    }

    /**
     * @return mixed
     */
    public function getPubmed()
    {
        return $this->pubmed;
    }

    /**
     * @param mixed $pubmed
     */
    public function setPubmed($pubmed)
    {
        $this->pubmed = $pubmed;
    }

    /**
     * @return mixed
     */
    public function getRemark()
    {
        return $this->remark;
    }

    /**
     * @param mixed $remark
     */
    public function setRemark($remark)
    {
        $this->remark = $remark;
    }
}