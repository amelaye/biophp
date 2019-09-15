<?php
/**
 * Swissprot database parsing
 * Freely inspired by BioPHP's project biophp.org
 * Created 15 february 2019
 * Last modified 15 september 2019
 */
namespace AppBundle\Service;

use AppBundle\Entity\Sequence;
use AppBundle\Interfaces\ParseDatabaseInterface;
use AppBundle\Traits\FormatsTrait;
use SeqDatabaseBundle\Entity\CollectionElement;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 * Class ParseSwissprotManager
 * @package AppBundle\Service
 * @author Amélie DUVERNET aka Amelaye
 */
class ParseSwissprotManager implements ParseDatabaseInterface
{
    use FormatsTrait;

    private $accession;
    private $date_r;
    private $desc;
    private $desc_lnctr;
    private $gename_r;
    private $os_linectr;
    private $os_str;
    private $oc_linectr;
    private $oc_str;
    private $ref_r;
    private $ra_r;
    private $ra_ctr;
    private $ra_str;
    private $rl_ctr;
    private $rl_str;
    private $db_r;
    private $ft_r;
    private $kw_str;
    private $kw_r;

    private $aLines;

    /**
     * ParseSwissprotManager constructor.
     */
    public function __construct()
    {
        $this->accession  = [];
        $this->date_r     = [];
        $this->desc       = "";
        $this->desc_lnctr = 0;
        $this->gename_r   = [];
        $this->os_linectr = 0;
        $this->os_str     = "";
        $this->oc_linectr = 0;
        $this->oc_str     = "";
        $this->ref_r      = [];
        $this->ra_r       = [];
        $this->ra_ctr     = 0;
        $this->ra_str     = "";
        $this->rl_ctr     = 0;
        $this->rl_str     = "";
        $this->db_r       = [];
        $this->ft_r       = [];
        $this->kw_str     = "";
        $this->kw_r       = [];
    }

    /**
     * Parses a Swissprot data file and returns a Seq object containing parsed data.
     * Parses the Feature Table lines (those that begin with FT) in a Swissprot
     * data file, extracts the feature key name, from endpoint, to endpoint, and description, and
     * stores them in a (simple) array.
     * @param   array       $aFlines
     * @return  Sequence    $oSequence
     * @group   Legacy
     * @throws  \Exception
     */
    public function parseDataFile($aFlines)
    {
        $oSequence = new Sequence();
        $this->aLines = new \ArrayIterator($aFlines); // <3

        $aSwiss = [];
        $organelle = null;

        /* Parsing the whole data */
        foreach($this->aLines as $lineno => $linestr) {
            $linelabel = $this->left($this->aLines->current(), 2);
            $linedata = trim(substr($this->aLines->current(), 3));

            switch($linelabel) {
                case "ID":
                    $this->buildIDFields($aSwiss,$oSequence); // ok
                    break;
                case "AC":
                    $this->buildACFields(); // ok
                    break;
                case "DT":
                    $this->buildDTFields($aSwiss,$oSequence); // ok
                    break;
                case "DE":
                    $this->buildDEFields($aSwiss); // ok
                    break;
                case "KW":
                    $this->buildKWFields(); // ok
                    break;
                case "OS":
                    $this->buildOSFields($aSwiss,$oSequence); // ok
                    break;
                case "OG":
                    $organelle = $this->rem_right($linedata); // ok
                    break;
                case "OC":
                    $this->buildOCField($aSwiss,$oSequence); // ok
                    break;
                case "FT":
                    $this->buildFTField(); // ok
                    break;
                case "DR":
                    $this->buildDRField(); // ok
                    break;
                case "RN":
                    $this->buildRNField($aFlines); // ok
                    break;
                case "GN":
                    $this->buildGNField(); // ok
                    break;
                case "SQ":
                    $this->buildSQField($aSwiss,$oSequence);
                    break;
            }
        }

        $oSequence->setAccession($this->accession[0]);

        $aSwiss["ACCESSION"]     = $this->accession; // ACCESSION is an ARRAY.
        $aSwiss["PRIM_AC"]       = $this->accession[0];

        array_shift($this->accession);
        $oSequence->setSecAccession($this->accession);
        $oSequence->setDefinition($this->desc);
        $oSequence->setKeywords($this->kw_r);

        $genbank_ref_r = $this->makeRefArray();
        $oSequence->setReference($genbank_ref_r);

        $aSwiss["DESC"]          = $this->desc;
        $aSwiss["KEYWORDS"]      = $this->kw_r; // KEYWORDS is an ARRAY.
        $aSwiss["ORGANELLE"]     = $organelle;
        $this->processFT($aSwiss); // FT_<keyword> is an ARRAY.
        $aSwiss["GENE_NAME"]     = $this->gename_r; // GENE_NAME is an ARRAY.
        $aSwiss["REFERENCE"]     = $this->ref_r; // REFERENCE is an ARRAY.

        $oSequence->setSwissprot($aSwiss);
        return $oSequence;
    }

    /**
     * Parses ID line
     * Format : ID PROTNAME_PROTSOURCE DATA_CLASS; MOL_TYPE; LENGTH AA.
     * @param   array           $aSwiss
     * @param   Sequence        $oSequence
     * @throws  \Exception
     */
    private function buildIDFields(&$aSwiss, &$oSequence)
    {
        try {
            $explode          = explode(" ", substr($this->aLines->current(), 5));
            foreach($explode as $exp) {
                if($exp != '') {
                    $words[] = $exp;
                }
            }
            $entry_name     = $words[0];
            $namesrc        = preg_split("/_/", $entry_name);
            $protein_name   = $namesrc[0];
            $protein_source = $namesrc[1];
            $data_class     = $words[1];
            $moltype        = $words[2];
            $length         = (int)$words[3];

            $aSwiss["ID"]            = $protein_name;
            $aSwiss["PROT_NAME"]     = $protein_name;
            $aSwiss["MOL_TYPE"]      = $moltype;
            $aSwiss["PROT_SOURCE"]   = $protein_source;
            $aSwiss["DATA_CLASS"]    = $data_class;
            $aSwiss["LENGTH"]        = $length;

            $oSequence->setId($protein_name);
            $oSequence->setSeqlength($length);
            $oSequence->setMoltype($moltype);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Parses AC line
     * Format : AC P01375;
     * @throws  \Exception
     */
    private function buildACFields()
    {
        try {
            $linedata = trim(substr($this->aLines->current(), 3));

            $accstr = substr($linedata, 0, strlen($linedata)-1);
            $accline = preg_split("/;/", $this->intrim($accstr));
            $this->accession = array_merge($this->accession, $accline);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Parses DT Line
     * Format : DT 21-JUL-1986 (REL. 01, LAST SEQUENCE UPDATE)
     * @param   array           $aSwiss
     * @param   Sequence        $oSequence
     * @throws  \Exception
     */
    private function buildDTFields(&$aSwiss, &$oSequence)
    {
        try {
            $sequpd_date = $create_date = null;
            $create_rel  = $sequpd_rel  = null;
            $notupd_date = $notupd_rel  = null;

            $linedata = trim(substr($this->aLines->current(), 3));

            $datestr = substr($linedata, 0, strlen($linedata)-1);
            $words = preg_split("/\(/", $datestr);
            $firstcomma = strpos($words[1], ",");
            $comment = trim(substr($words[1], $firstcomma+1));

            switch($comment) {
                case "CREATED":
                    // this DT line is a DATE CREATED line.
                    $create_date = substr($words[0], 0, 11);
                    $create_rel = substr($words[1], 5, ($firstcomma-5));
                    $this->date_r[$comment] = array($create_date, $create_rel);
                    break;
                case "LAST SEQUENCE UPDATE":
                    $sequpd_date = substr($words[0], 0, 11);
                    $sequpd_rel = substr($words[1], 5, ($firstcomma-5));
                    $this->date_r[$comment] = array($sequpd_date, $sequpd_rel);
                    break;
                case "LAST ANNOTATION UPDATE":
                    $notupd_date = substr($words[0], 0, 11);
                    $notupd_rel = substr($words[1], 5, ($firstcomma-5));
                    $this->date_r[$comment] = array($notupd_date, $notupd_rel);
                    break;
                default:
                    // For now, we do not check vs. duplicate comments.
                    // We just overwrite the older comment with new one.
                    $other_date = substr($words[0], 0, 11);
                    $other_rel = substr($words[1], 5, ($firstcomma-5));
                    $this->date_r[$comment] = array($other_date, $other_rel);
                    break;
            }

            $aSwiss["CREATE_DATE"] = $aSwiss["CREATE_DATE"]   ?? $create_date;
            $aSwiss["CREATE_REL"]  = $aSwiss["CREATE_REL"]    ?? $create_rel;

            $aSwiss["SEQUPD_DATE"]  = $aSwiss["SEQUPD_DATE"]   ?? $sequpd_date;
            $aSwiss["SEQUPD_REL"]   = $aSwiss["SEQUPD_REL"]    ?? $sequpd_rel;
            $aSwiss["NOTUPD_DATE"]  = $aSwiss["NOTUPD_DATE"]   ?? $notupd_date;
            $aSwiss["NOTUPD_REL"]   = $aSwiss["NOTUPD_REL"]    ?? $notupd_rel;

            if($create_date != null) {
                $oSequence->setDate($create_date);
            }
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Parses DE line
     * Format : DE TUMOR NECROSIS FACTOR PRECURSOR (TNF-ALPHA) (CACHECTIN).
     * @param   array           $aSwiss
     * @throws  \Exception
     */
    private function buildDEFields(&$aSwiss)
    {
        try {
            $linedata = trim(substr($this->aLines->current(), 3));

            $this->desc_lnctr++;
            $linestr = $linedata;
            if ($this->desc_lnctr == 1) {
                $this->desc .= $linestr;
            } else {
                $this->desc .= " " . $linestr;
            }

            // Checks if (FRAGMENT) or (FRAGMENTS) is found at the end
            // of the DE line to determine if sequence is complete.
            if ($this->right($linestr, 1) == ".") {
                if ((strtoupper($this->right($linestr, 11)) == "(FRAGMENT).")
                    && (strtoupper($this->right($linestr, 12)) == "(FRAGMENTS).")) {
                    $is_fragment = true;
                } else {
                    $is_fragment = false;
                }
                $aSwiss["IS_FRAGMENT"]   = $is_fragment;
            }
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Parses KW Fields
     * Format : KW WORD1; WORD2; WORD3; etc ...
     * @throws \Exception
     */
    private function buildKWFields()
    {
        try {
            $linedata = trim(substr($this->aLines->current(), 3));
            $lineend = $this->right($linedata, 1);

            $this->kw_str .= $linedata;
            if ($lineend == ".") {
                $this->kw_str = $this->rem_right($this->kw_str);
                $this->kw_r = preg_split("/;/", $this->kw_str);
                array_walk($this->kw_r, function(&$value) {
                    $value = trim($value);
                });
                $this->kw_str = "";
            }
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Parses OS line
     * Format : OS HOMO SAPIENS (HUMAN).
     * @param   array           $aSwiss
     * @param   Sequence        $oSequence
     * @throws \Exception
     */
    private function buildOSFields(&$aSwiss, &$oSequence)
    {
        try {
            $linedata = trim(substr($this->aLines->current(), 3));
            $lineend = $this->right($linedata, 1);

            $this->os_linectr++;
            if ($lineend != ".") {
                if ($this->os_linectr == 1) {
                    $this->os_str .= $linedata;
                } else {
                    $this->os_str .= " $linedata";
                }
            } else {
                $this->os_str .= " $linedata";
                $this->os_str = $this->rem_right($this->os_str);
                $os_line = preg_split("/\, AND /", $this->os_str);
            }

            $aSwiss["ORGANISM"]      = $os_line; // ORGANISM is an ARRAY.
            $oSequence->setSource($os_line);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Parses OC lines
     * Format :
     * OC EUKARYOTA; METAZOA; CHORDATA; VERTEBRATA; TETRAPODA; MAMMALIA;
     * OC EUTHERIA; PRIMATES.
     * @param   array           $aSwiss
     * @param   Sequence        $oSequence
     * @throws \Exception
     */
    private function buildOCField(&$aSwiss, &$oSequence)
    {
        try {
            $linedata = trim(substr($this->aLines->current(), 3));
            $lineend = $this->right($linedata, 1);

            $this->oc_linectr++;
            if ($lineend != ".") {
                if ($this->oc_linectr == 1) {
                    $this->oc_str .= $linedata;
                } else {
                    $this->oc_str .= " $linedata";
                }
            } else {
                $this->oc_str .= " $linedata";
                $this->oc_str = $this->rem_right($this->oc_str);
                $oc_line = preg_split("/;/", $this->oc_str);
                array_walk($oc_line, function(&$value) {
                    $value = trim($value);
                });

                // ORG_CLASS is an ARRAY.
                $aSwiss["ORG_CLASS"]     = $oc_line;
                $oSequence->setOrganism($oc_line);
            }
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Parses FT lines
     * Format : FT KEY START END COMMENT.
     * @throws \Exception
     */
    private function buildFTField()
    {
        try {
            $linestr = $this->aLines->current();
            $aFtExplode = explode(" ", $linestr);
            array_shift($aFtExplode);
            $ft_key = $aFtExplode[0];
            array_shift($aFtExplode);
            $ft_from = (int)$aFtExplode[0];
            array_shift($aFtExplode);
            $ft_to = (int)$aFtExplode[0];
            array_shift($aFtExplode);

            $ft_desc = $this->rem_right(trim(implode(" ", $aFtExplode)));
            $this->ft_r[] = array($ft_key, $ft_from, $ft_to, $ft_desc);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Parses DR lines
     * Format : DR DATA_BANK_IDENTIFIER; PRIMARY_IDENTIFIER; SECONDARY_IDENTIFIER
     * We assume that all three data items are mandatory/present in all DR entries.
     * ( refno => ( (dbname1, pid1, sid1), (dbname2, pid2, sid2), ... ), 1 => ( ... ) )
     * ( 0 => ( (REBASE, pid1, sid1), (WORPEP, pid2, sid2), ... ), 1 => ( ... ) )
     * ( rn => ( "rp" => "my rp", "rc" => ("tok1" => "value", ...) ) )
     * ( 10 => ( "RP" => "my rp", "RC" => ("PLASMID" => "PLA_VAL", ... ) ) )
     * Example: DR AARHUS/GHENT-2DPAGE; 8006; IEF.
     * @throws \Exception
     */
    private function buildDRField()
    {
        try {
            $linedata = $this->rem_right(trim(substr($this->aLines->current(), 3)));
            $dr_line = preg_split("/;/", $linedata);
            array_walk($dr_line, function(&$value) {
                $value = trim($value);
            });
            $db_name = $dr_line[0];
            $db_pid = $dr_line[1];
            $db_sid = $dr_line[2];
            $this->db_r[] = [$db_name, $db_pid, $db_sid];
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Parses RN lines - This is a paragraph which contains several lines
     * Example :
     * RN [8]
     * RP X-RAY CRYSTALLOGRAPHY (2.6 ANGSTROMS).
     * RX MEDLINE; 90008932.
     * RA ECK M.J., SPRANG S.R.;
     * RL J. BIOL. CHEM. 264:17595-17605(1989).
     * @param   array           $aFlines
     * @throws  \Exception
     * @todo : search RC examples
     */
    private function buildRNField($aFlines)
    {
        try {
            $linedata = trim(substr($this->aLines->current(), 3));

            // Remove the [ and ] between the reference number.
            $refno = substr($this->rem_right($linedata), 1);

            $rc_str = "";
            $inner_r = [];

            // Jump line
            $this->aLines->next();

            while(1) {
                $linelabel = $this->left($this->aLines->current(), 2);
                $linedata = trim(substr($this->aLines->current(), 3));
                $lineend = $this->right($linedata, 1);

                switch($linelabel) {
                    case "RP":
                        $inner_r["RP"] = $linedata;
                        break;
                    case "RC":
                        $rc_str .= $linedata;
                        /*while (list($no, $linestr) = each($flines)) {
                            $linelabel = $this->left($linestr, 2);
                            $linedata = trim(substr($linestr, 5));
                            $lineend = $this->right($linedata, 1);
                            if ($linelabel == "RC") {
                                $rc_str .= " $linedata";
                            } else {
                                prev($flines);
                                break;
                            }
                        }*/
                        // we remove the last character if it is ";"
                        $rc_str = trim($rc_str);
                        if (right($rc_str,1) == ";") {
                            $rc_str = $this->rem_right($rc_str);
                        }
                        $rc_line = preg_split("/;/", trim($rc_str));
                        array_walk($rc_line, function(&$value) {
                            $value = trim($value);
                        });
                        $innermost = array();
                        foreach($rc_line as $tokval_str) {
                            // here we assume that there is no whitespace
                            // before or after (left or right of) the "=".
                            $tokval_r = preg_split("/=/", $tokval_str);
                            $token = $tokval_r[0];
                            $value = $tokval_r[1];
                            $innermost[$token] = $value;
                        }
                        $inner_r["RC"] = $innermost;
                        break;
                    case "RM":
                        // We have no idea what RM is about, so we assume it's a single-line entry.
                        // which may occur 0 to 1 times inside a SWISSPROT SEQUENCE RECORD.
                        $inner_r["RM"] = $linedata;
                        break;
                    case "RX":
                        $linedata = $this->rem_right($linedata);
                        $rx_line = preg_split("/;/", $this->intrim($linedata));
                        $inner_r["RX_BDN"] = $rx_line[0];
                        $inner_r["RX_ID"] = $rx_line[1];
                        break;
                    case "RA":
                        $this->ra_ctr++;
                        if ($this->ra_ctr == 1) {
                            $this->ra_str = $linedata;
                        } else {
                            $this->ra_str .= " $linedata";
                        }
                        if ($lineend == ";") {
                            $this->ra_str = $this->rem_right($this->ra_str);
                            $this->ra_r = preg_split("/\,/", $this->ra_str);

                            array_walk($this->ra_r, function(&$value) {
                                $value = trim($value);
                            });
                            $inner_r["RA"] = $this->ra_r;
                        }
                        break;
                    case "RL":
                        $this->rl_ctr++;
                        if ($this->rl_ctr == 1) {
                            $this->rl_str = $linedata;
                        } else {
                            $this->rl_str .= " $linedata";
                        }
                        $inner_r["RL"] = $linedata;
                        break;
                }

                $sHead = trim(substr($aFlines[$this->aLines->key()+1],0, 2));
                if($sHead != "RP" && $sHead != "RX" && $sHead != "RA" && $sHead != "RM" && $sHead != "RC" && $sHead != "RL") { // Stop if we change feature
                    break;
                }

                $this->aLines->next();
            }

            $this->ref_r[$refno-1] = $inner_r;
            $this->ra_str = "";
            $this->ra_ctr = 0;
            $this->rl_str = "";
            $this->rl_ctr = 0;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Parses GN line - GN is always exactly one line.
     * GNAME1 OR GNAME2               ( (GNAME1, GNAME2) )
     * GNAME1 AND GNAME2              ( (GNAME1), (GNAME2) )
     * GNAME1 AND (GNAME2 OR GNAME3)  ( (GNAME1), (GNAME2, GNAME3) )
     * GNAME1 OR (GNAME2 AND GNAME3)  NOT POSSIBLE!!!
     * ALGORITHM:
     * 1) Split expressions by " AND ".
     * 2) Test each "token" if in between parentheses or not.
     * 3) If not, then token is a singleton, else it's a multiple-ton.
     * 4) Singletons are translated into (GNAME1).
     * Multiple-tons are translated into (GNAME1, GNAME 2).
     * 5) Push gene name array into larger array. Go to next token.
     * @throws \Exception
     */
    private function buildGNField()
    {
        try {
            // Remove "GN " at the beginning of our line.
            $linestr = trim(substr($this->aLines->current(), 3));
            // Remove the last character which is always a period.
            $linestr = substr($linestr, 0, strlen($linestr)-1);

            // Go here if you detect at least one ( or ).
            if (!(strpos($linestr, "("))) { // GN Line does not contain any parentheses.
                // Ergo, it is made up of all OR's or AND's but not both.
                if (strpos($linestr, " OR ")) {
                    // Case 1: GNAME1 OR GNAME2.
                    $temp = preg_split("/ OR /", $linestr);
                    $this->gename_r[] = $temp;
                } elseif (strpos($linestr, " AND ")) {
                    // Case 2: GNAME1 AND GNAME2 AND GNAME3.
                    $temp = preg_split("/ AND /", $linestr);
                    foreach($temp as $gene) {
                        $this->gename_r[] = array($gene);
                    }
                } else {
                    $this->gename_r[] = array($linestr);
                }
                // Case 0: GN GENENAME1. One gene name (no OR, AND).
            } else {
                // GN Line contains at least one pair of parentheses.
                // Case 3: GNAME1 AND (GNAME2 OR GNAME3) => ( (GNAME1), (GNAME2, GNAME3) )
                // COMMENTS # 1 below.
                $temp = preg_split("/ AND /", $linestr);
                foreach($temp as $gene) {
                    if (substr($gene, 0, 1) == "(") { // a list of 2 or more gene names OR'ed together
                        // remove the "(" and ")" at both ends of the string.
                        $gene = substr($gene, 1);
                        $gene = substr($gene, 0, strlen($gene)-1);
                        $genelist = preg_split("/ OR /", $gene);
                        $this->gename_r[] = $genelist;
                    } else { // singleton
                        $this->gename_r[] = array($gene);
                    }
                }
            }
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Parses SQ lines and below
     * SQ   SEQUENCE XXXX AA; XXXXX MW; XXXXX CN;
     * @param   array           $aSwiss
     * @param   Sequence        $oSequence
     * @throws  \Exception
     */
    private function buildSQField(&$aSwiss, &$oSequence)
    {
        try {

            $linedata = trim(substr($this->aLines->current(), 3));
            $linedata = $this->rem_right($linedata);
            dump($linedata);

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

            $this->aLines->next();
            while(1) {
                $linelabel = $this->left($this->aLines->current(), 2);
                if ($linelabel == "//") { // end of file
                    break;
                } else {
                    $linedata = $this->intrim(trim($this->aLines->current()));
                    $sequence .= $linedata;
                    $this->aLines->next();
                }
            }
            $aSwiss["AMINO_COUNT"]   = $aa_count;
            $aSwiss["MOLWT"]         = $mol_wt;
            $aSwiss["CHK_NO"]        = $chk_no;
            $aSwiss["CHK_METHOD"]    = $chk_method;
            $aSwiss["SEQUENCE"]      = $sequence;

            $oSequence->setSequence($sequence);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Then pushes this array into a larger associative array, called $swiss, which is
     * also an attribute of the Seq object. It is assigned a key of the form: FT_<feature_key_name>.
     * Examples are: FT_PEPTIDE, FT_DISULFID.
     * @param   array       $swiss
     * @throws  \Exception
     */
    private function processFT(&$swiss)
    {
        try {
            foreach($this->ft_r as $element) {
                $index = "FT_" . $element[0];
                array_shift($element);
                if (!isset($swiss[$index]) || (isset($swiss[$index]) && count($swiss[$index]) == 0)) {
                    $swiss[$index] = array();
                    array_push($swiss[$index], $element);
                } else {
                    array_push($swiss[$index], $element);
                }
            }
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Creates references array
     * @return      array
     * @throws      \Exception
     */
    private function makeRefArray()
    {
        try {
            $genbank_ref_r = [];
            $inner_r = [];

            foreach($this->ref_r as $key => $value) {
                $inner_r["REFNO"]   = $key;
                $db_id              = $value["RX_BDN"] ?? null;
                $inner_r[$db_id]    = $value["RX_ID"] ?? null;
                $inner_r["REMARKS"] = $value["RP"] ?? null;
                $inner_r["COMMENT"] = $value["RC"] ?? null;
                $inner_r["TITLE"]   = $value["RL"] ?? null;
                $inner_r["JOURNAL"] = $value["RL"] ?? null;
                $inner_r["AUTHORS"] = $value["RA"] ?? null;
                $genbank_ref_r[]    = $inner_r;
            }
            return $genbank_ref_r;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
}