<?php
/**
 * Entrez genome record parsing
 * Freely inspired by BioPHP's project biophp.org
 * Created 12 September 2026
 * Last modified 12 September 2026
 */
namespace Amelaye\BioPHP\Domain\Parser\Service;

use Amelaye\BioPHP\Domain\Database\Interfaces\ParseDatabaseInterface;
use Amelaye\BioPHP\Domain\Parser\Entity\EntrezReference;
use Amelaye\BioPHP\Domain\Parser\Interfaces\EntrezReferenceInterface;

/**
 * Class ParseEntrezManager
 * An Entrez genome record describes a whole genome the way GenBank describes an entry : same
 * LOCUS columns, same 12-character label column. It carries neither a FEATURES table nor an
 * ORIGIN sequence though, so it holds annotation only and this class exposes plain scalars and
 * EntrezReferenceInterface objects rather than the Sequence/Feature entities of ParseDbAbstractManager.
 * Two deliberate departures from Legacy/entrez.inc.php, which called three helpers
 * (is_notmt(), monthno(), topo_code()) that exist nowhere in the original source and could
 * therefore never run : the strand count reads SINGLE/DOUBLE/MIXED and the date is kept as the
 * file writes it, both as ParseGenbankManager already does for the very same LOCUS columns,
 * rather than being normalised to "SS"/"DS" and to an ISO date.
 * @package Amelaye\BioPHP\Domain\Parser\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class ParseEntrezManager implements ParseDatabaseInterface
{
    /**
     * Subkeys of a REFERENCE block, indented under it.
     */
    private const REFERENCE_SUBKEYS = ["AUTHORS", "TITLE", "JOURNAL", "MEDLINE", "PUBMED", "REMARK"];

    /**
     * @var string
     */
    private $entryName = "";

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
    private $entryDate = "";

    /**
     * @var string
     */
    private $division = "";

    /**
     * @var string
     */
    private $topology = "";

    /**
     * @var string
     */
    private $strands = "";

    /**
     * @var string
     */
    private $definition = "";

    /**
     * @var string
     */
    private $primAcc = "";

    /**
     * @var array
     */
    private $accession = [];

    /**
     * @var string
     */
    private $version = "";

    /**
     * @var string
     */
    private $ncbiGiId = "";

    /**
     * @var array
     */
    private $keywords = [];

    /**
     * @var string
     */
    private $source = "";

    /**
     * @var string
     */
    private $organism = "";

    /**
     * @var array
     */
    private $taxonomy = [];

    /**
     * @var EntrezReferenceInterface[]
     */
    private $references = [];

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
        return "ENTREZ";
    }

    /**
     * Tells whether a line opens a new Entrez genome record.
     * @param   string      $sLine          The line to analyze
     * @return  bool
     */
    public static function isEntryStart(string $sLine) : bool
    {
        return substr($sLine, 0, 5) == "LOCUS";
    }

    /**
     * Tells whether a line closes an Entrez genome record.
     * @param   string      $sLine          The line to analyze
     * @return  bool
     */
    public static function isEntryEnd(string $sLine) : bool
    {
        return substr($sLine, 0, 2) == "//";
    }

    /**
     * Extracts the identifier uniquely naming an Entrez genome record, which is its first
     * accession number. A record short of an ACCESSION line falls back on its LOCUS name.
     * @param   array       $aFlines        The whole file, buffered
     * @param   string      $sLine          The line opening the entry
     * @return  string
     */
    public static function getEntryId(array $aFlines, string $sLine) : string
    {
        foreach($aFlines as $sCurrent) {
            if (self::readLabel($sCurrent) == "ACCESSION") {
                $aWords = preg_split("/\s+/", trim(substr($sCurrent, 12)), -1, PREG_SPLIT_NO_EMPTY);

                return $aWords[0] ?? "";
            }
        }

        return trim(substr($sLine, 12, 16));
    }

    /**
     * Reads the label a line carries in its first 12 characters. A continuation line leaves
     * that column blank, which is how a field tells it goes on.
     * @param   string      $sLine          The line to analyze
     * @return  string
     */
    private static function readLabel(string $sLine) : string
    {
        return trim(substr($sLine, 0, 12));
    }

    /**
     * Parses an Entrez genome data file and populates this manager's fields.
     * @param   array       $aFlines        The lines the script has to parse
     * @throws  \Exception
     */
    public function parseDataFile($aFlines)
    {
        $aLines = new \ArrayIterator($aFlines);

        foreach ($aLines as $lineno => $linestr) {
            $sLabel = self::readLabel($aLines->current());

            switch ($sLabel) {
                case "LOCUS":
                    $this->parseLocus($aLines->current());
                    break;
                case "DEFINITION":
                    $this->definition = $this->accumulate($aLines, $aFlines);
                    break;
                case "ACCESSION":
                    $this->accession = preg_split(
                        "/\s+/",
                        $this->accumulate($aLines, $aFlines),
                        -1,
                        PREG_SPLIT_NO_EMPTY
                    );
                    $this->primAcc = $this->accession[0] ?? "";
                    break;
                case "VERSION":
                    $this->parseVersion($aLines->current());
                    break;
                case "KEYWORDS":
                    $this->keywords = $this->splitList($this->accumulate($aLines, $aFlines));
                    break;
                case "SOURCE":
                    $this->source = $this->accumulate($aLines, $aFlines);
                    break;
                case "ORGANISM":
                    // The organism names itself on its own line, its lineage follows on the
                    // continuation lines below it.
                    $this->organism = trim(substr($aLines->current(), 12));
                    $this->taxonomy = $this->splitList($this->accumulate($aLines, $aFlines, true));
                    break;
                case "REFERENCE":
                    $this->references[] = $this->startReference($aLines->current());
                    break;
                default:
                    if (in_array($sLabel, self::REFERENCE_SUBKEYS)) {
                        $this->fillReference($sLabel, $aLines, $aFlines);
                    }
                    break;
            }
        }
    }

    /**
     * Accumulates a field written over several lines : the data of the current line, plus every
     * continuation line below it, joined by a space. Advances $aLines past what it reads.
     * @param   \ArrayIterator  $aLines
     * @param   array           $aFlines
     * @param   bool            $bSkipFirstLine     Reads the continuation lines only
     * @return  string
     */
    private function accumulate(\ArrayIterator $aLines, $aFlines, $bSkipFirstLine = false)
    {
        $sResult = $bSkipFirstLine ? "" : trim(substr($aLines->current(), 12));

        while (true) {
            $sNextLine = $aFlines[$aLines->key() + 1] ?? null;
            if ($sNextLine === null || self::readLabel($sNextLine) != "") {
                break;
            }
            $aLines->next();
            $sResult = trim($sResult . " " . trim(substr($aLines->current(), 12)));
        }

        return $sResult;
    }

    /**
     * Splits a field listing its values, semicolon separated and closed by a period, into its
     * items. A field holding just the period holds nothing.
     * @param   string      $sText
     * @return  array
     */
    private function splitList($sText)
    {
        $sText = trim($sText);
        if ($sText == "" || $sText == ".") {
            return [];
        }

        return array_values(array_filter(array_map(function ($sItem) {
            return trim($sItem);
        }, explode(";", rtrim($sText, ".")))));
    }

    /**
     * Parses the LOCUS line, whose fields sit at fixed columns.
     * Columns : 13-28 entry name, 30-40 length, 45-47 strands, 48-53 molecule type,
     * 56-63 topology, 65-67 division, 69-79 date.
     * @param   string      $sLine
     */
    private function parseLocus($sLine)
    {
        $this->entryName = trim(substr($sLine, 12, 16));
        $this->length    = (int) trim(substr($sLine, 29, 11));
        $this->molType   = trim(substr($sLine, 47, 6));

        switch (substr($sLine, 44, 3)) {
            case "ss-":
                $this->strands = "SINGLE";
                break;
            case "ds-":
                $this->strands = "DOUBLE";
                break;
            case "ms-":
                $this->strands = "MIXED";
                break;
        }

        $this->topology  = strtoupper(trim(substr($sLine, 55, 8)));
        $this->division  = strtoupper(trim(substr($sLine, 64, 3)));
        $this->entryDate = strtoupper(trim(substr($sLine, 68, 11)));
    }

    /**
     * Parses the VERSION line.
     * Format : VERSION     NC_001416.1  GI:9626243
     * @param   string      $sLine
     */
    private function parseVersion($sLine)
    {
        $aTokens = preg_split("/\s+/", trim(substr($sLine, 12)), -1, PREG_SPLIT_NO_EMPTY);

        $this->version  = $aTokens[0] ?? "";
        $this->ncbiGiId = $aTokens[1] ?? "";
    }

    /**
     * Opens the reference a REFERENCE line announces.
     * Format : REFERENCE   1  (bases 1 to 48502)
     * @param   string      $sLine
     * @return  EntrezReferenceInterface
     */
    private function startReference($sLine)
    {
        $aTokens = preg_split("/\s+/", trim(substr($sLine, 12)), -1, PREG_SPLIT_NO_EMPTY);

        $oReference = new EntrezReference();
        $oReference->setRefNo(array_shift($aTokens) ?? "");
        $oReference->setBaseRange(implode(" ", $aTokens));

        return $oReference;
    }

    /**
     * Fills one subkey of the reference being read. A subkey met before any REFERENCE line has
     * no reference to belong to and is dropped.
     * @param   string          $sSubkey
     * @param   \ArrayIterator  $aLines
     * @param   array           $aFlines
     */
    private function fillReference($sSubkey, \ArrayIterator $aLines, $aFlines)
    {
        if ($this->references == []) {
            return;
        }

        $oReference = end($this->references);
        $sValue = $this->accumulate($aLines, $aFlines);

        switch ($sSubkey) {
            case "AUTHORS":
                $oReference->setAuthors($this->splitAuthors($sValue));
                break;
            case "TITLE":
                $oReference->setTitle($sValue);
                break;
            case "JOURNAL":
                $oReference->setJournal($sValue);
                break;
            case "MEDLINE":
                $oReference->setMedline($sValue);
                break;
            case "PUBMED":
                $oReference->setPubmed($sValue);
                break;
            case "REMARK":
                $oReference->setRemark($sValue);
                break;
        }
    }

    /**
     * Splits an AUTHORS field into individual names. Names are separated by a comma and a
     * space, the last two by "and" - the comma inside a name itself ("Sanger,F.") carries no
     * space and holds the name together.
     * Format : Sanger,F., Coulson,A.R., Hong,G.F. and Petersen,G.B.
     * @param   string      $sText
     * @return  array
     */
    private function splitAuthors($sText)
    {
        $aAuthors = preg_split("/,\s/", trim($sText), -1, PREG_SPLIT_NO_EMPTY);
        if ($aAuthors == []) {
            return [];
        }

        $aLast = preg_split("/\sand\s/", array_pop($aAuthors), -1, PREG_SPLIT_NO_EMPTY);

        return array_values(array_filter(array_map('trim', array_merge($aAuthors, $aLast))));
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
    public function getEntryDate(): string
    {
        return $this->entryDate;
    }

    /**
     * @return string
     */
    public function getDivision(): string
    {
        return $this->division;
    }

    /**
     * @return string
     */
    public function getTopology(): string
    {
        return $this->topology;
    }

    /**
     * @return string
     */
    public function getStrands(): string
    {
        return $this->strands;
    }

    /**
     * @return string
     */
    public function getDefinition(): string
    {
        return $this->definition;
    }

    /**
     * @return string
     */
    public function getPrimAcc(): string
    {
        return $this->primAcc;
    }

    /**
     * @return array
     */
    public function getAccession(): array
    {
        return $this->accession;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @return string
     */
    public function getNcbiGiId(): string
    {
        return $this->ncbiGiId;
    }

    /**
     * @return array
     */
    public function getKeywords(): array
    {
        return $this->keywords;
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @return string
     */
    public function getOrganism(): string
    {
        return $this->organism;
    }

    /**
     * @return array
     */
    public function getTaxonomy(): array
    {
        return $this->taxonomy;
    }

    /**
     * @return EntrezReferenceInterface[]
     */
    public function getReferences(): array
    {
        return $this->references;
    }
}
