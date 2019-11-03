<?php
/**
 * Enzyme restriction manager
 * Freely inspired by BioPHP's project biophp.org
 * Created 11 february 2019
 * Last modified 3 november 2019
 */
namespace AppBundle\Service;

use AppBundle\Bioapi\Bioapi;
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
     * @var SequenceManager
     */
    private $sequenceManager;

    /**
     * RestrictionEnzymeManager constructor.
     * @param Bioapi $bioapi
     */
    public function __construct(Bioapi $bioapi)
    {
        $this->aRestEnzimDB = $bioapi->getTypeIIEndonucleasesForRest();
        $this->enzyme = new Enzyme();
    }

    /**
     * Sets a sequence object
     * @param SequenceManager $sequenceManager
     */
    public function setSequenceManager(SequenceManager $sequenceManager) {
        $this->sequenceManager = $sequenceManager;
    }

    /**
     * It creates a new Enzyme object and initializes its properties accordingly.
     * If passed with make = 'custom', object will be added to aRestEnzimDB.
     * If not, the function will attemp to retrieve data from aRestEnzimDB.
     * If unsuccessful in retrieving data, it will return an error flag.
     * @param   string      $sName
     * @param   string      $sPattern
     * @param   string      $sCutpos
     * @param   string      $sMake
     * @throws  \Exception
     */
    public function parseEnzyme($sName, $sPattern, $sCutpos, $sMake = "custom")
    {
        if ($sMake == "custom") {
            $this->enzyme->setName($sName);
            $this->enzyme->setPattern($sPattern);
            $this->enzyme->setCutpos($sCutpos);
            $this->enzyme->setLength(strlen($this->enzyme->getPattern()));

            $inner = array();
            $inner[] = $sPattern;
            $inner[] = $sCutpos;
            $this->aRestEnzimDB[$this->enzyme->getName()] = $inner;
        } else {
            // Look for given endonuclease in the aRestEnzimDB array.
            $this->enzyme->setName($sName);
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
     * @param   string             $options            May be "N" or "O".  If "N", the sequence is cut using the patpos() group
     * of methods (no overlapping patterns).  If "O", the sequence is cut using the patposo() group
     * of methods (with overlapping patterns). If omitted, this defaults to "N".
     * @return  array       An array of fragments (substrings of the parameter sequence)
     * @throws  \Exception
     */
    public function cutSeq($options = "N")
    {
        if ($options == "N") {
            return $this->nTreatment();
        } elseif ($options == "O") {
            return $this->oTreatment();
        }
    } 

    /**
     * Returns the pattern associated with a given restriction endonuclease.
     * @param string $RestEn_Name
     * @return \AppBundle\Services\type
     */
    public function getPattern($RestEn_Name)
    {
        return $this->aRestEnzimDB[$RestEn_Name][0];
    }


    /**
     * Returns the cutting position of the restriction enzyme object.
     * @param   string      $RestEn_Name
     * @return  int         Returns the cutting position (an integer) of the restriction enzyme object.
     */
    public function getCutPos($RestEn_Name)
    {
        return $this->aRestEnzimDB[$RestEn_Name][1];
    }


    /**
     * Returns the length of the cutting pattern of the restriction enzyme object.
     * @param   string  $RestEn_Name
     * @return  int     The length (integer) of the restriction pattern recognized by the enzyme.
     */
    public function getLength($RestEn_Name = "")
    {
        if ($RestEn_Name == "") {
            return strlen($this->enzyme->getPattern());
        } else {
            return strlen($this->aRestEnzimDB[$RestEn_Name][0]);
        }
    }


    private function fetchPatternOnly($pattern)
    {
        foreach($this->aRestEnzimDB as $key => $value) {
            if ($value[0] == $pattern) {
                $RestEn_List[] = $key;
            }
        }
        return $RestEn_List;
    }

    /**
     * A powerful method for searching our database of endonucleases for a particular
     * restriction enzyme exhibiting certain properties like pattern, cutting position,
     * and length, or combinations thereof.
     * @param   string      $pattern    The pattern of the restriction enzyme we wish to look for.
     * @param   int         $cutpos     The cutting position of the restriction enzyme we wish to look for.
     * @param   int         $plen       The length of the restriction enzyme we wish to look for.
     * @return  array       A list of restriction enyzmes that meet the criteria specified by the $pattern, $cutpos,
     * and $plen parameters.
     * @throws  \Exception
     */
    public function findRestEn($pattern = "", $cutpos = "", $plen = "")
    {
        // 5 Cases: pattern only, cutpos only, patternlength only
        //          pattern and cutpos, cutpos and patternlength
        $RestEn_List = [];

        // Case 1: Pattern only
        if (($pattern != "") && ($cutpos == "") && ($plen == "")) {
            $RestEn_List = $this->fetchPatternOnly($pattern);
            return $RestEn_List;
        }

        // Case 2: Cutpos only
        if (($pattern == "") && ($cutpos != "") && ($plen == "")) {
            //$firstchar = substr($cutpos, 0, 1);
            //$first2chars = substr($cutpos, 0, 2);
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
     * Cuts the sequence with option "O"
     * @return  array
     * @throws  \Exception
     */
    private function oTreatment()
    {
        $oSequence  = $this->sequenceManager->getSequence();
        $aFragment  = array();
        $iPrevIndex = 0;

        $aPos = $this->sequenceManager->patposo(null, $this->enzyme->getPattern(), "I", $this->enzyme->getCutpos());

        $iCtr = 0;
        foreach($aPos as $iCurrIndex) {
            $iCtr++;
            if ($iCtr == 1) {
                $aFragment[] = substr($oSequence->getSequence(), 0, $iCurrIndex + $this->enzyme->getCutpos());
                $iPrevIndex = $iCurrIndex;
                continue;
            }
            if (($iCurrIndex - $iPrevIndex) >= $this->enzyme->getCutpos()) {
                $iNewCount = $iCurrIndex - $iPrevIndex;
                $aFragment[] = substr($oSequence->getSequence(), $iPrevIndex + $this->enzyme->getCutpos(), $iNewCount);
                $iPrevIndex = $iCurrIndex;
            } else {
                continue;
            }
        }
        // The last (right-most) fragment.
        $aFragment[] = substr($oSequence->getSequence(), $iPrevIndex + $this->enzyme->getCutpos());

        return $aFragment;
    }

    /**
     * Cuts the sequence with option "N"
     * @return array
     * @throws \Exception
     */
    private function nTreatment()
    {
        $oSequence  = $this->sequenceManager->getSequence();
        $iPrevIndex = 0;
        $aFragment  = array();

        // patpos() returns: ( "PAT1" => (0, 12), "PAT2" => (7, 29, 53) )
        $aPatPos = $this->sequenceManager->patpos($this->enzyme->getPattern(), "I");

        foreach($aPatPos as $aPos) {
            $iCtr = 0;
            foreach($aPos as $iCurrIndex) {
                $iCtr++;
                if ($iCtr == 1) {
                    // 1st fragment is everything to the left of the 1st occurrence of pattern
                    $aFragment[] = substr($oSequence->getSequence(), 0, $iCurrIndex + $this->enzyme->getCutpos());
                    $iPrevIndex = $iCurrIndex;
                    continue;
                }
                if (($iCurrIndex - $iPrevIndex) >= $this->enzyme->getCutpos()) {
                    $iNewCount = $iCurrIndex - $iPrevIndex;
                    $aFragment[] = substr($oSequence->getSequence(), $iPrevIndex + $this->enzyme->getCutpos(), $iNewCount);
                    $iPrevIndex = $iCurrIndex;
                } else {
                    continue;
                }
            }
            // The last (right-most) fragment.
            $aFragment[] = substr($oSequence->getSequence(), $iPrevIndex + $this->enzyme->getCutpos());
        }
        return $aFragment;
    }
}
