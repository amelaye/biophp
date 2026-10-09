<?php
/**
 * AAINDEX1 database parsing (physico-chemical indices of amino acids)
 * Freely inspired by BioPHP's project biophp.org
 * Created 25 August 2026
 * Last modified 8 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Parser\Service;

use Amelaye\BioPHP\Domain\Database\Interfaces\ParseDatabaseInterface;

/**
 * Class ParseAaindexManager
 * An AAINDEX1 entry describes a numerical property of the twenty amino acids, not a sequence,
 * so this class exposes plain scalars rather than the Sequence/Feature entities of
 * ParseDbAbstractManager. Its data fields are single letter tags whose continuation lines start
 * with a space, the GenBank way. The C field lists the entries the index correlates with, and the
 * I field the index values themselves, one per amino acid : the original BioPHP parser left both
 * aside, so the data of the entry was never available.
 * @package Amelaye\BioPHP\Domain\Parser\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class ParseAaindexManager implements ParseDatabaseInterface
{
    /**
     * @var string
     */
    private string $accession = "";

    /**
     * @var string
     */
    private string $description = "";

    /**
     * @var string
     */
    private string $author = "";

    /**
     * @var string
     */
    private string $title = "";

    /**
     * @var string
     */
    private string $journal = "";

    /**
     * @var array
     */
    private array $litRefs = [];

    /**
     * The entries this index correlates with (C field), accession => correlation coefficient
     * @var array<string,float>
     */
    private array $correlations = [];

    /**
     * The index value of each amino acid (I field), one-letter code => value, null for "NA"
     * @var array<string,float|null>
     */
    private array $index = [];

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
    public function parseDataFile(array $aFlines) {
        $aBuffers = ["D" => "", "A" => "", "T" => "", "J" => "", "C" => "", "I" => ""];
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
                case "C":
                case "I":
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
        $this->correlations = $this->parseCorrelations($aBuffers["C"]);
        $this->index       = $this->parseIndex($aBuffers["I"]);
    }

    /**
     * Reads the C field : pairs of an entry's accession and its correlation coefficient.
     * Example: C    BUNA790101    0.949  ZIMJ680102    0.917
     * @param   string      $sData          The C field, tag stripped, its lines joined
     * @return  array<string,float>
     */
    private function parseCorrelations(string $sData) : array
    {
        $aCorrelations = [];
        $aTokens = preg_split("/\s+/", trim($sData), -1, PREG_SPLIT_NO_EMPTY);
        for ($i = 0; $i + 1 < count($aTokens); $i += 2) {
            if (is_numeric($aTokens[$i + 1])) {
                $aCorrelations[$aTokens[$i]] = (float) $aTokens[$i + 1];
            }
        }
        return $aCorrelations;
    }

    /**
     * Reads the I field : a header of ten pairs naming the amino acids ("A/L R/K ..."), then a row
     * of values for the first residue of each pair and a row for the second one. A value missing
     * from the original study is written "NA".
     * Example: I    A/L     R/K     N/M  ...
     *              4.35    4.38    4.75 ...
     *              4.17    4.36    4.52 ...
     * @param   string      $sData          The I field, tag stripped, its lines joined
     * @return  array<string,float|null>
     */
    private function parseIndex(string $sData) : array
    {
        $aTokens = preg_split("/\s+/", trim($sData), -1, PREG_SPLIT_NO_EMPTY);
        $aPairs = [];
        while ($aTokens !== [] && strpos($aTokens[0], "/") !== false) {
            $aPairs[] = explode("/", array_shift($aTokens), 2);
        }
        $iPairs = count($aPairs);
        if ($iPairs === 0 || count($aTokens) < 2 * $iPairs) {
            return [];
        }

        $aIndex = [];
        foreach ([0, 1] as $iRow) {
            foreach ($aPairs as $iColumn => $aPair) {
                $sValue = $aTokens[$iRow * $iPairs + $iColumn];
                $aIndex[$aPair[$iRow]] = is_numeric($sValue) ? (float) $sValue : null;
            }
        }
        return $aIndex;
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
     * @return array<string,float>  The entries this index correlates with, accession => coefficient
     */
    public function getCorrelations(): array
    {
        return $this->correlations;
    }

    /**
     * @return array<string,float|null>  One-letter amino acid code => index value, null when the
     * entry has none ("NA")
     */
    public function getIndex(): array
    {
        return $this->index;
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
