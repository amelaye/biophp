<?php
// This code is covered by GPL Version 2.
require_once("etc.inc");

// complement() gets the genetic complement of a DNA or RNA sequence. 
function complement($seq, $moltype)
	{
	if (isset($moltype) == FALSE)
		if (isset($this->moltype) == TRUE) $moltype = $this->moltype;
		else $moltype = "DNA"; // default to DNA.
	
	$dna_complements = array("A" => "T",
								 "T" => "A",
								 "G" => "C",
								 "C" => "G");
	
	$rna_complements = array("A" => "U",
								 "U" => "A",
								 "G" => "C",
								 "C" => "G");

	$moltype = strtoupper($moltype);
	if ($moltype == "DNA") $comp_r = $dna_complements;
	elseif ($moltype == "RNA") $comp_r = $rna_complements;
	$seqlen = strlen($seq);
	$compseq = "";
	for($i = 0; $i < $seqlen; $i++)
		{
		$symbol = substr($seq, $i, 1);
		$compseq .= $comp_r[$symbol];
		}
	return $compseq;
	}

// revcomp() first gets the complement of a DNA or RNA sequence, and then returns it in reverse order.
function revcomp($seq, $moltype)
	{
	return strrev(complement($seq, $moltype));
	}

// halfstr() returns one of the two palindromic "halves" of a palindromic string. 
function halfstr($string, $no)
	{
	// for now, this holds for mirror repeats.
	if (is_odd(strlen($string)))
		{
		$comp_len = (int) (strlen($string)/2);
		if ($no == 0) return substr($string, 0, $comp_len);
		else return substr($string, $comp_len + 1);
		}
	else
		{
		$comp_len = strlen($string)/2;
		if ($no == 0) return substr($string, 0, $comp_len);
		else return substr($string, $comp_len);
		}
	}

// getbridge() returns the sequence located between two palindromic halves of a palindromic string. 
// Take note that the "bridge" as I call it, is not necessarily a genetic mirror or a palindrome.

function get_bridge($string)
	{
	if (is_odd(strlen($string)))
		{
		$comp_len = (int) (strlen($string)/2);
		return substr($string, $comp_len, 1);
		}
	else return "";
	}

// expand_na returns the expansion of a nucleic acid sequence, replacing special wildcard symbols 
// with the proper PERL regular expression. 

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

class protein
{
var $id;
var $name;
var $sequence;

// seqlen() returns the length of a protein sequence().
function seqlen() { return strlen($this->sequence); }

// molwt() computes the molecular weight of a protein sequence.
function molwt()
	{ // OPENS function molwt()
	$lowerlimit = 0;
	$upperlimit = 1;
	$wts = array( "A" => array(89.09, 89.09),
					  "B" => array(132.12, 133.1),
					  "C" => array(121.15, 121.15),
					  "D" => array(133.1, 133.1),
					  "E" => array(147.13, 147.13),
					  "F" => array(165.19, 165.19),
					  "G" => array(75.07, 75.07),
					  "H" => array(155.16, 155.16),
					  "I" => array(131.18, 131.18),
					  "K" => array(146.19, 146.19),
					  "L" => array(131.18, 131.18),
					  "M" => array(149.22, 149.22),
					  "N" => array(132.12, 132.12),
					  "P" => array(115.13, 115.13),
					  "Q" => array(146.15, 146.15),
					  "R" => array(174.21, 174.21),
					  "S" => array(105.09, 105.09),
					  "T" => array(119.12, 119.12),
					  "V" => array(117.15, 117.15),
					  "W" => array(204.22, 204.22),
					  "X" => array(75.07, 204.22),
					  "Y" => array(181.19, 181.19),
					  "Z" => array(146.15, 147.13)
					);

	// Check if characters outside our 20-letter amino alphabet is included in the sequence.
	preg_match_all("/[^GAVLIPFYWSTCMNQDEKRHBXZ]/", $this->sequence, $match);
	// If there are unknown characters, then do not compute molwt and instead return FALSE.
	if (count($match[0]) > 0) return FALSE;

	// Otherwise, continue and calculate molecular weight of amino acid chain.
	$mwt = array(0, 0);
	$amino_len = $this->seqlen();
	 for($i = 0; $i < $amino_len; $i++)
		{
		$amino = substr($this->sequence, $i, 1);
		$mwt[$lowerlimit] += $wts[$amino][$lowerlimit];
		$mwt[$upperlimit] += $wts[$amino][$upperlimit];
		} // closes FOR loop
	$mwt_water = 18.015;
	$mwt[$lowerlimit] = $mwt[$lowerlimit] - (($this->seqlen() - 1) * $mwt_water);
	$mwt[$upperlimit] = $mwt[$upperlimit] - (($this->seqlen() - 1) * $mwt_water);
	return $mwt;
	} // closes FUNCTION MOLWT()
} // closes definition of CLASS PROTEIN

class seq
{
var $id;
var $strands;
var $moltype;
var $topology;
var $division;
var $date;
var $definition;
var $seqlength;

var $accession;
var $sec_accession;      // array
var $version;
var $ncbi_gi_id;
var $keywords;
var $segment_no;
var $segment_count;
var $segment;
var $source;

var $organism;
var $sequence;
var $reference;          // array
var $features;           // array
	
// Used by SeqAlign class
var $start;
var $end;
		
// Used when DBFORMAT is "SWISSPROT"
var $swissprot;    		// array

// molwt() calculates the molecular weight of a sequence.
function molwt()
	{
	// Check if characters outside our 20-letter amino alphabet is included in the sequence.
	if ($this->moltype == "DNA")
		{
		preg_match_all("/[^ACGTMRWSYKVHDBXN]/", $this->sequence, $match);
		// If there are unknown characters, then do not compute molwt and instead return FALSE.
		if (count($match[0]) > 0) return FALSE;
		}
	elseif ($this->moltype == "RNA")
		{
		preg_match_all("/[^ACGUMRWSYKVHDBXN]/", $this->sequence, $match);
		// If there are unknown characters, then do not compute molwt and instead return FALSE.
		if (count($match[0]) > 0) return FALSE;
		}
   elseif ($this->moltype == "PROTEIN")
      { // sequence is a protein, so invoke the Protein class' molwt() method.       
      $prot = new Protein();
      $prot->sequence = $this->sequence;
      return $prot->molwt();
      } 
   else return FALSE;     // return FALSE when encountering unknown molecule types		

	$lowerlimit = 0;
	$upperlimit = 1;

	$Car = 12.01;
	$Oxy = 16.00;
	$Nit = 14.01;
	$Hyd = 1.01;
	$Pho = 30.97;
	$water = 18.015;

	$adenine = (5 * $Car) + (5 * $Nit) + (5 * $Hyd);
	$guanine = (5 * $Car) + (5 * $Nit) + (1 * $Oxy) + (5 * $Hyd);
	$cytosine = (4 * $Car) + (3 * $Nit) + (1 * $Oxy) + (5 * $Hyd);
	$thymine = (5 * $Car) + (2 * $Nit) + (2 * $Oxy) + (6 * $Hyd);
	$uracil = (4 * $Car) + (2 * $Nit) + (2 * $Oxy) + (4 * $Hyd);

	// neutral (unionized) form
	$ribo_pho = (5 * $Car) + (7 * $Oxy) + (9 * $Hyd) + (1 * $Pho);
	$deoxy_pho = (5 * $Car) + (6 * $Oxy) + (9 * $Hyd) + (1 * $Pho);

	// the following are single strand molecular weights / base
	$rna_A_wt = $adenine + $ribo_pho - $water;
	$rna_C_wt = $cytosine + $ribo_pho - $water;
	$rna_G_wt = $guanine + $ribo_pho - $water;
	$rna_U_wt = $uracil + $ribo_pho - $water;

	$dna_A_wt = $adenine + $deoxy_pho - $water;
	$dna_C_wt = $cytosine + $deoxy_pho - $water;
	$dna_G_wt = $guanine + $deoxy_pho - $water;
	$dna_T_wt = $thymine + $deoxy_pho - $water;

	$dna_wts = array('A' => array($dna_A_wt, $dna_A_wt),          // Adenine
						  'C' => array($dna_C_wt, $dna_C_wt),          // Cytosine
						  'G' => array($dna_G_wt, $dna_G_wt),          // Guanine
						  'T' => array($dna_T_wt, $dna_T_wt),          // Thymine
						  'M' => array($dna_C_wt, $dna_A_wt),          // A or C
						  'R' => array($dna_A_wt, $dna_G_wt),          // A or G
						  'W' => array($dna_T_wt, $dna_A_wt),          // A or T
						  'S' => array($dna_C_wt, $dna_G_wt),          // C or G
						  'Y' => array($dna_C_wt, $dna_T_wt),          // C or T
						  'K' => array($dna_T_wt, $dna_G_wt),          // G or T
						  'V' => array($dna_C_wt, $dna_G_wt),          // A or C or G
						  'H' => array($dna_C_wt, $dna_A_wt),          // A or C or T
						  'D' => array($dna_T_wt, $dna_G_wt),          // A or G or T
						  'B' => array($dna_C_wt, $dna_G_wt),          // C or G or T
						  'X' => array($dna_C_wt, $dna_G_wt),          // G or A or T or C
						  'N' => array($dna_C_wt, $dna_G_wt)           // G or A or T or C
						 );

	$rna_wts = array('A' => array($rna_A_wt, $rna_A_wt),      // Adenine
						  'C' => array($rna_C_wt, $rna_C_wt),       // Cytosine
						  'G' => array($rna_G_wt, $rna_G_wt),       // Guanine
						  'U' => array($rna_U_wt, $rna_U_wt),       // Uracil
						  'M' => array($rna_C_wt, $rna_A_wt),       // A or C
						  'R' => array($rna_A_wt, $rna_G_wt),       // A or G
						  'W' => array($rna_U_wt, $rna_A_wt),       // A or U
						  'S' => array($rna_C_wt, $rna_G_wt),       // C or G
						  'Y' => array($rna_C_wt, $rna_U_wt),       // C or U
						  'K' => array($rna_U_wt, $rna_G_wt),       // G or U
						  'V' => array($rna_C_wt, $rna_G_wt),       // A or C or G
						  'H' => array($rna_C_wt, $rna_A_wt),       // A or C or U
						  'D' => array($rna_U_wt, $rna_G_wt),       // A or G or U
						  'B' => array($rna_C_wt, $rna_G_wt),       // C or G or U
						  'X' => array($rna_C_wt, $rna_G_wt),       // G or A or U or C
						  'N' => array($rna_C_wt, $rna_G_wt)        // G or A or U or C
						 );

	$all_na_wts = array("DNA" => $dna_wts, "RNA" => $rna_wts);
	$na_wts = $all_na_wts[$this->moltype];

	$weight_lower_bound += $water;
	$weight_upper_bound += $water;

	$mwt = array(0, 0);
	$NA_len = $this->seqlen();
	for($i = 0; $i < $NA_len; $i++)
		{
		$NA_base = substr($this->sequence, $i, 1);
		$mwt[$lowerlimit] += $na_wts[$NA_base][$lowerlimit];
		$mwt[$upperlimit] += $na_wts[$NA_base][$upperlimit];
		// print $mwt[$lowerlimit] . " : " . $mwt[$upperlimit];
		// print "<BR>";
		} // closes FOR loop
	$mwt_water = 18.015;
	$mwt[$lowerlimit] += $mwt_water;
	$mwt[$upperlimit] += $mwt_water;
	return $mwt;
	}

// count_codons() counts the number of codons (trios of base-pairs) in a DNA/RNA sequence.
function count_codons()
	{
	if (isset($this->features["CDS"]["/codon_start"]) == FALSE) $codstart = 1;
	else $codstart = $this->features["CDS"]["/codon_start"];
	$codcount = (int) (($this->seqlen() - $codstart + 1)/3);
	return $codcount;
	}

function subseq($start, $count)
	{
	$newseq = new seq();
	$newseq->sequence = substr($this->sequence, $start, $count);
	return $newseq;
	}

/*
patpos() returns a two-dimensional associative array where each key is a substring matching a 
given pattern, and each value is an array of positional indexes which indicate the location of 
each occurrence of the substring (needle) in the larger string (haystack). This DOES NOT allow
for pattern overlaps.

Return value example: ( "PAT1" => (0, 17), "PAT2" => (8, 29) )
*/
function patpos($pattern, $options = "I")
	{
	$outer = array();
	$pf = $this->patfreq($pattern, $options);
	$haystack = $this->sequence;
	if (strtoupper($options) == "I") $haystack = strtoupper($haystack);

	foreach($pf as $key=>$value)
		{
		if ($options == "I") $key = strtoupper($key);
		$inner = array();
		$start = 0;
		for($i = 0; $i < $value; $i++)
			{
			$lastpos = strpos($haystack, $key, $start);
			array_push($inner, $lastpos);
			$start = $lastpos + strlen($key);
			}
		$outer[$key] = $inner;
		}
	return $outer;
   }

/*
patpos() is similar to patpos() except that this allows for overlapping patterns.

Return value format: (index1, index2, ... )
Return value sample: ( 0, 8, 17, 29)
*/
function patposo($pattern, $options = "I", $cutpos = 1)
	{
	$outer = array();
	$haystack = $this->sequence;
	if (strtoupper($options) == "I") $haystack = strtoupper($haystack);
	$pf = $this->patfreq($pattern, $options);
	$relpos_r = array();
	$currentpos = -1 * $cutpos;
	$lastpos = -1 * $cutpos;
	$ctr = 0;
	$runsum_start = 0;
	while(strlen($haystack) >= strlen($pattern))
		{
		$ctr++;
		if ($ctr == 1) $start = 0;
		else $start = $lastpos + $cutpos;
		$haystack = substr($haystack, $start);
		$runsum_start += $start;
		$minpos = 999999;
		$found_flag = FALSE;
		foreach($pf as $key=>$value)
			{
			$currentpos = strpos($haystack, $key);
			if (gettype($currentpos) == "integer")
				{
				$found_flag = TRUE;
				if ($currentpos < $minpos) $minpos = $currentpos;
				}
			}
		if ($found_flag == FALSE) break;
		$currentpos = $minpos;
		if ($ctr == 1) $abspos[] = $currentpos;
		else $abspos[] = $runsum_start + $currentpos;
		$lastpos = $currentpos;
		}
	return $abspos;
	}

/*
patfreq() returns a one-dimensional associative array where each key is a substring matching the
given pattern, and  each value is the frequency count of the substring within the larger string.

Return value example: ( "GAATTC" => 3, "ATAT" => 4, ... )
*/
function patfreq($pattern, $options = "I")
	{
	$match = $this->findpattern($pattern, $options);
	return array_count_values($match[0]);
	}

// findpattern returns: ( "GCG", "GCG", "GCG" ) if pattern is exactly "GCG".
function findpattern($pattern, $options = "I")
	{
	if (firstChar($pattern) == "_")
		$pattern = getpattern($pattern);
	if (strtoupper($options) == "I")
		preg_match_all("/" . expand_na(strtoupper($pattern)) . "/", strtoupper($this->sequence), $match);
	else preg_match_all("/" . expand_na($pattern) . "/", $this->sequence, $match);
	return $match;
	}

function seqlen()
	{
	return strlen($this->sequence);
	}

// Apr 10, 2003 - This now returns 0 instead of NULL when
// $symbol is not found.  0 is the preferred return value.
function symfreq($symbol)
   {
   $symtally = count_chars(strtoupper($this->sequence), 1);
   if ($symtally[ord($symbol)] == NULL) return 0;
   else return $symtally[ord($symbol)];
   }

function getcodon($index, $readframe = 0)
	{
	return strtoupper(substr($this->sequence, ($index * 3) + $readframe, 3));
	}

function translate($readframe = 0, $format = 3)
	{
	$codon_index = 0;
	$result = "";
	while(1)
		{
		$codon = $this->getcodon($codon_index, $readframe);
		if ($codon == "") break;
		if ($format == 1) $result .= $this->translate_codon($codon, $format);
		elseif ($format == 3) $result .= " " . $this->translate_codon($codon, $format);
		else die("Invalid format parameter");
		$codon_index++;
		}
	return $result;
	}

// Function charge() accepts a string of amino acids in single-letter format and outputs
// a string of charges in single-letter format also.  A for acidic, C for basic, and N
// for neutral.
function charge($amino_seq)
	{
	$charge_seq = "";
	$ctr = 0;
	while(1)
		{
		$amino_letter = substr($amino_seq, $ctr, 1);
		if ($amino_letter == "") break;
		if (($amino_letter == "D") or ($amino_letter == "E"))
			$charge_seq .= "A";
		elseif (($amino_letter == "K") or ($amino_letter == "R") or ($amino_letter == "H"))
			$charge_seq .= "C";
		elseif ($amino_letter == "*") $charge_seq .= "*";
		elseif ($amino_letter == "X") $charge_seq .= "X";
		elseif (substr_count("GAVLISTNQFYWCMP", $amino_letter) >= 1)
			$charge_seq .= "N";
		else die("Invalid amino acid symbol in input sequence.");
		$ctr++;
		}
	return $charge_seq;
	}

// Chemical groups: L - GAVLI, H - ST, M - NQ, R - FYW, S - CM, I - P, A - DE, C - KRH, * - *, X - X
function chemgrp($amino_seq)
	{
	$chemgrp_seq = "";
	$ctr = 0;
	while(1)
		{
		$amino_letter = substr($amino_seq, $ctr, 1);
		if ($amino_letter == "") break;
		if (substr_count("GAVLI", $amino_letter) == 1) $chemgrp_seq .= "L";
		elseif (($amino_letter == "S") or ($amino_letter == "T")) $chemgrp_seq .= "H";
		elseif (($amino_letter == "N") or ($amino_letter == "Q")) $chemgrp_seq .= "M";
		elseif (substr_count("FYW", $amino_letter) == 1) $chemgrp_seq .= "R";
		elseif (($amino_letter == "C") or ($amino_letter == "M")) $chemgrp_seq .= "S";
		elseif ($amino_letter == "P") $chemgrp_seq .= "I";
		elseif (($amino_letter == "D") or ($amino_letter == "E")) $chemgrp_seq .= "A";
		elseif (($amino_letter == "K") or ($amino_letter == "R") or ($amino_letter == "H"))
			$chemgrp_seq .= "C";
		elseif ($amino_letter == "*") $chemgrp_seq .= "*";
		elseif ($amino_letter == "X") $chemgrp_seq .= "X";
		else die("Invalid amino acid symbol in input sequence.");
		$ctr++;
		}
	return $chemgrp_seq;
	}

function translate_codon($codon, $format = 3)
	{
	if (($format != 3) and ($format != 1)) die("Invalid format parameter.");
	if (strlen($codon) < 3)
		if ($format == 3) return "XXX";
		else return "X";

	$codon = strtoupper($codon);
	$codon = ereg_replace("T", "U", $codon);
	$letter1 = substr($codon, 0, 1);
	$letter2 = substr($codon, 1, 1);
	$letter3 = substr($codon, 2, 1);

	if ($format == 3)
		{ // OPENS if ($format == 3)

		if ($letter1 == "U")
			{ // OPENS if ($letter1 == "U")
			if ($letter2 == "U")
				{
				if ($letter3 == "U") return "Phe";
				elseif ($letter3 == "C") return "Phe";
				elseif ($letter3 == "A") return "Leu";
				elseif ($letter3 == "G") return "Leu";
				}
			if ($letter2 == "C") return "Ser";
			if ($letter2 == "A")
				{
				if ($letter3 == "U") return "Tyr";
				elseif ($letter3 == "C") return "Tyr";
				elseif ($letter3 == "A") return "STP";
				elseif ($letter3 == "G") return "STP";
				}
			if ($letter2 == "G")
				{
				if ($letter3 == "U") return "Cys";
				elseif ($letter3 == "C") return "Cys";
				elseif ($letter3 == "A") return "STP";
				elseif ($letter3 == "G") return "Trp";
				}
			} // CLOSES if ($letter1 == "U")

		// Code to handle 3-letter codon strings that start with "C".
		if ($letter1 == "C")
			{
			if ($letter2 == "U")
				{
				return "Leu";
				}
			if ($letter2 == "C")
				{
				return "Pro";
				}
			if ($letter2 == "A")
				{
				if ($letter3 == "U")
					{
					return "His";
					}
				elseif ($letter3 == "C")
					{
					return "His";
					}
				elseif ($letter3 == "A")
					{
					return "Gln";
					}
				elseif ($letter3 == "G")
					{
					return "Gln";
					}
				}
			if ($letter2 == "G")
				{
				return "Arg";
				}
			}

		// Code to handle 3-letter codon strings that start with "A".
		if ($letter1 == "A")
			{
			if ($letter2 == "U")
				{
				if ($letter3 == "G") { return "Met"; }
				else { return "Ile"; }
				}
			if ($letter2 == "C")
				{
				return "Thr";
				}
			if ($letter2 == "A")
				{
				if ($letter3 == "U")
					{
					return "Asn";
					}
				elseif ($letter3 == "C")
					{
					return "Asn";
					}
				elseif ($letter3 == "A")
					{
					return "Lys";
					}
				elseif ($letter3 == "G")
					{
					return "Lys";
					}
				}
			if ($letter2 == "G")
				{
				if ($letter3 == "U")
					{
					return "Ser";
					}
				elseif ($letter3 == "C")
					{
					return "Ser";
					}
				elseif ($letter3 == "A")
					{
					return "Arg";
					}
				elseif ($letter3 == "G")
					{
					return "Arg";
					}
				}
			}

		// Code to handle 3-letter codon strings that start with "G".
		if ($letter1 == "G")
			{
			if ($letter2 == "U")
				return "Val";
			if ($letter2 == "C")
				return "Ala";
			if ($letter2 == "A")
				{
				if ($letter3 == "U")
					return "Asp";
				elseif ($letter3 == "C")
					return "Asp";
				elseif ($letter3 == "A")
					return "Glu";
				elseif ($letter3 == "G")
					return "Glu";
				}
			if ($letter2 == "G")
				{
				return "Gly";
				}
			}
		} // CLOSES if ($format == 3)
	elseif ($format == 1)
		{ // OPENS elseif ($format == 1)
		if ($letter1 == "U")
			{ // OPENS if ($letter1 == "U")
			if ($letter2 == "U")
				{
				if ($letter3 == "U")
					return "F";
				elseif ($letter3 == "C")
					return "F";
				elseif ($letter3 == "A")
					return "L";
				elseif ($letter3 == "G")
					return "L";
				}
			if ($letter2 == "C")
				return "S";
			if ($letter2 == "A")
				{
				if ($letter3 == "U")
					return "Y";
				elseif ($letter3 == "C")
					return "Y";
				elseif ($letter3 == "A")
					return "*";
				elseif ($letter3 == "G")
					return "*";
				}
			if ($letter2 == "G")
				{
				if ($letter3 == "U")
					return "C";
				elseif ($letter3 == "C")
					return "C";
				elseif ($letter3 == "A")
					return "*";
				elseif ($letter3 == "G")
					return "W";
				}
			} // CLOSES if ($letter1 == "U")

		// Code to handle 3-letter codon strings that start with "C".
		if ($letter1 == "C")
			{ // OPENS if ($letter1 == "C")
			if ($letter2 == "U")
				return "L";
			if ($letter2 == "C")
				return "P";
			if ($letter2 == "A")
				{
				if ($letter3 == "U")
					return "H";
				elseif ($letter3 == "C")
					return "H";
				elseif ($letter3 == "A")
					return "Q";
				else return "Q";
				}
			if ($letter2 == "G")
				return "R";
			} // CLOSES if ($letter1 == "C")

		// Code to handle 3-letter codon strings that start with "A".
		if ($letter1 == "A")
			{ // OPENS if ($letter1 == "A")
			if ($letter2 == "U")
				{
				if ($letter3 == "G") { return "M"; }
				else { return "I"; }
				}
			if ($letter2 == "C")
				{
				return "T";
				}
			if ($letter2 == "A")
				{
				if ($letter3 == "U")
					return "N";
				elseif ($letter3 == "C")
					return "N";
				elseif ($letter3 == "A")
					return "K";
				elseif ($letter3 == "G")
					return "K";
				}
			if ($letter2 == "G")
				{
				if ($letter3 == "U")
					return "S";
				elseif ($letter3 == "C")
					return "S";
				elseif ($letter3 == "A")
					return "R";
				elseif ($letter3 == "G")
					return "R";
				}
			} // CLOSES if ($letter == "A")

		// Code to handle 3-letter codon strings that start with "G".
		if ($letter1 == "G")
			{ // OPENS if ($letter1 == "G")
			if ($letter2 == "U")
				return "V";
			if ($letter2 == "C")
				return "A";
			if ($letter2 == "A")
				{
				if ($letter3 == "U")
					return "D";
				elseif ($letter3 == "C")
					return "D";
				elseif ($letter3 == "A")
					return "E";
				elseif ($letter3 == "G")
					return "E";
				}
			if ($letter2 == "G") return "G";
			} // CLOSES if ($letter1 == "G")
		} // CLOSES elseif ($format == 3)
	return "X";
	} // CLOSES function translate_codon()
	
function trunc($start, $count)
	{
	return substr($this->sequence, $start, $count);
	}
	
/* Definition of terms:
	MIRROR: The equivalent of a string palindrome in programming terms.
			  Comes in two varieties -- ODD-LENGTH and EVEN-LENGTH.
			  The strict biological definition of mirrors are EVEN-LENGTH only.
	MIRROR SEQUENCE: seq1-[X]-seq2, where X is an optional nucleotide base (A, G, C, or T).
	Seq1 and Seq2 are called the complementary sequences or halves.
	For our purposes, we shall call [X] as the "bridge".
*/
	
function is_mirror($string = "")
	{
	if (strlen($string) == 0) $string = $this->sequence;
	if ($string == strrev($string)) return true;
	else return false;
	}		
		
// Returns 3D assoc array: ( [2] => ( ("AA", 3), ("GG", 7) ), [4] => ( ("GAAG", 16) ) )
function find_mirror($haystack, $pallen1, $pallen2 = "", $options = "E")
	{
	$haylen = strlen($haystack);
	if ($haylen == 0) 
		{
		$haystack = $this->sequence;
		$haylen = strlen($haystack);
		if ($haylen == 0) return FALSE;
		}
	if (isset($pallen1) == FALSE) return FALSE;
	if ($pallen1 < 2) return FALSE;
	if ($pallen1 > $haylen) return FALSE;
	if (gettype($pallen1) != "integer") return FALSE;
	// if third parameter (representing upper palindrome length) is missing
	if ((gettype($pallen2) == "string") and ($pallen2 == "")) $pallen2 = $pallen1;
	elseif (gettype($pallen2) != "integer") return FALSE;
	elseif ($pallen2 < $pallen1) return FALSE;
	$options = strtoupper($options);
	if (($options != "E") and ($options != "O") and ($options != "A"))
		return FALSE;

	$outer_r = array();
	for($currlen = $pallen1; $currlen <= $pallen2; $currlen++)
		{
		if (($options == "E") and (is_odd($currlen) == TRUE)) continue;
		if (($options == "O") and (is_even($currlen) == TRUE)) continue;

		$string_count = $haylen - $currlen + 1;
		$middle_r = array();
		for($j = 0; $j < $string_count; $j++)
			{
			$string = substr($haystack, $j, $currlen);
			if ($this->is_mirror($string))
				{
				$inner_r = array($string, $j);
				$middle_r[] = $inner_r;
				}
			}
		if (count($middle_r) > 0) $outer_r[$currlen] = $middle_r;
		}
	return $outer_r;
	}		
	
// For mirror repeats, we allow strings with both ODD and EVEN lengths.
function is_palindrome($string = "")
	{
	if (strlen($string) == 0) $string = $this->sequence;
	
	// By definition, odd-lengthed strings cannot be a palindrome.
	if (is_odd(strlen($string))) return FALSE;
	$half1 = halfstr($string, 0);
	$half2 = halfstr($string, 1);
	if ($half1 == @revcomp($half2)) return TRUE;
	else return FALSE;
	}	
	
// find_palindrome() returns a two-dimensional array containing palindromic substrings found in a sequence,
// and their location, in terms of zero-based indices.  E.g. ( ("ATGttCAT", 2), ("ATGccccccCAT", 18), ... )

function find_palindrome($haystack, $seqlen = "", $pallen = "")
	{ // OPENS function find_palindrome()
	/* CASES:
		1) seqlen is not set, pallen is not set. - return FALSE (function error)
		2) seqlen is set, pallen is set.
		3) seqlen is set, pallen is not set.
		4) seqlen is not set, pallen is set.
	*/

	// CASE 1) seqlen is not set, pallen is not set. - return FALSE (function error)
	if ( is_blankstr($seqlen) and is_blankstr($pallen) ) return FALSE;

	if ( !(is_blankstr($seqlen)) and !(is_blankstr($pallen)) )
		{ // OPENS CASE 2) seqlen is set, pallen is set.
		$haylen = strlen($haystack);
		$string_count = $haylen - $seqlen + 1;
		$outer_r = array();
		for($j = 0; $j < $string_count; $j++)
			{ // OPENS for($j...
			$string = substr($haystack, $j, $seqlen);
			$halfstr_count = (int) (strlen($haystack)/2);
			$palstring1 = substr($string, 0, $pallen);
			$palstring2 = right($string, $pallen);
			if ( $palstring1 == revcomp($palstring2, "DNA") ) $outer_r[] = array($string, $j);
			}
		return $outer_r;
		} // CLOSES CASE 2) seqlen is set, pallen is set.
	elseif ( !(is_blankstr($seqlen)) and is_blankstr($pallen) )
		{ // OPENS CASE 3) seqlen is set, pallen is not set.
		$haylen = strlen($haystack);
		$string_count = $haylen - $seqlen + 1;
		$outer_r = array();
		for($j = 0; $j < $string_count; $j++)
			{ // OPENS for($j...
			$string = substr($haystack, $j, $seqlen);
			$halfstr_count = (int) (strlen($haystack)/2);
			$palstring = "";
			for($k = 0; $k < $halfstr_count; $k++)
				{
				$let1 = substr($string, $k, 1);
				$let2 = substr($string, strlen($string)-1-$k, 1);
				if ($let1 == complement($let2, "DNA")) $palstring .= $let1;
				else break;
				}
			if (strlen($palstring) >= 3)
				{
				$inner_r = array($string, $j);
				$outer_r[] = $inner_r;
				}
			}
		return $outer_r;
		} // CLOSES CASE 3) seqlen is set, pallen is not set.
	elseif ( is_blankstr($seqlen) and !(is_blankstr($pallen)) )
		{ // OPENS CASE 4) seqlen is not set, pallen is set.
		$haylen = strlen($haystack);
		$string_count = ($haylen - $pallen + 1) - $pallen;
		$middle_r = array();
		$outer_r = array();
		$newseq = new seq();

		for($j = 0; $j < $string_count; $j++)
			{ // OPENS for($j...
			$whole = substr($haystack, $j);
			$head = substr($whole, 0, $pallen);
			$tail = substr($whole, $pallen);
			// $tail_len = $haylen - ($pallen + $j);
			$tail_len = strlen($tail);
			$needle = complement(strrev($head), "DNA");
			$newseq->sequence = $tail;
			$pos_r = $newseq->patposo($needle, "I");
			if (count($pos_r) == 0) continue;
			foreach($pos_r as $posidx)
				{ // OPENS foreach($pos_r...
				// Output: ( ("ATGttCAT", 2), ("ATGccccccCAT", 18), ... )
				$seqstr = substr($whole, 0, $posidx + 2*$pallen);
				$inner_r = array($seqstr, $j);
				array_push($outer_r, $inner_r);
				} // CLOSES foreach($pos_r...
			} // CLOSES for($j...
		} // CLOSES CASE 4) seqlen is not set, pallen is set.
	return $outer_r;
	} // CLOSES function find_palindrome()	
} // CLOSES class seq()
?>
