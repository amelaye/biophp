<?php
/**
* Entity used by form ChaosGameRepresentation
* @author Amélie DUVERNET akka Amelaye
* Freely inspired by BioPHP's project biophp.org
* Created 26 february 2019
* Last modified 26 february 2019
*/
namespace MinitoolsBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class ChaosGameRepresentation
{
    private $seqName;

    private $s;

    private $len;

    private $seq;

    private $map;

    private $freq;


    public function getSeqName()
    {
        return $this->seqName;
    }
    public function setSeqName($seqName)
    {
        $this->seqName = $seqName;
    }

    public function getS()
    {
        return $this->s;
    }
    public function setS($s){
        $this->s = $s;
    }

    public function getLen()
    {
        return $this->len;
    }
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
     */
    public function getSeq()
    {
        return $this->seq;
    }
    public function setSeq($seq)
    {
        $this->seq = $seq;
    }

    public function  getMap()
    {
        return $this->map;
    }
    public function  setMap($map)
    {
        $this->map = $map;
    }

    public function getFreq()
    {
        return $this->freq;
    }
    public function setFreq($freq)
    {
        $this->freq = $freq;
    }
}