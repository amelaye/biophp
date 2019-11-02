<?php
/**
 * Database Managing
 * @author Amélie DUVERNET akka Amelaye
 * Freely inspired by BioPHP's project biophp.org
 * Created 11 february 2019
 * Last modified 14 february 2019
 */
namespace AppBundle\Entity;

class Enzyme
{
    /**
     * The short name of the restriction endonuclease following the accepted naming convention
     * @var string
     */
    private $name;

    /**
     * A string representing the restriction pattern recognized by the enzyme.
     * @var string
     */
    private $pattern;

    /**
     * An integer representing the position within the restriction pattern where the enzyme
     * actually cuts the DNA strand. This could range from 0 to 1 less than the length of the restriction pattern.
     * @var int
     */
    private $cutpos;

    /**
     * The number of symbols (or base pairs) in the restriction pattern.
     * @var int
     */
    private $length;

    
    public function getName()
    {
        return $this->name;
    }
    public function setName($name)
    {
        $this->name = $name;
    }
    
    public function getPattern()
    {
        return $this->pattern;
    }
    public function setPattern($pattern)
    {
        $this->pattern = $pattern;
    }
    
    public function getCutpos()
    {
        return $this->cutpos;
    }
    public function setCutpos($cutpos)
    {
        $this->cutpos = $cutpos;
    }
    
    public function getLength()
    {
        return $this->length;
    }
    public function setLength($length)
    {
        $this->length = $length;
    }
}

