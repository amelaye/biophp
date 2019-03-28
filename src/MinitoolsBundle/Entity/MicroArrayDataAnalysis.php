<?php
/**
 * Microarray Data Analysis
 * Spots are analyzed by the adaptive quantification method with local background subtractions
 * Freely inspired by BioPHP's project biophp.org
 * Created 28 march 2019
 * Last modified 28 march 2019
 * RIP Pasha, gone 27 february 2019 =^._.^= ∫
 */
namespace MinitoolsBundle\Entity;

/**
 * Class MicroArrayDataAnalysis
 * @package MinitoolsBundle\Entity
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class MicroArrayDataAnalysis
{
    /**
     * @var string
     */
    private $data;

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }
}