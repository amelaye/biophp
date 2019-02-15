<?php
/**
 * Protein Entity
 * @author Amélie DUVERNET akka Amelaye
 * Freely inspired by BioPHP's project biophp.org
 * Created 11 february 2019
 * Last modified 14 february 2019
 */
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
