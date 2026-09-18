<?php
/**
 * BLOCKS database parsing (conserved protein family motifs)
 * Freely inspired by BioPHP's project biophp.org
 * Created 25 August 2026
 * Last modified 12 September 2026
 */
namespace Amelaye\BioPHP\Domain\Parser\Service;

use Amelaye\BioPHP\Domain\Database\Interfaces\ParseDatabaseInterface;

/**
 * Class ParseBlocksManager
 * A BLOCKS entry describes an ungapped conserved region of a protein family, not a sequence,
 * so this class exposes plain scalars rather than the Sequence/Feature entities of
 * ParseDbAbstractManager.
 * @package Amelaye\BioPHP\Domain\Parser\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class ParseBlocksManager implements ParseDatabaseInterface
{
    /**
     * @var string
     */
    private $id = "";

    /**
     * @var string
     */
    private $accession = "";

    /**
     * @var int
     */
    private $distMin = 0;

    /**
     * @var int
     */
    private $distMax = 0;

    /**
     * @var string
     */
    private $description = "";

    /**
     * @var string
     */
    private $aaTriplet = "";

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
        return "BLOCKS";
    }

    /**
     * Tells whether a line opens a new BLOCKS entry.
     * @param   string      $sLine          The line to analyze
     * @return  bool
     */
    public static function isEntryStart(string $sLine) : bool
    {
        return substr($sLine, 0, 2) == "ID";
    }

    /**
     * Tells whether a line closes a BLOCKS entry.
     * @param   string      $sLine          The line to analyze
     * @return  bool
     */
    public static function isEntryEnd(string $sLine) : bool
    {
        return substr($sLine, 0, 2) == "//";
    }

    /**
     * Extracts the identifier uniquely naming a BLOCKS entry, read from its AC line.
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
     * Parses a BLOCKS data file and populates this manager's fields.
     * @param   array       $aFlines        The lines the script has to parse
     * @throws  \Exception
     */
    public function parseDataFile($aFlines)
    {
        $sDescription = "";

        foreach($aFlines as $sLine) {
            $sLabel = substr($sLine, 0, 2);
            $sData  = trim(substr($sLine, 5));

            switch($sLabel) {
                case "ID":
                    $aTokens = preg_split("/;/", $sData, -1, PREG_SPLIT_NO_EMPTY);
                    $this->id = isset($aTokens[0]) ? trim($aTokens[0]) : "";
                    break;
                case "AC":
                    $this->parseAccession($sData);
                    break;
                case "DE":
                    $sDescription .= $sData . " ";
                    break;
                case "BL":
                    $aTokens = preg_split("/;/", $sData, -1, PREG_SPLIT_NO_EMPTY);
                    $this->aaTriplet = isset($aTokens[0]) ? trim($aTokens[0]) : "";
                    break;
            }

            if ($sLabel == "//") {
                break;
            }
        }

        $this->description = trim($sDescription);
    }

    /**
     * Reads the AC line, which carries the accession then the distance range to the previous
     * block.
     * Example: AC   IPB002128C; distance from previous block=(30,31)
     * @param   string      $sData          The AC line, tag stripped
     */
    private function parseAccession(string $sData) : void
    {
        $aTokens = preg_split("/;/", $sData, -1, PREG_SPLIT_NO_EMPTY);

        $this->accession = isset($aTokens[0]) ? trim($aTokens[0]) : "";

        if (!isset($aTokens[1])) {
            return;
        }

        if (preg_match("/\((\d+)\s*,\s*(\d+)\)/", $aTokens[1], $aMatch)) {
            $this->distMin = (int) $aMatch[1];
            $this->distMax = (int) $aMatch[2];
        }
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getAccession(): string
    {
        return $this->accession;
    }

    /**
     * Minimum distance, in residues, from the previous block of the family.
     * @return int
     */
    public function getDistMin(): int
    {
        return $this->distMin;
    }

    /**
     * Maximum distance, in residues, from the previous block of the family.
     * @return int
     */
    public function getDistMax(): int
    {
        return $this->distMax;
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
    public function getAaTriplet(): string
    {
        return $this->aaTriplet;
    }
}
