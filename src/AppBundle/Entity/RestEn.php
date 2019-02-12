<?php

namespace AppBundle\Entity;

class RestEn 
{
    private $name;
    private $pattern;
    private $cutpos;
    private $length;
    
    /**
     * Constructor method for the RestEn class.  It creates a new
     * RestEn object and initializes its properties accordingly.
     * RestEn() behavior:
     * If passed with make = 'custom', object will be added to RestEn_DB.
     * If not, the function will attemp to retrieve data from RestEn_DB.
     * If unsuccessful in retrieving data, it will return an error flag.
     * @global type $RestEn_DB
     * @param type $args
     */
    public function __constructor($args)
    {
        global $RestEn_DB;

        $arguments = parse_args($args);

        if ($arguments["make"] == "custom") {
            $this->name = $arguments["name"];
            $this->pattern = $arguments["pattern"];
            $this->cutpos = $arguments["cutpos"];
            $this->length = strlen($this->pattern);

            $inner = array();
            $inner[] = $arguments["pattern"];
            $inner[] = $arguments["cutpos"];
            $RestEn_DB[$this->name] = $inner;
        } else {
            // Look for given endonuclease in the RestEn_DB array.
            $this->name = $arguments["name"];
            $temp = $this->GetPattern($this->name);
            if ($temp == FALSE) {
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
}

