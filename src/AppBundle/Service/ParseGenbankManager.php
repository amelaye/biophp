<?php
/**
 * Genbank database parsing
 * Freely inspired by BioPHP's project biophp.org
 * Created 15 february 2019
 * Last modified 10 april 2019
 */
namespace AppBundle\Service;

use AppBundle\Entity\Sequence;
use AppBundle\Interfaces\ParseDatabaseInterface;

/**
 * Class ParseGenbankManager
 * @package AppBundle\Service
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class ParseGenbankManager implements ParseDatabaseInterface
{
    /**
     * @var array
     */
    private $ref_array;

    /**
     * @var array
     */
    private $aLines;


    /**
     * ParseGenbankManager constructor.
     */
    public function __construct()
    {
        $this->ref_array      = [];
    }


    /**
     * Parses a GenBank data file and returns a Seq object containing parsed data.
     * @param   array       $aFlines        The lines the script has to parse
     * @return  Sequence    $oSequence      The Sequence object to analyze
     * @throws \Exception
     */
    public function parseDataFile($aFlines)
    {
        try {
            $oSequence = new Sequence();
            $this->aLines = new \ArrayIterator($aFlines); // <3

            foreach($this->aLines as $lineno => $linestr) {

                switch(trim(substr($this->aLines->current(),0,12))) {
                    case "LOCUS":
                        $this->parseLocus($oSequence);
                        break;
                    case "DEFINITION":
                        $this->parseDefinition($oSequence, $aFlines);
                        break;
                    case "ORGANISM":
                        $oSequence->setOrganism(trim(substr($linestr,12)));
                        break;
                    case "VERSION":
                        $this->parseVersion($oSequence);
                        break;
                    case "KEYWORDS":
                        $this->parseKeywords($oSequence);
                        break;
                    case "ACCESSION":
                        $this->parseAccession($oSequence);
                        break;
                    case "FEATURES":
                        $aFeatures = [];
                        while(1) {
                            // Verify next line
                            $sHead = trim(substr($aFlines[$this->aLines->key()+1],0, 20));
                            $aFields = ["source", "gene", "exon", "CDS", "misc_feature"];
                            if($sHead != "" && !in_array($sHead, $aFields)) {
                                break;
                            }
                            $this->aLines->next();
                            $sHead = trim(substr($this->aLines->current(), 0, 20));
                            if(in_array($sHead, $aFields)) {
                                $this->parseFeatures($aFeatures, $aFlines, $sHead);
                            }
                        }
                        $oSequence->setFeatures($aFeatures);
                        break;
                    case "REFERENCE":
                        $ref_rec = $this->parseReferences($aFlines);
                        array_push($this->ref_array, $ref_rec);
                        $oSequence->setReference($this->ref_array);
                        break;
                    case "SOURCE":
                        $oSequence->setSource(trim(substr($linestr, 12)));
                        break;
                    case "ORIGIN":
                        $aSequence = [];
                        while(1) {
                            $this->aLines->next();
                            $key = trim(substr($this->aLines->current(), 0, 9));
                            $aWords = preg_split("/\s+/", trim(substr($this->aLines->current(),9)));
                            $aSequence[$key] = $aWords;
                            $sHead = trim(substr($aFlines[$this->aLines->key()+1],0, 20));
                            if($sHead == '//') {
                                break;
                            }
                        }
                        $oSequence->setSequence($aSequence);
                        break;
                }
            }
            return $oSequence;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }


    /**
     * @param   array       $aFlines    The lines the script has to parse
     * @return  array
     * @throws  \Exception
     */
    private function parseReferences($aFlines)
    {
        try {
            $aWords = preg_split("/\s+/", trim(substr($this->aLines->current(),12)));
            $aReferences = [];
            $aReferences["REFNO"] = $aWords[0];
            array_shift($aWords);
            $aReferences["BASERANGE"] = implode(" ", $aWords);

            $sAuthors   = "";
            $sTitle     = "";
            $sJournal   = "";
            $sMedline   = "";
            $sPubmed    = "";
            $sRemark    = "";
            $sComment   = "";

            $this->aLines->next();

            if(trim(substr($this->aLines->current(),0,12)) == "AUTHORS") {
                $this->seekReferences($sAuthors);
            }

            if(trim(substr($this->aLines->current(),0,12)) == "CONSRTM") {
                $this->aLines->next();
            }

            if(trim(substr($this->aLines->current(),0,12)) == "TITLE") {
                $this->seekReferences($sTitle);
            }

            if(trim(substr($this->aLines->current(),0,12)) == "JOURNAL") {
                $this->seekReferences($sJournal);
            }

            if(trim(substr($this->aLines->current(),0,12)) == "MEDLINE") {
                $this->seekReferences($sMedline);
            }

            if(trim(substr($this->aLines->current(),0,12)) == "PUBMED") {
                $aPubmed = preg_split("/\s+/", trim(substr($this->aLines->current(), 12)));
                $sPubmed = trim($sPubmed." ".implode(" ", $aPubmed));
                // If reference following, don't jump line
                if(trim(substr($aFlines[$this->aLines->key()+1],0, 12)) != "REFERENCE") {
                    $this->aLines->next();
                }
            }

            if(trim(substr($this->aLines->current(),0,12)) == "REMARK") {
                while(1) {
                    $sRemark .= " ".trim(substr($this->aLines->current(), 12));
                    // If reference following, don't jump line
                    if(trim(substr($aFlines[$this->aLines->key()+1],0, 12)) != "REFERENCE") {
                        $this->aLines->next();
                        $sHead = trim(substr($this->aLines->current(), 0, 12));
                        if ($sHead != "") {
                            break;
                        }
                    } else {
                        break;
                    }
                }
            }

            $aReferences["AUTHORS"] = trim($sAuthors);
            $aReferences["TITLE"]   = trim($sTitle);
            $aReferences["JOURNAL"] = trim($sJournal);
            $aReferences["MEDLINE"] = trim($sMedline);
            $aReferences["PUBMED"]  = trim($sPubmed);
            $aReferences["REMARK"]  = trim($sRemark);
            $aReferences["COMMENT"] = trim($sComment);

            return $aReferences;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Parse every multi-line fields from REFERENCES
     * @param   string          $sReferenceProperty     The references part
     * @return  string
     * @throws  \Exception
     */
    private function seekReferences(&$sReferenceProperty)
    {
        try {
            while(1) {
                $sReferenceProperty .= " ".trim(substr($this->aLines->current(), 12));
                $this->aLines->next();
                $head = trim(substr($this->aLines->current(), 0, 12));
                if ($head != "") {
                    break;
                }
            }
            return $sReferenceProperty;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }


    /**
     * Parses line LOCUS
     * @param       Sequence    $oSequence  The object Sequence to be analyzed
     * @return      mixed
     * @throws      \Exception
     */
    private function parseLocus(&$oSequence)
    {
        try {
            $oSequence->setId(trim(substr($this->aLines->current(), 12, 16)));
            $oSequence->setSeqlength(trim(substr($this->aLines->current(), 29, 11)) * 1);
            $oSequence->setMoltype(trim(substr($this->aLines->current(), 47, 6)));

            switch(substr($this->aLines->current(), 44, 3)) {
                case "ss-":
                    $oSequence->setStrands("SINGLE");
                    break;
                case "ds-":
                    $oSequence->setStrands("DOUBLE");
                    break;
                case "ms-":
                    $oSequence->setStrands("MIXED");
                    break;
            }


            $oSequence->setTopology(strtoupper(trim(substr($this->aLines->current(), 55, 8))));
            $oSequence->setDivision(strtoupper(trim(substr($this->aLines->current(), 64, 3))));
            $oSequence->setDate(strtoupper(trim(substr($this->aLines->current(), 68, 11))));
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }


    /**
     * Parses DEFINITION field
     * @param       Sequence        $oSequence      The object Sequence to be analyzed
     * @throws      \Exception
     */
    private function parseDefinition(&$oSequence, $flines)
    {
        try {
            $wordarray = explode(" ", $this->aLines->current());
            array_shift($wordarray);
            $sDefinition = trim(implode(" ", $wordarray));
            while(1) {
                $head = trim(substr($flines[$this->aLines->key()+1],0, 12));
                if($head != "") {
                    break;
                }
                $this->aLines->next();
                $sDefinition .= " ".trim($this->aLines->current());
            }
            $oSequence->setDefinition($sDefinition);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }


    /**
     * Parses VERSION field
     * @param       Sequence    $oSequence  The object Sequence to be analyzed
     * @throws      \Exception
     */
    private function parseVersion(&$oSequence)
    {
        try {
            $wordarray = preg_split("/\s+/", trim($this->aLines->current()));
            $oSequence->setVersion($wordarray[1]);
            if (count($wordarray) == 3) {
                $oSequence->setNcbiGiId($wordarray[2]);
            }
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }


    /**
     * Parses KEYWORDS field
     * @param       Sequence    $oSequence  The object Sequence to be analyzed
     * @throws      \Exception
     */
    private function parseKeywords(&$oSequence)
    {
        try {
            $wordarray = preg_split("/\s+/", trim($this->aLines->current()));
            array_shift($wordarray);
            $wordarray = preg_split("/;+/", implode(" ", $wordarray));
            if ($wordarray[0] != ".") {
                $oSequence->setKeywords($wordarray);
            }
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }


    /**
     * Parses ACCESSION field
     * @param       Sequence    $oSequence  The object Sequence to be analyzed
     * @throws      \Exception
     */
    private function parseAccession(&$oSequence)
    {
        try {
            $wordarray = preg_split("/\s+/", trim($this->aLines->current()));
            $oSequence->setAccession($wordarray[1]);
            array_shift($wordarray);
            array_shift($wordarray);
            $oSequence->setSecAccession($wordarray);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }


    /**
     * Parses each fields for FEATURES
     * @param   array   $aFeatures      The features part
     * @param   array   $aFlines
     * @param   string  $sField
     * @throws  \Exception
     */
    private function parseFeatures(&$aFeatures, $aFlines, $sField)
    {
        try {
            $sKey = $sField ." ". trim(substr($this->aLines->current(), 20));
            $aMyFeature = [];
            $this->aLines->next();
            $sLine = trim(substr($this->aLines->current(), 20));
            while (1) {
                // If line begins with  /
                // Adding line in array
                if(trim($aFlines[$this->aLines->key()+1])[0] == "/") {
                    $aMyFeature[] = $sLine;
                    $sLine = ""; // RAZ
                }

                $this->aLines->next();
                $sLine .= " ".trim(substr($this->aLines->current(), 20));

                $sHead = trim(substr($aFlines[$this->aLines->key()+1],0, 12));
                if($sHead != "") { // Stop if we change feature
                    $aMyFeature[] = $sLine;
                    break;
                }
            }
            $aFeatures[$sKey] = $aMyFeature;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
}
