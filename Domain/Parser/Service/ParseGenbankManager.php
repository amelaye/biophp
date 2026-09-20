<?php
/**
 * Genbank database parsing
 * Freely inspired by BioPHP's project biophp.org
 * Created 24 november 2019
 * Last modified 18 September 2026
 */
namespace Amelaye\BioPHP\Domain\Parser\Service;

use Amelaye\BioPHP\Domain\Database\Service\ParseDbAbstractManager;
use Amelaye\BioPHP\Domain\Sequence\Entity\Accession;
use Amelaye\BioPHP\Domain\Sequence\Entity\Author;
use Amelaye\BioPHP\Domain\Sequence\Entity\Feature;
use Amelaye\BioPHP\Domain\Sequence\Entity\Keyword;
use Amelaye\BioPHP\Domain\Sequence\Entity\Reference;

/**
 * Class ParseGenbankManager
 * @package Amelaye\BioPHP\Domain\Parser\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */

final class ParseGenbankManager extends ParseDbAbstractManager
{
    /**
     * @var array
     */
    private $aLines;

    /**
     * The name this format is known by in the collection records and in DatabaseParserFactory.
     * @return string
     */
    public static function getFormat() : string
    {
        return "GENBANK";
    }

    /**
     * Tells whether a line opens a new GenBank entry.
     * @param   string      $sLine          The line to analyze
     * @return  bool
     */
    public static function isEntryStart(string $sLine) : bool
    {
        return substr($sLine, 0, 5) == "LOCUS";
    }

    /**
     * Tells whether a line closes a GenBank entry.
     * @param   string      $sLine          The line to analyze
     * @return  bool
     */
    public static function isEntryEnd(string $sLine) : bool
    {
        return substr($sLine, 0, 2) == "//";
    }

    /**
     * Extracts the identifier uniquely naming a GenBank entry.
     * @param   array       $aFlines        The whole file, buffered
     * @param   string      $sLine          The line opening the entry
     * @return  string
     */
    public static function getEntryId(array $aFlines, string $sLine) : string
    {
        $aLocus = preg_split("/\s+/", trim($sLine));

        return trim($aLocus[1]);
    }

    /**
     * Parses a GenBank data file and returns a Seq object containing parsed data.
     * @param   array       $aFlines        The lines the script has to parse
     * @throws \Exception
     */
    public function parseDataFile($aFlines)
    {
        $this->aLines = new \ArrayIterator($aFlines); // <3

        foreach($this->aLines as $lineno => $linestr) {

            switch(trim(substr($this->aLines->current(),0,12))) {
                case "LOCUS":
                    $this->parseLocus();
                    break;
                case "DEFINITION":
                    $this->parseDefinition($aFlines);
                    break;
                case "ORGANISM":
                    $this->parseOrganism($aFlines);
                    //$this->sequence->setOrganism(trim(substr($linestr,12)));
                    break;
                case "VERSION":
                    $this->parseVersion();
                    break;
                case "KEYWORDS":
                    $this->parseKeywords();
                    break;
                case "ACCESSION":
                    $this->parseAccession();
                    break;
                case "FEATURES":
                    while(1) {
                        // Verify next line. A feature key (whether we recognize it or not) and a
                        // qualifier continuation line are both indented; only an unindented line
                        // (the next top-level section, or the "//" record terminator) means the
                        // FEATURES table is over. A feature key we don't parse (e.g. "mRNA") must
                        // not stop the loop, or every feature after it in the record is lost.
                        $sNextLine = $aFlines[$this->aLines->key()+1] ?? "";
                        $bStillInFeatureTable = ($sNextLine === "") || ctype_space(substr($sNextLine, 0, 1));
                        if(!$bStillInFeatureTable) {
                            break;
                        }
                        $this->aLines->next();
                        $sHead = trim(substr($this->aLines->current(), 0, 20));
                        $aFields = ["source", "gene", "exon", "CDS", "misc_feature"];
                        if(in_array($sHead, $aFields)) {
                            $this->parseFeatures($aFlines, $sHead);
                        }
                    }
                    break;
                case "REFERENCE":
                    $this->parseReferences($aFlines);
                    break;
                case "SOURCE":
                    $this->sequence->setSource(trim(substr($linestr, 12)));
                    break;
                case "ORIGIN":
                    $sWords = "";
                    while(1) {
                        $this->aLines->next();
                        $sWords .= trim(substr($this->aLines->current(),9))." ";
                        $sHead = trim(substr($aFlines[$this->aLines->key()+1],0, 20));
                        if($sHead == '//') {
                            break;
                        }
                    }
                    $this->sequence->setSequence(trim($sWords));
                    break;
            }
        }
    }


    /**
     * @param   array       $aFlines    The lines the script has to parse
     * @throws  \Exception
     */
    private function parseReferences($aFlines)
    {
        $oReference = new Reference();
        $aWords = preg_split("/\s+/", trim(substr($this->aLines->current(),12)));
        $oReference->setPrimAcc($this->sequence->getPrimAcc());
        $oReference->setRefno($aWords[0]);
        array_shift($aWords);
        $sbaseRange = implode(" ", $aWords);
        $sbaseRange = str_replace(["(bases ",")"], "", $sbaseRange);
        $oReference->setBaseRange($sbaseRange);

        $sAuthors = $sTitle = $sJournal = $sMedline = $sPubmed = $sRemark = "";
        $this->aLines->next();

        if(trim(substr($this->aLines->current(),0,12)) == "AUTHORS") {
            $this->seekReferences($sAuthors);
            $sAuthors = trim($sAuthors);
            $sAuthors = str_replace(" and ", ",", $sAuthors);
            $sAuthors = str_replace(".", "", $sAuthors);
            $aAuthors = explode(",",$sAuthors);
            foreach($aAuthors as $sAuthor) {
                $oAuthor = new Author();
                $oAuthor->setPrimAcc($this->sequence->getPrimAcc());
                $oAuthor->setRefno($oReference->getRefno());
                $oAuthor->setAuthor(trim($sAuthor));
                $this->authors[] = $oAuthor;
            }
        }

        if(trim(substr($this->aLines->current(),0,12)) == "CONSRTM") {
            $this->aLines->next();
        }

        if(trim(substr($this->aLines->current(),0,12)) == "TITLE") {
            $this->seekReferences($sTitle);
            $oReference->setTitle(trim($sTitle));
        }

        if(trim(substr($this->aLines->current(),0,12)) == "JOURNAL") {
            $this->seekReferences($sJournal);
            $oReference->setJournal(trim($sJournal));
        }

        if(trim(substr($this->aLines->current(),0,12)) == "MEDLINE") {
            $this->seekReferences($sMedline);
            $oReference->setMedline($sMedline);
        }

        if(trim(substr($this->aLines->current(),0,12)) == "PUBMED") {
            $aPubmed = preg_split("/\s+/", trim(substr($this->aLines->current(), 12)));
            $sPubmed = trim($sPubmed." ".implode(" ", $aPubmed));
            $oReference->setPubmed($sPubmed);
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
            $oReference->setRemark(trim($sRemark));
        }
           $this->references[] = $oReference;
    }

    /**
     * Parse every multi-line fields from REFERENCES
     * @param   string          $sReferenceProperty     The references part
     * @return  string
     * @throws  \Exception
     */
    private function seekReferences(&$sReferenceProperty)
    {
        while(1) {
            $sReferenceProperty .= " ".trim(substr($this->aLines->current(), 12));
            $this->aLines->next();
            $head = trim(substr($this->aLines->current(), 0, 12));
            if ($head != "") {
                break;
            }
        }
        return $sReferenceProperty;
    }

    /**
     * Parses information about organism
     * @throws \Exception
     */
    private function parseOrganism($flines)
    {
        $organism = array();
        $organism[] = trim(substr($this->aLines->current(),12));
        while(1) {
            $head = trim(substr($flines[$this->aLines->key()+1],0, 12));
            if($head != "") {
                break;
            }
            $this->aLines->next();
            $sLine = trim($this->aLines->current());
            $aElems = explode(";", $sLine);
            foreach($aElems as $sElem) {
                if($sElem != "") {
                    $organism[] = trim($sElem);
                }
            }
        }
        $this->sequence->setOrganism($organism);
    }

    /**
     * Parses line LOCUS
     * @return      mixed
     * @throws      \Exception
     */
    private function parseLocus()
    {
        $this->sequence->setPrimAcc(trim(substr($this->aLines->current(), 12, 16)));
        $this->gbSequence->setPrimAcc($this->sequence->getPrimAcc());

        $this->sequence->setSeqlength(trim(substr($this->aLines->current(), 29, 11)) * 1);
        $this->sequence->setMoltype(trim(substr($this->aLines->current(), 47, 6)));

        switch(substr($this->aLines->current(), 44, 3)) {
            case "ss-":
                $this->gbSequence->setStrands("SINGLE");
                break;
            case "ds-":
                $this->gbSequence->setStrands("DOUBLE");
                break;
            case "ms-":
                $this->gbSequence->setStrands("MIXED");
                break;
        }

        $this->gbSequence->setTopology(strtoupper(trim(substr($this->aLines->current(), 55, 8))));
        $this->gbSequence->setDivision(strtoupper(trim(substr($this->aLines->current(), 64, 3))));
        $this->sequence->setDate(strtoupper(trim(substr($this->aLines->current(), 68, 11))));
    }


    /**
     * Parses DEFINITION field
     * @ param      array            $flines
     * @throws      \Exception
     */
    private function parseDefinition($flines)
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
        $this->sequence->setDescription($sDefinition);
    }


    /**
     * Parses VERSION field
     * @throws      \Exception
     */
    private function parseVersion()
    {
        $wordarray = preg_split("/\s+/", trim($this->aLines->current()));
        $this->gbSequence->setVersion($wordarray[1]);
        if (count($wordarray) == 3) {
            $this->gbSequence->setNcbiGiId($wordarray[2]);
        }
    }


    /**
     * Parses KEYWORDS field
     * @throws      \Exception
     */
    private function parseKeywords()
    {
        $wordarray = preg_split("/\s+/", trim($this->aLines->current()));
        array_shift($wordarray);
        $wordarray = preg_split("/;+/", implode(" ", $wordarray));
        if ($wordarray[0] != ".") {
            foreach($wordarray as $word) {
                $oKeyword = new Keyword();
                $oKeyword->setPrimAcc($this->sequence->getPrimAcc());
                $oKeyword->setKeywords($word);
                $this->keywords[] = $oKeyword;
            }
        }
    }


    /**
     * Parses ACCESSION field
     * @throws      \Exception
     */
    private function parseAccession()
    {
        $wordarray = preg_split("/\s+/", trim($this->aLines->current()));
        $this->sequence->setPrimAcc($wordarray[1]);
        array_shift($wordarray);
        array_shift($wordarray);
        foreach($wordarray as $word) {
            $oAccession = new Accession();
            $oAccession->setPrimAcc($this->sequence->getPrimAcc());
            $oAccession->setAccession($word);
            $this->accession[] = $oAccession;
        }
    }


    /**
     * Parses each fields for FEATURES
     * @param   array   $aFlines
     * @param   string  $sField
     * @throws  \Exception
     */
    private function parseFeatures($aFlines, $sField)
    {
        $sKey = $sField;
        $sLocation = trim(substr($this->aLines->current(), 20));
        // A location can wrap across several physical lines (a spliced join() feature commonly
        // does). Keep appending lines to it until the next one starts a qualifier ("/...") or a
        // new feature/section begins.
        while (true) {
            $sNextLine = $aFlines[$this->aLines->key() + 1] ?? "";
            $sNextTrimmed = trim($sNextLine);
            if ($sNextTrimmed === "" || $sNextTrimmed[0] === "/") {
                break;
            }
            if (trim(substr($sNextLine, 0, 12)) != "") {
                break;
            }
            $this->aLines->next();
            $sLocation .= trim(substr($this->aLines->current(), 20));
        }
        $aBounds = $this->parseLocationBounds($sLocation);
        $this->aLines->next();
        $sLine = trim(substr($this->aLines->current(), 20));
        while (1) {
            // Decide from the *next* line, before consuming it: a new "/qualifier=" line means
            // the one just accumulated in $sLine is complete. A new feature key (or a top-level
            // section like ORIGIN) occupies columns 0-11, same as the check that opens a
            // feature's own key/location line; a qualifier's own wrapped continuation line never
            // does, since its content starts only past column 20. Checking this on the line
            // about to be consumed - not one line later, once it has already been swallowed - is
            // what keeps the next feature's key/location line from being absorbed as if it were
            // more of this feature's qualifier text.
            $sNextLine = $aFlines[$this->aLines->key()+1] ?? "";
            $sNextTrimmed = trim($sNextLine);
            $bNextStartsQualifier = ($sNextTrimmed !== "") && ($sNextTrimmed[0] === "/");
            $bNextIsNewFeatureOrSection = trim(substr($sNextLine, 0, 12)) !== "";

            if ($bNextStartsQualifier || $bNextIsNewFeatureOrSection) {
                $this->buildFeature($sLine, $sKey, $aBounds);
                $sLine = ""; // RAZ
            }
            if ($bNextIsNewFeatureOrSection) {
                break;
            }
            $this->aLines->next();
            $sLine .= " ".trim(substr($this->aLines->current(), 20));
        }
    }

    /**
     * Creates Feature object
     * @param   string  $sLine
     * @param   string  $sKey
     * @param   array   $aBounds    [$iFtFrom, $iFtTo, $sStrand], as returned by
     * parseLocationBounds().
     */
    private function buildFeature($sLine, $sKey, $aBounds)
    {
        $sLine = str_replace("/","",trim($sLine));
        $aLine = explode("=",str_replace('"',"",$sLine));
        $oFeature = new Feature();
        $oFeature->setPrimAcc($this->sequence->getPrimAcc());
        $oFeature->setFtKey($sKey);
        $oFeature->setFtQual($aLine[0]);
        $oFeature->setFtValue($aLine[1]);
        $oFeature->setFtFrom($aBounds[0]);
        $oFeature->setFtTo($aBounds[1]);
        $oFeature->setStrand($aBounds[2] ?? null);
        $this->features[] = $oFeature;
    }
}
