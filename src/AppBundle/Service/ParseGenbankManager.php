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
    private $seqarr;
    private $inseq_flag;
    private $seqdata_flag;
    private $accession_flag;
    private $ref_array;
    private $feature_array;
    private $entry_ctr;
    private $ref_ctr;
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
        $this->seqarr         = [];
        $this->inseq_flag     = false;
        $this->seqdata_flag   = false;
        $this->accession_flag = false;
        $this->ref_array      = [];
        $this->feature_array  = [];
        $this->entry_ctr      = 0;
        $this->ref_ctr        = 0;
        $this->maxlength      = 0;
        $this->minlength      = 999999;
        $this->tot_seqlength  = 0;
        $this->seqdata        = "";
    }



    /**
     * Parses a GenBank data file and returns a Seq object containing parsed data.
     * @param   type        $flines
     * @return  Sequence    $oSequence
     */
    public function parse_id($flines)
    {
        $oSequence = new Sequence();

        $this->aLines = new \ArrayIterator($flines);


        foreach($this->aLines as $lineno => $linestr) {

            if (substr($linestr,0,2) == "//") { // We are at the end ...
                $oSequence->setSequence($this->seqdata);
                break;
            }

            switch(trim(substr($this->aLines->current(),0,12))) {
                case "LOCUS":
                    if($oSequence->getId() == null) {
                        $this->parseLocus($oSequence);
                    }
                    break;
                case "DEFINITION":
                    if($oSequence->getDefinition() == null) {
                        $this->parseDefinition($oSequence);
                    }
                    break;
                case "ORGANISM":
                    $oSequence->setOrganism(trim(substr($linestr,12)));
                    break;
                case "VERSION":
                    if($oSequence->getVersion() == null) {
                        $this->parseVersion($oSequence);
                    }
                    break;
                case "KEYWORDS":
                    if($oSequence->getKeywords() == null) {
                        $this->parseKeywords($oSequence);
                    }
                    break;
                case "ACCESSION":
                    if($oSequence->getAccession() == null) {
                        $this->parseAccession($oSequence);
                    }
                    break;
                case "FEATURES":

                    break;
                case "REFERENCE":
                    $wordarray = preg_split("/\s+/", trim(substr($this->aLines->current(),12)));
                    $ref_rec = array();
                    $ref_rec["REFNO"] = $wordarray[0];
                    array_shift($wordarray);
                    $ref_rec["BASERANGE"] = implode(" ", $wordarray);

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
                        $pubmed = preg_split("/\s+/", trim(substr($this->aLines->current(), 12))); // AUTHORS
                        $sPubmed = trim($sPubmed." ".implode(" ", $pubmed));
                        // If reference following, don't jump line
                        if(trim(substr($flines[$this->aLines->key()+1],0, 12)) != "REFERENCE") {
                            $this->aLines->next();
                        }
                    }

                    if(trim(substr($this->aLines->current(),0,12)) == "REMARK") {
                        while(1) {
                            $remark = preg_split("/\s+/", trim(substr($this->aLines->current(), 12))); // AUTHORS
                            $sRemark = trim($sRemark." ".implode(" ", $remark));
                            // If reference following, don't jump line
                            if(trim(substr($flines[$this->aLines->key()+1],0, 12)) != "REFERENCE") {
                                $this->aLines->next();
                                $head = trim(substr($this->aLines->current(), 0, 12));
                                if ($head != "") {
                                    break;
                                }
                            } else {
                                break;
                            }
                        }
                    }

                    $ref_rec["AUTHORS"] = $sAuthors;
                    $ref_rec["TITLE"]   = $sTitle;
                    $ref_rec["JOURNAL"] = $sJournal;
                    $ref_rec["MEDLINE"] = $sMedline;
                    $ref_rec["PUBMED"]  = $sPubmed;
                    $ref_rec["REMARK"]  = $sRemark;
                    $ref_rec["COMMENT"] = $sComment;

                    array_push($this->ref_array, $ref_rec);
                    $oSequence->setReference($this->ref_array);
                    break;

                case "SOURCE":
                    $oSequence->setSource(trim(substr($linestr, 12)));
                    break;

            }
        }

        dump($oSequence);
    }

    /**
     * Parse every multi-line fields from REFERENCES
     * @param $sReferenceProperty
     * @return string
     */
    private function seekReferences(&$sReferenceProperty)
    {
        while(1) {
            $referencePropertyLine = preg_split("/\s+/", trim(substr($this->aLines->current(), 12))); // AUTHORS
            $sReferenceProperty = trim($sReferenceProperty." ".implode(" ", $referencePropertyLine));
            $this->aLines->next();
            $head = trim(substr($this->aLines->current(), 0, 12));
            if ($head != "") {
                break;
            }
        }
        return $sReferenceProperty;
    }

    /*public function parse_id($flines)
    {
        $oSequence = new Sequence();
        $feat_r = [];

        foreach($flines as $lineno => $linestr) {


            if (trim(substr($linestr,0,10)) == "BASE COUNT") {
                $oSequence = $this->parseBaseCount($feat_r, $oSequence);
            }

            if (trim(substr($linestr,0,12)) == "FEATURES") {
                // The REFERENCE section was present for this SEQUENCE ENTRY so we set REFERENCE attribute.
                if (count($this->ref_array) > 0) {
                    $oSequence->setReference($this->ref_array);
                }
                $lastsubkey = "";

                $feat_r = [];
                $qual_r = [];

                // Go to the next line.
                list($lineno, $linestr) = each($flines);


                // This loops through each line in the entire FEATURES SECTION.
                while(substr($linestr,0,10) != "BASE COUNT") {
                    $label = trim(substr($linestr,0,21));
                    $data = trim(substr($linestr,21));

                    if (strlen($label) != 0) {
                        // At the beginning of a new SUBKEY.
                        $subkey = $label;
                        // Add/save the qualifier array (qual_r) of the previous SUBKEY to our big feat_r array.
                        if (count($qual_r) > 0) {
                            $feat_r[$lastsubkey] = $qual_r;
                            $qual_r = array();
                        }

                        $qual = $subkey;
                        $qual_r[$qual] = "";
                        $qual_ctr = 0;
                        dump($oSequence);exit();
                        do {
                            $qual_ctr++;
                            $qual_r[$qual] .= " " . $data;

                            //list($lineno, $linestr) = each($flines);
                            $label = trim(substr($linestr,0,21));
                            $data = trim(substr($linestr,21));
                        } while ($label == "" && !($this->isa_qualifier($data)));

                        if (!($label == "")) {
                            $lastsubkey = $subkey;
                            $subkey = $label;
                        }
                    } else { // we are inside a subkey section but on the 2nd, 3rd, nth line which have blank LABELS.
                        if ($this->isa_qualifier($data)) {
                            $wordarray = preg_split("/=/", $data);
                            $qual = $wordarray[0];
                            $data = $wordarray[1];
                            $qual_r[$qual] = "";
                            $qual_ctr = 0;

                            do {
                                $qual_ctr++;
                                $qual_r[$qual] .= " " . $data;
                                //list($lineno, $linestr) = each($flines);
                                $label = trim(substr($linestr,0,21));
                                $data = trim(substr($linestr,21));
                            } while($label == "" && !($this->isa_qualifier($data)));

                            if (!($label == "")) {
                                $lastsubkey = $subkey;
                                $subkey = $label;
                            }
                        }
                    }
                }

                if (count($qual_r) > 0) {
                    $feat_r[$lastsubkey] = $qual_r;
                    $qual_r = [];
                }

                prev($flines);
            }



            if ($this->inseq_flag) {
                if (trim(substr($linestr, 0, 12)) == "REFERENCE") {
                    // at this point, we are at the line with REFERENCE x (base y of z) in it.
                    $wordarray = preg_split("/\s+/", trim(substr($linestr,12)));

                    $ref_rec = array();
                    $ref_rec["REFNO"] = $wordarray[0];
                    array_shift($wordarray);
                    $ref_rec["BASERANGE"] = implode(" ", $wordarray);
                    $lastsubkey = "";
                    $subkey_lnctr = 0;

                    while(list($lineno, $linestr) = each($flines)) {
                        $subkey = trim(substr($linestr,0,12));

                        // If current subkey is blank string, then this is a continuation of the last subsection.
                        if (strlen($subkey) == 0) {
                            $subkey = $lastsubkey;
                        }

                        // If we are at the next subkey section (e.g. lastsubkey was AUTHORS, and current is TITLE).
                        if ($subkey != $lastsubkey) {
                            $subkey_lnctr = 0;
                        }


                        if ($subkey == "FEATURES") {
                            prev($flines);
                            break;
                        }
                        if ($subkey == "REFERENCE") {
                            $this->ref_ctr++;
                            prev($flines);
                            break;
                        }
                        $lastsubkey = $subkey;
                    }
                    array_push($this->ref_array, $ref_rec);
                }

                if (trim(substr($linestr, 0, 12)) == "SEGMENT") {
                    $oSequence->setSegment(substr($linestr, 12));
                    $wordarray = preg_split("/\s+/", trim(substr($linestr,12)));
                    $oSequence->setSegmentNo($wordarray[0]);
                    $oSequence->setSegmentCount($wordarray[2]);
                }

                if ($this->accession_flag) {
                    // 2nd, 3rd, etc. line of ACCESSION field.
                    $wordarray = preg_split("/\s+/", trim($linestr));
                    //$this->sec_accession = array_merge($this->sec_accession, $wordarray); -> mistake ?
                    $oSequence->setSecAccession(array_merge($oSequence->getSecAccession(), $wordarray));
                }

                if (($this->seqdata_flag == true) && (substr($linestr,0,2) != "//")) {
                    $wordarray = explode(" ", trim($linestr));
                    array_shift($wordarray);
                    $seqline = implode("", $wordarray);
                    $this->seqdata .= $seqline;
                }
                if (substr($linestr,0,6) == "ORIGIN") {
                    $this->seqdata_flag = true;
                }
                if (substr($linestr,0,2) == "//") {
                    $oSequence->setSequence($this->seqdata);
                    $this->seqarr[$this->database->getId()] = $this;
                    $this->seqdata_flag = false;
                    $this->inseq_flag = false;
                    break;
                }
            }

        }
        $oSequence->setSequence($this->seqarr);
        return $oSequence;
    }*/


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

        $this->inseq_flag = true;
        
        return $oSequence;
    }

    /**
     * Parses line DEFINITION
     * @param $oSequence
     */
    private function parseDefinition(&$oSequence)
    {
        $wordarray = explode(" ", $this->aLines->current());
        array_shift($wordarray);
        $sDefinition = trim(implode(" ", $wordarray));
        while(1) {
            $this->aLines->next();
            $head = trim(substr($this->aLines->current(),0,12));
            if($head != "") {
                $this->aLines->rewind();
                break;
            }
            $sDefinition .= " ".trim($this->aLines->current());
        }
        $oSequence->setDefinition($sDefinition);
    }

    private function parseVersion(&$oSequence)
    {
        $wordarray = preg_split("/\s+/", trim($this->aLines->current()));
        $oSequence->setVersion($wordarray[1]);
        if (count($wordarray) == 3) {
            $oSequence->setNcbiGiId($wordarray[2]);
        }
    }

    private function parseKeywords(&$oSequence)
    {
        $wordarray = preg_split("/\s+/", trim($this->aLines->current()));
        array_shift($wordarray);
        $wordarray = preg_split("/;+/", implode(" ", $wordarray));
        if ($wordarray[0] != ".") {
            $oSequence->setKeywords($wordarray);
        }
    }

    private function parseAccession(&$oSequence)
    {
        $wordarray = preg_split("/\s+/", trim($this->aLines->current()));
        $oSequence->setAccession($wordarray[1]);
        array_shift($wordarray);
        array_shift($wordarray);
        $oSequence->setSecAccession($wordarray);
    }
}