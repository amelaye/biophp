<?php
/**
 * ProDom database parsing (protein domain families)
 * Freely inspired by BioPHP's project biophp.org
 * Created 25 August 2026
 * Last modified 12 September 2026
 */
namespace Amelaye\BioPHP\Domain\Parser;

use Amelaye\BioPHP\Domain\Database\Interfaces\ParseDatabaseInterface;

/**
 * Class ParseProdomManager
 * A ProDom entry describes a family of protein domains, not a sequence, so this class exposes
 * plain scalars rather than the Sequence/Feature entities of ParseDbAbstractManager. Only the
 * fields the original BioPHP parser read are decomposed : the alignment block (AL/CO) and the
 * cross-references (DR) are left aside, as they were there.
 * @package Amelaye\BioPHP\Domain\Parser
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class ParseProdomManager implements ParseDatabaseInterface
{
    /**
     * @var string
     */
    private $entryNo = "";

    /**
     * @var string
     */
    private $accession = "";

    /**
     * @var string
     */
    private $release = "";

    /**
     * @var int
     */
    private $domainCount = 0;

    /**
     * @var array
     */
    private $freqNames = [];

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
        return "PRODOM";
    }

    /**
     * Tells whether a line opens a new ProDom entry.
     * @param   string      $sLine          The line to analyze
     * @return  bool
     */
    public static function isEntryStart(string $sLine) : bool
    {
        return substr($sLine, 0, 2) == "ID";
    }

    /**
     * Tells whether a line closes a ProDom entry.
     * @param   string      $sLine          The line to analyze
     * @return  bool
     */
    public static function isEntryEnd(string $sLine) : bool
    {
        return substr($sLine, 0, 2) == "//";
    }

    /**
     * Extracts the identifier uniquely naming a ProDom entry, read from its AC line.
     * @param   array       $aFlines        The whole file, buffered
     * @param   string      $sLine          The line opening the entry
     * @return  string
     */
    public static function getEntryId(array $aFlines, string $sLine) : string
    {
        foreach($aFlines as $sCurrent) {
            if (substr($sCurrent, 0, 2) == "AC") {
                return trim(substr($sCurrent, 5));
            }
        }

        return "";
    }

    /**
     * Parses a ProDom data file and populates this manager's fields.
     * @param   array       $aFlines        The lines the script has to parse
     * @throws  \Exception
     */
    public function parseDataFile($aFlines)
    {
        foreach($aFlines as $sLine) {
            $sLabel = substr($sLine, 0, 2);
            $sData  = trim(substr($sLine, 5));

            switch($sLabel) {
                case "ID":
                    // The ID line is the only one whose data starts at column 3.
                    $this->parseIdentifier(trim(substr($sLine, 3)));
                    break;
                case "AC":
                    $this->accession = $sData;
                    break;
                case "KW":
                    $this->parseKeywords($sData);
                    break;
            }

            if (self::isEntryEnd($sLine)) {
                break;
            }
        }
    }

    /**
     * Reads the ID line, which carries the entry number, the release and the domain count.
     * Example: ID 20167 p2002.1                           10 seq.
     * @param   string      $sData          The ID line, tag stripped
     */
    private function parseIdentifier(string $sData) : void
    {
        $aTokens = preg_split("/\s+/", $sData, -1, PREG_SPLIT_NO_EMPTY);

        $this->entryNo = isset($aTokens[0]) ? $aTokens[0] : "";
        // The release is prefixed with a "p" in the file, which is not part of its name.
        $this->release = isset($aTokens[1]) ? substr($aTokens[1], 1) : "";
        $this->domainCount = isset($aTokens[2]) ? (int) $aTokens[2] : 0;
    }

    /**
     * Reads the KW line, which carries the frequent names and their occurrence count, then the
     * keywords, the two halves being separated by a double slash.
     * Example: KW   FADR(2) Y586(1) // COMPLETE PROTEOME DNA-BINDING FATTY
     * @param   string      $sData          The KW line, tag stripped
     */
    private function parseKeywords(string $sData) : void
    {
        $aHalves = preg_split("/\/\//", $sData, -1, PREG_SPLIT_NO_EMPTY);

        if (isset($aHalves[0])) {
            foreach(preg_split("/\s+/", trim($aHalves[0]), -1, PREG_SPLIT_NO_EMPTY) as $sName) {
                if (preg_match("/^(.+)\((\d+)\)$/", $sName, $aMatch)) {
                    $this->freqNames[$aMatch[1]] = (int) $aMatch[2];
                    continue;
                }
                // A name written without its count is kept all the same : dropping it would
                // lose a name the entry does carry, where a count of zero says it went unread.
                $this->freqNames[$sName] = 0;
            }
        }

        if (isset($aHalves[1])) {
            $this->keywords = preg_split("/\s+/", trim($aHalves[1]), -1, PREG_SPLIT_NO_EMPTY);
        }
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
    public function getAccession(): string
    {
        return $this->accession;
    }

    /**
     * @return string
     */
    public function getRelease(): string
    {
        return $this->release;
    }

    /**
     * @return int
     */
    public function getDomainCount(): int
    {
        return $this->domainCount;
    }

    /**
     * Frequent protein names, keyed by name, valued by how many times they occur.
     * @return array
     */
    public function getFreqNames(): array
    {
        return $this->freqNames;
    }

    /**
     * @return array
     */
    public function getKeywords(): array
    {
        return $this->keywords;
    }
}
