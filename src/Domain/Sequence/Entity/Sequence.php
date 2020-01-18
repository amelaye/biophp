<?php
/**
 * Doctrine Entity Sequence
 * Freely inspired by BioPHP's project biophp.org
 * Created 23 march 2019
 * Last modified 18 january 2020
 */
namespace AppBundle\Domain\Sequence\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Sequence
 * @package AppBundle\Entity\Sequencing
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 * @ORM\Entity
 * @ORM\Table(
 *     name = "sequence",
 *     indexes = {
 *         @ORM\Index(name = "locus_name", columns = {"entry_name", "mol_type"})
 *     }
 * )
 */
class Sequence
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
     * @ORM\OneToOne(targetEntity = "AppBundle\Domain\Sequence\Entity\GbSequence")
     * @ORM\OneToMany(targetEntity = "AppBundle\Domain\Sequence\Entity\Feature")
     * @ORM\OneToOne(targetEntity = "AppBundle\Domain\Sequence\Entity\ScForm")
     * @ORM\OneToMany(targetEntity = "AppBundle\Domain\Sequence\Entity\Accession")
     * @ORM\OneToMany(targetEntity = "AppBundle\Domain\Sequence\Entity\Keyword")
     * @ORM\OneToMany(targetEntity = "AppBundle\Domain\Sequence\Entity\Reference")
     */
    private $primAcc;

    /**
     * @var string
     * @ORM\Column(
     *     type = "string",
     *     length = 8,
     *     nullable = false
     * )
     */
    private $entryName;

    /**
     * @var int
     * @ORM\Column(
     *     type = "integer",
     *     length = 11,
     *     nullable = true
     * )
     */
    private $seqLength;

    /**
     * @var int
     * @ORM\Column(
     *     type = "integer",
     *     length = 11,
     *     nullable = true
     * )
     */
    private $start;

    /**
     * @var int
     * @ORM\Column(
     *     type = "integer",
     *     length = 11,
     *     nullable = true
     * )
     */
    private $end;

    /**
     * @var string
     * @ORM\Column(
     *     type = "string",
     *     length = 6,
     *     nullable = true
     * )
     */
    private $molType;

    /**
     * @var string
     * @ORM\Column(
     *     type = "date",
     *     nullable = true
     * )
     */
    private $date;

    /**
     * @var string
     * @ORM\Column(
     *     type = "string",
     *     nullable = true
     * )
     */
    private $source;

    /**
     * @var string
     * @ORM\Column(
     *     type = "text",
     *     nullable = false
     * )
     */
    private $sequence;

    /**
     * @var string
     * @ORM\Column(
     *     type = "string",
     *     nullable = true
     * )
     */
    private $description;

    /**
     * @var array
     * @ORM\Column(
     *     type = "array",
     *     nullable = true
     * )
     */
    private $organism;

    /**
     * @var int
     * @ORM\Column(
     *     type = "integer",
     *     length = 1,
     *     nullable = true
     * )
     */
    private $fragment;

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
    public function getEntryName()
    {
        return $this->entryName;
    }

    /**
     * @param string $entryName
     */
    public function setEntryName($entryName)
    {
        $this->entryName = $entryName;
    }

    /**
     * @return int
     */
    public function getSeqLength()
    {
        return $this->seqLength;
    }

    /**
     * @param int $seqLength
     */
    public function setSeqLength($seqLength)
    {
        $this->seqLength = $seqLength;
    }

    /**
     * @return int
     */
    public function getStart(): int
    {
        return $this->start;
    }

    /**
     * @param int $start
     */
    public function setStart(int $start): void
    {
        $this->start = $start;
    }

    /**
     * @return int
     */
    public function getEnd(): int
    {
        return $this->end;
    }

    /**
     * @param int $end
     */
    public function setEnd(int $end): void
    {
        $this->end = $end;
    }

    /**
     * @return string
     */
    public function getMolType()
    {
        return $this->molType;
    }

    /**
     * @param string $molType
     */
    public function setMolType($molType)
    {
        $this->molType = $molType;
    }

    /**
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param string $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param string $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @return string
     */
    public function getSequence()
    {
        return $this->sequence;
    }

    /**
     * @param string $sequence
     */
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return array
     */
    public function getOrganism()
    {
        return $this->organism;
    }

    /**
     * @param array $organism
     */
    public function setOrganism($organism)
    {
        $this->organism = $organism;
    }

    /**
     * @return int
     */
    public function getFragment(): int
    {
        return $this->fragment;
    }

    /**
     * @param int $fragment
     */
    public function setFragment(int $fragment): void
    {
        $this->fragment = $fragment;
    }
}