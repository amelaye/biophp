<?php
/**
 * Entity used by form FindPalindromesType
 * Freely inspired by BioPHP's project biophp.org
 * Created 26 march 2019
 * Last modified 26 march 2019
 */
namespace MinitoolsBundle\Entity;

/**
 * Class MeltingTemperature
 * @package MinitoolsBundle\Entity
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class MeltingTemperature
{
    /**
     * @var string
     */
    private $primer;

    /**
     * @var bool
     */
    private $basic;

    /**
     * @var bool
     */
    private $nearestNeighbor;

    /**
     * @var int
     */
    private $cp;

    /**
     * @var int
     */
    private $cs;

    /**
     * @var int
     */
    private $cmg;

    /**
     * @return string
     */
    public function getPrimer()
    {
        return $this->primer;
    }

    /**
     * @param string $primer
     */
    public function setPrimer($primer)
    {
        $this->primer = $primer;
    }

    /**
     * @return bool
     */
    public function isBasic()
    {
        return $this->basic;
    }

    /**
     * @param bool $basic
     */
    public function setBasic($basic)
    {
        $this->basic = $basic;
    }

    /**
     * @return bool
     */
    public function isNearestNeighbor()
    {
        return $this->nearestNeighbor;
    }

    /**
     * @param bool $nearestNeighbor
     */
    public function setNearestNeighbor($nearestNeighbor)
    {
        $this->nearestNeighbor = $nearestNeighbor;
    }

    /**
     * @return int
     */
    public function getCp()
    {
        return $this->cp;
    }

    /**
     * @param int $cp
     */
    public function setCp($cp)
    {
        $this->cp = $cp;
    }

    /**
     * @return int
     */
    public function getCs()
    {
        return $this->cs;
    }

    /**
     * @param int $cs
     */
    public function setCs($cs)
    {
        $this->cs = $cs;
    }

    /**
     * @return int
     */
    public function getCmg()
    {
        return $this->cmg;
    }

    /**
     * @param int $cmg
     */
    public function setCmg($cmg)
    {
        $this->cmg = $cmg;
    }
}