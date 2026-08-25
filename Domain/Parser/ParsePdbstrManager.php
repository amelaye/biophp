<?php
/**
 * PDBSTR database parsing (structural families derived from PDB)
 * Freely inspired by BioPHP's project biophp.org
 * Created 25 August 2026
 * Last modified 25 August 2026
 */
namespace Amelaye\BioPHP\Domain\Parser;

use Amelaye\BioPHP\Domain\Database\Interfaces\ParseDatabaseInterface;

/**
 * Class ParsePdbstrManager
 * A PDBSTR entry describes one member of a structural family in a single MEMBER line, so this
 * class exposes plain scalars rather than the Sequence/Feature entities of
 * ParseDbAbstractManager. Not to be confused with ParsePdbManager, which reads the atomic
 * coordinate files themselves.
 * @package Amelaye\BioPHP\Domain\Parser
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class ParsePdbstrManager implements ParseDatabaseInterface
{
    /**
     * @var string
     */
    private $entryId = "";

    /**
     * @var string
     */
    private $molType = "";

    /**
     * @var int
     */
    private $length = 0;

    /**
     * @var string
     */
    private $entryGroup = "";

    /**
     * @var string
     */
    private $createDate = "";

    /**
     * @var string
     */
    private $updDate = "";

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
        return "PDBSTR";
    }

    /**
     * Tells whether a line opens a new PDBSTR entry.
     * @param   string      $sLine          The line to analyze
     * @return  bool
     */
    public static function isEntryStart(string $sLine) : bool
    {
        return trim(substr($sLine, 0, 12)) == "MEMBER";
    }

    /**
     * Tells whether a line closes a PDBSTR entry.
     * @param   string      $sLine          The line to analyze
     * @return  bool
     */
    public static function isEntryEnd(string $sLine) : bool
    {
        return substr($sLine, 0, 2) == "//";
    }

    /**
     * Extracts the identifier uniquely naming a PDBSTR entry.
     * @param   array       $aFlines        The whole file, buffered
     * @param   string      $sLine          The line opening the entry
     * @return  string
     */
    public static function getEntryId(array $aFlines, string $sLine) : string
    {
        $aTokens = preg_split("/\s+/", trim(substr($sLine, 12)), -1, PREG_SPLIT_NO_EMPTY);

        return isset($aTokens[0]) ? $aTokens[0] : "";
    }

    /**
     * Parses a PDBSTR data file and populates this manager's fields.
     * All six data items of a MEMBER line are mandatory, as in the original BioPHP parser.
     * Example: MEMBER      1YIC_01       108    PROTEIN      1YIC  97/02/18   97/07/23
     * @param   array       $aFlines        The lines the script has to parse
     * @throws  \Exception
     */
    public function parseDataFile($aFlines)
    {
        try {
            foreach($aFlines as $sLine) {
                $sLabel = trim(substr($sLine, 0, 12));
                $sData  = trim(substr($sLine, 12));

                if ($sLabel == "MEMBER") {
                    $aTokens = preg_split("/\s+/", $sData, -1, PREG_SPLIT_NO_EMPTY);

                    $this->entryId    = isset($aTokens[0]) ? $aTokens[0] : "";
                    $this->length     = isset($aTokens[1]) ? (int) $aTokens[1] : 0;
                    $this->molType    = isset($aTokens[2]) ? $aTokens[2] : "";
                    $this->entryGroup = isset($aTokens[3]) ? $aTokens[3] : "";
                    $this->createDate = isset($aTokens[4]) ? $aTokens[4] : "";
                    $this->updDate    = isset($aTokens[5]) ? $aTokens[5] : "";
                }

                if ($sLabel == "//") {
                    break;
                }
            }
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }

    /**
     * The identifier of the family member described by the MEMBER line.
     * @return string
     */
    public function getMemberId(): string
    {
        return $this->entryId;
    }

    /**
     * @return string
     */
    public function getMolType(): string
    {
        return $this->molType;
    }

    /**
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * @return string
     */
    public function getEntryGroup(): string
    {
        return $this->entryGroup;
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
}
