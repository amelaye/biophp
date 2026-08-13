<?php
/**
 * EMBL database parsing
 * Freely inspired by BioPHP's project biophp.org
 * Created 12 August 2026
 * Last modified 12 August 2026
 */
namespace Amelaye\BioPHP\Domain\Database\Service;

use Amelaye\BioPHP\Domain\Sequence\Entity\Accession;
use Amelaye\BioPHP\Domain\Sequence\Entity\Author;
use Amelaye\BioPHP\Domain\Sequence\Entity\Feature;
use Amelaye\BioPHP\Domain\Sequence\Entity\Keyword;
use Amelaye\BioPHP\Domain\Sequence\Entity\Reference;

/**
 * Class ParseEmblManager
 * EMBL flat files use the same "one field per record type, feature table shared with
 * GenBank" shape as GenBank's LOCUS/DEFINITION/ACCESSION/.../FEATURES/ORIGIN, only the
 * line tags differ (2-char codes like ID/AC/DE/OS/OC/RN/FT/SQ instead of full keywords).
 * This class mirrors ParseGenbankManager's decomposition (one private parseXxx() per
 * record type, \ArrayIterator lookahead) applied to those EMBL tags.
 * @package Amelaye\BioPHP\Domain\Database\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class ParseEmblManager extends ParseDbAbstractManager
{
    /**
     * @var \ArrayIterator
     */
    private $aLines;

    /**
     * Parses an EMBL data file and returns a Seq object containing parsed data.
     * @param   array       $aFlines        The lines the script has to parse
     * @throws  \Exception
     */
    public function parseDataFile($aFlines)
    {
        try {
            $this->aLines = new \ArrayIterator($aFlines);

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
                        $this->parseKeywords();
                        break;
                    case "OS":
                        $this->parseOrganism($aFlines);
                        break;
                    case "RN":
                        $this->parseReferences($aFlines);
                        break;
                    case "FT":
                        if (trim(substr($this->aLines->current(), 5, 15)) != "") {
                            $this->parseFeatures($aFlines);
                        }
                        break;
                    case "SQ":
                        $this->parseSequence();
                        break;
                }
            }
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Parses the ID line.
     * Format : ID   ENTRYNAME; SV VERSION; TOPOLOGY; MOLTYPE; DATACLASS; DIVISION; LENGTH BP.
     * @throws  \Exception
     */
    private function parseId()
    {
        try {
            $aParts = array_map('trim', explode(";", trim(substr($this->aLines->current(), 5))));

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
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Parses AC line(s).
     * Format : AC   AB012345;
     * @throws  \Exception
     */
    private function parseAccession()
    {
        try {
            $sLineData = trim(substr($this->aLines->current(), 5));
            $aAccessions = array_filter(array_map('trim', explode(";", $sLineData)));
            $aAccessions = array_values($aAccessions);

            if ($this->sequence->getPrimAcc() == "") {
                $this->sequence->setPrimAcc($aAccessions[0]);
            }

            foreach (array_slice($aAccessions, 1) as $sAccession) {
                $oAccession = new Accession();
                $oAccession->setPrimAcc($this->sequence->getPrimAcc());
                $oAccession->setAccession($sAccession);
                $this->accession[] = $oAccession;
            }
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Parses DT lines - only the "Created" one is kept, to mirror GenBank's single date field.
     * Format : DT   DD-MMM-YEAR (Rel. XX, Created)
     * @throws  \Exception
     */
    private function parseDate()
    {
        try {
            $sLineData = trim(substr($this->aLines->current(), 5));
            $aWords = preg_split("/\(/", $sLineData);
            $iFirstComma = strpos($aWords[1], ",");
            $sComment = strtoupper(trim(substr($aWords[1], $iFirstComma + 1)));

            if ($sComment == "CREATED)") {
                $this->sequence->setDate(trim($aWords[0]));
            }
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Parses DE line(s), possibly on several lines.
     * @param   array       $aFlines
     * @throws  \Exception
     */
    private function parseDescription($aFlines)
    {
        try {
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
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Parses KW line(s).
     * Format : KW   WORD1; WORD2; WORD3.
     * @throws  \Exception
     */
    private function parseKeywords()
    {
        try {
            $sLineData = trim(substr($this->aLines->current(), 5));
            $sLineData = rtrim($sLineData, ".");
            $aKeywords = array_filter(array_map('trim', explode(";", $sLineData)));

            foreach ($aKeywords as $sKeyword) {
                $oKeyword = new Keyword();
                $oKeyword->setPrimAcc($this->sequence->getPrimAcc());
                $oKeyword->setKeywords($sKeyword);
                $this->keywords[] = $oKeyword;
            }
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Parses the OS line and the OC lines that follow it.
     * Format : OS   Species (common name)
     *          OC   Lineage; Tokens; Separated; By; Semicolons.
     * @param   array       $aFlines
     * @throws  \Exception
     */
    private function parseOrganism($aFlines)
    {
        try {
            $sSpecies = trim(substr($this->aLines->current(), 5));
            $this->sequence->setSource($sSpecies);

            $aOrganism = [$sSpecies];
            while (true) {
                $sHead = substr($aFlines[$this->aLines->key() + 1] ?? "", 0, 2);
                if ($sHead != "OC") {
                    break;
                }
                $this->aLines->next();
                $aTokens = explode(";", trim(substr($this->aLines->current(), 5)));
                foreach ($aTokens as $sToken) {
                    if (trim($sToken) != "") {
                        $aOrganism[] = trim($sToken);
                    }
                }
            }
            $this->sequence->setOrganism($aOrganism);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Parses a reference block: RN, then optionally RP, RX, RA, RT, RL.
     * @param   array       $aFlines
     * @throws  \Exception
     */
    private function parseReferences($aFlines)
    {
        try {
            $oReference = new Reference();
            $oReference->setPrimAcc($this->sequence->getPrimAcc());
            $oReference->setRefno(trim(trim(substr($this->aLines->current(), 5)), "[]"));

            $this->aLines->next();

            if (substr($this->aLines->current(), 0, 2) == "RP") {
                $oReference->setBaseRange(trim(substr($this->aLines->current(), 5)));
                $this->aLines->next();
            }

            while (substr($this->aLines->current(), 0, 2) == "RX") {
                $sRx = rtrim(trim(substr($this->aLines->current(), 5)), ".");
                $aRx = array_map('trim', explode(";", $sRx));
                if (strtoupper($aRx[0]) == "PUBMED" && isset($aRx[1])) {
                    $oReference->setPubmed($aRx[1]);
                }
                $this->aLines->next();
            }

            if (substr($this->aLines->current(), 0, 2) == "RA") {
                $sAuthors = rtrim(trim(substr($this->aLines->current(), 5)), ";");
                $sAuthors = str_replace(".", "", $sAuthors);
                $aAuthors = explode(",", $sAuthors);
                foreach ($aAuthors as $sAuthor) {
                    $oAuthor = new Author();
                    $oAuthor->setPrimAcc($this->sequence->getPrimAcc());
                    $oAuthor->setRefno($oReference->getRefno());
                    $oAuthor->setAuthor(trim($sAuthor));
                    $this->authors[] = $oAuthor;
                }
                $this->aLines->next();
            }

            if (substr($this->aLines->current(), 0, 2) == "RT") {
                $sTitle = trim(substr($this->aLines->current(), 5));
                while (true) {
                    $sHead = substr($aFlines[$this->aLines->key() + 1] ?? "", 0, 2);
                    if ($sHead != "RT") {
                        break;
                    }
                    $this->aLines->next();
                    $sTitle .= " " . trim(substr($this->aLines->current(), 5));
                }
                $sTitle = trim($sTitle, " \";");
                $oReference->setTitle($sTitle);
                $this->aLines->next();
            }

            if (substr($this->aLines->current(), 0, 2) == "RL") {
                $oReference->setJournal(trim(substr($this->aLines->current(), 5)));
            }

            $this->references[] = $oReference;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Parses one feature: the FT key/location line, then every /qualifier="value" line
     * that follows it until the next feature key or the end of the feature table.
     * @param   array       $aFlines
     * @throws  \Exception
     */
    private function parseFeatures($aFlines)
    {
        try {
            $sKey = trim(substr($this->aLines->current(), 5, 15));
            $sLocation = str_replace(["complement(", "join(", ")"], "", trim(substr($this->aLines->current(), 21)));
            $aBounds = explode("..", $sLocation);

            $sQualifiers = "";
            while (true) {
                $sNextLine = $aFlines[$this->aLines->key() + 1] ?? "";
                if (substr($sNextLine, 0, 2) != "FT" || trim(substr($sNextLine, 5, 15)) != "") {
                    break;
                }
                $this->aLines->next();
                $sQualifiers .= " " . trim(substr($this->aLines->current(), 21));
            }

            $aQualifiers = array_filter(preg_split("/\s+\//", trim($sQualifiers)));
            foreach ($aQualifiers as $sQualifier) {
                $aQualifier = explode("=", str_replace('"', "", ltrim($sQualifier, "/")), 2);
                $oFeature = new Feature();
                $oFeature->setPrimAcc($this->sequence->getPrimAcc());
                $oFeature->setFtKey($sKey);
                $oFeature->setFtQual($aQualifier[0]);
                $oFeature->setFtValue($aQualifier[1] ?? "");
                $oFeature->setFtFrom((int) ($aBounds[0] ?? 0));
                $oFeature->setFtTo((int) ($aBounds[1] ?? 0));
                $this->features[] = $oFeature;
            }
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Parses the SQ header line and every sequence data line that follows it, up to "//".
     * @throws  \Exception
     */
    private function parseSequence()
    {
        try {
            $sSequence = "";
            $this->aLines->next();
            while (substr($this->aLines->current(), 0, 2) != "//") {
                $sLine = preg_replace("/\d+\s*$/", "", $this->aLines->current());
                $sSequence .= str_replace(" ", "", $sLine);
                $this->aLines->next();
            }
            $this->sequence->setSequence(trim($sSequence));
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
}
