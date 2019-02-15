<?php
/**
 * Database Managing
 * @author Amélie DUVERNET akka Amelaye
 * Freely inspired by BioPHP's project biophp.org
 * Created 11 february 2019
 * Last modified 14 february 2019
 */
namespace AppBundle\Entity;

class RestrictionEnzime 
{
    private $name;
    private $pattern;
    private $cutpos;
    private $length;
    private $aRestEnzimDB;
    
    /**
     * Constructor method for the RestEn class.  It creates a new
     * RestEn object and initializes its properties accordingly.
     * RestEn() behavior:
     * If passed with make = 'custom', object will be added to aRestEnzimDB.
     * If not, the function will attemp to retrieve data from aRestEnzimDB.
     * If unsuccessful in retrieving data, it will return an error flag.
     * @param type $args
     */
    public function __constructor($args)
    {
        $arguments = parse_args($args);

        if ($arguments["make"] == "custom") {
            $this->name = $arguments["name"];
            $this->pattern = $arguments["pattern"];
            $this->cutpos = $arguments["cutpos"];
            $this->length = strlen($this->pattern);

            $inner = array();
            $inner[] = $arguments["pattern"];
            $inner[] = $arguments["cutpos"];
            $this->aRestEnzimDB[$this->name] = $inner;
        } else {
            // Look for given endonuclease in the aRestEnzimDB array.
            $this->name = $arguments["name"];
            $temp = $this->GetPattern($this->name);
            if (!$temp) {
                throw new \Exception("Cannot find entry in restriction endonuclease database.");
            } else {
                $this->pattern = $temp;
                $this->cutpos = $this->GetCutPos($this->name);
                $this->length = strlen($this->pattern);
            }
        }
    }
    
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
    
    public function getRestEnzimDB()
    {
        return $this->aRestEnzimDB;
    }
    public function setRestEnzimDB($aRestEnzimDB)
    {
        $this->aRestEnzimDB = $aRestEnzimDB;
    }
}

