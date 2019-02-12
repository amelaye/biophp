<?php

namespace AppBundle\Traits;

trait SwissprotTrait {
    /*
    We begin by describing parse_swissprot() first.
    parse_swissprot() parses the Feature Table lines (those that begin with FT) in a Swissprot
    data file, extracts the feature key name, from endpoint, to endpoint, and description, and
    stores them in a (simple) array.
    */
    /**
     * Then pushes this array into a larger associative array, called $swiss, which is
     * also an attribute of the Seq object. It is assigned a key of the form: FT_<feature_key_name>.
     * Examples are: FT_PEPTIDE, FT_DISULFID.
     * @param array $swiss
     * @param type $ft_r
     */
    function process_ft(&$swiss, $ft_r)
    {
	foreach($ft_r as $element) {
            $index = "FT_" . $element[0];
            array_shift($element);					
            if (count($swiss[$index]) == 0) {
		$swiss[$index] = array();
		array_push($swiss[$index], $element);
            } else {
                array_push($swiss[$index], $element);
            }
	}
    }

    /**
     * Tests if the file pointer is at the start of a new sequence entry.
     * @param type $linestr
     * @param type $dbformat
     * @return type
     */
    function at_entrystart($linestr, $dbformat) {
	if ($dbformat == "GENBANK") {
            return (substr($linestr,0,5) == "LOCUS");
        } elseif ($dbformat == "SWISSPROT") {
            return (substr($linestr,0,2) == "ID");
        }
    }


    /**
     * gets the primary accession number of the sequence entry which we are
     * currently processing.  This uniquely identifies a sequence entry.
     * @param type $flines
     * @param type $linestr
     * @param type $dbformat
     * @return type
     */
    function get_entryid(&$flines, $linestr, $dbformat)
    {
        if ($dbformat == "GENBANK") {
            return trim(substr($linestr, 12, 16));
        } elseif ($dbformat == "SWISSPROT") {
            list($lineno, $linestr) = each($flines);
            if (substr($linestr,0,2) == "AC") {
                $words = preg_split("/;/", intrim(substr($linestr,5)));
                prev($flines);
                return $words[0];
            }
        }
    }


    /**
     * Copies the lines belonging to a single sequence entry into an array.
     * @param type $fpseq
     * @return boolean
     */
    function line2r($fpseq)
    {
        $flines = array();
        while(1) {
            $linestr = fgets($fpseq, 101);
            $flines[] = $linestr;
            if (left($linestr,2) == '//') {
                return $flines;
            }
        }
        return false;
    }


    /**
     * Tests if the file pointer is at a line containing a feature qualifier.
     * This applies only to GenBank sequence files.
     * @param type $str
     * @return boolean
     */
    function isa_qualifier($str)
    {
        if (firstchar($str) == '/') {
            return true;
        } else {
            return false;
        }
    }

    
    /**
     * Gets the byte offset (from beginning of file) of a particular line.  The file is
     * identified by $fp file pointer, while the line is identified by $lineno, which is zero-based.
     * @param type $fp
     * @param type $lineno
     * @return type
     */
    function fseekline($fp, $lineno)
    {
        $linectr = 0;
        fseek($fp, 0);
        while(!feof($fp)) {
            $linestr = fgets($fp,101);
            if ($linectr == $lineno) {
                fseek($fp, $byteoff);
                return $byteoff;
            }
            $linectr++;
            $byteoff = ftell($fp);
        }
    }


    /**
     * Searches for a particular sequence id ($seqid) within an *.IDX file
     * (identified by $fp file pointer), and returns data located in its $col-th column.
     * @param type $fp
     * @param type $col
     * @param type $seqid
     * @return boolean
     */
    function bsrch_tabfile($fp, $col, $seqid)
    {
        $linectr = 0;
        fseek($fp, 0);
        while(!feof($fp)) {
            fgets($fp, 41);
            $linectr++;
        }
        $lastline = $linectr;
        rewind($fp);

        if (!$fp) {
            throw new \Exception("CANT OPEN FILE");
        }

        $searchspace = $lastline;
        $floor = 0;
        $ceiling = $lastline - 1;

        while(1) {
            $offset = ((int) ($searchspace/2));
            $lineno = $floor + $offset;

            fseekline($fp, $lineno);
            $word = preg_split("/\s+/", trim(fgets($fp,81)));
            if ($word[$col] == $seqid) {
                $word[] = $lineno;
                return $word;
            } elseif ($seqid > $word[$col]) {
                $floor = $lineno + 1;
                $searchspace = $ceiling - $floor + 1;
                if ($searchspace <= 0) {
                    return FALSE;
                }
            } else {
                $ceiling = $lineno - 1;
                $searchspace = $ceiling - $floor + 1;
                if ($searchspace <= 0) {
                    return FALSE;
                }
            }
        }
    }
} 
