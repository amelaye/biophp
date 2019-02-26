<?php
/**
* Entity used by form DistanceAmongSequences
* @author Amélie DUVERNET akka Amelaye
* Freely inspired by BioPHP's project biophp.org
* Created 26 february 2019
* Last modified 26 february 2019
*/
namespace MinitoolsBundle\Entity;


class DistanceAmongSequences
{
    private $seq;

    private $method;

    private $len;

    public function getSeq()
    {
        return $this->seq;
    }
    public function setSeq($seq)
    {
        $this->seq = $seq;
    }

    public function getMethod()
    {
        return $this->method;
    }
    public function setMethod($method)
    {
        $this->method = $method;
    }

    public function getLen()
    {
        return $this->len;
    }
    public function setLen($len)
    {
        $this->len = $len;
    }
}