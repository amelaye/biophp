<?php
/**
 * Entity for Form to calculate Oligonucleotide Frequency
 * Freely inspired by BioPHP's project biophp.org
 * Created 31 march 2019
 * Last modified 31 march 2019
 */
namespace MinitoolsBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class OligoNucleotideFrequency
 * @package MinitoolsBundle\Entity
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class OligoNucleotideFrequency
{
    /**
     * @var string
     * @Assert\Length(
     *      min = 0,
     *      max = 1000000,
     *      minMessage = "Query sequence not provided. Plase go back and try again.",
     *      maxMessage = "Sequence is too long."
     * )
     */
    private $sequence;

    /**
     * @var int
     */
    private $len;

    /**
     * @var int
     */
    private $strands;

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
     * @return int
     */
    public function getLen()
    {
        return $this->len;
    }

    /**
     * @param int $len
     */
    public function setLen($len)
    {
        $this->len = $len;
    }

    /**
     * @return int
     */
    public function getStrands()
    {
        return $this->strands;
    }

    /**
     * @param int $strands
     */
    public function setStrands($strands)
    {
        $this->strands = $strands;
    }
}