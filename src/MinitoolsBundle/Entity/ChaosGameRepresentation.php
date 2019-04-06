<?php
/**
* Entity used by form ChaosGameRepresentation
* Freely inspired by BioPHP's project biophp.org
* Created 26 february 2019
* Last modified 26 february 2019
*/
namespace MinitoolsBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ChaosGameRepresentation
 * @package MinitoolsBundle\Entity
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class ChaosGameRepresentation
{
    /**
     * @var string
     */
    private $seqName;

    /**
     * @var int
     */
    private $size;

    /**
     * @var string
     */
    private $s;

    /**
     * @var int
     */
    private $len;

    /**
     * @var string
     */
    private $seq;

    /**
     * @var bool
     */
    private $map;

    /**
     * @var bool
     */
    private $freq;

    /**
     * @return string
     */
    public function getSeqName()
    {
        return $this->seqName;
    }

    /**
     * @param string $seqName
     */
    public function setSeqName($seqName)
    {
        $this->seqName = $seqName;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param int $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * @return string
     */
    public function getS()
    {
        return $this->s;
    }

    /**
     * @param string $s
     */
    public function setS($s){
        $this->s = $s;
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
     * @Assert\Length(
     *      min = 50,
     *      max = 5000000,
     *      minMessage = "Minumum sequence length: {{ min }} bp",
     *      maxMessage = "Sequence is longer than {{ max }} bp. At this moment we can not provide this service to such a long sequences."
     * )
     * @return string
     */
    public function getSeq()
    {
        return $this->seq;
    }

    /**
     * @param string $seq
     */
    public function setSeq($seq)
    {
        $this->seq = $seq;
    }

    /**
     * @return bool
     */
    public function  getMap()
    {
        return $this->map;
    }

    /**
     * @param bool $map
     */
    public function  setMap($map)
    {
        $this->map = $map;
    }

    /**
     * @return bool
     */
    public function getFreq()
    {
        return $this->freq;
    }

    /**
     * @param bool $freq
     */
    public function setFreq($freq)
    {
        $this->freq = $freq;
    }
}