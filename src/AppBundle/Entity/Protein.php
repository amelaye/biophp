<?php

namespace AppBundle\Entity;

class Protein
{
    private $id;
    private $name;
    private $sequence;
    
    public function getId()
    {
        return $this->id;
    }
    
    public function setId($id)
    {
        $this->id = $id;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function setName($name)
    {
        $this->name = $name;
    }
    
    public function getSequence()
    {
        return $this->sequence;
    }
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;
    }
}
