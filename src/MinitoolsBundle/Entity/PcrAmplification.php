<?php
/**
 * Entity for Form to calculate PCR Amplification
 * Freely inspired by BioPHP's project biophp.org
 * Created 31 march 2019
 * Last modified 31 march 2019
 */
namespace MinitoolsBundle\Entity;

/**
 * Class PcrAmplification
 * @package MinitoolsBundle\Entity
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class PcrAmplification
{
    /**
     * @var string
     */
    private $sequence;

    /**
     * @var string
     */
    private $primer1;

    /**
     * @var string
     */
    private $primer2;

    /**
     * @var bool
     */
    private $allowmismatch;

    /**
     * @var int
     */
    private $length;

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
        $sequence = preg_replace("/\\W|\\d/","", strtoupper($sequence));
        $this->sequence = $sequence;
    }

    /**
     * @return string
     */
    public function getPrimer1()
    {
        return $this->primer1;
    }

    /**
     * @param string $primer1
     */
    public function setPrimer1($primer1)
    {
        // All non-word characters (\\W) and digits(\\d) are remove from primers and from sequence file
        $primer1 = preg_replace("/\\W|\\d/","", strtoupper($primer1));
        $this->primer1 = $primer1;
    }

    /**
     * @return string
     */
    public function getPrimer2()
    {
        return $this->primer2;
    }

    /**
     * @param string $primer2
     */
    public function setPrimer2($primer2)
    {
        $primer2 = preg_replace("/\\W|\\d/","", strtoupper($primer2));
        $this->primer2 = $primer2;
    }

    /**
     * @return bool
     */
    public function isAllowmismatch()
    {
        return $this->allowmismatch;
    }

    /**
     * @param bool $allowmismatch
     */
    public function setAllowmismatch($allowmismatch)
    {
        $this->allowmismatch = $allowmismatch;
    }

    /**
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param int $length
     */
    public function setLength($length)
    {
        $this->length = $length;
    }
}