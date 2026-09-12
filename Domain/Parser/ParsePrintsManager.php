<?php
/**
 * PRINTS database parsing (protein fingerprints)
 * Freely inspired by BioPHP's project biophp.org
 * Created 25 August 2026
 * Last modified 12 September 2026
 */
namespace Amelaye\BioPHP\Domain\Parser;

use Amelaye\BioPHP\Domain\Database\Interfaces\ParseDatabaseInterface;

/**
 * Class ParsePrintsManager
 * A PRINTS entry describes a protein fingerprint, not a sequence, so this class exposes plain
 * scalars rather than the Sequence/Feature entities of ParseDbAbstractManager. Its data fields
 * are lower case three-character tags ending with a semicolon (gc;, gn;, ga;, gd;), unlike
 * every other format the library reads.
 * @package Amelaye\BioPHP\Domain\Parser
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class ParsePrintsManager implements ParseDatabaseInterface
{
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
    private $createDate = "";

    /**
     * @var string
     */
    private $updDate = "";

    /**
     * @var string
     */
    private $description = "";

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
        return "PRINTS";
    }

    /**
     * Tells whether a line opens a new PRINTS entry. The gc; field carries the entry name and
     * comes first.
     * @param   string      $sLine          The line to analyze
     * @return  bool
     */
    public static function isEntryStart(string $sLine) : bool
    {
        return substr($sLine, 0, 3) == "gc;";
    }

    /**
     * PRINTS entries carry no end-of-entry marker, so a stream holding several of them cannot be
     * split : reading stops at the end of the file. This mirrors the original BioPHP parser,
     * which had the same limitation.
     * @param   string      $sLine          The line to analyze
     * @return  bool
     */
    public static function isEntryEnd(string $sLine) : bool
    {
        return false;
    }

    /**
     * Extracts the identifier uniquely naming a PRINTS entry.
     * @param   array       $aFlines        The whole file, buffered
     * @param   string      $sLine          The line opening the entry
     * @return  string
     */
    public static function getEntryId(array $aFlines, string $sLine) : string
    {
        return trim(substr($sLine, 4));
    }

    /**
     * Parses a PRINTS data file and populates this manager's fields.
     * @param   array       $aFlines        The lines the script has to parse
     * @throws  \Exception
     */
    public function parseDataFile($aFlines)
    {
        $sDescription = "";

        foreach($aFlines as $sLine) {
            $sLabel = substr($sLine, 0, 3);
            $sData  = trim(substr($sLine, 4));

            switch($sLabel) {
                case "gc;":
                    $this->entryName = $sData;
                    break;
                case "gn;":
                    $this->entryType = $sData;
                    break;
                case "ga;":
                    $this->parseDates($sData);
                    break;
                case "gd;":
                    $sDescription .= $sData . " ";
                    break;
            }
        }

        $this->description = trim($sDescription);
    }

    /**
     * Reads the ga; line, which carries the creation date then any number of "KEY value" pairs.
     * Example: ga; 16-NOV-1995; UPDATE 06-JUN-1999
     * @param   string      $sData          The ga; line, tag stripped
     */
    private function parseDates(string $sData) : void
    {
        $aTokens = preg_split("/;/", $sData, -1, PREG_SPLIT_NO_EMPTY);
        if (count($aTokens) == 0) {
            return;
        }

        $this->createDate = trim(array_shift($aTokens));

        foreach($aTokens as $sPair) {
            $aWords = preg_split("/\s+/", trim($sPair), -1, PREG_SPLIT_NO_EMPTY);
            if (count($aWords) < 2) {
                continue;
            }

            $sKey = array_shift($aWords);
            if ($sKey == "UPDATE") {
                $this->updDate = implode(" ", $aWords);
            }
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
    public function getEntryType(): string
    {
        return $this->entryType;
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
    public function getUpdDate(): string
    {
        return $this->updDate;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }
}
