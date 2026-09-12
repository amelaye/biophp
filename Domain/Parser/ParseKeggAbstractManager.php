<?php
/**
 * Shared reading of the KEGG flat files
 * Freely inspired by BioPHP's project biophp.org
 * Created 12 September 2026
 * Last modified 12 September 2026
 */
namespace Amelaye\BioPHP\Domain\Parser;

use Amelaye\BioPHP\Domain\Database\Interfaces\ParseDatabaseInterface;

/**
 * Class ParseKeggAbstractManager
 * KEGG describes metabolism through several files - compound, enzyme, reaction, ortholog,
 * genome - written in one same grammar : a label in the first twelve columns, its data from the
 * thirteenth, a line leaving the label column blank continuing the field above it, and "///"
 * closing the record. That grammar and the ENTRY and NAME fields every record carries live
 * here; each subclass adds the fields of its own file.
 * Where Legacy/kegg.inc.php kept one flag per field and closed a field only when the next one
 * opened, the fields are gathered first and read afterwards, which spares each subclass the
 * three-branch dance and closes the last field of a record whether or not "///" follows it.
 * @package Amelaye\BioPHP\Domain\Parser
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
abstract class ParseKeggAbstractManager implements ParseDatabaseInterface
{
    /**
     * @var string
     */
    protected $entry = "";

    /**
     * @var array
     */
    protected $names = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
    }

    /**
     * Tells whether a line opens a new KEGG record.
     * @param   string      $sLine          The line to analyze
     * @return  bool
     */
    public static function isEntryStart(string $sLine) : bool
    {
        return self::readLabel($sLine) == "ENTRY";
    }

    /**
     * Tells whether a line closes a KEGG record. KEGG closes on three slashes where most flat
     * files use two.
     * @param   string      $sLine          The line to analyze
     * @return  bool
     */
    public static function isEntryEnd(string $sLine) : bool
    {
        return substr($sLine, 0, 3) == "///";
    }

    /**
     * Extracts the identifier uniquely naming a KEGG record.
     * @param   array       $aFlines        The whole file, buffered
     * @param   string      $sLine          The line opening the entry
     * @return  string
     */
    public static function getEntryId(array $aFlines, string $sLine) : string
    {
        foreach($aFlines as $sCurrent) {
            if (self::readLabel($sCurrent) == "ENTRY") {
                return self::readEntryId(self::readData($sCurrent));
            }
        }

        return "";
    }

    /**
     * Reads the identifier out of an ENTRY line. Its last word names the kind of record rather
     * than the record itself - "C00031  Compound", "EC 2.7.1.1  Enzyme" - so the identifier is
     * what comes before it, which for an enzyme is the two words "EC" and its number.
     * @param   string      $sData
     * @return  string
     */
    protected static function readEntryId(string $sData) : string
    {
        $aWords = preg_split("/\s+/", trim($sData), -1, PREG_SPLIT_NO_EMPTY);
        if ($aWords == []) {
            return "";
        }
        if (count($aWords) == 1) {
            return $aWords[0];
        }

        array_pop($aWords);

        return implode(" ", $aWords);
    }

    /**
     * Reads the label a line carries in its first twelve columns.
     * @param   string      $sLine          The line to analyze
     * @return  string
     */
    protected static function readLabel(string $sLine) : string
    {
        return trim(substr($sLine, 0, 12));
    }

    /**
     * Reads the data a line carries, which starts at its thirteenth column.
     * @param   string      $sLine          The line to analyze
     * @return  string
     */
    protected static function readData(string $sLine) : string
    {
        return trim(substr($sLine, 12));
    }

    /**
     * Gathers a record into its fields : one entry per label, holding the data of its own line
     * and of every continuation line below it.
     * @param   array       $aFlines        The lines the script has to parse
     * @return  array
     */
    protected function readFields($aFlines)
    {
        $aFields  = [];
        $sCurrent = "";

        foreach ($aFlines as $sLine) {
            if (self::isEntryEnd($sLine)) {
                break;
            }

            $sLabel = self::readLabel($sLine);
            if ($sLabel != "") {
                $sCurrent = $sLabel;
            }
            if ($sCurrent == "" || trim($sLine) == "") {
                continue;
            }

            $aFields[$sCurrent][] = self::readData($sLine);
        }

        $this->entry = isset($aFields["ENTRY"]) ? self::readEntryId($aFields["ENTRY"][0]) : "";
        $this->names = isset($aFields["NAME"]) ? $this->readNames($aFields["NAME"]) : [];

        return $aFields;
    }

    /**
     * Reads the NAME field, which gives the record its preferred name first and its synonyms
     * after. A semicolon separates them, whether they sit on one line or on several.
     * @param   array       $aLines
     * @return  array
     */
    private function readNames($aLines)
    {
        return array_values(array_filter(array_map(
            'trim',
            explode(";", implode(" ", $aLines))
        )));
    }

    /**
     * Joins the lines of a field into one string. A line opening with "$" continues the word
     * the line above broke off, so it joins without a space.
     * @param   array       $aLines
     * @return  string
     */
    protected function joinLines($aLines)
    {
        $sResult = "";
        foreach ($aLines as $sLine) {
            if (substr($sLine, 0, 1) == '$') {
                $sResult .= substr($sLine, 1);
                continue;
            }
            $sResult = $sResult == "" ? $sLine : $sResult . " " . $sLine;
        }

        return trim($sResult);
    }

    /**
     * Splits the lines of a field into the whitespace-separated identifiers they list.
     * @param   array       $aLines
     * @return  array
     */
    protected function splitTokens($aLines)
    {
        return preg_split("/\s+/", trim(implode(" ", $aLines)), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }

    /**
     * Reads a PATHWAY field into pairs of map identifier and pathway name.
     * Format : PATHWAY     PATH: map00010  Glycolysis / Gluconeogenesis
     * @param   array       $aLines
     * @return  array
     */
    protected function parsePathways($aLines)
    {
        $aPathways = [];
        foreach (preg_split("/PATH:/", implode(" ", $aLines), -1, PREG_SPLIT_NO_EMPTY) as $sPath) {
            $sPath = trim($sPath);
            if ($sPath == "") {
                continue;
            }
            $aPathways[] = [trim(substr($sPath, 0, 10)), trim(substr($sPath, 10))];
        }

        return $aPathways;
    }

    /**
     * Reads a DBLINKS field into pairs of database name and identifier, one per line.
     * Format : DBLINKS     CAS: 50-99-7
     * @param   array       $aLines
     * @return  array
     */
    protected function parseDbLinks($aLines)
    {
        $aLinks = [];
        foreach ($aLines as $sLine) {
            $aTokens = preg_split("/:\s/", trim($sLine), 2, PREG_SPLIT_NO_EMPTY);
            if (count($aTokens) < 2) {
                continue;
            }
            $aLinks[] = [trim($aTokens[0]), trim($aTokens[1])];
        }

        return $aLinks;
    }

    /**
     * @return string
     */
    public function getEntry(): string
    {
        return $this->entry;
    }

    /**
     * @return array
     */
    public function getNames(): array
    {
        return $this->names;
    }
}
