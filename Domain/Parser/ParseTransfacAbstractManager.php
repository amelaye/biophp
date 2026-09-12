<?php
/**
 * Shared reading of the TRANSFAC flat files
 * Freely inspired by BioPHP's project biophp.org
 * Created 12 September 2026
 * Last modified 12 September 2026
 */
namespace Amelaye\BioPHP\Domain\Parser;

use Amelaye\BioPHP\Domain\Database\Interfaces\ParseDatabaseInterface;

/**
 * Class ParseTransfacAbstractManager
 * TRANSFAC ships one flat file per kind of record - matrix.dat, gene.dat, class.dat, cell.dat,
 * factor.dat, site.dat - which all share the same grammar : a two-letter label in the first
 * columns, its data from the fifth, "XX" lines spacing the fields apart and "//" closing the
 * record. That grammar, the AC/ID/DT fields every record carries and the way a field spanning
 * several lines is joined, live here; each subclass adds the fields of its own record.
 * The date reads by tokens rather than at the fixed offset Legacy/transfac.inc.php used for
 * five of its six record types : that offset assumed a two-digit year ("20.06.90") and misses
 * the marker as soon as the file writes the year in full, which TRANSFAC has long been doing.
 * @package Amelaye\BioPHP\Domain\Parser
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
abstract class ParseTransfacAbstractManager implements ParseDatabaseInterface
{
    /**
     * @var string
     */
    protected $accession = "";

    /**
     * @var string
     */
    protected $id = "";

    /**
     * @var string
     */
    protected $dateCreated = "";

    /**
     * @var string
     */
    protected $dateUpdated = "";

    /**
     * Constructor.
     */
    public function __construct()
    {
    }

    /**
     * Tells whether a line opens a new TRANSFAC record, which the accession number does.
     * @param   string      $sLine          The line to analyze
     * @return  bool
     */
    public static function isEntryStart(string $sLine) : bool
    {
        return substr($sLine, 0, 2) == "AC";
    }

    /**
     * Tells whether a line closes a TRANSFAC record.
     * @param   string      $sLine          The line to analyze
     * @return  bool
     */
    public static function isEntryEnd(string $sLine) : bool
    {
        return substr($sLine, 0, 2) == "//";
    }

    /**
     * Extracts the identifier uniquely naming a TRANSFAC record, its accession number.
     * @param   array       $aFlines        The whole file, buffered
     * @param   string      $sLine          The line opening the entry
     * @return  string
     */
    public static function getEntryId(array $aFlines, string $sLine) : string
    {
        foreach($aFlines as $sCurrent) {
            if (self::readLabel($sCurrent) == "AC") {
                return self::readData($sCurrent);
            }
        }

        return "";
    }

    /**
     * Reads the two-letter label a line carries.
     * @param   string      $sLine          The line to analyze
     * @return  string
     */
    protected static function readLabel(string $sLine) : string
    {
        return trim(substr($sLine, 0, 2));
    }

    /**
     * Reads the data a line carries, which starts at its fifth column.
     * @param   string      $sLine          The line to analyze
     * @return  string
     */
    protected static function readData(string $sLine) : string
    {
        return trim(substr($sLine, 4));
    }

    /**
     * Reads the fields every TRANSFAC record carries, and says whether it did : a subclass
     * calls this first and only looks at the labels of its own record when it did not.
     * @param   string      $sLabel
     * @param   string      $sData
     * @return  bool
     */
    protected function parseCommonField($sLabel, $sData)
    {
        switch ($sLabel) {
            case "AC":
                $this->accession = $sData;
                return true;
            case "ID":
                $this->id = $sData;
                return true;
            case "DT":
                $this->parseDate($sData);
                return true;
        }

        return false;
    }

    /**
     * Parses one DT line, which dates either the creation or the last update of the record and
     * may carry a time of day.
     * Format : DT  20.06.90 11:00:03 (created); ewi.
     * @param   string      $sData
     */
    protected function parseDate($sData)
    {
        $aTokens = preg_split("/\s+/", $sData, -1, PREG_SPLIT_NO_EMPTY);

        $sMarker = "";
        $sDate   = "";
        foreach ($aTokens as $iIndex => $sToken) {
            if ($sToken == "(created);" || $sToken == "(updated);") {
                $sMarker = $sToken;
                $sDate   = implode(" ", array_slice($aTokens, 0, $iIndex));
                break;
            }
        }

        if ($sMarker == "(created);") {
            $this->dateCreated = $sDate;
        }
        if ($sMarker == "(updated);") {
            $this->dateUpdated = $sDate;
        }
    }

    /**
     * Appends the data of a line to a field written over several of them.
     * @param   string      $sBuffer
     * @param   string      $sData
     * @return  string
     */
    protected function append($sBuffer, $sData)
    {
        return trim($sBuffer . " " . $sData);
    }

    /**
     * Splits a field listing its values, semicolon separated and possibly closed by a period,
     * into its items.
     * @param   string      $sText
     * @param   string      $sSeparator
     * @return  array
     */
    protected function splitList($sText, $sSeparator = ";")
    {
        $sText = rtrim(trim($sText), ".");
        if ($sText == "") {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode($sSeparator, $sText))));
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
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getDateCreated(): string
    {
        return $this->dateCreated;
    }

    /**
     * @return string
     */
    public function getDateUpdated(): string
    {
        return $this->dateUpdated;
    }
}
