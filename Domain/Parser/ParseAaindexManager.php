<?php
/**
 * AAINDEX1 database parsing (physico-chemical indices of amino acids)
 * Freely inspired by BioPHP's project biophp.org
 * Created 25 August 2026
 * Last modified 25 August 2026
 */
namespace Amelaye\BioPHP\Domain\Parser;

use Amelaye\BioPHP\Domain\Database\Interfaces\ParseDatabaseInterface;

/**
 * Class ParseAaindexManager
 * An AAINDEX1 entry describes a numerical property of the twenty amino acids, not a sequence,
 * so this class exposes plain scalars rather than the Sequence/Feature entities of
 * ParseDbAbstractManager. Its data fields are single letter tags whose continuation lines start
 * with a space, the GenBank way. The C (correlated entries) and I (index values) fields are not
 * decomposed : they are fixed numeric tables the original BioPHP parser never touched either.
 * @package Amelaye\BioPHP\Domain\Parser
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class ParseAaindexManager implements ParseDatabaseInterface
{
    /**
     * @var string
     */
    private $accession = "";

    /**
     * @var string
     */
    private $description = "";

    /**
     * @var string
     */
    private $author = "";

    /**
     * @var string
     */
    private $title = "";

    /**
     * @var string
     */
    private $journal = "";

    /**
     * @var array
     */
    private $litRefs = [];

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
        return "AAINDEX";
    }

    /**
     * Tells whether a line opens a new AAINDEX1 entry.
     * @param   string      $sLine          The line to analyze
     * @return  bool
     */
    public static function isEntryStart(string $sLine) : bool
    {
        return substr($sLine, 0, 1) == "H";
    }

    /**
     * Tells whether a line closes an AAINDEX1 entry.
     * @param   string      $sLine          The line to analyze
     * @return  bool
     */
    public static function isEntryEnd(string $sLine) : bool
    {
        return substr($sLine, 0, 2) == "//";
    }

    /**
     * Extracts the identifier uniquely naming an AAINDEX1 entry.
     * @param   array       $aFlines        The whole file, buffered
     * @param   string      $sLine          The line opening the entry
     * @return  string
     */
    public static function getEntryId(array $aFlines, string $sLine) : string
    {
        return trim(substr($sLine, 2));
    }

    /**
     * Parses an AAINDEX1 data file and populates this manager's fields.
     * @param   array       $aFlines        The lines the script has to parse
     * @throws  \Exception
     */
    public function parseDataFile($aFlines)
    {
        try {
            $aBuffers = ["D" => "", "A" => "", "T" => "", "J" => ""];
            $sCurrent = "";

            foreach($aFlines as $sLine) {
                $sLabel = substr($sLine, 0, 1);
                $sData  = trim(substr($sLine, 2));

                if ($sLabel == " ") {
                    if ($sCurrent != "") {
                        $aBuffers[$sCurrent] .= $sData . " ";
                    }
                    continue;
                }

                $sCurrent = "";

                switch($sLabel) {
                    case "H":
                        $this->accession = $sData;
                        break;
                    case "D":
                    case "A":
                    case "T":
                    case "J":
                        $aBuffers[$sLabel] = $sData . " ";
                        $sCurrent = $sLabel;
                        break;
                    case "R":
                        $this->parseReferences($sData);
                        break;
                }

                if (self::isEntryEnd($sLine)) {
                    break;
                }
            }

            $this->description = trim($aBuffers["D"]);
            $this->author      = trim($aBuffers["A"]);
            $this->title       = trim($aBuffers["T"]);
            $this->journal     = trim($aBuffers["J"]);
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }

    /**
     * Reads the R line, which lists cross-references as "DBNAME:ID" pairs. The line is allowed
     * to be empty.
     * Example: R LIT:1810048b PMID:1575719
     * @param   string      $sData          The R line, tag stripped
     */
    private function parseReferences(string $sData) : void
    {
        if (trim($sData) == "") {
            return;
        }

        foreach(preg_split("/\s+/", $sData, -1, PREG_SPLIT_NO_EMPTY) as $sItem) {
            $aTokens = preg_split("/:/", $sItem, -1, PREG_SPLIT_NO_EMPTY);
            if (count($aTokens) < 2) {
                continue;
            }

            $this->litRefs[$aTokens[0]] = $aTokens[1];
        }
    }

    /**
     * @return string
     */
    public function getAccession(): string
    {
        return $this->accession;
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
    public function getAuthor(): string
    {
        return $this->author;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getJournal(): string
    {
        return $this->journal;
    }

    /**
     * Cross-references, keyed by database name.
     * @return array
     */
    public function getLitRefs(): array
    {
        return $this->litRefs;
    }
}
