<?php
/**
 * Class ReduceAlphabet
 * Freely inspired by BioPHP's project biophp.org
 * Created 20 april 2019
 * Last modified 20 april 2019
 */
namespace MinitoolsBundle\Entity;

/**
 * Class SequenceAlignment
 * @package MinitoolsBundle\Entity
 * @author Amelie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class SequenceAlignment
{
    /**
     * @var string
     */
    private $id1;

    /**
     * @var string
     */
    private $sequence;

    /**
     * @var string
     */
    private $id2;

    /**
     * @var string
     */
    private $sequence2;

    /**
     * @return string
     */
    public function getId1()
    {
        return $this->id1;
    }

    /**
     * @param string $id1
     */
    public function setId1($id1)
    {
        $this->id1 = $id1;
    }

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
        $sequence = preg_replace("/\W|\d/", "", $sequence); // remove useless characters
        $sequence = preg_replace("/U/", "T", $sequence);    // from RNA to DNA
        $sequence = preg_replace("/X/", "N", $sequence);    // substitute X -> N
        $this->sequence = $sequence;
    }

    /**
     * @return string
     */
    public function getId2()
    {
        return $this->id2;
    }

    /**
     * @param string $id2
     */
    public function setId2($id2)
    {
        $this->id2 = $id2;
    }

    /**
     * @return string
     */
    public function getSequence2()
    {
        return $this->sequence2;
    }

    /**
     * @param string $sequence2
     */
    public function setSequence2($sequence2)
    {
        $sequence2 = strtoupper($sequence2);
        $sequence2 = preg_replace("/\W|\d/", "", $sequence2); // remove useless characters
        $sequence2 = preg_replace("/U/", "T", $sequence2);    // from RNA to DNA
        $sequence2 = preg_replace("/X/", "N", $sequence2);    // substitute X -> N
        $this->sequence2 = $sequence2;
    }
}