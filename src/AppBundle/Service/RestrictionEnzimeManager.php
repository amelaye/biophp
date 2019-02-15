<?php
/**
 * Database Managing
 * @author Amélie DUVERNET akka Amelaye
 * Freely inspired by BioPHP's project biophp.org
 * Created 11 february 2019
 * Last modified 14 february 2019
 */
namespace AppBundle\Service;

use AppBundle\Entity\Sequence;
use AppBundle\Entity\RestrictionEnzime;

class RestrictionEnzimeManager
{
    private $resten;
    
    public function __construct(RestrictionEnzime $oRestEn) {
        $this->resten = $oRestEn;
    }
    
    /**
     * Cuts a DNA sequence into fragments using the restriction enzyme object.
     * @param   Sequence    $oSequence
     * @param   string      $options
     * @return type
     */
    public function CutSeq(Sequence $oSequence, $options = "N")
    {
        if ($options == "N") {
            // patpos() returns: ( "PAT1" => (0, 12), "PAT2" => (7, 29, 53) )
            $patpos_r = $oSequence->patpos($this->resten->getPattern(), "I");
            $frag = array();
            foreach($patpos_r as $patkey => $pos_r) {
                $ctr = 0;
                foreach($pos_r as $currindex) {
                    $ctr++;
                    if ($ctr == 1) {
                        // 1st fragment is everything to the left of the 1st occurrence of pattern
                        $frag[] = substr($oSequence->getSequence(), 0, $currindex + $this->resten->getCutpos());
                        $previndex = $currindex;
                        continue;
                    }
                    if (($currindex - $previndex) >= $this->resten->getCutpos()) {
                        $newcount = $currindex - $previndex;
                        $frag[] = substr($oSequence->getSequence(), $previndex + $this->resten->getCutpos(), $newcount);
                        $previndex = $currindex;
                    } else {
                        continue;
                    }
                }
                // The last (right-most) fragment.
                $frag[] = substr($oSequence->getSequence(), $previndex + $this->resten->getCutpos());
            } 
            return $frag;
        } elseif ($options == "O") {
            $pos_r = $oSequence->patposo($this->resten->getPattern(), "I", $this->resten->getCutpos());
            $ctr = 0;
            foreach($pos_r as $currindex) {
                $ctr++;
                if ($ctr == 1) {
                    $frag[] = substr($oSequence->getSequence(), 0, $currindex + $this->resten->getCutpos());
                    $previndex = $currindex;
                    continue;
                }
                if (($currindex - $previndex) >= $this->resten->getCutpos()) {
                    $newcount = $currindex - $previndex;
                    $frag[] = substr($oSequence->getSequence(), $previndex + $this->resten->getCutpos(), $newcount);
                    $previndex = $currindex;
                } else {
                    continue;
                }
            }
            // The last (right-most) fragment.
            $frag[] = substr($oSequence->getSequence(), $previndex + $this->resten->getCutpos());
            return $frag;
        }
    } 


    /**
     * returns the pattern associated with a given restriction endonuclease.
     * @global \AppBundle\Services\type $RestEn_DB
     * @param type $RestEn_Name
     * @return \AppBundle\Services\type
     */
    public function GetPattern($RestEn_Name)
    {
        global $RestEn_DB;
        return $RestEn_DB[$RestEn_Name][0];
    }


    /**
     * Returns the cutting position of the restriction enzyme object.
     * @global \AppBundle\Services\type $RestEn_DB
     * @param type $RestEn_Name
     * @return \AppBundle\Services\type
     */
    public function GetCutPos($RestEn_Name)
    {
        global $RestEn_DB;
        return $RestEn_DB[$RestEn_Name][1];
    }


    /**
     * Returns the length of the cutting pattern of the restriction enzyme object.
     * @global \AppBundle\Services\type $RestEn_DB
     * @param type $RestEn_Name
     * @return type
     */
    public function GetLength($RestEn_Name = "")
    {
        global $RestEn_DB;

        if ($RestEn_Name == "") {
            return strlen($this->resten->getPattern());
        } else {
            return strlen($RestEn_DB[$RestEn_Name][0]);
        }
    }


    /**
     * Flexible method for searching the Restriction Enzyme database 
     * for entries meeting complex criteria.  It returns an array of RestEn objects.
     * @global \AppBundle\Services\type $RestEn_DB
     * @param type $pattern
     * @param type $cutpos
     * @param type $plen
     * @return type
     */
    public function FindRestEn($pattern = "", $cutpos = "", $plen = "")
    {
        global $RestEn_DB;

        // 5 Cases: pattern only, cutpos only, patternlength only
        //          pattern and cutpos, cutpos and patternlength
        $RestEn_List = [];

        // Case 1: Pattern only
        if (($pattern != "") && ($cutpos == "") && ($plen == "")) {
            foreach($RestEn_DB as $key => $value) {
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
                    foreach($RestEn_DB as $key => $value) {
                        if ($value[1] < (int) substr($cutpos,1)) {
                            $RestEn_List[] = $key;
                        }
                    }
                    return $RestEn_List;
                } elseif (preg_match("/^>\d+$/", $cutpos)) {
                    foreach($RestEn_DB as $key => $value) {
                        if ($value[1] > (int) substr($cutpos,1)) {
                            $RestEn_List[] = $key;
                        }
                    }
                    return $RestEn_List;
                } elseif (preg_match("/^>=\d+$/", $cutpos)) {
                    foreach($RestEn_DB as $key => $value) {
                        if ($value[1] >= (int) substr($cutpos,2)) {
                            $RestEn_List[] = $key;
                        }
                    }
                    return $RestEn_List;
                } elseif (preg_match("/^<=\d+$/", $cutpos)) {
                    foreach($RestEn_DB as $key => $value) {
                        if ($value[1] <= (int) substr($cutpos,2)) {
                            $RestEn_List[] = $key;
                        }
                    }
                    return $RestEn_List;
                } elseif (preg_match("/^=\d+$/", $cutpos)) {
                    foreach($RestEn_DB as $key => $value) {
                        if ($value[1] == substr($cutpos,1)) {
                            $RestEn_List[] = $key;
                        }
                    }
                    return $RestEn_List;
                } else {
                    throw new \Exception("Malformed cutpos parameter.");
                }
            } elseif (is_int($cutpos)) {
                foreach($RestEn_DB as $key => $value)
                    if ($value[1] == $cutpos) {
                        $RestEn_List[] = $key;
                    }
                    return $RestEn_List;
            }
        } 

        // Case 3: Patternlength only
        if (($pattern == "") && ($cutpos == "") && ($plen != "")) {
            foreach($RestEn_DB as $key => $value) {
                if (strlen($value[0]) == $plen) {
                    $RestEn_List[] = $key;
                }
            }
            return $RestEn_List;
        }

        // Case 4: Pattern and cutpos only
        if (($pattern != "") && ($cutpos != "") && ($plen == "")) {
            foreach($RestEn_DB as $key => $value) {
                if (($value[0] == $pattern) && ($value[1] == $cutpos)) {
                    $RestEn_List[] = $key;
                }
            }
            return $RestEn_List;
        }

        // Case 5: Cutpos and plen only.
        if (($pattern == "") && ($cutpos != "") && ($plen != "")) {
            foreach($RestEn_DB as $key => $value) {
                if (($value[1] == $cutpos) && (strlen($value[0]) == $plen)) {
                    $RestEn_List[] = $key;
                }
            }
            return $RestEn_List;
        }

        throw new \Exception("Invalid combination of function parameters.");
    }
}