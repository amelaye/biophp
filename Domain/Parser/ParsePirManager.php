<?php
/**
 * PIR database parsing (Protein Information Resource)
 * Freely inspired by BioPHP's project biophp.org
 * Created 25 August 2026
 * Last modified 25 August 2026
 */
namespace Amelaye\BioPHP\Domain\Parser;

use Amelaye\BioPHP\Domain\Database\Interfaces\ParseDatabaseInterface;

/**
 * Class ParsePirManager
 * A PIR entry qualifies most of its fields with "#key value" pairs, a pattern this class reads
 * once and reuses everywhere. It exposes plain scalars rather than the Sequence/Feature entities
 * of ParseDbAbstractManager.
 * @package Amelaye\BioPHP\Domain\Parser
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class ParsePirManager implements ParseDatabaseInterface
{
    /**
     * Width of the label column, the data starting right after it.
     */
    private const LABEL_WIDTH = 16;

    /**
     * @var string
     */
    private $entryName = "";

    /**
     * @var string
     */
    private $entryType = "";

    /**
     * @var string
     */
    private $title = "";

    /**
     * @var array
     */
    private $accessions = [];

    /**
     * @var string
     */
    private $organism = "";

    /**
     * @var string
     */
    private $species = "";

    /**
     * @var string
     */
    private $createDate = "";

    /**
     * @var string
     */
    private $seqrevDate = "";

    /**
     * @var string
     */
    private $txtchgDate = "";

    /**
     * @var int
     */
    private $length = 0;

    /**
     * @var float
     */
    private $molwt = 0.0;

    /**
     * @var string
     */
    private $checksum = "";

    /**
     * @var array
     */
    private $keywords = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
    }

    /**
     * The name this format is known by in the collection records and in DatabaseParserFactory.
     * @return string
     */
    public static function getFormat() : string
    {
        return "PIR";
    }

    /**
     * Tells whether a line opens a new PIR entry.
     * @param   string      $sLine          The line to analyze
     * @return  bool
     */
    public static function isEntryStart(string $sLine) : bool
    {
        return trim(substr($sLine, 0, self::LABEL_WIDTH)) == "ENTRY";
    }

    /**
     * Tells whether a line closes a PIR entry.
     * @param   string      $sLine          The line to analyze
     * @return  bool
     */
    public static function isEntryEnd(string $sLine) : bool
    {
        return substr($sLine, 0, 2) == "//";
    }

    /**
     * Extracts the identifier uniquely naming a PIR entry, which is the entry name preceding the
     * qualifiers.
     * Example: ENTRY           RHTDTO  #type complete
     * @param   array       $aFlines        The whole file, buffered
     * @param   string      $sLine          The line opening the entry
     * @return  string
     */
    public static function getEntryId(array $aFlines, string $sLine) : string
    {
        $aTokens = preg_split(
            "/#/",
            trim(substr($sLine, self::LABEL_WIDTH)),
            -1,
            PREG_SPLIT_NO_EMPTY
        );

        return isset($aTokens[0]) ? trim($aTokens[0]) : "";
    }

    /**
     * Splits a "value #key1 val1 #key2 val2" string into its leading value and its qualifiers.
     * @param   string      $sData          The field content
     * @return  array                       [leading value, [key => value]]
     */
    private static function splitQualifiers(string $sData) : array
    {
        $aTokens = preg_split("/#/", $sData, -1, PREG_SPLIT_NO_EMPTY);
        if (count($aTokens) == 0) {
            return ["", []];
        }

        // A field starting with a qualifier has no leading value of its own.
        $sLeading = substr(ltrim($sData), 0, 1) == "#" ? "" : trim((string) array_shift($aTokens));

        $aQualifiers = [];
        foreach($aTokens as $sPair) {
            $aWords = preg_split("/\s+/", trim($sPair), -1, PREG_SPLIT_NO_EMPTY);
            if (count($aWords) == 0) {
                continue;
            }

            $sKey = array_shift($aWords);
            $aQualifiers[$sKey] = implode(" ", $aWords);
        }

        return [$sLeading, $aQualifiers];
    }

    /**
     * Parses a PIR data file and populates this manager's fields.
     * @param   array       $aFlines        The lines the script has to parse
     * @throws  \Exception
     */
    public function parseDataFile($aFlines)
    {
        try {
            $aBuffers = [
                "ENTRY" => "", "TITLE" => "", "ORGANISM" => "", "DATE" => "",
                "ACCESSIONS" => "", "KEYWORDS" => "", "SUMMARY" => ""
            ];
            $sCurrent = "";

            foreach($aFlines as $sLine) {
                if (self::isEntryEnd($sLine)) {
                    break;
                }

                $sLabel = trim(substr($sLine, 0, self::LABEL_WIDTH));
                $sData  = trim(substr($sLine, self::LABEL_WIDTH));

                if ($sLabel == "") {
                    if ($sCurrent != "") {
                        $aBuffers[$sCurrent] .= $sData . " ";
                    }
                    continue;
                }

                $sCurrent = isset($aBuffers[$sLabel]) ? $sLabel : "";
                if ($sCurrent != "") {
                    $aBuffers[$sCurrent] .= $sData . " ";
                }
            }

            $this->readEntry($aBuffers["ENTRY"]);
            $this->readOrganism($aBuffers["ORGANISM"]);
            $this->readDates($aBuffers["DATE"]);
            $this->readSummary($aBuffers["SUMMARY"]);

            $this->title      = trim($aBuffers["TITLE"]);
            $this->accessions = $this->splitList($aBuffers["ACCESSIONS"]);
            $this->keywords   = $this->splitList($aBuffers["KEYWORDS"]);
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }

    /**
     * Example: ENTRY           RHTDTO  #type complete
     * @param   string      $sData          The accumulated ENTRY lines
     */
    private function readEntry(string $sData) : void
    {
        list($sName, $aQualifiers) = self::splitQualifiers($sData);

        $this->entryName = $sName;
        $this->entryType = isset($aQualifiers["type"]) ? $aQualifiers["type"] : "";
    }

    /**
     * Example: ORGANISM        #formal_name Oryctolagus cuniculus #common_name domestic rabbit
     * @param   string      $sData          The accumulated ORGANISM lines
     */
    private function readOrganism(string $sData) : void
    {
        list(, $aQualifiers) = self::splitQualifiers($sData);

        $this->organism = isset($aQualifiers["common_name"]) ? $aQualifiers["common_name"] : "";
        $this->species  = isset($aQualifiers["formal_name"]) ? $aQualifiers["formal_name"] : "";
    }

    /**
     * Example: DATE            15-Jun-2001 #sequence_revision 15-Jun-2001 #text_change 15-Jun-2001
     * @param   string      $sData          The accumulated DATE lines
     */
    private function readDates(string $sData) : void
    {
        list($sCreated, $aQualifiers) = self::splitQualifiers($sData);

        $this->createDate = $sCreated;
        $this->seqrevDate = isset($aQualifiers["sequence_revision"])
            ? $aQualifiers["sequence_revision"] : "";
        $this->txtchgDate = isset($aQualifiers["text_change"])
            ? $aQualifiers["text_change"] : "";
    }

    /**
     * Example: SUMMARY         #length 3  #molecular-weight 380  #checksum 465
     * @param   string      $sData          The accumulated SUMMARY lines
     */
    private function readSummary(string $sData) : void
    {
        list(, $aQualifiers) = self::splitQualifiers($sData);

        $this->length   = isset($aQualifiers["length"]) ? (int) $aQualifiers["length"] : 0;
        $this->molwt    = isset($aQualifiers["molecular-weight"])
            ? (float) $aQualifiers["molecular-weight"] : 0.0;
        $this->checksum = isset($aQualifiers["checksum"]) ? $aQualifiers["checksum"] : "";
    }

    /**
     * Accessions and keywords are semicolon separated lists which may run over several lines.
     * @param   string      $sData          The accumulated lines
     * @return  array
     */
    private function splitList(string $sData) : array
    {
        $aItems = [];
        foreach(preg_split("/;/", $sData, -1, PREG_SPLIT_NO_EMPTY) as $sItem) {
            $sItem = trim($sItem);
            if ($sItem != "") {
                $aItems[] = $sItem;
            }
        }

        return $aItems;
    }

    /**
     * @return string
     */
    public function getEntryName(): string
    {
        return $this->entryName;
    }

    /**
     * @return string
     */
    public function getEntryType(): string
    {
        return $this->entryType;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return array
     */
    public function getAccessions(): array
    {
        return $this->accessions;
    }

    /**
     * The common name of the source organism.
     * @return string
     */
    public function getOrganism(): string
    {
        return $this->organism;
    }

    /**
     * The scientific name of the source organism.
     * @return string
     */
    public function getSpecies(): string
    {
        return $this->species;
    }

    /**
     * @return string
     */
    public function getCreateDate(): string
    {
        return $this->createDate;
    }

    /**
     * @return string
     */
    public function getSeqrevDate(): string
    {
        return $this->seqrevDate;
    }

    /**
     * @return string
     */
    public function getTxtchgDate(): string
    {
        return $this->txtchgDate;
    }

    /**
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * @return float
     */
    public function getMolwt(): float
    {
        return $this->molwt;
    }

    /**
     * @return string
     */
    public function getChecksum(): string
    {
        return $this->checksum;
    }

    /**
     * @return array
     */
    public function getKeywords(): array
    {
        return $this->keywords;
    }
}
