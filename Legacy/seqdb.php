<?php
require_once("etc.inc");
require_once("seq.inc");
 
// ================== FUNCTIONS ========================

/*
We begin by describing parse_swissprot() first.

parse_swissprot() parses the Feature Table lines (those that begin with FT) in a Swissprot
data file, extracts the feature key name, from endpoint, to endpoint, and description, and
stores them in a (simple) array.  

process_ft() then pushes this array into a larger associative array, called $swiss, which is 
also an attribute of the Seq object. It is assigned a key of the form: FT_<feature_key_name>.
Examples are: FT_PEPTIDE, FT_DISULFID.
*/
function process_ft(&$swiss, $ft_r)
	{
	foreach($ft_r as $element)
		{
		$index = "FT_" . $element[0];
		array_shift($element);					
		if (count($swiss[$index]) == 0) 
			{
			$swiss[$index] = array();
			array_push($swiss[$index], $element);
			}
		else array_push($swiss[$index], $element); 
		}
	}

// at_entrystart() tests if the file pointer is at the start of a new sequence entry. 
function at_entrystart($linestr, $dbformat)
	{
	if ($dbformat == "GENBANK")
		return (substr($linestr,0,5) == "LOCUS");
	elseif ($dbformat == "SWISSPROT")
		return (substr($linestr,0,2) == "ID");
	}

// get_entryid() gets the primary accession number of the sequence entry which we are 
// currently processing.  This uniquely identifies a sequence entry.
function get_entryid(&$flines, $linestr, $dbformat)
	{
	if ($dbformat == "GENBANK")
		return trim(substr($linestr, 12, 16));
	elseif ($dbformat == "SWISSPROT")
		{
		list($lineno, $linestr) = each($flines);
		if (substr($linestr,0,2) == "AC")
			{
			$words = preg_split("/;/", intrim(substr($linestr,5)));
			prev($flines);
			return $words[0];
			}
		}
	}

// line2r() copies the lines belonging to a single sequence entry into an array.
function line2r($fpseq)
	{
	$flines = array();
	while(1)
		{
		$linestr = fgets($fpseq, 101);
		$flines[] = $linestr;
		if (left($linestr,2) == '//') return $flines;
		}
	return FALSE;
	}

// isa_qualifier() tests if the file pointer is at a line containing a feature qualifier.
// This applies only to GenBank sequence files.  
function isa_qualifier($str)
	{
	if (firstchar($str) == '/') return true;
	else return false;
	}

// fseekline() gets the byte offset (from beginning of file) of a particular line.  The file is
// identified by $fp file pointer, while the line is identified by $lineno, which is zero-based.
function fseekline($fp, $lineno)
	{
	$linectr = 0;
	fseek($fp, 0);
	while(!feof($fp))
		{
		$linestr = fgets($fp,101);
		if ($linectr == $lineno)
			{
			fseek($fp, $byteoff);
			return $byteoff;
			}
		$linectr++;
		$byteoff = ftell($fp);
		}
	}

// bsrch_tabfile() searches for a particular sequence id ($seqid) within an *.IDX file
// (identified by $fp file pointer), and returns data located in its $col-th column.
function bsrch_tabfile($fp, $col, $seqid)
	{
	$linectr = 0;
	fseek($fp, 0);
	while(!feof($fp))
		{
		fgets($fp, 41);
		$linectr++;
		}
	$lastline = $linectr;
	rewind($fp);

	if ($fp == FALSE) die("CANT OPEN FILE");
	$searchspace = $lastline;
	$floor = 0;
	$ceiling = $lastline - 1;

	while(1)
		{
		$offset = ((int) ($searchspace/2));
		$lineno = $floor + $offset;

		fseekline($fp, $lineno);
		$word = preg_split("/\s+/", trim(fgets($fp,81)));
		if ($word[$col] == $seqid)
			{
			$word[] = $lineno;
			return $word;
			}
		elseif ($seqid > $word[$col])
			{
			$floor = $lineno + 1;
			$searchspace = $ceiling - $floor + 1;
			if ($searchspace <= 0) return FALSE;
			}
		else
			{
			$ceiling = $lineno - 1;
			$searchspace = $ceiling - $floor + 1;
			if ($searchspace <= 0) return FALSE;
			}
		}
	// fclose($fpidx);
	}

// ================== CLASSES ========================

class SeqDB
{ // OPENS definition of SEQDB CLASS.
var $dbname;
var $data_fn;
var $data_fp;
var $dir_fn;
var $dir_fp;
var $seqptr;
var $seqcount;
var $dbformat;
var $bof;
var $eof;

// We need the functions bof() and eof() to determine if we've reached the end of
// file or not.
// Two ways of doing this: 1) examine value of seqptr, or 2) maintain boolean variables eof and bof

// first() positions the sequence pointer (i.e. the seqptr property of a Seq object) to 
// the first sequence in a database (SeqDB object).
function first()
	{
	$this->seqptr = 0;
	}

// last() positions the sequence pointer (i.e. the seqptr property of a Seq object) to 
// the last sequence in a database (SeqDB object).
function last()
	{
	$this->seqptr = $this->seqcount-1;
	}

// prev() (short for previous) positions the sequence pointer (i.e. the seqptr property of
// a Seq object) to the sequence that comes before the current sequence.  
function prev()
	{
	if ($this->seqptr > 0) $this->seqptr--;
	else $this->bof = TRUE;
	}

// next() positions the sequence pointer (i.e. the seqptr property of a Seq object) to the 
// sequence that comes after the current sequence.  
function next()
	{
	if ($this->seqptr < $this->seqcount-1) $this->seqptr++;
	else $this->eof = TRUE;
	}
	
// fetch() retrieves all data from the specified sequence record and returns them in the 
// form of a Seq object.  This method invokes one of several parser methods.
function fetch()
	{
	if ($this->data_fn == "") die("Cannot invoke fetch() method from a closed object.");
	@$seqid = func_get_arg(0);

	// IDX and DIR files remain open for the duration of the FETCH() method.
	$fp = fopen($this->data_fn, "r");
	$fpdir = fopen($this->dir_fn, "r");

	if ($seqid != FALSE)
		{
		$idx_r = bsrch_tabfile($fp, 0, $seqid);
		if ($idx_r == FALSE) return FALSE;
		else $this->seqptr = $idx_r[3];
		}
	else
		 {
		 // For now, SEQPTR determines CURRENT SEQUENCE ID.  Alternative is to track curr line.
		 fseekline($fp, $this->seqptr);
		 $idx_r = preg_split("/\s+/", trim(fgets($fp, 81)));
		 }
	$dir_r = bsrch_tabfile($fpdir, 0, $idx_r[1]);

	$fpseq = fopen($dir_r[1], "r");
	fseekline($fpseq, $idx_r[2]);

	$flines = line2r($fpseq);

	$myseq = new seq();
	if ($this->dbformat == "GENBANK")
		$myseq = $this->parse_id($flines);
	elseif ($this->dbformat == "SWISSPROT")
		$myseq = $this->parse_swissprot($flines);

	fclose($fp);
	fclose($fpdir);
	fclose($fpseq);

	return $myseq;
	}

// parse_swissprot() parses a Swissprot data file and returns a Seq object containing parsed data.
function parse_swissprot($flines)
	{ // OPENS parse_swissprot() function
	$accession = array();
	$date_r = array();
	$desc = "";
	$desc_lnctr = 0;
	$gename_r = array();
	$os_r = array();
	$os_linectr = 0;
	$os_str = "";
	$oc_linectr = 0;
	$oc_str = "";
	$ref_r = array();
	$ra_r = array();
	$ra_ctr = 0;
	$ra_str = "";
	$rl_ctr = 0;
	$rl_str = "";
	$db_r = array();
	$ft_r = array();
	$kw_str = "";
	$kw_r = array();
	
	while ( list($no, $linestr) = each($flines) )
		{ // OPENS 1st (outermost) while ( list($no, $linestr) = each($flines) ) 
	  	$linelabel = left($linestr, 2);
	  	$linedata = trim(substr($linestr, 5));
	  	$lineend = right($linedata, 1);
	  	if (left($linestr, 2) == "ID")
			{ // OPENS if (left($linestr, 2) == "ID") 
			$words = preg_split("/;/", substr($linestr, 5));    
			$endc = preg_split("/\s/", $words[0]);
			$entry_name = $endc[0];
			$namesrc = preg_split("/_/", $entry_name);  
			$protein_name = $namesrc[0];
			$protein_source = $namesrc[1]; 
			$data_class = $endc[1]; 
			$moltype = $words[1];
			$length = (int) substr($words[2], 0, strlen($words[2])-4);         
			} // CLOSES if (left($linestr, 2) == "ID")
		if (left($linestr, 2) == "AC")
			{ // OPENS if (left($linestr, 2) == "AC") 
			$accstr = $linedata;
			$accstr = substr($accstr, 0, strlen($accstr)-1);
			$accline = preg_split("/;/", intrim($accstr));                    
			$accession = array_merge($accession, $accline);
			} // CLOSES if (left($linestr, 2) == "AC") 
		if (left($linestr, 2) == "DT")
			{ // OPENS if (left($linestr, 2) == "DT") 
			// DT DD-MMM-YEAR (REL. XX, COMMENT)
			$datestr = $linedata;
			$datestr = substr($datestr, 0, strlen($datestr)-1); 
			$words = preg_split("/\(/", $datestr);	
			// ( "DD-MMM-YEAR ", "REL. XX, COMMENT")
			$firstcomma = strpos($words[1], ",");
			$comment = trim(substr($words[1], $firstcomma+1));
		  
			// ( "CREATED" => (date, rel), "LAST SEQUENCE UPDATE" => (date, rel),
			//   "LAST ANNOTATION UPDATE" => (date, rel), COMMENT1 => (date, rel),
			//   "COMMENT2" => (date, rel), ... )
		  
			if ($comment == "CREATED")
				{ // OPENS if ($comment == "CREATED") 
				// this DT line is a DATE CREATED line.
				$create_date = substr($words[0], 0, 11); 
				$create_rel = substr($words[1], 5, ($firstcomma-5));
				$date_r[$comment] = array($create_date, $create_rel);  
				} // CLOSES if ($comment == "CREATED")
			elseif ($comment == "LAST SEQUENCE UPDATE")
				{ // OPENS elseif ($comment == "LAST SEQUENCE UPDATE")
				$sequpd_date = substr($words[0], 0, 11);
				$sequpd_rel = substr($words[1], 5, ($firstcomma-5));
				$date_r[$comment] = array($sequpd_date, $sequpd_rel);  
				} // CLOSES elseif ($comment == "LAST SEQUENCE UPDATE")
			elseif ($comment == "LAST ANNOTATION UPDATE")
				{ // OPENS elseif ($comment == "LAST ANNOTATION UPDATE")
				$notupd_date = substr($words[0], 0, 11);
				$notupd_rel = substr($words[1], 5, ($firstcomma-5));
				$date_r[$comment] = array($notupd_date, $notupd_rel);  
				} // CLOSES elseif ($comment == "LAST ANNOTATION UPDATE")
			else
				{ // OPENS else part of if ($comment == "CREATED")  
				// For now, we do not check vs. duplicate comments.
				// We just overwrite the older comment with new one.
				$other_comment = $comment; 
				$other_date = substr($words[0], 0, 11);
				$other_rel = substr($words[1], 5, ($firstcomma-5));
				$date_r[$comment] = array($other_date, $other_rel);
				} // CLOSES else part of if ($comment == "CREATED")
			} // CLOSES if (left($linestr, 2) == "DT")    

			if (left($linestr, 2) == "DE")
				{ // OPENS if (left($linestr, 2) == "DE") 
				$desc_lnctr++;
				$linestr = $linedata;
				if ($desc_lnctr == 1) $desc .= $linestr;
				else $desc .= " " . $linestr;
				
				// Checks if (FRAGMENT) or (FRAGMENTS) is found at the end
				// of the DE line to determine if sequence is complete.				  
				if (right($linestr, 1) == ".")
					{ // OPENS if (right($linestr, 1) == ".")
					if ( 	(strtoupper(right($linestr, 11)) == "(FRAGMENT).") or 
						(strtoupper(right($linestr, 12)) == "(FRAGMENTS).") )
						$is_fragment = TRUE;
					else $is_fragment = FALSE;
					} // CLOSE if (right($linestr, 1) == ".")
				} // CLOSES if (left($linestr, 2) == "DE")
			  
			if ($linelabel == "KW")
				{
				$kw_str .= $linedata;				  
				if ($lineend == ".")
					{					     
					$kw_str = rem_right($kw_str);
					$kw_r = preg_split("/;/", $kw_str);
					array_walk($kw_r, "trim_element");					  
					$kw_str = "";
					}				  
				}	  
			  
			if ($linelabel == "OS")			  
				{ // OPENS if ($linelabel == "OS") 
				$os_linectr++;
				if ($lineend != ".")
					{ // we are not yet at the last OS line.
					if ($os_linectr == 1) $os_str .= $linedata;
					else $os_str .= " $linedata";
					}
				else 
					{ // we are at the last OS line.
					$os_str .= " $linedata";
					$os_str = rem_right($os_str);
					$os_line = preg_split("/\, AND /", $os_str);
					}
				} // CLOSES if ($linelabel == "OS")

			if ($linelabel == "OG")
				$organelle = rem_right($linedata);
				
			if ($linelabel == "OC")
				{
				$oc_linectr++;
				if ($lineend != ".")
					{ // we are not yet at the last OS line.
					if ($oc_linectr == 1) $oc_str .= $linedata;
					else $oc_str .= " $linedata";
					}
				else 
					{ // we are at the last OS line.
					$oc_str .= " $linedata";
					$oc_str = rem_right($oc_str);
					$oc_line = preg_split("/;/", $oc_str);
					array_walk($oc_line, "trim_element");
					}
				}		  
				
			if ($linelabel == "FT")
				{
				$ft_key = trim(substr($linestr, 5, 8));  				
				$ft_from = (int) trim(substr($linestr, 14, 6));
				$ft_to = (int) trim(substr($linestr, 21, 6));
				$ft_desc = rem_right(trim(substr($linestr, 34)));
				$ft_r[] = array($ft_key, $ft_from, $ft_to, $ft_desc);
				}  
			  
		  // ( rn => ( "rp" => "my rp", "rc" => ("tok1" => "value", ...) ) )
		  // ( 10 => ( "RP" => "my rp", "RC" => ("PLASMID" => "PLA_VAL", ... ) ) )
		  // Example: DR AARHUS/GHENT-2DPAGE; 8006; IEF.
		  
			if ($linelabel == "DR")
				{
				// DR DATA_BANK_IDENTIFIER; PRIMARY_IDENTIFIER; SECONDARY_IDENTIFIER
				// We assume that all three data items are mandatory/present in all DR entries.
				// ( refno => ( (dbname1, pid1, sid1), (dbname2, pid2, sid2), ... ), 1 => ( ... ) )
				// ( 0 => ( (REBASE, pid1, sid1), (WORPEP, pid2, sid2), ... ), 1 => ( ... ) )
				$linedata = rem_right($linedata);
				$dr_line = preg_split("/;/", $linedata); 
				array_walk($dr_line, "trim_element");
				$db_name = $dr_line[0];
				$db_pid = $dr_line[1];
				$db_sid = $dr_line[2];
				$db_r[] = array($db_name, $db_pid, $db_sid); 		  
				}			  
		  
			if ($linelabel == "RN")
				{ // OPENS "RN"
				// Remove the [ and ] between the reference number.
				$refno = substr(rem_right($linedata), 1);
				
				$rc_ctr = 0;
				$rc_str = "";
				$rc_flag = FALSE;
				$inner_r = array();
				while ( list($no, $linestr) = each($flines) )
					{ // OPENS 2nd WHILE
					$linelabel = left($linestr, 2);
					$linedata = trim(substr($linestr, 5));
					$lineend = right($linedata, 1);
					if ($linelabel == "RP") $inner_r["RP"] = $linedata;
					elseif ($linelabel == "RC")
						{ // OPENS elseif ($linelabel == "RC")
						$rc_str .= $linedata;
						while ( list($no, $linestr) = each($flines) )
							{ // OPENS 3rd WHILE
							$linelabel = left($linestr, 2);
							$linedata = trim(substr($linestr, 5));
							$lineend = right($linedata, 1);
							if ($linelabel == "RC")
								$rc_str .= " $linedata";                       
							else
								{ // opens else
								prev($flines);
								break;
								} // closes else
							} // CLOSES 3rd WHILE

						// we remove the last character if it is ";"
						$rc_str = trim($rc_str);
						if (right($rc_str,1) == ";") $rc_str = rem_right($rc_str);
						$rc_line = preg_split("/;/", trim($rc_str));
						array_walk($rc_line, "trim_element");
						$innermost = array();
						
						foreach($rc_line as $tokval_str)
							{
							// here we assume that there is no whitespace
							// before or after (left or right of) the "=".
							$tokval_r = preg_split("/=/", $tokval_str);
							$token = $tokval_r[0];
							$value = $tokval_r[1];
							$innermost[$token] = $value;
							}
						$inner_r["RC"] = $innermost; 
						} // CLOSES elseif ($linelabel == "RC")
					elseif ($linelabel == "RM")
						{ // We have no idea what RM is about, so we assume it's a single-line entry.
						// which may occur 0 to 1 times inside a SWISSPROT SEQUENCE RECORD.
						$inner_r["RM"] = $linedata;
						}
					elseif ($linelabel == "RX")
						{
						$linedata = rem_right($linedata);
						$rx_line = preg_split("/;/", intrim($linedata));
						$inner_r["RX_BDN"] = $rx_line[0];
						$inner_r["RX_ID"] = $rx_line[1];
						}
					elseif ($linelabel == "RA")
						{
						$ra_ctr++;
						if ($ra_ctr == 1) $ra_str = $linedata;
						else $ra_str .= " $linedata";
						if ($lineend == ";")
							{
							$ra_str = rem_right($ra_str);
							$ra_r = preg_split("/\,/", $ra_str);
							array_walk($ra_r, "trim_element");
							$inner_r["RA"] = $ra_r;                   
							}
						}
					elseif ($linelabel == "RL")
						{
						$rl_ctr++;
						if ($rl_ctr == 1) $rl_str = $linedata;
						else $rl_str .= " $linedata";
						}
					else
						{
						$inner_r["RL"] = $rl_str;
						prev($flines);
						break;
						}
					} // CLOSES 2nd WHILE

				$ref_r[$refno-1] = $inner_r;
				$ra_str = "";
				$ra_ctr = 0;
				$rl_str = "";
				$rl_ctr = 0;
				} // CLOSES "RN"

			if (left($linestr, 2) == "GN")
				{ // OPENS if (left($linestr, 2) == "GN") 
				// GN is always exactly one line.
				// GNAME1 OR GNAME2               ( (GNAME1, GNAME2) )
				// GNAME1 AND GNAME2              ( (GNAME1), (GNAME2) )
				// GNAME1 AND (GNAME2 OR GNAME3)  ( (GNAME1), (GNAME2, GNAME3) )
				// GNAME1 OR (GNAME2 AND GNAME3)  NOT POSSIBLE!!!
				
				/* ALGORITHM:
				  1) Split expressions by " AND ".
				  2) Test each "token" if in between parentheses or not.
				  3) If not, then token is a singleton, else it's a multiple-ton.
				  4) Singletons are translated into (GNAME1).
					  Multiple-tons are translated into (GNAME1, GNAME 2).
				  5) Push gene name array into larger array. Go to next token.
				*/

				// Remove "GN " at the beginning of our line.
				$linestr = trim(substr($linestr, 5));
				// Remove the last character which is always a period.
				$linestr = substr($linestr, 0, strlen($linestr)-1); 

				// Go here if you detect at least one ( or ). 
				if ( is_false(strpos($linestr, "(")) )
					{ // GN Line does not contain any parentheses.
                 // Ergo, it is made up of all OR's or AND's but not both.
					if (strpos($linestr, " OR ") != FALSE)
						{
						// Case 1: GNAME1 OR GNAME2.
						$temp = preg_split("/ OR /", $linestr);
						$gename_r[] = $temp;
						}
					elseif (strpos($linestr, " AND ") != FALSE)
						{
						// Case 2: GNAME1 AND GNAME2 AND GNAME3.
						$temp = preg_split("/ AND /", $linestr);
						foreach($temp as $gene)
							$gename_r[] = array($gene);
						}
					else $gename_r[] = array($linestr);
					// Case 0: GN GENENAME1. One gene name (no OR, AND).
					}
				else
					{ // OPENS else part of if ( is_false(strpos($linestr, "(")) )  
                 // GN Line contains at least one pair of parentheses.
                 // Case 3: GNAME1 AND (GNAME2 OR GNAME3) => ( (GNAME1), (GNAME2, GNAME3) )
                 // COMMENTS # 1 below.
					$temp = preg_split("/ AND /", $linestr);
					foreach($temp as $gene)
						{ // OPENS foreach($temp as $gene) 
						if (substr($gene, 0, 1) == "(")
							{ // a list of 2 or more gene names OR'ed together
							// remove the "(" and ")" at both ends of the string.
							$gene = substr($gene, 1);
							$gene = substr($gene, 0, strlen($gene)-1);
							$genelist = preg_split("/ OR /", $gene);
							$gename_r[] = $genelist;
							}
						else
							{ // singleton
							$gename_r[] = array($gene);
							}
						} // CLOSES foreach($temp as $gene)
					} // CLOSES else part of if ( is_false(strpos($linestr, "(")) )  
				} // CLOSES if (left($linestr, 2) == "GN") 
			  
				// 0123456789012345678901234567890123456789
				// SQ   SEQUENCE XXXX AA; XXXXX MW; XXXXX CN;
				if ($linelabel == "SQ")
					{ // OPENS if ($linelabel == "SQ")
					$linedata = rem_right($linedata);
					// XXXX AA, XXXX MW, XXXX CN				  				  
					$words = preg_split("/;/", substr($linedata, 8));				  
					$aa = preg_split("/\s+/", trim($words[0]));
					$aa_count = (int) trim($aa[0]);
					$mw = preg_split("/\s+/", trim($words[1]));
					$mol_wt = (int) trim($mw[0]);				  				  
					$cn = preg_split("/\s+/", trim($words[2]));
					$chk_no = trim($cn[0]);
					$chk_method = trim($cn[1]); 
					$sequence = "";
					while ( list($no, $linestr) = each($flines) )
						{ 
						$linelabel = left($linestr, 2);
						if ($linelabel == "//") break;
						$linedata = intrim(trim($linestr));
						$sequence .= $linedata;					   						  
						} 
					} // CLOSES if ($linelabel == "SQ")			  

	} // CLOSES 1st (outermost) while ( list($no, $linestr) = each($flines) )  			  
		
	$seqobj = new seq();			
	$seqobj->id = $protein_name; 
	$seqobj->seqlength = $length;				
	$seqobj->moltype = $moltype;
	$seqobj->date = $create_date;
	$seqobj->accession = $accession[0];
	array_shift($accession);
	$seqobj->sec_accession = $accession; 
	$seqobj->source = $os_line;
	$seqobj->organism = $oc_line;
	$seqobj->sequence = $sequence;  			
	$seqobj->definition = $desc;
	$seqobj->keywords = $kw_r;

	$genbank_ref_r = array();
	$inner_r = array();
	foreach($ref_r as $key => $value)
		{
		$inner_r["REFNO"] = $key;								
		$db_id = $value["RX_BDN"];
		$inner_r[$db_id] = $value["RX_ID"];								
		$inner_r["REMARKS"] = $value["RP"];
		$inner_r["COMMENT"] = $value["RC"];							
		$inner_r["TITLE"] = $value["RL"];
		$inner_r["JOURNAL"] = $value["RL"];  	
		$inner_r["AUTHORS"] = $value["RA"];
		$genbank_ref_r[] = $inner_r;
		}
	$seqobj->reference = $genbank_ref_r;							

	$swiss = array();
	$swiss["ID"] = $protein_name;
	$swiss["PROT_NAME"] = $protein_name;
	$swiss["MOL_TYPE"] = $moltype;
	$swiss["PROT_SOURCE"] = $protein_source;
	$swiss["DATA_CLASS"] = $data_class;
	$swiss["LENGTH"] = $length;			
	$swiss["CREATE_DATE"] = $create_date;  
	$swiss["CREATE_REL"] = $create_rel; 
	$swiss["SEQUPD_DATE"] = $sequpd_date;
	$swiss["SEQUPD_REL"] = $sequpd_rel;
	$swiss["NOTUPD_DATE"] = $notupd_date;
	$swiss["NOTUPD_REL"] = $notupd_rel;
	// ACCESSION is an ARRAY.
	$swiss["ACCESSION"] = $accession;
	$swiss["PRIM_AC"] = $accession[0]; 
	$swiss["DESC"] = $desc;
	$swiss["IS_FRAGMENT"] = $is_fragment;
	// KEYWORDS is an ARRAY.
	$swiss["KEYWORDS"] = $kw_r;
	// ORGANISM is an ARRAY.
	$swiss["ORGANISM"] = $os_line;
	$swiss["ORGANELLE"] = $organelle;
	// FT_<keyword> is an ARRAY.
	process_ft($swiss, $ft_r);		
	
	$swiss["AMINO_COUNT"] = $aa_count;
	$swiss["MOLWT"] = $mol_wt;
	$swiss["CHK_NO"] = $chk_no;
	$swiss["CHK_METHOD"] = $chk_method; 
	$swiss["SEQUENCE"] = $sequence;	
	// GENE_NAME is an ARRAY.
	$swiss["GENE_NAME"] = $gename_r;
	// ORG_CLASS is an ARRAY.			
	$swiss["ORG_CLASS"] = $oc_line;			
	// REFERENCE is an ARRAY.
	$swiss["REFERENCE"] = $ref_r;
	
	$seqobj->swissprot = $swiss;		// ARRAY	  
	return $seqobj;						
	} // CLOSES parse_swissprot()

// parse_id() parses a GenBank data file and returns a Seq object containing parsed data.
function parse_id($flines)
	{
	$seqarr = array();
	$inseq_flag = false;
	$seqdata_flag = false;
	$accession_flag = false;
	$ref_array = array();
	$feature_array = array();
	$entry_ctr = 0;
	$ref_ctr = 0;
	$maxlength = 0;
	$minlength = 999999;
	$tot_seqlength = 0;
		
	while( list($lineno, $linestr) = each($flines) )
		{ // OPENS outermost while( list($lineno, $linestr) = each($flines) )
      if (substr($linestr,0,5) == "LOCUS")
         {
         $entry_ctr++;
         $ref_ctr = 0;
         $ref_array = array();

         // This is the beginning of a SEQUENCE ENTRY.
         $seqdata = "";

         $seqobj = new seq();
         $seqobj->id = trim(substr($linestr, 12, 16));
         $seqobj->seqlength = trim(substr($linestr, 29, 11)) * 1;
         $tot_seqlength += $seqobj->seqlength;

         if ($seqobj->seqlength > $maxlength) $maxlength = $seqobj->seqlength;
         if ($seqobj->seqlength < $minlength) $minlength = $seqobj->seqlength;

         $seqobj->moltype = substr($linestr, 47, 6);
         if (substr($linestr, 44, 3) == "ss-") $seqobj->strands = "SINGLE";
         elseif (substr($linestr, 44, 3) == "ds-") $seqobj->strands = "DOUBLE";
         elseif (substr($linestr, 44, 3) == "ms-") $seqobj->strands = "MIXED";

         $seqobj->topology = strtoupper(substr($linestr, 55, 8));
         $seqobj->division = strtoupper(substr($linestr, 64, 3));
         $seqobj->date = strtoupper(substr($linestr, 68, 11));

         $inseq_flag = true;
         }

		if (trim(substr($linestr,0,10)) == "BASE COUNT")
         {
         if (count($feat_r) > 0)
            $seqobj->features = $feat_r;
         }
			
      if (trim(substr($linestr,0,12)) == "FEATURES")
         { // OPENS if (trim(substr($linestr,0,12)) == "FEATURES") 
         // The REFERENCE section was present for this SEQUENCE ENTRY so we set REFERENCE attribute.
         if (count($ref_array) > 0) $seqobj->reference = $ref_array;
         $lastsubkey = "";

         $feat_r = array();
         $qual_r = array();

         // Go to the next line.
         list($lineno, $linestr) = each($flines);

         // This loops through each line in the entire FEATURES SECTION.
         while( substr($linestr,0,10) != "BASE COUNT" )
            { // FEATURES WHILE LOOP
            $label = trim(substr($linestr,0,21));
            $data = trim(substr($linestr,21));

            if (strlen($label) != 0)
               {
               // At the beginning of a new SUBKEY.
               $subkey = $label;
               // Add/save the qualifier array (qual_r) of the previous SUBKEY to our big feat_r array.
               if (count($qual_r) > 0)
                  {
                  $feat_r[$lastsubkey] = $qual_r;
                  $qual_r = array();
                  }

               $qual = $subkey;
               $qual_r[$qual] = "";
               $qual_ctr = 0;

               do
                  { // QUALIFIER WHILE LOOP
                  $qual_ctr++;
                  $qual_r[$qual] .= " " . $data;

                  list($lineno, $linestr) = each($flines);
                  $label = trim(substr($linestr,0,21));
                  $data = trim(substr($linestr,21));
                  } while( is_blank($label) and !(isa_qualifier($data)) );

               if (!(is_blank($label)))
                  {
                  $lastsubkey = $subkey;
                  $subkey = $label;
                  }
               }
            else
               { // we are inside a subkey section but on the 2nd, 3rd, nth line which have blank LABELS.
               if (isa_qualifier($data))
                  {
                  $wordarray = preg_split("/=/", $data);
                  $qual = $wordarray[0];
                  $data = $wordarray[1];
                  $qual_r[$qual] = "";
                  $qual_ctr = 0;

                  do
                     { // QUALIFIER WHILE LOOP
                     $qual_ctr++;
                     $qual_r[$qual] .= " " . $data;
                     list($lineno, $linestr) = each($flines);
                     $label = trim(substr($linestr,0,21));
                     $data = trim(substr($linestr,21));
                     } while( is_blank($label) and !(isa_qualifier($data)) );

                  if (!(is_blank($label)))
                     {
                     $lastsubkey = $subkey;
                     $subkey = $label;
                     }
                  }
               } // ELSE PART of if (strlen($subkey) != 0)
            } // FEATURES WHILE LOOP

            if (count($qual_r) > 0)
               {
               $feat_r[$lastsubkey] = $qual_r;
               $qual_r = array();
               }

            prev($flines);
         } // CLOSES if (trim(substr($linestr,0,12)) == "FEATURES") 
			
      if (substr($linestr,0,10) == "DEFINITION")
         {
         $wordarray = explode(" ", $linestr);
         array_shift($wordarray);
         $seqobj->definition = implode(" ", $wordarray);
         }
					
      if ($inseq_flag == TRUE)
         { // OPENS if ($inseq_flag == TRUE) 
         if (trim(substr($linestr, 0, 12)) == "REFERENCE")
            {
            // at this point, we are at the line with REFERENCE x (base y of z) in it.
            $wordarray = preg_split("/\s+/", trim(substr($linestr,12)));

            $ref_rec = array();
            $ref_rec["REFNO"] = $wordarray[0];
            array_shift($wordarray);
            $ref_rec["BASERANGE"] = implode(" ", $wordarray);
            $lastsubkey = "";
            $subkey_lnctr = 0;

            while( list($lineno, $linestr) = each($flines) )
               {
               $subkey = trim(substr($linestr,0,12));

               // If current subkey is blank string, then this is a continuation of the last subsection.
               if (strlen($subkey) == 0) $subkey = $lastsubkey;

               // If we are at the next subkey section (e.g. lastsubkey was AUTHORS, and current is TITLE).
               if ($subkey != $lastsubkey) $subkey_lnctr = 0;

               switch ($subkey)
                  {
                  case "AUTHORS":
                     $subkey_lnctr++;
                     $wordarray = preg_split("/\s+/", trim(substr($linestr,12)));
                     // we remove comma at the end of a name, and the element "and".
                     $newarray = array();
                     foreach($wordarray as $authname)
                        {
                        if (strtoupper($authname) != "AND")
                           {
                           if (substr($authname, strlen($authname)-1, 1) == ",")
                              $authname = substr($authname, 0, strlen($authname)-1);
                           $newarray[] = $authname;
                           }
                        }
                     if ($subkey_lnctr == 1) $ref_rec["AUTHORS"] = $newarray;
                     else $ref_rec["AUTHORS"] = array_merge($ref_rec["AUTHORS"], $newarray);
                     break;
                  case "TITLE":
                     $subkey_lnctr++;
                     if ($subkey_lnctr == 1) $ref_rec["TITLE"] = trim(substr($linestr,12));
                     else $ref_rec["TITLE"] .= " " . trim(substr($linestr,12));
                     break;
                  case "JOURNAL":
                     $subkey_lnctr++;
                     if ($subkey_lnctr == 1) $ref_rec["JOURNAL"] = trim(substr($linestr,12));
                     else $ref_rec["JOURNAL"] .= " " . trim(substr($linestr,12));
                     break;
                  case "MEDLINE":
                     $ref_rec["MEDLINE"] = substr($linestr, 12, 8);
                     break;
                  case "PUBMED":
                     $ref_rec["PUBMED"] = substr($linestr, 12, 8);
                     break;
                  case "REMARK":
                     $subkey_lnctr++;
                     if ($subkey_lnctr == 1) $ref_rec["REMARK"] = trim(substr($linestr,12));
                     else $ref_rec["REMARK"] .= " " . trim(substr($linestr,12));
                     break;
                  case "COMMENT":
                     $subkey_lnctr++;
                     if ($subkey_lnctr == 1) $ref_rec["COMMENT"] = trim(substr($linestr,12));
                     else $ref_rec["COMMENT"] .= " " . trim(substr($linestr,12));
                     break;
                  }
               if ($subkey == "FEATURES")
                  {
                  prev($flines);
                  break;
                  }
               if ($subkey == "REFERENCE")
                  {
                  $ref_ctr++;
                  prev($flines);
                  break;
                  }
               $lastsubkey = $subkey;
               }
            array_push($ref_array, $ref_rec);
            }
         if (trim(substr($linestr, 0, 12)) == "SOURCE")
            {
            // For now, assume a single-line SOURCE field.
            $seqobj->source = substr($linestr, 12);
            }
         if (trim(substr($linestr, 0, 12)) == "SEGMENT")
            {
            $seqobj->segment = substr($linestr, 12);
            $wordarray = preg_split("/\s+/", trim(substr($linestr,12)));
            $seqobj->segment_no = $wordarray[0];
            $seqobj->segment_count = $wordarray[2];
            }

         // For now, assume that KEYWORDS field consists of exactly one line.
         if (trim(substr($linestr, 0, 12)) == "KEYWORDS")
            {
            $wordarray = preg_split("/\s+/", trim($linestr));
            array_shift($wordarray);
            $wordarray = preg_split("/;+/", implode(" ", $wordarray));
            if ($wordarray[0] != ".") $seqobj->keywords = $wordarray;
            }
         if (substr($linestr, 0, 7) == "VERSION")
            {
            // Assume that VERSION line is made up of exactly 2 or 3 tokens.
            $wordarray = preg_split("/\s+/", trim($linestr));
            $seqobj->version = $wordarray[1];
            if (count($wordarray) == 3) $seqobj->ncbi_gi_id = $wordarray[2];
            $accession_flag = false;
            }
         if ($accession_flag == TRUE)
            {
            // 2nd, 3rd, etc. line of ACCESSION field.
            $wordarray = preg_split("/\s+/", trim($linestr));
            $this->sec_accession = array_merge($this->sec_accession, $wordarray);
            }
         if (substr($linestr,0,9) == "ACCESSION")
            {
            $wordarray = preg_split("/\s+/", trim($linestr));
            $seqobj->accession = $wordarray[1];
            array_shift($wordarray);
            array_shift($wordarray);
            $seqobj->sec_accession = $wordarray;
            $accession_flag = true;
            }
         if (substr($linestr,0,10) == "  ORGANISM")
            {
            $seqobj->organism = substr($linestr,12);
            }
         if (($seqdata_flag == true) && (substr($linestr,0,2) != "//"))
            {
            $wordarray = explode(" ", trim($linestr));
            array_shift($wordarray);
            $seqline = implode("", $wordarray);
            $seqdata .= $seqline;
            }
         if (substr($linestr,0,6) == "ORIGIN") $seqdata_flag = true;
         if (substr($linestr,0,2) == "//")
            {
            $seqobj->sequence = $seqdata;
            $seqarr[$this->id] = $this;
            $seqdata_flag = false;
            $inseq_flag = false;
            break;
            }
         } // CLOSES if ($inseq_flag == TRUE) 
      } // CLOSES outermost while( list($lineno, $linestr) = each($flines) )
	$seqobj->seqarray = $seqarr;
	return $seqobj;
	} // Closes parse_id() constructor function definition

// open() opens or prepares the SeqDB for processing.  Opposite of close().
function open($dbname)
	{
	if (file_exists($dbname . ".idx") == FALSE) die("ERROR: Index file $dbname.IDX does not exist!");
	if (file_exists($dbname . ".dir") == FALSE) die("ERROR: Index file $dbname.DIR does not exist!");

	$this->dbname = $dbname;
	$this->data_fn = $dbname . ".idx";
	$this->dir_fn = $dbname . ".dir";
	$this->seqptr = 0;
	}

// close() closes the SeqDB database after we're through using it.  Opposite of open() method.  
function close()
	{ //
	// Close simply assigns null values to attributes of the seqdb() object.
	// Methods like fetch would not function properly if these values are null.
	$this->dbname = "";
	$this->data_fn = "";
	$this->dir_fn = "";
	$this->seqptr = -1;
	}

/*
SeqDB() is the constructor method for the SeqDB class.  It does many things like create
and/or read a database's index files, initialize certain SeqDB properties, etc.
Syntax: $seqdb = new seqdb($dbname, $dbformat, $file1, $file2, ...);
Behavior: if $dbname exists and user gave no specific values for $file1, $file2, ...
             then seqdb() object USES/OPENS existing database (index files).
          if $dbname exists and user gave specific values for $file1, $file2, ...
             then seqdb() object OVERWRITES existing database (index files).
          if $dbname does not exist, then seqdb() object CREATES new database.
             even if $file1, $file2, ... are not specified.
We provide the create() method to explicitly create a new database.
We provide the use() or open() method to explicitly use an existing database.
*/
function SeqDB()
	{
	// Get all the arguments passed to this function.
	$args = func_get_args();		
	$dbname = $args[0];
	$dbformat = strtoupper($args[1]);		
	if (strlen($dbformat) == 0) $dbformat = "GENBANK";		
	$this->dbformat = $dbformat;

	$datafile = array();
	for($i = 2; $i < count($args); $i++)
		$datafile[] = $args[$i];

	/* db exists   fileX args   ACTION   TESTED
			Y            Y        create   okay
			Y            N        use
			N            Y        create    okay
			N            N        create    okay
	*/
	// if user provided specific values for $file1, $file2, ... parameters.
	if ((file_exists($dbname)) and (count($datafile) > 0))
		{
		// For now, assume USING/OPENING a database is to be done in READ ONLY MODE.
		$this->open($dbname);
		}
	else
		{
		// March 26, 2003: I switched the 2 lines below with
		// the line: $this->open($dbname); to avoid die-ing with
		// the error message: "ERROR: Index file does not exist!"
		$fp = fopen($dbname . ".idx", "w+");
		$fpdir = fopen($dbname . ".dir", "w+");

		// Creates blank data and directory index files, and sets seqptr to 0, etc.
		$this->open($dbname);

		// if user did not provide any datafile name.
		if (count($datafile) == 0) return;

		$temp_r = array();
		// Build our *.DIR file
		foreach($datafile as $fileno=>$filename)
			{
			$outline = "$fileno $filename\n";
			fputs($fpdir, $outline);

			// Automatically create an index file containing info across all data files.
			$flines = file($filename);
			$totlines = count($flines);

			while( list($lineno, $linestr) = each($flines) )
			  {
			  // if (substr($linestr,0,5) == "LOCUS")
			  if (at_entrystart($linestr, $dbformat))
				  {
				  // $current_id = trim(substr($linestr, 12, 16));
				  $current_id =  get_entryid($flines, $linestr, $dbformat);
				  $outline = "$current_id $fileno $lineno\n";
				  // Put entries in an array first, sort them, then write to *.IDX file.
				  // temp_r = ("AB1234" => ("AB1234", 1, 12), "BC4321" => ... );
				  $temp_r[$current_id] = array($current_id, $fileno, $lineno);
				  }
				}
			// Sort our array by its keys.
			ksort($temp_r);
			}
		// Build our *.IDX array.
		$this->seqcount = count($temp_r);
		foreach($temp_r as $seqid=>$line_r)
			{
			$outline = $line_r[0] . " " . $line_r[1] . " " . $line_r[2] . "\n";
			$fio = fputs($fp, $outline);
			}
		}
	fclose($fp);
	fclose($fpdir);
	} // CLOSES definition of SeqDB constructor function.
	
} // CLOSES definition of SEQDB CLASS.
?>