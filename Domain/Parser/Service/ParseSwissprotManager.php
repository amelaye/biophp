<?php
/**
 * Swissprot database parsing
 * Freely inspired by BioPHP's project biophp.org
 * Created 15 february 2019
 * Last modified 18 September 2026
 */
namespace Amelaye\BioPHP\Domain\Parser\Service;

use Amelaye\BioPHP\Domain\Database\Service\ParseDbAbstractManager;
use Amelaye\BioPHP\Domain\Sequence\Entity\Accession;
use Amelaye\BioPHP\Domain\Sequence\Entity\Author;
use Amelaye\BioPHP\Domain\Sequence\Entity\Feature;
use Amelaye\BioPHP\Domain\Sequence\Entity\Keyword;
use Amelaye\BioPHP\Domain\Sequence\Entity\Reference;
use Amelaye\BioPHP\Domain\Sequence\Entity\SpDatabank;

/**
 * Class ParseSwissprotManager
 * @package Amelaye\BioPHP\Domain\Parser\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class ParseSwissprotManager extends ParseDbAbstractManager
{
    /**
     * @var array
     */
    private $aLines;

    /**
     * Date of the last sequence update, read from the DT lines. The Sequence entity only
     * carries the creation date, so the two other DT dates stay on the parser.
     * @var string
     */
    private $sequpdDate = "";

    /**
     * Date of the last annotation update, read from the DT lines.
     * @var string
     */
    private $notupdDate = "";

    /**
     * Gene names, as groups of synonyms: ( (GNAME1, GNAME2), (GNAME3) ).
     * @var array
     */
    private $geneNames = [];

    /**
     * The name this format is known by in the collection records and in DatabaseParserFactory.
     * @return string
     */
    public static function getFormat() : string
    {
        return "SWISSPROT";
    }

    /**
     * Tells whether a line opens a new Swiss-Prot entry.
     * @param   string      $sLine          The line to analyze
     * @return  bool
     */
    public static function isEntryStart(string $sLine) : bool
    {
        return substr($sLine, 0, 2) == "ID";
    }

    /**
     * Tells whether a line closes a Swiss-Prot entry.
     * @param   string      $sLine          The line to analyze
     * @return  bool
     */
    public static function isEntryEnd(string $sLine) : bool
    {
        return substr($sLine, 0, 2) == "//";
    }

    /**
     * Extracts the identifier uniquely naming a Swiss-Prot entry.
     * @param   array       $aFlines        The whole file, buffered
     * @param   string      $sLine          The line opening the entry
     * @return  string
     */
    public static function getEntryId(array $aFlines, string $sLine) : string
    {
        foreach($aFlines as $sCurrent) {
            if (substr($sCurrent, 0, 2) == "AC") {
                $sCurrent = str_replace(' ', '', substr($sCurrent, 5));
                $aWords = preg_split("/;/", $sCurrent);

                return $aWords[0];
            }
        }

        return "";
    }

    /**
     * Parses a Swissprot data file and returns a Seq object containing parsed data.
     * Parses the Feature Table lines (those that begin with FT) in a Swissprot
     * data file, extracts the feature key name, from endpoint, to endpoint, and description, and
     * stores them in a (simple) array.
     * @param   array       $aFlines
     * @throws  \Exception
     */
    public function parseDataFile($aFlines)
    {
        $this->aLines = new \ArrayIterator($aFlines); // <3
        $aReferences = [];
        $aAccessions = [];

        $organelle = null;
        $sKeywords = "";

        $sDescription = "";
        $iDescCpt = 0;

        $sSource = "";
        $iSourceCpt = 0;

        $sOrganism = "";
        $iOrgaCpt = 0;

        $aAuthors = [];

        /* Parsing the whole data */
        foreach($this->aLines as $lineno => $linestr) {
            $linelabel = $this->left($this->aLines->current(), 2);

            switch($linelabel) {
                case "ID":
                    $this->buildIDFields(); // ok
                    break;
                case "AC":
                    $this->buildACFields($aAccessions);
                    $this->sequence->setPrimAcc($aAccessions[0]);
                    break;
                case "DT":
                    $this->buildDTFields();
                    break;
                case "DE":
                    $this->buildDEFields($sDescription, $iDescCpt);
                    break;
                case "KW":
                    $this->buildKWFields($sKeywords);
                    break;
                case "OS":
                    $this->buildOSFields($sSource, $iSourceCpt);
                    break;
                case "OC":
                    $this->buildOCField($sOrganism, $iOrgaCpt);
                    break;
                case "FT":
                    $this->buildFTField();
                    break;
                case "DR":
                    $this->buildDRField();
                    break;
                case "RN":
                    $this->buildRNField($aFlines, $aReferences, $aAuthors);
                    break;
                case "GN":
                    $this->buildGNField();
                    break;
                case "SQ":
                    $this->buildSQField();
                    break;
            }
        }

        array_shift($aAccessions);

        foreach($aAccessions as $sAccession) {
            $oAccession = new Accession();
            $oAccession->setPrimAcc($this->sequence->getPrimAcc());
            $oAccession->setAccession($sAccession);
            $this->accession[] = $oAccession;
        }

        $this->makeRefArray($aReferences);

        $this->sequence->setDescription($sDescription);
    }

    /**
     * Parses ID line
     * Format : ID PROTNAME_PROTSOURCE DATA_CLASS; MOL_TYPE; LENGTH AA.
     * @throws  \Exception
     */
    private function buildIDFields()
    {
        $aWords = [];
        // Offset 3 (not 5) then drop the empty tokens: reads both the 3-space flat-file
        // form ("ID   TNFA_HUMAN") and the 1-space form, where offset 5 ate "TN".
        $aLineData  = explode(" ", substr($this->aLines->current(), 3));
        foreach($aLineData as $sData) {
            $sData = trim($sData);
            if($sData != '') {
                $aWords[] = $sData;
            }
        }
        $sEntryName     = $aWords[0];
        $aNameSrc       = preg_split("/_/", $sEntryName);
        $sProteinName   = $aNameSrc[0];
        $sProteinSource = $aNameSrc[1];
        $sMoltype       = $aWords[2];
        $iLength        = (int)$aWords[3];


        $this->sequence->setEntryName($sProteinName);
        $this->sequence->setMolType($sMoltype);
        $this->sequence->setSource($sProteinSource);
        $this->sequence->setSeqLength($iLength);
    }

    /**
     * Parses AC line
     * Format : AC P01375;
     * @param   array           $aAccess
     * @return  array
     * @throws  \Exception
     */
    private function buildACFields(&$aAccess)
    {
        $sLineData = trim(substr($this->aLines->current(), 3));
        $sAccession = substr($sLineData, 0, strlen($sLineData)-1);
        $sAccessionLine = preg_split("/;/", $this->intrim($sAccession));
        $aAccess = array_merge($aAccess, $sAccessionLine);
        return($aAccess);
    }

    /**
     * Parses DT Line
     * Format : DT 21-JUL-1986 (REL. 01, LAST SEQUENCE UPDATE)
     * @throws  \Exception
     */
    private function buildDTFields()
    {
        $sLineData = trim(substr($this->aLines->current(), 3));

        $sDateStr = substr($sLineData, 0, strlen($sLineData)-1);
        $aWords = preg_split("/\(/", $sDateStr);
        $iFirstComma = strpos($aWords[1], ",");
        $sComment = trim(substr($aWords[1], $iFirstComma+1));
        $sDate = substr($aWords[0], 0, 11);

        switch($sComment) {
            case "CREATED":
                $this->sequence->setDate($sDate);
                break;
            case "LAST SEQUENCE UPDATE":
                $this->sequpdDate = $sDate;
                break;
            case "LAST ANNOTATION UPDATE":
                $this->notupdDate = $sDate;
                break;
        }
    }

    /**
     * Parses DE line
     * Format : DE TUMOR NECROSIS FACTOR PRECURSOR (TNF-ALPHA) (CACHECTIN).
     * @param   string      $sDescription
     * @param   int         $iDescCpt
     * @throws  \Exception
     */
    private function buildDEFields(&$sDescription, &$iDescCpt)
    {
        $sLine = trim(substr($this->aLines->current(), 3));

        $iDescCpt++;
        if ($iDescCpt == 1) {
            $sDescription .= $sLine;
        } else {
            $sDescription .= " " . $sLine;
        }

        // Checks if (FRAGMENT) or (FRAGMENTS) is found at the end
        // of the DE line to determine if sequence is complete.
        if ($this->right($sLine, 1) == ".") {
            if ((strtoupper($this->right($sLine, 11)) == "(FRAGMENT).")
                && (strtoupper($this->right($sLine, 12)) == "(FRAGMENTS).")) {
                $bIsFragment = 1;
            } else {
                $bIsFragment = 0;
            }
            $this->sequence->setFragment($bIsFragment);
        }
    }

    /**
     * Parses KW Fields
     * Format : KW WORD1; WORD2; WORD3; etc ...
     * @param   string      $sKeywords
     * @throws  \Exception
     */
    private function buildKWFields(&$sKeywords)
    {
        $sLineData = trim(substr($this->aLines->current(), 3));
        $sLineEnd = $this->right($sLineData, 1);
        $sKeywords .= $sLineData;

        if ($sLineEnd == ".") {
            $sKeywords = $this->rem_right($sKeywords);
            $aKeywords = preg_split("/;/", $sKeywords);
            array_walk($aKeywords, function(&$sValue) {
                $sValue = trim($sValue);
                $oKeyword = new Keyword();
                $oKeyword->setPrimAcc($this->sequence->getPrimAcc());
                $oKeyword->setKeywords($sValue);
                $this->keywords[] = $oKeyword;
            });
        }
    }

    /**
     * Parses OS line
     * Format : OS HOMO SAPIENS (HUMAN).
     * @param  string       $sSource
     * @param  int          $iSourceCpt
     * @throws \Exception
     */
    private function buildOSFields(&$sSource, &$iSourceCpt)
    {
        $sLineData = trim(substr($this->aLines->current(), 3));
        $sLineEnd = $this->right($sLineData, 1);

        $iSourceCpt++;
        if ($sLineEnd != ".") {
            if ($iSourceCpt == 1) {
                $sSource .= $sLineData;
            } else {
                $sSource .= " $sLineData";
            }
        } else {
            $sSource .= " $sLineData";
            $sSource = $this->rem_right($sSource);
            $aOSLine = preg_split("/\, AND /", $sSource);
            $this->sequence->setSource(trim($aOSLine[0]));
        }
    }

    /**
     * Parses OC lines
     * Format :
     * OC EUKARYOTA; METAZOA; CHORDATA; VERTEBRATA; TETRAPODA; MAMMALIA;
     * OC EUTHERIA; PRIMATES.
     * @param  string       $sOrganism
     * @param  int          $iOrgaCpt
     * @throws \Exception
     */
    private function buildOCField(&$sOrganism, &$iOrgaCpt)
    {
        $sLineData = trim(substr($this->aLines->current(), 3));
        $sLineEnd = $this->right($sLineData, 1);

        $iOrgaCpt++;
        if ($sLineEnd != ".") {
            if ($iOrgaCpt == 1) {
                $sOrganism .= $sLineData;
            } else {
                $sOrganism .= " $sLineData";
            }
        } else {
            $sOrganism .= " $sLineData";
            $sOrganism = $this->rem_right($sOrganism);
            $aOCLine = preg_split("/;/", $sOrganism);
            array_walk($aOCLine, function(&$sValue) {
                $sValue = trim($sValue);
            });

            $this->sequence->setOrganism($aOCLine);
        }
    }

    /**
     * Parses FT lines
     * Format : FT KEY START END COMMENT.
     * @throws \Exception
     */
    private function buildFTField()
    {
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

        $oFeature = new Feature();
        $oFeature->setPrimAcc($this->sequence->getPrimAcc());
        $oFeature->setFtKey($sFTKey);
        $oFeature->setFtFrom($iFTFrom);
        $oFeature->setFtTo($iFTTo);
        $oFeature->setFtValue($sFTKey);
        $oFeature->setFtDesc($sFTDesc);
        $this->features[] = $oFeature;
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
        $sLineData = $this->rem_right(trim(substr($this->aLines->current(), 3)));
        $aDrLine = preg_split("/;/", $sLineData);
        array_walk($aDrLine, function(&$sValue) {
            $sValue = trim($sValue);
        });
        $oSpDatabank = new SpDatabank();
        $oSpDatabank->setPrimAcc($this->sequence->getPrimAcc());
        $oSpDatabank->setDbName($aDrLine[0]);
        $oSpDatabank->setPid1($aDrLine[1]);
        $oSpDatabank->setPid2($aDrLine[2]);

        $this->spDatabank[] = $oSpDatabank;
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
     * @param   array           $aReferences
     * @param   array           $aAuthors
     * @throws  \Exception
     */
    private function buildRNField($aFlines, &$aReferences, &$aAuthors)
    {
        $ra_ctr = 0;
        $rl_ctr = 0;
        $ra_str = "";
        $rl_str = "";

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
                    // we remove the last character if it is ";"
                    $sRCLine = trim($sRCLine);
                    if ($this->right($sRCLine, 1) == ";") {
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
                    $ra_ctr++;
                    // An author list spans as many RA lines as it needs and only the last
                    // one ends with ";": every line has to be appended, not to replace.
                    $ra_str = ($ra_ctr == 1) ? $sLineData : $ra_str . " " . $sLineData;
                    if ($sLineEnd == ";") {
                        $ra_str = $this->rem_right($ra_str);
                        $aAuthors = preg_split("/\,/", $ra_str);
                        array_walk($aAuthors, function(&$sValue) {
                            $sValue = trim($sValue);
                        });
                        $aInner["RA"] = $aAuthors;
                    }
                    break;
                case "RL":
                    $rl_ctr++;
                    $rl_str = ($rl_ctr == 1) ? $sLineData : $rl_str . " " . $sLineData;
                    $aInner["RL"] = $rl_str;
                    break;
            }

            $sHead = trim(substr($aFlines[$this->aLines->key()+1],0, 2));
            $aElements = ["RP", "RX", "RA", "RM", "RC", "RL"];
            if(!in_array($sHead, $aElements)) { // Stop if we change feature
                break;
            }

            $this->aLines->next();
        }

        $aReferences[$iRefNo - 1] = $aInner;
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
        $aGename = [];
        // Remove "GN " at the beginning of our line.
        $sLine = trim(substr($this->aLines->current(), 3));
        // Remove the last character which is always a period.
        $sLine = substr($sLine, 0, strlen($sLine)-1);

        // Strict comparison: a "(" opening the line sits at offset 0, which a loose test
        // reads as "not found" and routes to the wrong branch.
        if (strpos($sLine, "(") === false) { // GN Line does not contain any parentheses.
            // Ergo, it is made up of all OR's or AND's but not both.
            if (strpos($sLine, " OR ") !== false) {
                // Case 1: GNAME1 OR GNAME2.
                $aTemp = preg_split("/ OR /", $sLine);
                $aGename[] = $aTemp;
            } elseif (strpos($sLine, " AND ") !== false) {
                // Case 2: GNAME1 AND GNAME2 AND GNAME3.
                $aTemp = preg_split("/ AND /", $sLine);
                foreach($aTemp as $sGene) {
                    $aGename[] = array($sGene);
                }
            } else {
                $aGename[] = array($sLine);
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
                    $aGename[]          = $aGeneList;
                } else { // singleton
                    $aGename[]          = array($sGene);
                }
            }
        }

        $this->geneNames = array_merge($this->geneNames, $aGename);
    }

    /**
     * Parses SQ lines and below
     * SQ   SEQUENCE XXXX AA; XXXXX MW; XXXXX CN;
     * @throws  \Exception
     */
    private function buildSQField()
    {
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

        $this->sequence->setSequence($sSequence);
    }

    /**
     * Date of the last sequence update (DT line).
     * @return string
     */
    public function getSequpdDate(): string
    {
        return $this->sequpdDate;
    }

    /**
     * Date of the last annotation update (DT line).
     * @return string
     */
    public function getNotupdDate(): string
    {
        return $this->notupdDate;
    }

    /**
     * Gene names (GN line), as groups of synonyms.
     * @return array
     */
    public function getGeneNames(): array
    {
        return $this->geneNames;
    }

    /**
     * Creates references array
     * @param       array       $aReferences
     * @throws      \Exception
     */
    private function makeRefArray($aReferences)
    {
        foreach($aReferences as $key => $value) {
            $oReference = new Reference();
            $oReference->setPrimAcc($this->sequence->getPrimAcc());
            $oReference->setRefno($key);
            if(isset($value["RL"])) {
                $oReference->setTitle($value["RL"]);
            }
            // A reference carrying no RX line has no cross-reference to read.
            if(isset($value["RX_BDN"], $value["RX_ID"])) {
                if($value["RX_BDN"] == 'MEDLINE') {
                    $oReference->setMedline($value["RX_ID"]);
                }
                if($value["RX_BDN"] == 'PUBMED') {
                    $oReference->setPubmed($value["RX_ID"]);
                }
            }
            if(isset($value["RP"])) {
                $oReference->setRemark($value["RP"]);
            }
            if(isset($value["RL"] )) {
                $oReference->setJournal($value["RL"]);
            }
            if(isset($value["RC"])) {
                $oReference->setComments($value["RC"]);
            }
            if(isset($value["RA"])) {
                $aAuthors = $value["RA"];
                foreach($aAuthors as $sAuthor) {
                    $oAuthor = new Author();
                    $oAuthor->setPrimAcc($this->sequence->getPrimAcc());
                    $oAuthor->setRefno($key);
                    $oAuthor->setAuthor($sAuthor);
                    $this->authors[] = $oAuthor;
                }
            }
            $this->references[] = $oReference;
        }
    }
}