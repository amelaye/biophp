<?php
/**
 * Genbank database parsing
 * @author Amélie DUVERNET akka Amelaye
 * Freely inspired by BioPHP's project biophp.org
 * Created 15 february 2019
 * Last modified 15 february 2019
 */
namespace AppBundle\Service;

use AppBundle\Entity\Sequence;

class ParseGenbankManager
{
    private $ref_array;
    private $maxlength;
    private $minlength;
    private $tot_seqlength;
    private $seqdata;
    private $aLines;


    /**
     * ParseGenbankManager constructor.
     */
    public function __construct()
    {
        $this->ref_array      = [];
        $this->maxlength      = 0;
        $this->minlength      = 999999;
        $this->tot_seqlength  = 0;
        $this->seqdata        = "";
    }


    /**
     * Parses a GenBank data file and returns a Seq object containing parsed data.
     * @param   type        $aFlines
     * @return  Sequence    $oSequence
     */
    public function parse_id($aFlines)
    {
        $oSequence = new Sequence();
        $this->aLines = new \ArrayIterator($aFlines);

        foreach($this->aLines as $lineno => $linestr) {

            if (substr($linestr,0,2) == "//") { // We are at the end ...
                $oSequence->setSequence($this->seqdata);
                break;
            }

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
                    $aFeatures = array();
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

            }
        }

        dump($oSequence);
        return $oSequence;
    }


    /**
     * @param   array $aFlines
     * @return  array
     */
    private function parseReferences($aFlines)
    {
        $aWords = preg_split("/\s+/", trim(substr($this->aLines->current(),12)));
        $aReferences = array();
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
            $aPubmed = preg_split("/\s+/", trim(substr($this->aLines->current(), 12))); // AUTHORS
            $sPubmed = trim($sPubmed." ".implode(" ", $aPubmed));
            // If reference following, don't jump line
            if(trim(substr($aFlines[$this->aLines->key()+1],0, 12)) != "REFERENCE") {
                $this->aLines->next();
            }
        }

        if(trim(substr($this->aLines->current(),0,12)) == "REMARK") {
            while(1) {
                $aRemark = preg_split("/\s+/", trim(substr($this->aLines->current(), 12))); // AUTHORS
                $sRemark = trim($sRemark." ".implode(" ", $aRemark));
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

        $aReferences["AUTHORS"] = $sAuthors;
        $aReferences["TITLE"]   = $sTitle;
        $aReferences["JOURNAL"] = $sJournal;
        $aReferences["MEDLINE"] = $sMedline;
        $aReferences["PUBMED"]  = $sPubmed;
        $aReferences["REMARK"]  = $sRemark;
        $aReferences["COMMENT"] = $sComment;

        return $aReferences;
    }

    /**
     * Parse every multi-line fields from REFERENCES
     * @param $sReferenceProperty
     * @return string
     */
    private function seekReferences(&$sReferenceProperty)
    {
        while(1) {
            $referencePropertyLine = preg_split("/\s+/", trim(substr($this->aLines->current(), 12)));
            $sReferenceProperty = trim($sReferenceProperty." ".implode(" ", $referencePropertyLine));
            $this->aLines->next();
            $head = trim(substr($this->aLines->current(), 0, 12));
            if ($head != "") {
                break;
            }
        }
        return $sReferenceProperty;
    }


    /**
     * Parses line LOCUS
     * @param $oSequence
     * @return mixed
     */
    private function parseLocus(&$oSequence)
    {
        $oSequence->setId(trim(substr($this->aLines->current(), 12, 16)));
        $oSequence->setSeqlength(trim(substr($this->aLines->current(), 29, 11)) * 1);

        $this->tot_seqlength += $oSequence->getSeqlength();

        if ($oSequence->getSeqlength() > $this->maxlength) {
            $this->maxlength = $oSequence->getSeqlength();
        }
        if ($oSequence->getSeqlength() < $this->minlength) {
            $this->minlength = $oSequence->getSeqlength();
        }

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
    }


    /**
     * Parses DEFINITION field
     * @param $oSequence
     */
    private function parseDefinition(&$oSequence, $flines)
    {
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
    }


    /**
     * Parses VERSION field
     * @param $oSequence
     */
    private function parseVersion(&$oSequence)
    {
        $wordarray = preg_split("/\s+/", trim($this->aLines->current()));
        $oSequence->setVersion($wordarray[1]);
        if (count($wordarray) == 3) {
            $oSequence->setNcbiGiId($wordarray[2]);
        }
    }


    /**
     * Parses KEYWORDS field
     * @param $oSequence
     */
    private function parseKeywords(&$oSequence)
    {
        $wordarray = preg_split("/\s+/", trim($this->aLines->current()));
        array_shift($wordarray);
        $wordarray = preg_split("/;+/", implode(" ", $wordarray));
        if ($wordarray[0] != ".") {
            $oSequence->setKeywords($wordarray);
        }
    }


    /**
     * Parses ACCESSION field
     * @param object $oSequence
     */
    private function parseAccession(&$oSequence)
    {
        $wordarray = preg_split("/\s+/", trim($this->aLines->current()));
        $oSequence->setAccession($wordarray[1]);
        array_shift($wordarray);
        array_shift($wordarray);
        $oSequence->setSecAccession($wordarray);
    }


    /**
     * Parses each fields for FEATURES
     * @param   array   $aFeatures
     * @param   array   $aFlines
     * @param   string  $sField
     */
    private function parseFeatures(&$aFeatures, $aFlines, $sField)
    {
        $sKey = $sField ." ". implode(" ", preg_split("/\s+/", trim(substr($this->aLines->current(), 20))));
        $aMyFeature = array();
        $this->aLines->next();
        $sLine = implode(" ", preg_split("/\s+/", trim(substr($this->aLines->current(), 20))));
        while (1) {
            // If line begins with  /
            // Adding line in array
            if(substr(trim($aFlines[$this->aLines->key()+1]), 0, 1) == "/") {
                $aMyFeature[] = $sLine;
                $sLine = ""; // RAZ
            }

            $this->aLines->next();
            $sLine .= " ".implode(" ", preg_split("/\s+/", trim(substr($this->aLines->current(), 20))));

            $sHead = trim(substr($aFlines[$this->aLines->key()+1],0, 12));
            if($sHead != "") { // Stop if we change feature
                $aMyFeature[] = $sLine;
                break;
            }
        }
        $aFeatures[$sKey] = $aMyFeature;
    }
}