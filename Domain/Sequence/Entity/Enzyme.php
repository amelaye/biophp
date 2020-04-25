<?php
/**
 * Enzymes entity
 * Freely inspired by BioPHP's project biophp.org
 * Created 11 february 2019
 * Last modified 18 january 2020
 */
namespace Amelaye\BioPHP\Domain\Sequence\Entity;

/**
 * Class Enzyme
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 * @package Amelaye\BioPHP\Domain\Sequence\Entity
 */
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

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name) : void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getPattern() : string
    {
        return $this->pattern;
    }

    /**
     * @param string $pattern
     */
    public function setPattern(string $pattern) : void
    {
        $this->pattern = $pattern;
    }

    /**
     * @return int
     */
    public function getCutpos() : int
    {
        return $this->cutpos;
    }

    /**
     * @param int $cutpos
     */
    public function setCutpos(int $cutpos) : void
    {
        $this->cutpos = $cutpos;
    }

    /**
     * @return int
     */
    public function getLength() : int
    {
        return $this->length;
    }

    /**
     * @param int $length
     */
    public function setLength(int $length) : void
    {
        $this->length = $length;
    }
}

