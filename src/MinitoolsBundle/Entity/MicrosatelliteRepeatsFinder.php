<?php
/**
 * Microsatellite repeats finder
 * Freely inspired by BioPHP's project biophp.org
 * Created 31 march 2019
 * Last modified 31 march 2019
 * RIP Pasha, gone 27 february 2019 =^._.^= ∫
 */

namespace MinitoolsBundle\Entity;

/**
 * Class MicrosatelliteRepeatsFinder
 * @package MinitoolsBundle\Entity
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class MicrosatelliteRepeatsFinder
{
    /**
     * @var string
     */
    private $sequence;

    /**
     * @var int
     */
    private $min;

    /**
     * @var int
     */
    private $max;

    /**
     * @var int
     */
    private $minRepeats;

    /**
     * @var int
     */
    private $lengthOfMR;

    /**
     * @var int
     */
    private $mismatch;

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
    public function getMin()
    {
        return $this->min;
    }

    /**
     * @param int $min
     */
    public function setMin($min)
    {
        $this->min = $min;
    }

    /**
     * @return int
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * @param int $max
     */
    public function setMax($max)
    {
        $this->max = $max;
    }

    /**
     * @return int
     */
    public function getMinRepeats()
    {
        return $this->minRepeats;
    }

    /**
     * @param int $minRepeats
     */
    public function setMinRepeats($minRepeats)
    {
        $this->minRepeats = $minRepeats;
    }

    /**
     * @return int
     */
    public function getLengthOfMR()
    {
        return $this->lengthOfMR;
    }

    /**
     * @param int $lengthOfMR
     */
    public function setLengthOfMR($lengthOfMR)
    {
        $this->lengthOfMR = $lengthOfMR;
    }

    /**
     * @return int
     */
    public function getMismatch()
    {
        return $this->mismatch;
    }

    /**
     * @param int $mismatch
     */
    public function setMismatch($mismatch)
    {
        $this->mismatch = $mismatch;
    }
}