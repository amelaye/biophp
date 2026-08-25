<?php
/**
 * EPD database parsing (Eukaryotic Promoter Database)
 * Freely inspired by BioPHP's project biophp.org
 * Created 25 August 2026
 * Last modified 25 August 2026
 */
namespace Amelaye\BioPHP\Domain\Parser;

use Amelaye\BioPHP\Domain\Database\Interfaces\ParseDatabaseInterface;

/**
 * Class ParseEpdManager
 * An EPD entry describes a promoter. It borrows the two character tag layout of EMBL, but its
 * fields carry different things, so this class exposes plain scalars rather than the
 * Sequence/Feature entities of ParseDbAbstractManager. The three dated events the format defines
 * are all read here, where the original BioPHP parser had commented two of them out.
 * @package Amelaye\BioPHP\Domain\Parser
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class ParseEpdManager implements ParseDatabaseInterface
{
    /**
     * @var string
     */
    private $entryName = "";

    /**
     * @var string
     */
    private $dataClass = "";

    /**
     * @var string
     */
    private $insiteType = "";

    /**
     * @var string
     */
    private $taxDiv = "";

    /**
     * @var array
     */
    private $accessions = [];

    /**
     * @var string
     */
    private $createDate = "";

    /**
     * @var string
     */
    private $createRel = "";

    /**
     * @var string
     */
    private $sequpdDate = "";

    /**
     * @var string
     */
    private $sequpdRel = "";

    /**
     * @var string
     */
    private $notupdDate = "";

    /**
     * @var string
     */
    private $notupdRel = "";

    /**
     * @var string
     */
    private $description = "";

    /**
     * @var string
     */
    private $comments = "";

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
        return "EPD";
    }

    /**
     * Tells whether a line opens a new EPD entry.
     * @param   string      $sLine          The line to analyze
     * @return  bool
     */
    public static function isEntryStart(string $sLine) : bool
    {
        return substr($sLine, 0, 2) == "ID";
    }

    /**
     * Tells whether a line closes an EPD entry.
     * @param   string      $sLine          The line to analyze
     * @return  bool
     */
    public static function isEntryEnd(string $sLine) : bool
    {
        return substr($sLine, 0, 2) == "//";
    }

    /**
     * Extracts the identifier uniquely naming an EPD entry, read from its AC line.
     * @param   array       $aFlines        The whole file, buffered
     * @param   string      $sLine          The line opening the entry
     * @return  string
     */
    public static function getEntryId(array $aFlines, string $sLine) : string
    {
        foreach($aFlines as $sCurrent) {
            if (substr($sCurrent, 0, 2) == "AC") {
                $aTokens = preg_split("/;/", trim(substr($sCurrent, 5)), -1, PREG_SPLIT_NO_EMPTY);

                return isset($aTokens[0]) ? trim($aTokens[0]) : "";
            }
        }

        return "";
    }

    /**
     * Parses an EPD data file and populates this manager's fields.
     * @param   array       $aFlines        The lines the script has to parse
     * @throws  \Exception
     */
    public function parseDataFile($aFlines)
    {
        try {
            $sDescription = "";
            $sComments    = "";

            foreach($aFlines as $sLine) {
                if (self::isEntryEnd($sLine)) {
                    break;
                }

                $sLabel = substr($sLine, 0, 2);
                $sData  = trim(substr($sLine, 5));

                switch($sLabel) {
                    case "ID":
                        $this->parseIdentifier($sData);
                        break;
                    case "AC":
                        foreach(preg_split("/;/", $sData, -1, PREG_SPLIT_NO_EMPTY) as $sAcc) {
                            $sAcc = trim($sAcc);
                            if ($sAcc != "") {
                                $this->accessions[] = $sAcc;
                            }
                        }
                        break;
                    case "DT":
                        $this->parseDate($sData);
                        break;
                    case "DE":
                        $sDescription .= $sData . " ";
                        break;
                    case "CC":
                        $sComments .= $sData . " ";
                        break;
                }
            }

            $this->description = trim($sDescription);
            $this->comments    = trim($sComments);
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }

    /**
     * Reads the ID line, which names the entry then qualifies it.
     * Example: ID   HS_ACHA_1     standard; single; HUM.
     * @param   string      $sData          The ID line, tag stripped
     */
    private function parseIdentifier(string $sData) : void
    {
        $aParts = preg_split("/;/", $sData);

        $aWords = preg_split("/\s+/", trim($aParts[0]), -1, PREG_SPLIT_NO_EMPTY);
        $this->entryName = isset($aWords[0]) ? $aWords[0] : "";
        $this->dataClass = isset($aWords[1]) ? $aWords[1] : "";

        $this->insiteType = isset($aParts[1]) ? trim($aParts[1]) : "";
        $this->taxDiv = isset($aParts[2]) ? rtrim(trim($aParts[2]), ".") : "";
    }

    /**
     * Reads a DT line, which dates one event and names the release it happened in.
     * Example: DT   15-JUN-2001 (REL. 75, CREATED)
     * @param   string      $sData          The DT line, tag stripped
     */
    private function parseDate(string $sData) : void
    {
        if (!preg_match("/^(\S+)\s*\(\s*REL\.\s*([^,]+),\s*(.+?)\s*\)\s*$/i", $sData, $aMatch)) {
            return;
        }

        $sDate    = trim($aMatch[1]);
        $sRelease = trim($aMatch[2]);
        $sEvent   = strtoupper(trim($aMatch[3]));

        if ($sEvent == "CREATED") {
            $this->createDate = $sDate;
            $this->createRel  = $sRelease;
        } elseif (strpos($sEvent, "LAST SEQUENCE UPDATE") !== false) {
            $this->sequpdDate = $sDate;
            $this->sequpdRel  = $sRelease;
        } elseif (strpos($sEvent, "LAST ANNOTATION UPDATE") !== false) {
            $this->notupdDate = $sDate;
            $this->notupdRel  = $sRelease;
        }
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
    public function getDataClass(): string
    {
        return $this->dataClass;
    }

    /**
     * @return string
     */
    public function getInsiteType(): string
    {
        return $this->insiteType;
    }

    /**
     * @return string
     */
    public function getTaxDiv(): string
    {
        return $this->taxDiv;
    }

    /**
     * @return array
     */
    public function getAccessions(): array
    {
        return $this->accessions;
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
    public function getCreateRel(): string
    {
        return $this->createRel;
    }

    /**
     * @return string
     */
    public function getSequpdDate(): string
    {
        return $this->sequpdDate;
    }

    /**
     * @return string
     */
    public function getSequpdRel(): string
    {
        return $this->sequpdRel;
    }

    /**
     * @return string
     */
    public function getNotupdDate(): string
    {
        return $this->notupdDate;
    }

    /**
     * @return string
     */
    public function getNotupdRel(): string
    {
        return $this->notupdRel;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getComments(): string
    {
        return $this->comments;
    }
}
