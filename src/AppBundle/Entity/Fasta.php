<?php
/**
 * Fasta Entity
 * @author Amélie DUVERNET akka Amelaye
 * Freely inspired by BioPHP's project biophp.org
 * Created 26 february 2019
 * Last modified 26 february 2019
 */
namespace AppBundle\Entity;

class Fasta {

    private $id;
    private $length;
    private $xy;
    private $region;
    private $run;
    private $sequence;


    public function getId()
    {
        return $this->id;
    }
    public function setId($id)
    {
        $this->id = $id;
    }


    public function getLength()
    {
        return $this->length;
    }
    public function setLength($length)
    {
        $this->length = $length;
    }


    public function getXy()
    {
        return $this->xy;
    }
    public function setXy($xy)
    {
        $this->xy = $xy;
    }


    public function getRegion()
    {
        return $this->region;
    }
    public function setRegion($region)
    {
        $this->region = $region;
    }


    public function getRun()
    {
        return $this->run;
    }
    public function setRun($run)
    {
        $this->run = $run;
    }


    public function getSequence()
    {
        return $this->sequence;
    }
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;
    }


    /**
     * cut sequence $ini with size $size
     * @param $ini
     * @param $size
     */
    public function cutSequence($ini, $size)
    {
        $this->sequence = substr($this->sequence, $ini, $size);
    }
}