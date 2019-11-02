<?php
/**
 * Swissprot database parsing
 * Freely inspired by BioPHP's project biophp.org
 * Created 15 february 2019
 * Last modified 25 october 2019
 */
namespace AppBundle\Service\IO;

use AppBundle\Entity\Sequence;
use AppBundle\Interfaces\ParseDatabaseInterface;
use AppBundle\Traits\FormatsTrait;

/**
 * Class ParseSwissprotManager
 * @package AppBundle\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
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
            $aWords = [];
            $aLineData  = explode(" ", substr($this->aLines->current(), 5));
            foreach($aLineData as $sData) {
                if($sData != '') {
                    $aWords[] = $sData;
                }
            }
            $sEntryName     = $aWords[0];
            $aNameSrc       = preg_split("/_/", $sEntryName);
            $sProteinName   = $aNameSrc[0];
            $sProteinSource = $aNameSrc[1];
            $sDataClass     = $aWords[1];
            $sMoltype       = $aWords[2];
            $iLength        = (int)$aWords[3];

            $aSwiss["ID"]            = $sProteinName;
            $aSwiss["PROT_NAME"]     = $sProteinName;
            $aSwiss["MOL_TYPE"]      = $sMoltype;
            $aSwiss["PROT_SOURCE"]   = $sProteinSource;
            $aSwiss["DATA_CLASS"]    = $sDataClass;
            $aSwiss["LENGTH"]        = $iLength;

            $oSequence->setId($sProteinName);
            $oSequence->setSeqlength($iLength);
            $oSequence->setMoltype($sMoltype);
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
            $sLineData = trim(substr($this->aLines->current(), 3));
            $sAccession = substr($sLineData, 0, strlen($sLineData)-1);
            $sAccessionLine = preg_split("/;/", $this->intrim($sAccession));
            $this->accession = array_merge($this->accession, $sAccessionLine);
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
            $sSequpdDate = $sCreateDate = null;
            $sCreateRel  = $sSequpdRel  = null;
            $sNotupdDate = $sNotupdRel  = null;

            $sLineData = trim(substr($this->aLines->current(), 3));

            $sDateStr = substr($sLineData, 0, strlen($sLineData)-1);
            $aWords = preg_split("/\(/", $sDateStr);
            $iFirstComma = strpos($aWords[1], ",");
            $sComment = trim(substr($aWords[1], $iFirstComma+1));

            switch($sComment) {
                case "CREATED":
                    // this DT line is a DATE CREATED line.
                    $sCreateDate = substr($aWords[0], 0, 11);
                    $sCreateRel = substr($aWords[1], 5, ($iFirstComma-5));
                    $this->date_r[$sComment] = array($sCreateDate, $sCreateRel);
                    break;
                case "LAST SEQUENCE UPDATE":
                    $sSequpdDate = substr($aWords[0], 0, 11);
                    $sSequpdRel = substr($aWords[1], 5, ($iFirstComma-5));
                    $this->date_r[$sComment] = array($sSequpdDate, $sSequpdRel);
                    break;
                case "LAST ANNOTATION UPDATE":
                    $sNotupdDate = substr($aWords[0], 0, 11);
                    $sNotupdRel = substr($aWords[1], 5, ($iFirstComma-5));
                    $this->date_r[$sComment] = array($sNotupdDate, $sNotupdRel);
                    break;
                default:
                    // For now, we do not check vs. duplicate comments.
                    // We just overwrite the older comment with new one.
                    $sOtherDate = substr($aWords[0], 0, 11);
                    $sOtherRel = substr($aWords[1], 5, ($iFirstComma-5));
                    $this->date_r[$sComment] = array($sOtherDate, $sOtherRel);
                    break;
            }

            $aSwiss["CREATE_DATE"] = $aSwiss["CREATE_DATE"]   ?? $sCreateDate;
            $aSwiss["CREATE_REL"]  = $aSwiss["CREATE_REL"]    ?? $sCreateRel;

            $aSwiss["SEQUPD_DATE"]  = $aSwiss["SEQUPD_DATE"]   ?? $sSequpdDate;
            $aSwiss["SEQUPD_REL"]   = $aSwiss["SEQUPD_REL"]    ?? $sSequpdRel;
            $aSwiss["NOTUPD_DATE"]  = $aSwiss["NOTUPD_DATE"]   ?? $sNotupdDate;
            $aSwiss["NOTUPD_REL"]   = $aSwiss["NOTUPD_REL"]    ?? $sNotupdRel;

            if($sCreateDate != null) {
                $oSequence->setDate($sCreateDate);
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
            $sLine = trim(substr($this->aLines->current(), 3));

            $this->desc_lnctr++;
            if ($this->desc_lnctr == 1) {
                $this->desc .= $sLine;
            } else {
                $this->desc .= " " . $sLine;
            }

            // Checks if (FRAGMENT) or (FRAGMENTS) is found at the end
            // of the DE line to determine if sequence is complete.
            if ($this->right($sLine, 1) == ".") {
                if ((strtoupper($this->right($sLine, 11)) == "(FRAGMENT).")
                    && (strtoupper($this->right($sLine, 12)) == "(FRAGMENTS).")) {
                    $bIsFragment = true;
                } else {
                    $bIsFragment = false;
                }
                $aSwiss["IS_FRAGMENT"]   = $bIsFragment;
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
            $sLineData = trim(substr($this->aLines->current(), 3));
            $sLineEnd = $this->right($sLineData, 1);

            $this->kw_str .= $sLineData;
            if ($sLineEnd == ".") {
                $this->kw_str = $this->rem_right($this->kw_str);
                $this->kw_r = preg_split("/;/", $this->kw_str);
                array_walk($this->kw_r, function(&$sValue) {
                    $sValue = trim($sValue);
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
            $sLineData = trim(substr($this->aLines->current(), 3));
            $sLineEnd = $this->right($sLineData, 1);

            $this->os_linectr++;
            if ($sLineEnd != ".") {
                if ($this->os_linectr == 1) {
                    $this->os_str .= $sLineData;
                } else {
                    $this->os_str .= " $sLineData";
                }
            } else {
                $this->os_str .= " $sLineData";
                $this->os_str = $this->rem_right($this->os_str);
                $aOSLine = preg_split("/\, AND /", $this->os_str);
                $aSwiss["ORGANISM"] = $aOSLine; // ORGANISM is an ARRAY.
                $oSequence->setSource($aOSLine);
            }
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
            $sLineData = trim(substr($this->aLines->current(), 3));
            $sLineEnd = $this->right($sLineData, 1);

            $this->oc_linectr++;
            if ($sLineEnd != ".") {
                if ($this->oc_linectr == 1) {
                    $this->oc_str .= $sLineData;
                } else {
                    $this->oc_str .= " $sLineData";
                }
            } else {
                $this->oc_str .= " $sLineData";
                $this->oc_str = $this->rem_right($this->oc_str);
                $aOCLine = preg_split("/;/", $this->oc_str);
                array_walk($aOCLine, function(&$sValue) {
                    $sValue = trim($sValue);
                });

                // ORG_CLASS is an ARRAY.
                $aSwiss["ORG_CLASS"] = $aOCLine;
                $oSequence->setOrganism($aOCLine);
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
            $sLineStr = $this->aLines->current();
            $aFtExplode = explode(" ", $sLineStr);
            array_shift($aFtExplode);
            $sFTKey = $aFtExplode[0];
            array_shift($aFtExplode);
            $iFTFrom = (int) $aFtExplode[0];
            array_shift($aFtExplode);
            $iFTTo = (int) $aFtExplode[0];
            array_shift($aFtExplode);

            $sFTDesc = $this->rem_right(trim(implode(" ", $aFtExplode)));
            $this->ft_r[] = array($sFTKey, $iFTFrom, $iFTTo, $sFTDesc);
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
            $sLineData = $this->rem_right(trim(substr($this->aLines->current(), 3)));
            $aDrLine = preg_split("/;/", $sLineData);
            array_walk($aDrLine, function(&$sValue) {
                $sValue = trim($sValue);
            });
            $sDbName = $aDrLine[0];
            $sDbPid = $aDrLine[1];
            $sDbSid = $aDrLine[2];
            $this->db_r[] = [$sDbName, $sDbPid, $sDbSid];
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
            $sMainLineData = trim(substr($this->aLines->current(), 3));

            // Remove the [ and ] between the reference number.
            $iRefNo = substr($this->rem_right($sMainLineData), 1);
            $sRCLine = "";
            $aInner = [];

            $this->aLines->next(); // Jump line

            while(1) {
                $sLineLabel = $this->left($this->aLines->current(), 2);
                $sLineData = trim(substr($this->aLines->current(), 3));
                $sLineEnd = $this->right($sLineData, 1);

                switch($sLineLabel) {
                    case "RP":
                        $aInner["RP"] = $sLineData;
                        break;
                    case "RC":
                        $sRCLine .= $sLineData;
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
                        $sRCLine = trim($sRCLine);
                        if (right($sRCLine,1) == ";") {
                            $sRCLine = $this->rem_right($sRCLine);
                        }
                        $aRCLine = preg_split("/;/", trim($sRCLine));
                        array_walk($aRCLine, function(&$sValue) {
                            $sValue = trim($sValue);
                        });
                        $aInnermost = array();
                        foreach($aRCLine as $sTokval) {
                            // here we assume that there is no whitespace
                            // before or after (left or right of) the "=".
                            $aTokval = preg_split("/=/", $sTokval);
                            $sToken = $aTokval[0];
                            $sValue = $aTokval[1];
                            $aInnermost[$sToken] = $sValue;
                        }
                        $aInner["RC"] = $aInnermost;
                        break;
                    case "RM":
                        // We have no idea what RM is about, so we assume it's a single-line entry.
                        // which may occur 0 to 1 times inside a SWISSPROT SEQUENCE RECORD.
                        $aInner["RM"] = $sLineData;
                        break;
                    case "RX":
                        $sLineData = $this->rem_right($sLineData);
                        $aRXLine = preg_split("/;/", $this->intrim($sLineData));
                        $aInner["RX_BDN"] = $aRXLine[0];
                        $aInner["RX_ID"] = $aRXLine[1];
                        break;
                    case "RA":
                        $this->ra_ctr++;
                        $this->ra_str = ($this->ra_ctr == 1) ? $sLineData : " $sLineData";
                        if ($sLineEnd == ";") {
                            $this->ra_str = $this->rem_right($this->ra_str);
                            $this->ra_r = preg_split("/\,/", $this->ra_str);
                            array_walk($this->ra_r, function(&$sValue) {
                                $sValue = trim($sValue);
                            });
                            $aInner["RA"] = $this->ra_r;
                        }
                        break;
                    case "RL":
                        $this->rl_ctr++;
                        $this->rl_ctr = ($this->rl_ctr == 1) ? $sLineData : " $sLineData";
                        $aInner["RL"] = $sLineData;
                        break;
                }

                $sHead = trim(substr($aFlines[$this->aLines->key()+1],0, 2));
                $aElements = ["RP", "RX", "RA", "RM", "RC", "RL"];
                if(!in_array($sHead, $aElements)) { // Stop if we change feature
                    break;
                }

                $this->aLines->next();
            }

            $this->ref_r[$iRefNo - 1] = $aInner;
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
            $sLine = trim(substr($this->aLines->current(), 3));
            // Remove the last character which is always a period.
            $sLine = substr($sLine, 0, strlen($sLine)-1);

            // Go here if you detect at least one ( or ).
            if (!(strpos($sLine, "("))) { // GN Line does not contain any parentheses.
                // Ergo, it is made up of all OR's or AND's but not both.
                if (strpos($sLine, " OR ")) {
                    // Case 1: GNAME1 OR GNAME2.
                    $aTemp = preg_split("/ OR /", $sLine);
                    $this->gename_r[] = $aTemp;
                } elseif (strpos($sLine, " AND ")) {
                    // Case 2: GNAME1 AND GNAME2 AND GNAME3.
                    $aTemp = preg_split("/ AND /", $sLine);
                    foreach($aTemp as $sGene) {
                        $this->gename_r[] = array($sGene);
                    }
                } else {
                    $this->gename_r[] = array($sLine);
                }
                // Case 0: GN GENENAME1. One gene name (no OR, AND).
            } else {
                // GN Line contains at least one pair of parentheses.
                // Case 3: GNAME1 AND (GNAME2 OR GNAME3) => ( (GNAME1), (GNAME2, GNAME3) )
                // COMMENTS # 1 below.
                $aTemp = preg_split("/ AND /", $sLine);
                foreach($aTemp as $sGene) {
                    if (substr($sGene, 0, 1) == "(") { // a list of 2 or more gene names OR'ed together
                        // remove the "(" and ")" at both ends of the string.
                        $sGene              = substr($sGene, 1);
                        $sGene              = substr($sGene, 0, strlen($sGene)-1);
                        $aGeneList          = preg_split("/ OR /", $sGene);
                        $this->gename_r[]   = $aGeneList;
                    } else { // singleton
                        $this->gename_r[]   = array($sGene);
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

            $sLineData = trim(substr($this->aLines->current(), 3));
            $sLineData = $this->rem_right($sLineData);

            // XXXX AA, XXXX MW, XXXX CN
            $aWords     = preg_split("/;/", substr($sLineData, 8));
            $aAAData    = preg_split("/\s+/", trim($aWords[0]));
            $iAACount   = (int) trim($aAAData[0]);
            $aMWData    = preg_split("/\s+/", trim($aWords[1]));
            $iMolWeight = (int) trim($aMWData[0]);
            $aCNData    = preg_split("/\s+/", trim($aWords[2]));
            $sChkNo     = trim($aCNData[0]);
            $sChkMethod = trim($aCNData[1]);
            $sSequence  = "";

            $this->aLines->next();
            while(1) {
                $sLineLabel = $this->left($this->aLines->current(), 2);
                if ($sLineLabel == "//") { // end of file
                    break;
                } else {
                    $sLineData = $this->intrim(trim($this->aLines->current()));
                    $sSequence .= $sLineData;
                    $this->aLines->next();
                }
            }
            $aSwiss["AMINO_COUNT"]   = $iAACount;
            $aSwiss["MOLWT"]         = $iMolWeight;
            $aSwiss["CHK_NO"]        = $sChkNo;
            $aSwiss["CHK_METHOD"]    = $sChkMethod;
            $aSwiss["SEQUENCE"]      = $sSequence;

            $oSequence->setSequence($sSequence);
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