<?php
/**
 * Protein Entity
 * Freely inspired by BioPHP's project biophp.org
 * Created 11 february 2019
 * Last modified 14 february 2019
 */
namespace AppBundle\Entity;

/**
 * Class Protein -  * This class represents the end-products of genetic processes of translation and
 * transcription -- the proteins.  While a protein's primary structure (its amino
 * acid sequence) is ably represented as a Sequence object, its secondary and tertiary
 * structures are not. This is the main rationale for creating a separate Protein
 * class.
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 * @package AppBundle\Entity
 */
class Protein
{
    /**
     * A string that uniquely identifies a protein.
     * @var string
     */
    private $id;

    /**
     * The long name used to refer to this protein.
     * @var string
     */
    private $name;

    /**
     * @var string
     */
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
