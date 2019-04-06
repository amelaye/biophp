<?php
/**
 * Entity used by form FindPalindromesType
 * Freely inspired by BioPHP's project biophp.org
 * Created 18 march 2019
 * Last modified 18 march 2019
 */
namespace MinitoolsBundle\Entity;

/**
 * Class FindPalindromes
 * @package MinitoolsBundle\Entity
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class FindPalindromes
{
    private $seq;

    private $min;

    private $max;

    public function getSeq()
    {
        return $this->seq;
    }
    public function setSeq($seq)
    {
        $this->seq = $seq;
    }

    public function getMin()
    {
        return $this->min;
    }
    public function setMin($min)
    {
        $this->min = $min;
    }

    public function getMax()
    {
        return $this->max;
    }
    public function setMax($max)
    {
        $this->max = $max;
    }
}