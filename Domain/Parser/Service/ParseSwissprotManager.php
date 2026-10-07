<?php
/**
 * Swissprot database parsing
 * Freely inspired by BioPHP's project biophp.org
 * Created 15 february 2019
 * Last modified 7 October 2026
 */
declare(strict_types=1);

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
     * @var \ArrayIterator|null
     */
    private ?\ArrayIterator $aLines = null;

    /**
     * Date of the last sequence update, read from the DT lines. The Sequence entity only
     * carries the creation date, so the two other DT dates stay on the parser.
     * @var string
     */
    private string $sequpdDate = "";

    /**
     * Date of the last annotation update, read from the DT lines.
     * @var string
     */
    private string $notupdDate = "";

    /**
     * Gene names, as groups of synonyms: ( (GNAME1, GNAME2), (GNAME3) ).
     * @var array
     */
    private array $geneNames = [];

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
     * Parses a Swiss-Prot / UniProtKB data file. Both layouts are read : the original one
     * ("ID   TNFA_HUMAN  STANDARD;  PRT;  233 AA.", "DT ... (REL. 01, CREATED)", "RX   MEDLINE; n.")
     * and the current UniProt one ("ID   POLS2_HUMAN  Reviewed;  855 AA.", "DT ..., integrated
     * into UniProtKB/Swiss-Prot.", "RX   PubMed=n; DOI=...;", structured DE and GN lines), as
     * described in the UniProtKB user manual. The feature table is read in its column layout (key,
     * from, to, description, continued on lines whose key column is blank) and in the one used
     * since release 2019_11 ("FT   CHAIN   47..855" followed by /note="..." qualifiers).
     * @param   array       $aFlines
     * @throws  \Exception
     */
    public function parseDataFile(array $aFlines) {
        $this->aLines = new \ArrayIterator($aFlines); // <3
        $aReferences = [];
        $aAccessions = [];

        $sKeywords = "";
        $aDescription = [];
        $sSource = "";
        $iSourceCpt = 0;
        $sOrganism = "";
        $iOrgaCpt = 0;
        $aGeneLines = [];

        /* Parsing the whole data */
        foreach($this->aLines as $lineno => $linestr) {
            $linelabel = $this->left($this->aLines->current(), 2);

            switch($linelabel) {
                case "ID":
                    $this->buildIDFields();
                    break;
                case "AC":
                    $this->buildACFields($aAccessions);
                    $this->sequence->setPrimAcc($aAccessions[0]);
                    break;
                case "DT":
                    $this->buildDTFields();
                    break;
                case "DE":
                    $aDescription[] = trim(substr($this->aLines->current(), 2));
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
                    $this->buildFTField($aFlines);
                    break;
                case "DR":
                    $this->buildDRField();
                    break;
                case "RN":
                    $this->buildRNField($aFlines, $aReferences);
                    break;
                case "GN":
                    $aGeneLines[] = trim(substr($this->aLines->current(), 2));
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
        $this->buildDEFields($aDescription);
        $this->buildGNField($aGeneLines);
    }

    /**
     * Parses ID line
     * Format : ID   PROTNAME_PROTSOURCE  DATA_CLASS;  [MOL_TYPE;]  LENGTH AA.
     * The molecule type ("PRT") only appears in the original layout ; a UniProt entry is always a
     * protein, so it is "PRT" either way.
     * @throws  \Exception
     */
    private function buildIDFields()
    {
        // Offset 3 (not 5) then drop the empty tokens: reads both the 3-space flat-file
        // form ("ID   TNFA_HUMAN") and the 1-space form, where offset 5 ate "TN".
        $aWords = preg_split('/\s+/', trim(substr($this->aLines->current(), 3)));
        $aNameSrc = explode("_", $aWords[0], 2);

        $iLength = 0;
        foreach ($aWords as $i => $sWord) {
            if (strtoupper(rtrim($sWord, ".")) === "AA" && $i > 0) {
                $iLength = (int) $aWords[$i - 1];
            }
        }

        $this->sequence->setEntryName($aNameSrc[0]);
        $this->sequence->setMolType("PRT");
        $this->sequence->setSource($aNameSrc[1] ?? "");
        $this->sequence->setSeqLength($iLength);
    }

    /**
     * Parses AC lines, which may be several
     * Format : AC   P01375; Q9UIV3;
     * @param   array           $aAccess
     * @return  array
     * @throws  \Exception
     */
    private function buildACFields(&$aAccess) : array {
        $sLineData = $this->intrim(trim(substr($this->aLines->current(), 3)));
        $aAccess = array_merge($aAccess, array_values(array_filter(explode(";", $sLineData), 'strlen')));
        return($aAccess);
    }

    /**
     * Parses DT Line, in the original layout ("21-JUL-1986 (REL. 01, CREATED)", "LAST SEQUENCE
     * UPDATE", "LAST ANNOTATION UPDATE") or the UniProt one ("15-MAR-2005, integrated into
     * UniProtKB/Swiss-Prot.", "sequence version 2.", "entry version 123.").
     * @throws  \Exception
     */
    private function buildDTFields()
    {
        $sLineData = trim(substr($this->aLines->current(), 3));
        $sDate = substr($sLineData, 0, 11);
        $sComment = strtoupper($sLineData);

        if (strpos($sComment, "CREATED") !== false || strpos($sComment, "INTEGRATED INTO") !== false) {
            $this->sequence->setDate($sDate);
        } elseif (strpos($sComment, "SEQUENCE UPDATE") !== false || strpos($sComment, "SEQUENCE VERSION") !== false) {
            $this->sequpdDate = $sDate;
        } elseif (strpos($sComment, "ANNOTATION UPDATE") !== false || strpos($sComment, "ENTRY VERSION") !== false) {
            $this->notupdDate = $sDate;
        }
    }

    /**
     * Sets the description from the DE lines, joined, and whether the sequence is a fragment :
     * "(FRAGMENT)." or "(FRAGMENTS)." ending the original layout, "Flags: Fragment;" or
     * "Flags: Fragments;" in the UniProt one.
     * @param   string[]    $aLines     The DE lines, label removed
     */
    private function buildDEFields(array $aLines)
    {
        if ($aLines === []) {
            return;
        }
        $sDescription = implode(" ", $aLines);
        $this->sequence->setDescription($sDescription);

        $bIsFragment = preg_match('/\(FRAGMENTS?\)\.$/i', $sDescription)
            || preg_match('/Flags:.*\bFragments?;/i', $sDescription);
        $this->sequence->setFragment($bIsFragment ? 1 : 0);
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
        $sKeywords .= ($sKeywords === "" ? "" : " ") . $sLineData;

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
     * Parses one feature of the FT lines, with the lines continuing it.
     * Column layout : FT   KEY      FROM    TO       DESCRIPTION, a continuation line leaving the
     * key column (6-13) blank. Layout since 2019_11 : FT   KEY             FROM..TO, followed by
     * /note="...", /evidence="..." and /id="..." lines. The collapsed one-space form of the oldest
     * samples ("FT CHAIN 77 233 TUMOR NECROSIS FACTOR.") has no continuation line. A position
     * written "<1", ">855" or "?" (unknown) gives its number, or null when unknown.
     * @param   array       $aFlines
     * @throws  \Exception
     */
    private function buildFTField(array $aFlines)
    {
        $sLineStr = rtrim($this->aLines->current(), "\r\n");
        $bColumnLayout = substr($sLineStr, 2, 3) === "   ";
        if ($bColumnLayout && trim(substr($sLineStr, 5, 8)) === "") {
            return; // a continuation line, already read with the feature it continues
        }

        $aTokens = preg_split('/\s+/', trim(substr($sLineStr, 2)), 3);
        $sFTKey = $aTokens[0];
        if (strpos($aTokens[1] ?? "", "..") !== false) {
            [$sFrom, $sTo] = explode("..", $aTokens[1], 2);
            $sRest = $aTokens[2] ?? "";
        } else {
            $aRest = preg_split('/\s+/', trim(($aTokens[1] ?? "") . " " . ($aTokens[2] ?? "")), 3);
            $sFrom = $aRest[0] ?? "";
            $sTo = $aRest[1] ?? $sFrom;
            $sRest = $aRest[2] ?? "";
        }

        $aDescription = [trim($sRest)];
        while ($bColumnLayout) {
            $sNext = rtrim($aFlines[$this->aLines->key() + 1] ?? "", "\r\n");
            if (substr($sNext, 0, 2) !== "FT" || trim(substr($sNext, 5, 8)) !== "") {
                break;
            }
            $aDescription[] = trim(substr($sNext, 5));
            $this->aLines->next();
        }
        $sFTDesc = $this->featureDescription(array_values(array_filter($aDescription, 'strlen')));

        $oFeature = new Feature();
        $oFeature->setPrimAcc($this->sequence->getPrimAcc());
        $oFeature->setFtKey($sFTKey);
        $oFeature->setFtFrom($this->featurePosition($sFrom));
        $oFeature->setFtTo($this->featurePosition($sTo));
        $oFeature->setFtValue($sFTKey);
        $oFeature->setFtDesc($sFTDesc);
        $this->features[] = $oFeature;
    }

    /**
     * @param   string      $sPosition      "77", "<1", ">855" or "?"
     * @return  int|null                    Null for an unknown position
     */
    private function featurePosition(string $sPosition) : ?int
    {
        $sDigits = preg_replace('/[^0-9]/', "", $sPosition);
        return $sDigits === "" ? null : (int) $sDigits;
    }

    /**
     * Joins the description lines of a feature : free text continued over several lines, or the
     * /note="..." qualifier of the 2019_11 layout. A line broken right after a hyphen continues
     * the same word ("PROSITE-" + "ProRule"). The evidence tags ({ECO:0000255}), the /FTId, /id and
     * /evidence qualifiers and the final period are provenance and punctuation, not description.
     * @param   string[]    $aLines
     * @return  string
     */
    private function featureDescription(array $aLines) : string
    {
        $sText = "";
        foreach ($aLines as $sLine) {
            $sText .= ($sText === "" || substr($sText, -1) === "-" ? "" : " ") . $sLine;
        }
        if (preg_match('/\/note="([^"]*)"/', $sText, $aNote)) {
            $sText = $aNote[1];
        } else {
            $sText = preg_replace('/\s*\/\w+=.*$/', "", $sText);
        }
        // "Charge relay system. {ECO:0000250}." : the period before the tag goes with it.
        $sText = trim(preg_replace('/\.?\s*\{ECO:[^}]*\}/', "", $sText));
        return substr($sText, -1) === "." ? $this->rem_right($sText) : $sText;
    }

    /**
     * Parses DR lines
     * Format : DR   DATA_BANK; PRIMARY_IDENTIFIER; SECONDARY_IDENTIFIER[; ...]. [ISOFORM]
     * The first two identifiers are kept ; a trailing isoform reference ("[Q5K4E3-1]") is not
     * part of them.
     * Example: DR   EMBL; AJ627034; CAF25303.1; -; mRNA.
     * @throws \Exception
     */
    private function buildDRField()
    {
        $sLineData = trim(substr($this->aLines->current(), 3));
        $sLineData = trim(preg_replace('/\s*\[[^\]]*\]$/', "", $sLineData));
        if (substr($sLineData, -1) === ".") {
            $sLineData = $this->rem_right($sLineData);
        }
        $aDrLine = array_map('trim', explode(";", $sLineData));

        $oSpDatabank = new SpDatabank();
        $oSpDatabank->setPrimAcc($this->sequence->getPrimAcc());
        $oSpDatabank->setDbName($aDrLine[0]);
        $oSpDatabank->setPid1($aDrLine[1] ?? null);
        $oSpDatabank->setPid2($aDrLine[2] ?? null);

        $this->spDatabank[] = $oSpDatabank;
    }

    /**
     * Parses a reference : the RN line and the RP, RC, RX, RG, RA, RT and RL lines following it,
     * each of which may span several lines.
     * Example :
     * RN   [1]
     * RP   NUCLEOTIDE SEQUENCE [MRNA] (ISOFORM 1).
     * RC   TISSUE=Liver;
     * RX   PubMed=15536082; DOI=10.1074/jbc.M409139200;
     * RA   Cal S., Quesada V.;
     * RT   "Human polyserase-2, a novel enzyme.";
     * RL   J. Biol. Chem. 280:1953-1961(2005).
     * The original layout wrote RX as "MEDLINE; 87217060." and had no RT line.
     * @param   array           $aFlines
     * @param   array           $aReferences
     * @throws  \Exception
     */
    private function buildRNField(array $aFlines, &$aReferences) {
        $sMainLineData = trim(substr($this->aLines->current(), 3));
        $iRefNo = (int) trim($sMainLineData, "[] ");

        $aBlocks = [];
        while (true) {
            $sHead = substr($aFlines[$this->aLines->key() + 1] ?? "", 0, 2);
            if (!in_array($sHead, ["RP", "RC", "RX", "RG", "RA", "RT", "RL", "RM"], true)) {
                break;
            }
            $this->aLines->next();
            $aBlocks[$sHead][] = trim(substr($this->aLines->current(), 3));
        }
        $aJoined = array_map(function (array $aLines) {
            return implode(" ", $aLines);
        }, $aBlocks);

        $aInner = [];
        if (isset($aJoined["RP"])) {
            $aInner["RP"] = $aJoined["RP"];
        }
        if (isset($aJoined["RC"])) {
            $aInner["RC"] = rtrim($aJoined["RC"], "; ");
        }
        if (isset($aJoined["RM"])) {
            $aInner["RM"] = $aJoined["RM"];
        }
        if (isset($aJoined["RX"])) {
            $sRX = rtrim($aJoined["RX"], ".; ");
            if (preg_match_all('/(\w+)=([^;]+)/', $sRX, $aPairs, PREG_SET_ORDER)) {
                foreach ($aPairs as $aPair) {
                    $aInner["RX"][strtoupper($aPair[1])] = trim($aPair[2]);
                }
            } else {
                $aRXLine = array_map('trim', explode(";", $sRX));
                $aInner["RX"][strtoupper($aRXLine[0])] = $aRXLine[1] ?? "";
            }
        }
        $aAuthors = [];
        if (isset($aJoined["RG"])) {
            foreach (explode(";", $aJoined["RG"]) as $sGroup) {
                if (trim($sGroup) !== "") {
                    $aAuthors[] = trim($sGroup);
                }
            }
        }
        if (isset($aJoined["RA"])) {
            foreach (explode(",", rtrim($aJoined["RA"], "; ")) as $sAuthor) {
                if (trim($sAuthor) !== "") {
                    $aAuthors[] = trim($sAuthor);
                }
            }
        }
        if ($aAuthors !== []) {
            $aInner["RA"] = $aAuthors;
        }
        if (isset($aJoined["RT"])) {
            $aInner["RT"] = trim(rtrim($aJoined["RT"], ";"), '"');
        }
        if (isset($aJoined["RL"])) {
            $aInner["RL"] = $aJoined["RL"];
        }

        $aReferences[$iRefNo] = $aInner;
    }

    /**
     * Sets the gene names from the GN lines, as groups of synonyms.
     * Original layout, one line : GNAME1 OR GNAME2 ( (GNAME1, GNAME2) ), GNAME1 AND GNAME2
     * ( (GNAME1), (GNAME2) ), GNAME1 AND (GNAME2 OR GNAME3) ( (GNAME1), (GNAME2, GNAME3) ).
     * UniProt layout : "Name=PRSS36; Synonyms=A, B; OrderedLocusNames=...; ORFNames=...;", a line
     * holding only "and" separating two genes ; each gene gives one group, its name first.
     * @param   string[]    $aLines     The GN lines, label removed
     */
    private function buildGNField(array $aLines)
    {
        if ($aLines === []) {
            return;
        }
        $sLine = implode(" ", $aLines);

        if (strpos($sLine, "=") !== false) {
            foreach (preg_split('/\s+and\s+/', $sLine) as $sGene) {
                $aGroup = [];
                if (preg_match_all('/(\w+)=([^;]+);/', $sGene, $aPairs, PREG_SET_ORDER)) {
                    foreach ($aPairs as $aPair) {
                        foreach (explode(",", preg_replace('/\s*\{[^}]*\}/', "", $aPair[2])) as $sName) {
                            if (trim($sName) !== "") {
                                $aGroup[] = trim($sName);
                            }
                        }
                    }
                }
                if ($aGroup !== []) {
                    $this->geneNames[] = $aGroup;
                }
            }
            return;
        }

        // Remove the last character which is always a period.
        $sLine = rtrim($sLine, ". ");
        $aGename = [];

        // Strict comparison: a "(" opening the line sits at offset 0, which a loose test
        // reads as "not found" and routes to the wrong branch.
        if (strpos($sLine, "(") === false) { // GN Line does not contain any parentheses.
            // Ergo, it is made up of all OR's or AND's but not both.
            if (strpos($sLine, " OR ") !== false) {
                // Case 1: GNAME1 OR GNAME2.
                $aGename[] = preg_split("/ OR /", $sLine);
            } elseif (strpos($sLine, " AND ") !== false) {
                // Case 2: GNAME1 AND GNAME2 AND GNAME3.
                foreach(preg_split("/ AND /", $sLine) as $sGene) {
                    $aGename[] = array($sGene);
                }
            } else {
                // Case 0: GN GENENAME1. One gene name (no OR, AND).
                $aGename[] = array($sLine);
            }
        } else {
            // Case 3: GNAME1 AND (GNAME2 OR GNAME3) => ( (GNAME1), (GNAME2, GNAME3) )
            foreach(preg_split("/ AND /", $sLine) as $sGene) {
                if (substr($sGene, 0, 1) == "(") { // a list of 2 or more gene names OR'ed together
                    $aGename[] = preg_split("/ OR /", trim($sGene, "()"));
                } else { // singleton
                    $aGename[] = array($sGene);
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
        while($this->aLines->valid()) {
            $sLineLabel = $this->left($this->aLines->current(), 2);
            if ($sLineLabel == "//") { // end of file
                break;
            }
            $sSequence .= preg_replace('/\s+/', "", $this->aLines->current());
            $this->aLines->next();
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
     * Creates the Reference and Author entities, numbered as the RN lines number them.
     * @param       array       $aReferences
     * @throws      \Exception
     */
    private function makeRefArray(array $aReferences) {
        foreach($aReferences as $iRefNo => $value) {
            $oReference = new Reference();
            $oReference->setPrimAcc($this->sequence->getPrimAcc());
            $oReference->setRefno($iRefNo);
            if(isset($value["RT"])) {
                $oReference->setTitle($value["RT"]);
            }
            if(isset($value["RX"]["MEDLINE"])) {
                $oReference->setMedline($value["RX"]["MEDLINE"]);
            }
            if(isset($value["RX"]["PUBMED"])) {
                $oReference->setPubmed($value["RX"]["PUBMED"]);
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
            foreach($value["RA"] ?? [] as $sAuthor) {
                $oAuthor = new Author();
                $oAuthor->setPrimAcc($this->sequence->getPrimAcc());
                $oAuthor->setRefno($iRefNo);
                $oAuthor->setAuthor($sAuthor);
                $this->authors[] = $oAuthor;
            }
            $this->references[] = $oReference;
        }
    }
}
