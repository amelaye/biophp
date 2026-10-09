<?php
/**
 * EMBL database parsing
 * Freely inspired by BioPHP's project biophp.org
 * Created 12 August 2026
 * Last modified 9 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Parser\Service;

use Amelaye\BioPHP\Domain\Database\Service\ParseDbAbstractManager;
use Amelaye\BioPHP\Domain\Sequence\Entity\Accession;
use Amelaye\BioPHP\Domain\Sequence\Entity\Author;
use Amelaye\BioPHP\Domain\Sequence\Entity\Keyword;
use Amelaye\BioPHP\Domain\Sequence\Entity\Reference;

/**
 * Class ParseEmblManager
 * EMBL flat files use the same "one field per record type, feature table shared with
 * GenBank" shape as GenBank's LOCUS/DEFINITION/ACCESSION/.../FEATURES/ORIGIN, only the
 * line tags differ (2-char codes like ID/AC/DE/OS/OC/RN/FT/SQ instead of full keywords).
 * This class mirrors ParseGenbankManager's decomposition (one private parseXxx() per
 * record type, \ArrayIterator lookahead) applied to those EMBL tags.
 * @package Amelaye\BioPHP\Domain\Parser\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class ParseEmblManager extends ParseDbAbstractManager
{
    /**
     * @var \ArrayIterator
     */
    private ?\ArrayIterator $aLines = null;

    /**
     * Whether an AC line has already been parsed for the entry being read. Only the very first
     * accession of the very first AC line is the entry's primary accession (already captured
     * from the ID line); every accession on every AC line after that, continuation lines
     * included, is a genuine secondary accession and must be kept.
     * @var bool
     */
    private bool $bAccessionLineSeen = false;

    /**
     * The name this format is known by in the collection records and in DatabaseParserFactory.
     * @return string
     */
    public static function getFormat() : string
    {
        return "EMBL";
    }

    /**
     * Tells whether a line opens a new EMBL entry.
     * @param   string      $sLine          The line to analyze
     * @return  bool
     */
    public static function isEntryStart(string $sLine) : bool
    {
        return substr($sLine, 0, 2) == "ID";
    }

    /**
     * Tells whether a line closes a EMBL entry.
     * @param   string      $sLine          The line to analyze
     * @return  bool
     */
    public static function isEntryEnd(string $sLine) : bool
    {
        return substr($sLine, 0, 2) == "//";
    }

    /**
     * Extracts the identifier uniquely naming a EMBL entry.
     * @param   array       $aFlines        The whole file, buffered
     * @param   string      $sLine          The line opening the entry
     * @return  string
     */
    public static function getEntryId(array $aFlines, string $sLine) : string
    {
        $aWords = preg_split("/;/", trim(substr($sLine, 5)));
        $sName = trim($aWords[0]);

        // Before release 87 the ID line opened with the entry name and its data class
        // ("HSERPG     standard; ...") : the name is no accession, the first AC line holds it, which
        // is what the parsed record is keyed by
        if (preg_match('/\s/', $sName)) {
            foreach ($aFlines as $sCurrent) {
                if (substr($sCurrent, 0, 2) == "AC") {
                    $sAccession = trim(explode(";", substr($sCurrent, 5))[0]);
                    if ($sAccession !== "") {
                        return $sAccession;
                    }
                }
            }

            return preg_split('/\s+/', $sName)[0];
        }

        return $sName;
    }

    /**
     * Parses an EMBL data file and returns a Seq object containing parsed data.
     * @param   array       $aFlines        The lines the script has to parse
     * @throws  \Exception
     */
    public function parseDataFile(array $aFlines) {
        $this->aLines = new \ArrayIterator($aFlines);
        // The feature table has GenBank's columns behind an "FT" tag : with the tag blanked out,
        // the lines read ahead look like GenBank's, and its feature parsing applies as is.
        $aFeatureLines = array_map(function ($sLine) {
            return substr($sLine, 0, 2) === "FT" ? "  " . substr($sLine, 2) : $sLine;
        }, $aFlines);

        foreach ($this->aLines as $lineno => $linestr) {
            switch (substr($this->aLines->current(), 0, 2)) {
                case "ID":
                    $this->parseId();
                    break;
                case "AC":
                    $this->parseAccession();
                    break;
                case "DT":
                    $this->parseDate();
                    break;
                case "DE":
                    $this->parseDescription($aFlines);
                    break;
                case "KW":
                    $this->parseKeywords($aFlines);
                    break;
                case "OS":
                    $this->parseOrganism($aFlines);
                    break;
                case "RN":
                    $this->parseReferences($aFlines);
                    break;
                case "FT":
                    $sKey = trim(substr($this->aLines->current(), 5, 15));
                    if (in_array($sKey, ParseGenbankManager::FEATURE_KEYS, true)) {
                        $this->parseInsdcFeature($this->aLines, $aFeatureLines, $sKey);
                    }
                    break;
                case "SQ":
                    $this->parseSequence();
                    break;
            }
        }
    }

    /**
     * Parses the ID line.
     * Format : ID   ENTRYNAME; SV VERSION; TOPOLOGY; MOLTYPE; DATACLASS; DIVISION; LENGTH BP.
     * Before release 87 (2006) : ID   ENTRYNAME  DATACLASS; [circular] MOLTYPE; DIVISION; LENGTH BP.
     * @throws  \Exception
     */
    private function parseId()
    {
        $aParts = array_map('trim', explode(";", trim(substr($this->aLines->current(), 5))));
        if (count($aParts) === 4) {
            $this->parseOldId($aParts);
            return;
        }

        $sEntryName = $aParts[0];
        $sVersion   = trim(str_replace("SV", "", $aParts[1]));
        $sTopology  = $aParts[2];
        $sMolType   = $aParts[3];
        $sDivision  = $aParts[5];
        $iLength    = (int) preg_replace("/\D/", "", $aParts[6]);

        $this->sequence->setPrimAcc($sEntryName);
        $this->sequence->setSeqLength($iLength);
        $this->sequence->setMolType($sMolType);

        $this->gbSequence->setPrimAcc($sEntryName);
        $this->gbSequence->setTopology(strtoupper($sTopology));
        $this->gbSequence->setDivision(strtoupper($sDivision));
        $this->gbSequence->setVersion($sEntryName . "." . $sVersion);
    }

    /**
     * Parses the ID line of the layout used before release 87, which has no sequence version. Its
     * first word is the entry name (HSERPG), a mnemonic, not the accession : the primary accession
     * is the first one of the AC line (see parseAccession()).
     * @param   string[]    $aParts     "ENTRYNAME DATACLASS", "[circular ]MOLTYPE", "DIVISION",
     * "LENGTH BP."
     */
    private function parseOldId(array $aParts)
    {
        $sEntryName = preg_split('/\s+/', $aParts[0])[0];
        $aMolecule = preg_split('/\s+/', $aParts[1]);
        $bCircular = count($aMolecule) > 1 && strtolower($aMolecule[0]) === "circular";

        $this->sequence->setEntryName($sEntryName);
        $this->sequence->setSeqLength((int) preg_replace("/\D/", "", $aParts[3]));
        $this->sequence->setMolType(end($aMolecule));

        $this->gbSequence->setTopology($bCircular ? "CIRCULAR" : "LINEAR");
        $this->gbSequence->setDivision(strtoupper($aParts[2]));
    }

    /**
     * Parses AC line(s).
     * Format : AC   AB012345;
     * @throws  \Exception
     */
    private function parseAccession()
    {
        $sLineData = trim(substr($this->aLines->current(), 5));
        $aAccessions = array_filter(array_map('trim', explode(";", $sLineData)));
        $aAccessions = array_values($aAccessions);

        if (!$this->bAccessionLineSeen) {
            if ($this->sequence->getPrimAcc() == "") {
                $this->sequence->setPrimAcc($aAccessions[0]);
                $this->gbSequence->setPrimAcc($aAccessions[0]);
            }
            $aAccessions = array_slice($aAccessions, 1);
            $this->bAccessionLineSeen = true;
        }

        foreach ($aAccessions as $sAccession) {
            $oAccession = new Accession();
            $oAccession->setPrimAcc($this->sequence->getPrimAcc());
            $oAccession->setAccession($sAccession);
            $this->accession[] = $oAccession;
        }
    }

    /**
     * Parses DT lines - only the "Created" one is kept, to mirror GenBank's single date field.
     * Format : DT   DD-MMM-YEAR (Rel. XX, Created)
     * @throws  \Exception
     */
    private function parseDate()
    {
        $sLineData = trim(substr($this->aLines->current(), 5));
        $aWords = preg_split("/\(/", $sLineData);
        if (!isset($aWords[1])) {
            return; // no "(Rel. XX, Created)" : nothing tells which date this is
        }
        $iFirstComma = strpos($aWords[1], ",");
        $sComment = strtoupper(trim(substr($aWords[1], $iFirstComma + 1)));

        if ($sComment == "CREATED)") {
            $this->sequence->setDate(trim($aWords[0]));
        }
    }

    /**
     * Parses DE line(s), possibly on several lines.
     * @param   array       $aFlines
     * @throws  \Exception
     */
    private function parseDescription(array $aFlines) {
        $sDescription = trim(substr($this->aLines->current(), 5));
        while (true) {
            $sHead = substr($aFlines[$this->aLines->key() + 1] ?? "", 0, 2);
            if ($sHead != "DE") {
                break;
            }
            $this->aLines->next();
            $sDescription .= " " . trim(substr($this->aLines->current(), 5));
        }
        $this->sequence->setDescription($sDescription);
    }

    /**
     * Parses KW line(s).
     * Format : KW   WORD1; WORD2; WORD3.
     * @throws  \Exception
     */
    private function parseKeywords(array $aFlines = [])
    {
        $sLineData = trim(substr($this->aLines->current(), 5));
        // A keyword wrapped over two KW lines is one keyword : the lines are joined before the split
        while (substr($aFlines[$this->aLines->key() + 1] ?? "", 0, 2) == "KW") {
            $this->aLines->next();
            $sLineData .= " " . trim(substr($this->aLines->current(), 5));
        }
        $sLineData = rtrim($sLineData, ".");
        $aKeywords = array_filter(array_map('trim', explode(";", $sLineData)));

        foreach ($aKeywords as $sKeyword) {
            $oKeyword = new Keyword();
            $oKeyword->setPrimAcc($this->sequence->getPrimAcc());
            $oKeyword->setKeywords($sKeyword);
            $this->keywords[] = $oKeyword;
        }
    }

    /**
     * Parses the OS line and the OC lines that follow it.
     * Format : OS   Species (common name)
     *          OC   Lineage; Tokens; Separated; By; Semicolons.
     * @param   array       $aFlines
     * @throws  \Exception
     */
    private function parseOrganism(array $aFlines) {
        $sSpecies = trim(substr($this->aLines->current(), 5));
        // A long species name goes on over further OS lines : each one used to replace the name
        // read before it, keeping the last part only.
        while (substr($aFlines[$this->aLines->key() + 1] ?? "", 0, 2) == "OS") {
            $this->aLines->next();
            $sSpecies .= " " . trim(substr($this->aLines->current(), 5));
        }
        $this->sequence->setSource($sSpecies);

        $aOrganism = [$sSpecies];
        // A rank holding a space ("Terrabacteria group") may be wrapped between two OC lines : the
        // lines are joined before the split, as the ranks end on ";" and not on the line.
        $sLineage = "";
        while (true) {
            $sHead = substr($aFlines[$this->aLines->key() + 1] ?? "", 0, 2);
            if ($sHead != "OC") {
                break;
            }
            $this->aLines->next();
            $sLineage .= " " . trim(substr($this->aLines->current(), 5));
        }
        foreach (explode(";", $sLineage) as $sToken) {
            if (trim($sToken) != "") {
                $aOrganism[] = trim($sToken);
            }
        }
        // The period closing the last OC line ends the lineage, it is no part of the last rank
        if (count($aOrganism) > 1) {
            $aOrganism[count($aOrganism) - 1] = rtrim($aOrganism[count($aOrganism) - 1], ".");
        }
        $this->sequence->setOrganism($aOrganism);
    }

    /**
     * Parses a reference block : the RN line, then RC, RP, RX, RG, RA, RT and RL lines, in that
     * order and each optional, any of them but RN possibly continued on several lines.
     * Example :
     * RN   [1]
     * RC   Comment.
     * RP   1-120
     * RX   DOI; 10.1016/0022-2836(89)90226-7.
     * RX   PUBMED; 12345678.
     * RG   The Consortium
     * RA   Smith J., Doe A.;
     * RT   "A test reference";
     * RL   J. Test Biol. 1(1):1-10(2020).
     * The author names keep their initials as written ("Smith J."), as GenBank's do.
     * @param   array       $aFlines
     * @throws  \Exception
     */
    private function parseReferences(array $aFlines) {
        $oReference = new Reference();
        $oReference->setPrimAcc($this->sequence->getPrimAcc());
        $oReference->setRefno((int) trim(trim(substr($this->aLines->current(), 5)), "[]"));

        $aBlocks = [];
        while (true) {
            $sHead = substr($aFlines[$this->aLines->key() + 1] ?? "", 0, 2);
            if (!in_array($sHead, ["RC", "RP", "RX", "RG", "RA", "RT", "RL"], true)) {
                break;
            }
            $this->aLines->next();
            $aBlocks[$sHead][] = trim(substr($this->aLines->current(), 5));
        }

        if (isset($aBlocks["RC"])) {
            $oReference->setComments(implode(" ", $aBlocks["RC"]));
        }
        if (isset($aBlocks["RP"])) {
            $oReference->setBaseRange(implode(" ", $aBlocks["RP"]));
        }
        foreach ($aBlocks["RX"] ?? [] as $sRx) {
            $aRx = array_map('trim', explode(";", rtrim($sRx, "."), 2));
            if (strtoupper($aRx[0]) == "PUBMED" && isset($aRx[1])) {
                $oReference->setPubmed($aRx[1]);
            }
            if (strtoupper($aRx[0]) == "MEDLINE" && isset($aRx[1])) {
                $oReference->setMedline($aRx[1]);
            }
        }

        $aAuthors = [];
        if (isset($aBlocks["RG"])) {
            $aAuthors[] = implode(" ", $aBlocks["RG"]);
        }
        if (isset($aBlocks["RA"])) {
            foreach (explode(",", rtrim(implode(" ", $aBlocks["RA"]), "; ")) as $sAuthor) {
                if (trim($sAuthor) !== "") {
                    $aAuthors[] = trim($sAuthor);
                }
            }
        }
        foreach ($aAuthors as $sAuthor) {
            $oAuthor = new Author();
            $oAuthor->setPrimAcc($this->sequence->getPrimAcc());
            $oAuthor->setRefno($oReference->getRefno());
            $oAuthor->setAuthor($sAuthor);
            $this->authors[] = $oAuthor;
        }

        if (isset($aBlocks["RT"])) {
            $sTitle = trim(implode(" ", $aBlocks["RT"]), " \";");
            if ($sTitle !== "") {
                $oReference->setTitle($sTitle);
            }
        }
        if (isset($aBlocks["RL"])) {
            $oReference->setJournal(implode(" ", $aBlocks["RL"]));
        }

        $this->references[] = $oReference;
    }

    /**
     * Parses the SQ header line and every sequence data line that follows it, up to "//" or the
     * end of the lines, a record cut short having none.
     * @throws  \Exception
     */
    private function parseSequence()
    {
        $sSequence = "";
        $this->aLines->next();
        while ($this->aLines->valid() && substr($this->aLines->current(), 0, 2) != "//") {
            $sLine = preg_replace("/\d+\s*$/", "", $this->aLines->current());
            $sSequence .= str_replace(" ", "", $sLine);
            $this->aLines->next();
        }
        $this->sequence->setSequence(trim($sSequence));
    }
}
