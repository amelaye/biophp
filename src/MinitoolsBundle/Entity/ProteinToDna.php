<?php
/**
 * Entity used by form ProteinPropertiesType
 * Freely inspired by BioPHP's project biophp.org
 * Created 6 april 2019
 * Last modified 6 april 2019
 */
namespace MinitoolsBundle\Entity;

/**
 * Class ProteinToDna
 * @package MinitoolsBundle\Entity
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class ProteinToDna
{
    /**
     * @var string
     */
    private $sequence;

    /**
     * @var string
     */
    private $geneticCode;

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
        $sequence = strtoupper($sequence);
        $sequence = preg_replace ("([^FLIMVSPTAY*HQNKDECWRGX\*])", "", $sequence);
        $this->sequence = $sequence;
    }

    /**
     * @return string
     */
    public function getGeneticCode()
    {
        return $this->geneticCode;
    }

    /**
     * @param string $geneticCode
     */
    public function setGeneticCode($geneticCode)
    {
        $this->geneticCode = $geneticCode;
    }
}