<?php
/**
 * Enzyme restriction manager
 * Freely inspired by BioPHP's project biophp.org
 * Created 11 february 2019
 * Last modified 2 november 2019
 */
namespace AppBundle\Service;

use AppBundle\Bioapi\Bioapi;
use AppBundle\Entity\Sequence;
use AppBundle\Entity\Enzyme;

/**
 * Class RestrictionEnzymeManager - substances that can "cut" a DNA strand
 * into two or more fragments along special sites called restriction sites. They
 * are an important tool in recombinant DNA technology.
 * @package AppBundle\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class RestrictionEnzymeManager
{
    /**
     * @var array
     */
    private $aRestEnzimDB;

    /**
     * @var Enzyme
     */
    private $enzyme;

    /**
     * @var
     */
    private $sequence;

    /**
     * RestrictionEnzymeManager constructor.
     * @param Bioapi $bioapi
     */
    public function __construct(Bioapi $bioapi)
    {
        $this->aRestEnzimDB = $bioapi->getTypeIIEndonucleasesForRest();
    }

    /**
     * Sets a RestrictionEnzyme object
     * @param Enzyme $restrictionEnzyme
     */
    public function setEnzyme(Enzyme $restrictionEnzyme)
    {
        $this->enzyme = $restrictionEnzyme;
    }

    /**
     * Sets a sequence object
     * @param Sequence $sequence
     */
    public function setSequence(Sequence $sequence) {
        $this->sequence = $sequence;
    }

    /**
     * It creates a new RestEn object and initializes its properties accordingly.
     * If passed with make = 'custom', object will be added to aRestEnzimDB.
     * If not, the function will attemp to retrieve data from aRestEnzimDB.
     * If unsuccessful in retrieving data, it will return an error flag.
     * @param   array       $args
     * @throws  \Exception
     */
    public function parseEnzyme($args)
    {
        $arguments = parse_args($args);

        if ($arguments["make"] == "custom") {
            $this->enzyme->setName($arguments["name"]);
            $this->enzyme->setPattern($arguments["pattern"]);
            $this->enzyme->setCutpos($arguments["cutpos"]);
            $this->enzyme->setLength(strlen($this->enzyme->getPattern()));

            $inner = array();
            $inner[] = $arguments["pattern"];
            $inner[] = $arguments["cutpos"];
            $this->aRestEnzimDB[$this->enzyme->getName()] = $inner;
        } else {
            // Look for given endonuclease in the aRestEnzimDB array.
            $this->enzyme->setName($arguments["name"]);
            $temp = $this->getPattern($this->enzyme->getName());
            if (!$temp) {
                throw new \Exception("Cannot find entry in restriction endonuclease database.");
            } else {
                $this->enzyme->setPattern($temp);
                $this->enzyme->setCutpos($this->getCutPos($this->enzyme->getName()));
                $this->enzyme->setLength(strlen($this->enzyme->getPattern()));
            }
        }
    }

    /**
     * Cuts a DNA sequence into fragments using the restriction enzyme object.
     * @param   SequenceManager    $sequenceManager    The sequence to cut using the current restriction enzyme object.
     * @param   string             $options            May be "N" or "O".  If "N", the sequence is cut using the patpos() group
     * of methods (no overlapping patterns).  If "O", the sequence is cut using the patposo() group
     * of methods (with overlapping patterns). If omitted, this defaults to "N".
     * @return  array       An array of fragments (substrings of the parameter sequence)
     * @throws  \Exception
     */
    public function cutSeq(SequenceManager $sequenceManager, $options = "N")
    {
        if ($options == "N") {
            return $this->nTreatment($sequenceManager);
        } elseif ($options == "O") {
            return $this->oTreatment($sequenceManager);
        }
    } 


    /**
     * returns the pattern associated with a given restriction endonuclease.
     * @param string $RestEn_Name
     * @return \AppBundle\Services\type
     */
    public function getPattern($RestEn_Name)
    {
        return $this->aRestEnzimDB[$RestEn_Name][0];
    }


    /**
     * Returns the cutting position of the restriction enzyme object.
     * @param string $RestEn_Name
     * @return \AppBundle\Services\type
     */
    public function getCutPos($RestEn_Name)
    {
        return $this->aRestEnzimDB[$RestEn_Name][1];
    }


    /**
     * Returns the length of the cutting pattern of the restriction enzyme object.
     * @param type $RestEn_Name
     * @return type
     */
    public function GetLength($RestEn_Name = "")
    {
        if ($RestEn_Name == "") {
            return strlen($this->resten->getPattern());
        } else {
            return strlen($this->aRestEnzimDB[$RestEn_Name][0]);
        }
    }


    /**
     * Flexible method for searching the Restriction Enzyme database
     * for entries meeting complex criteria.  It returns an array of RestEn objects.
     * @param type $pattern
     * @param type $cutpos
     * @param type $plen
     * @return type
     * @throws \Exception
     * @group Legacy
     */
    public function FindRestEn($pattern = "", $cutpos = "", $plen = "")
    {
        // 5 Cases: pattern only, cutpos only, patternlength only
        //          pattern and cutpos, cutpos and patternlength
        $RestEn_List = [];

        // Case 1: Pattern only
        if (($pattern != "") && ($cutpos == "") && ($plen == "")) {
            foreach($this->aRestEnzimDB as $key => $value) {
                if ($value[0] == $pattern) {
                    $RestEn_List[] = $key;
                }
            }
            return $RestEn_List;
        }

        // Case 2: Cutpos only
        if (($pattern == "") && ($cutpos != "") && ($plen == "")) {
            $firstchar = substr($cutpos, 0, 1);
            $first2chars = substr($cutpos, 0, 2);
            if (is_string($cutpos)) {
            if (preg_match("/^<\d+$/", $cutpos)) {
                    foreach($this->aRestEnzimDB as $key => $value) {
                        if ($value[1] < (int) substr($cutpos,1)) {
                            $RestEn_List[] = $key;
                        }
                    }
                    return $RestEn_List;
                } elseif (preg_match("/^>\d+$/", $cutpos)) {
                    foreach($this->aRestEnzimDB as $key => $value) {
                        if ($value[1] > (int) substr($cutpos,1)) {
                            $RestEn_List[] = $key;
                        }
                    }
                    return $RestEn_List;
                } elseif (preg_match("/^>=\d+$/", $cutpos)) {
                    foreach($this->aRestEnzimDB as $key => $value) {
                        if ($value[1] >= (int) substr($cutpos,2)) {
                            $RestEn_List[] = $key;
                        }
                    }
                    return $RestEn_List;
                } elseif (preg_match("/^<=\d+$/", $cutpos)) {
                    foreach($this->aRestEnzimDB as $key => $value) {
                        if ($value[1] <= (int) substr($cutpos,2)) {
                            $RestEn_List[] = $key;
                        }
                    }
                    return $RestEn_List;
                } elseif (preg_match("/^=\d+$/", $cutpos)) {
                    foreach($this->aRestEnzimDB as $key => $value) {
                        if ($value[1] == substr($cutpos,1)) {
                            $RestEn_List[] = $key;
                        }
                    }
                    return $RestEn_List;
                } else {
                    throw new \Exception("Malformed cutpos parameter.");
                }
            } elseif (is_int($cutpos)) {
                foreach($this->aRestEnzimDB as $key => $value)
                    if ($value[1] == $cutpos) {
                        $RestEn_List[] = $key;
                    }
                    return $RestEn_List;
            }
        } 

        // Case 3: Patternlength only
        if (($pattern == "") && ($cutpos == "") && ($plen != "")) {
            foreach($this->aRestEnzimDB as $key => $value) {
                if (strlen($value[0]) == $plen) {
                    $RestEn_List[] = $key;
                }
            }
            return $RestEn_List;
        }

        // Case 4: Pattern and cutpos only
        if (($pattern != "") && ($cutpos != "") && ($plen == "")) {
            foreach($this->aRestEnzimDB as $key => $value) {
                if (($value[0] == $pattern) && ($value[1] == $cutpos)) {
                    $RestEn_List[] = $key;
                }
            }
            return $RestEn_List;
        }

        // Case 5: Cutpos and plen only.
        if (($pattern == "") && ($cutpos != "") && ($plen != "")) {
            foreach($this->aRestEnzimDB as $key => $value) {
                if (($value[1] == $cutpos) && (strlen($value[0]) == $plen)) {
                    $RestEn_List[] = $key;
                }
            }
            return $RestEn_List;
        }

        throw new \Exception("Invalid combination of function parameters.");
    }

    /**
     * @param SequenceManager $sequenceManager
     * @return array
     * @throws \Exception
     */
    private function oTreatment(SequenceManager $sequenceManager)
    {
        $pos_r = $sequenceManager->patposo($this->resten->getPattern(), "I", $this->resten->getCutpos());
        $ctr = 0;
        foreach($pos_r as $currindex) {
            $ctr++;
            if ($ctr == 1) {
                $frag[] = substr($sequenceManager->getSequence()->getSequence(), 0, $currindex + $this->resten->getCutpos());
                $previndex = $currindex;
                continue;
            }
            if (($currindex - $previndex) >= $this->resten->getCutpos()) {
                $newcount = $currindex - $previndex;
                $frag[] = substr($sequenceManager->getSequence()->getSequence(), $previndex + $this->resten->getCutpos(), $newcount);
                $previndex = $currindex;
            } else {
                continue;
            }
        }
        // The last (right-most) fragment.
        $frag[] = substr($sequenceManager->getSequence()->getSequence(), $previndex + $this->resten->getCutpos());
        return $frag;
    }

    /**
     * @param SequenceManager $sequenceManager
     * @return array
     * @throws \Exception
     */
    private function nTreatment(SequenceManager $sequenceManager)
    {
        // patpos() returns: ( "PAT1" => (0, 12), "PAT2" => (7, 29, 53) )
        $patpos_r = $sequenceManager->patpos($this->resten->getPattern(), "I");
        $frag = array();
        foreach($patpos_r as $patkey => $pos_r) {
            $ctr = 0;
            foreach($pos_r as $currindex) {
                $ctr++;
                if ($ctr == 1) {
                    // 1st fragment is everything to the left of the 1st occurrence of pattern
                    $frag[] = substr($sequenceManager->getSequence()->getSequence(), 0, $currindex + $this->getCutpos());
                    $previndex = $currindex;
                    continue;
                }
                if (($currindex - $previndex) >= $this->resten->getCutpos()) {
                    $newcount = $currindex - $previndex;
                    $frag[] = substr($sequenceManager->getSequence()->getSequence(), $previndex + $this->resten->getCutpos(), $newcount);
                    $previndex = $currindex;
                } else {
                    continue;
                }
            }
            // The last (right-most) fragment.
            $frag[] = substr($sequenceManager->getSequence()->getSequence(), $previndex + $this->resten->getCutpos());
        }
        return $frag;
    }
}
