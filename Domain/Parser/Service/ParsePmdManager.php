<?php
/**
 * PMD database parsing (Protein Mutant Database)
 * Freely inspired by BioPHP's project biophp.org
 * Created 25 August 2026
 * Last modified 12 September 2026
 */
namespace Amelaye\BioPHP\Domain\Parser\Service;

use Amelaye\BioPHP\Domain\Database\Interfaces\ParseDatabaseInterface;

/**
 * Class ParsePmdManager
 * A PMD entry describes a mutation reported in the literature, not a sequence, so this class
 * exposes plain scalars rather than the Sequence/Feature entities of ParseDbAbstractManager.
 * The CHANGE, DISEASE and DBREF fields are left aside : their layout was never settled, and the
 * original BioPHP parser declared them without ever filling them.
 * @package Amelaye\BioPHP\Domain\Parser\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class ParsePmdManager implements ParseDatabaseInterface
{
    /**
     * Width of the label column, the data starting right after it.
     */
    private const LABEL_WIDTH = 16;

    /**
     * @var string
     */
    private $entryType = "";

    /**
     * @var string
     */
    private $entryNo = "";

    /**
     * @var string
     */
    private $mutationType = "";

    /**
     * @var string
     */
    private $articleNo = "";

    /**
     * @var array
     */
    private $authors = [];

    /**
     * @var string
     */
    private $medlineNo = "";

    /**
     * @var string
     */
    private $journal = "";

    /**
     * @var string
     */
    private $title = "";

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
        return "PMD";
    }

    /**
     * Tells whether a line opens a new PMD entry.
     * @param   string      $sLine          The line to analyze
     * @return  bool
     */
    public static function isEntryStart(string $sLine) : bool
    {
        return trim(substr($sLine, 0, self::LABEL_WIDTH)) == "ENTRY";
    }

    /**
     * Tells whether a line closes a PMD entry. PMD uses a triple slash, where the GenBank family
     * uses a double one.
     * @param   string      $sLine          The line to analyze
     * @return  bool
     */
    public static function isEntryEnd(string $sLine) : bool
    {
        return substr($sLine, 0, 3) == "///";
    }

    /**
     * Extracts the identifier uniquely naming a PMD entry, which is the entry type followed by
     * its number.
     * Example: ENTRY           A000300 - Artificial                    2607383
     * @param   array       $aFlines        The whole file, buffered
     * @param   string      $sLine          The line opening the entry
     * @return  string
     */
    public static function getEntryId(array $aFlines, string $sLine) : string
    {
        $aTokens = preg_split(
            "/\s+/",
            trim(substr($sLine, self::LABEL_WIDTH)),
            -1,
            PREG_SPLIT_NO_EMPTY
        );

        return isset($aTokens[0]) ? $aTokens[0] : "";
    }

    /**
     * Parses a PMD data file and populates this manager's fields.
     * @param   array       $aFlines        The lines the script has to parse
     * @throws  \Exception
     */
    public function parseDataFile($aFlines)
    {
        $aBuffers = ["AUTHORS" => "", "JOURNAL" => "", "TITLE" => ""];
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

            $sCurrent = "";

            switch($sLabel) {
                case "ENTRY":
                    $this->parseEntry($sData);
                    break;
                case "MEDLINE":
                    $this->medlineNo = $sData;
                    break;
                case "AUTHORS":
                case "JOURNAL":
                case "TITLE":
                    $aBuffers[$sLabel] = $sData . " ";
                    $sCurrent = $sLabel;
                    break;
            }
        }

        $this->journal = trim($aBuffers["JOURNAL"]);
        $this->title   = trim($aBuffers["TITLE"]);
        $this->authors = $this->splitAuthors($aBuffers["AUTHORS"]);
    }

    /**
     * Reads the ENTRY line. The entry type is its first character and the entry number the six
     * that follow, both at fixed positions.
     * @param   string      $sData          The ENTRY line, label stripped
     */
    private function parseEntry(string $sData) : void
    {
        $this->entryType = substr($sData, 0, 1);
        $this->entryNo   = substr($sData, 1, 6);

        $aTokens = preg_split("/\s+/", substr($sData, 10), -1, PREG_SPLIT_NO_EMPTY);
        $this->mutationType = isset($aTokens[0]) ? trim($aTokens[0]) : "";
        $this->articleNo    = isset($aTokens[1]) ? trim($aTokens[1]) : "";
    }

    /**
     * Authors are written as a list separated by commas and closed by an ampersand.
     * @param   string      $sAuthors       The accumulated AUTHORS lines
     * @return  array
     */
    private function splitAuthors(string $sAuthors) : array
    {
        $aAuthors = [];

        foreach(preg_split("/[,&]/", $sAuthors, -1, PREG_SPLIT_NO_EMPTY) as $sAuthor) {
            $sAuthor = trim($sAuthor);
            if ($sAuthor != "") {
                $aAuthors[] = $sAuthor;
            }
        }

        return $aAuthors;
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
    public function getEntryNo(): string
    {
        return $this->entryNo;
    }

    /**
     * @return string
     */
    public function getMutationType(): string
    {
        return $this->mutationType;
    }

    /**
     * @return string
     */
    public function getArticleNo(): string
    {
        return $this->articleNo;
    }

    /**
     * @return array
     */
    public function getAuthors(): array
    {
        return $this->authors;
    }

    /**
     * @return string
     */
    public function getMedlineNo(): string
    {
        return $this->medlineNo;
    }

    /**
     * @return string
     */
    public function getJournal(): string
    {
        return $this->journal;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }
}
