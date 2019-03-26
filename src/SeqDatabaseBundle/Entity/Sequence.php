<?php
/**
 * Doctrine Entity Sequence
 * Freely inspired by BioPHP's project biophp.org
 * Created 23 march 2019
 * Last modified 23 march 2019
 */
namespace SeqDatabaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Sequence
 * @package SeqDatabaseBundle\Entity
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 * @ORM\Entity
 * @ORM\Table(
 *     name = "sequence",
 *     indexes = {@ORM\Index(name = "locus_name", columns = {"entry_name", "mol_type"})}
 * )
 */
class Sequence
{
    /**
     * @ORM\Id
     * @ORM\Column(
     *     type = "string",
     *     length = 8,
     *     options = {"default":0},
     *     nullable = false
     * )
     */
    private $primAcc;

    /**
     * @ORM\Column(
     *     type = "string",
     *     length = 8,
     *     nullable = false
     * )
     */
    private $entryName;

    /**
     * @ORM\Column(
     *     type = "integer",
     *     length = 11,
     *     nullable = true
     * )
     */
    private $seqLength;

    /**
     * @ORM\Column(
     *     type = "string",
     *     length = 6,
     *     nullable = true
     * )
     */
    private $molType;

    /**
     * @ORM\Column(
     *     type = "date",
     *     nullable = true
     * )
     */
    private $date;

    /**
     * @ORM\Column(
     *     type = "string",
     *     nullable = true
     * )
     */
    private $source;

    /**
     * @ORM\Column(
     *     type = "text",
     *     nullable = false
     * )
     */
    private $sequence;

    /**
     * @ORM\Column(
     *     type = "string",
     *     nullable = true
     * )
     */
    private $description;

    /**
     * @ORM\Column(
     *     type = "string",
     *     nullable = true
     * )
     */
    private $organism;

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
    public function getEntryName()
    {
        return $this->entryName;
    }

    /**
     * @param mixed $entryName
     */
    public function setEntryName($entryName)
    {
        $this->entryName = $entryName;
    }

    /**
     * @return mixed
     */
    public function getSeqLength()
    {
        return $this->seqLength;
    }

    /**
     * @param mixed $seqLength
     */
    public function setSeqLength($seqLength)
    {
        $this->seqLength = $seqLength;
    }

    /**
     * @return mixed
     */
    public function getMolType()
    {
        return $this->molType;
    }

    /**
     * @param mixed $molType
     */
    public function setMolType($molType)
    {
        $this->molType = $molType;
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param mixed $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @return mixed
     */
    public function getSequence()
    {
        return $this->sequence;
    }

    /**
     * @param mixed $sequence
     */
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getOrganism()
    {
        return $this->organism;
    }

    /**
     * @param mixed $organism
     */
    public function setOrganism($organism)
    {
        $this->organism = $organism;
    }
}