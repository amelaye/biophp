<?php

namespace AppBundle\Services;

class RestEn
{
    private $name;
    private $pattern;
    private $cutpos;
    private $length;


    /**
     * Cuts a DNA sequence into fragments using the restriction enzyme object.
     * @param type $seq
     * @param type $options
     * @return type
     */
    function CutSeq($seq, $options = "N")
    {
	if ($options == "N") {
            // patpos() returns: ( "PAT1" => (0, 12), "PAT2" => (7, 29, 53) )
            $patpos_r = $seq->patpos($this->pattern, "I");
            $frag = array();
            foreach($patpos_r as $patkey => $pos_r) {
		$ctr = 0;
		foreach($pos_r as $currindex) {
                    $ctr++;
                    if ($ctr == 1) {
			// 1st fragment is everything to the left of the 1st occurrence of pattern
			$frag[] = substr($seq->sequence, 0, $currindex + $this->cutpos);
			$previndex = $currindex;
			continue;
                    }
                    if (($currindex - $previndex) >= $this->cutpos) {
			$newcount = $currindex - $previndex;
			$frag[] = substr($seq->sequence, $previndex + $this->cutpos, $newcount);
			$previndex = $currindex;
                    } else {
                        continue;
                    }
		}
		// The last (right-most) fragment.
		$frag[] = substr($seq->sequence, $previndex + $this->cutpos);
            } 
            return $frag;
	} elseif ($options == "O") {
            $pos_r = $seq->patposo($this->pattern, "I", $this->cutpos);
            $ctr = 0;
            foreach($pos_r as $currindex) {
		$ctr++;
		if ($ctr == 1) {
                    $frag[] = substr($seq->sequence, 0, $currindex + $this->cutpos);
                    $previndex = $currindex;
                    continue;
		}
		if (($currindex - $previndex) >= $this->cutpos) {
                    $newcount = $currindex - $previndex;
                    $frag[] = substr($seq->sequence, $previndex + $this->cutpos, $newcount);
                    $previndex = $currindex;
		} else {
                    continue;
                }
            }
            // The last (right-most) fragment.
            $frag[] = substr($seq->sequence, $previndex + $this->cutpos);
            return $frag;
	}
    } 

    
    /**
     * RestEn() is the constructor method for the RestEn class.  It creates a new
     * RestEn object and initializes its properties accordingly.
     * RestEn() behavior:
     * If passed with make = 'custom', object will be added to RestEn_DB.
     * If not, the function will attemp to retrieve data from RestEn_DB.
     * If unsuccessful in retrieving data, it will return an error flag.
     * @global type $RestEn_DB
     * @param type $args
     */
    function RestEn($args)
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
                die("Cannot find entry in restriction endonuclease database.");
            } else {
		$this->pattern = $temp;
		$this->cutpos = $this->GetCutPos($this->name);
		$this->length = strlen($this->pattern);
            }
	}
    }


    /**
     * returns the pattern associated with a given restriction endonuclease.
     * @global \AppBundle\Services\type $RestEn_DB
     * @param type $RestEn_Name
     * @return \AppBundle\Services\type
     */
    function GetPattern($RestEn_Name) 
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
    function GetCutPos($RestEn_Name)
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
    function GetLength($RestEn_Name = "")
    {
	global $RestEn_DB;

	if ($RestEn_Name == "") {
            return strlen($this->pattern);
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
    function FindRestEn($pattern = "", $cutpos = "", $plen = "")
    {
	global $RestEn_DB;

	// 5 Cases: pattern only, cutpos only, patternlength only
	//          pattern and cutpos, cutpos and patternlength
	$RestEn_List = array();

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
            if (gettype($cutpos) == "string") {
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
                    die("Malformed cutpos parameter.");
                }
            } elseif (gettype($cutpos) == "integer") {
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