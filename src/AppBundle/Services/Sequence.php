<?php

namespace AppBundle;

class Sequence
{
    /**
     * revcomp() first gets the complement of a DNA or RNA sequence, and then returns it in reverse order.
     * @param type $seq
     * @param type $moltype
     * @return type
     */
    function revcomp($seq, $moltype)
    {
	return strrev($this->complement($seq, $moltype));
    }

    /**
     * halfstr() returns one of the two palindromic "halves" of a palindromic string. 
     * @param type $string
     * @param type $no
     * @return type
     */
    function halfstr($string, $no)
    {
	// for now, this holds for mirror repeats.
        if(strlen($string) % 2 != 0) { //odd
            $comp_len = (int)(strlen($string)/2);
            if ($no == 0) {
                return substr($string, 0, $comp_len);
            } else {
                return substr($string, $comp_len + 1);
            }
        } else {
	    $comp_len = strlen($string)/2;
	    if ($no == 0) {
                return substr($string, 0, $comp_len);
            } else {
                return substr($string, $comp_len);
	    }
	}
    }

    /**
     * getbridge() returns the sequence located between two palindromic halves of a palindromic string.
     * Take note that the "bridge" as I call it, is not necessarily a genetic mirror or a palindrome.
     * @param type $string
     * @return string
     */
    function get_bridge($string)
    {
	if(strlen($string) % 2 != 0) { // odd
	    $comp_len = (int) (strlen($string)/2);
	    return substr($string, $comp_len, 1);
	} else {
            return "";
        }
    }

    /**
     * expand_na returns the expansion of a nucleic acid sequence, replacing special wildcard symbols 
     * with the proper PERL regular expression. 
     * @param type $string
     * @return type
     */
    function expand_na($string)
    {
	$string = preg_replace("/N|X/", ".", $string);
	$string = preg_replace("/R/", "[AG]", $string);
	$string = preg_replace("/Y/", "[CT]", $string);
	$string = preg_replace("/S/", "[GC]", $string);
	$string = preg_replace("/W/", "[AT]", $string);
	$string = preg_replace("/M/", "[AC]", $string);
	$string = preg_replace("/K/", "[TG]", $string);
	$string = preg_replace("/B/", "[CGT]", $string);
	$string = preg_replace("/D/", "[AGT]", $string);
	$string = preg_replace("/H/", "[ACT]", $string);
	$string = preg_replace("/R/", "[ACG]", $string);
	return $string;
    }
}