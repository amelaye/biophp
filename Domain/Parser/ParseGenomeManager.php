<?php
/**
 * Genome sequencing statistics parsing (the Legacy "DOGS" records)
 * Freely inspired by BioPHP's project biophp.org
 * Created 25 August 2026
 * Last modified 25 August 2026
 */
namespace Amelaye\BioPHP\Domain\Parser;

use Amelaye\BioPHP\Domain\Database\Interfaces\ParseDatabaseInterface;
use Amelaye\BioPHP\Domain\Model\GenomeReference;

/**
 * Class ParseGenomeManager
 * A genome record sums up how far an organism has been sequenced. It holds statistics and a list
 * of references rather than a sequence, so this class exposes plain scalars and GenomeReference
 * objects rather than the Sequence/Feature entities of ParseDbAbstractManager. Its continuation
 * lines are indented with a tab.
 * @package Amelaye\BioPHP\Domain\Parser
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class ParseGenomeManager implements ParseDatabaseInterface
{
    /**
     * @var string
     */
    private $organism = "";

    /**
     * @var string
     */
    private $commonName = "";

    /**
     * @var array
     */
    private $taxClass = [];

    /**
     * @var string
     */
    private $isComplete = "";

    /**
     * @var string
     */
    private $gbRelease = "";

    /**
     * @var int
     */
    private $gbEntries = 0;

    /**
     * @var int
     */
    private $gbBasepairs = 0;

    /**
     * @var int
     */
    private $size = 0;

    /**
     * @var GenomeReference[]
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
        return "GENOME";
    }

    /**
     * Tells whether a line opens a new genome record.
     * @param   string      $sLine          The line to analyze
     * @return  bool
     */
    public static function isEntryStart(string $sLine) : bool
    {
        return self::readLabel($sLine) == "ORGANISM";
    }

    /**
     * Tells whether a line closes a genome record.
     * @param   string      $sLine          The line to analyze
     * @return  bool
     */
    public static function isEntryEnd(string $sLine) : bool
    {
        return substr($sLine, 0, 2) == "//";
    }

    /**
     * Extracts the identifier uniquely naming a genome record, which is the scientific name of
     * the organism.
     * @param   array       $aFlines        The whole file, buffered
     * @param   string      $sLine          The line opening the entry
     * @return  string
     */
    public static function getEntryId(array $aFlines, string $sLine) : string
    {
        return self::readData($sLine);
    }

    /**
     * The label opening a line, upper cased. An indented line continues the one above it and has
     * no label of its own.
     * @param   string      $sLine          The line to analyze
     * @return  string
     */
    private static function readLabel(string $sLine) : string
    {
        if ($sLine != "" && preg_match("/^\s/", $sLine)) {
            return "";
        }

        $aTokens = preg_split("/\s+/", trim($sLine), 2, PREG_SPLIT_NO_EMPTY);

        return isset($aTokens[0]) ? strtoupper($aTokens[0]) : "";
    }

    /**
     * Everything a line carries after its label, or the whole of an indented line.
     * @param   string      $sLine          The line to analyze
     * @return  string
     */
    private static function readData(string $sLine) : string
    {
        if ($sLine != "" && preg_match("/^\s/", $sLine)) {
            return trim($sLine);
        }

        $aTokens = preg_split("/\s+/", trim($sLine), 2, PREG_SPLIT_NO_EMPTY);

        return isset($aTokens[1]) ? trim($aTokens[1]) : "";
    }

    /**
     * Parses a genome data file and populates this manager's fields.
     * @param   array       $aFlines        The lines the script has to parse
     * @throws  \Exception
     */
    public function parseDataFile($aFlines)
    {
        try {
            $sTaxonomy  = "";
            $sAuthors   = "";
            $sTitle     = "";
            $sCurrent   = "";
            $oReference = null;

            foreach($aFlines as $sLine) {
                if (self::isEntryEnd($sLine)) {
                    break;
                }

                $sLabel = self::readLabel($sLine);
                $sData  = self::readData($sLine);

                if ($sLabel == "") {
                    switch($sCurrent) {
                        case "CLASSIFICATION":
                            $sTaxonomy .= $sData . " ";
                            break;
                        case "REF_AUTHOR":
                            $sAuthors .= $sData . " ";
                            break;
                        case "REF_TITLE":
                            $sTitle .= $sData . " ";
                            break;
                    }
                    continue;
                }

                $sCurrent = $sLabel;

                switch($sLabel) {
                    case "ORGANISM":
                        $this->organism = $sData;
                        break;
                    case "COMMON_NAME":
                        $this->commonName = $sData;
                        break;
                    case "CLASSIFICATION":
                        $sTaxonomy .= $sData . " ";
                        break;
                    case "COMPLETED":
                        $this->isComplete = $sData;
                        break;
                    case "GB_RELEASE":
                        $this->gbRelease = $sData;
                        break;
                    case "GB_ENTRIES":
                        $this->gbEntries = (int) $sData;
                        break;
                    case "GB_BASEPAIRS":
                        $this->gbBasepairs = (int) $sData;
                        break;
                    case "GENOME_SIZE":
                        $this->size = (int) $sData;
                        break;
                    case "REF_TYPE":
                        // A new reference set starts here : close the one being filled, if any.
                        $this->closeReference($oReference, $sAuthors, $sTitle);
                        $sAuthors = "";
                        $sTitle   = "";

                        $oReference = new GenomeReference();
                        $oReference->setType($sData);
                        break;
                    case "REF_AUTHOR":
                        $sAuthors .= $sData . " ";
                        break;
                    case "REF_TITLE":
                        $sTitle .= $sData . " ";
                        break;
                    case "REF_JOURNAL":
                        if ($oReference !== null) {
                            $oReference->setJournal($sData);
                        }
                        break;
                    case "REF_VOLUME":
                        if ($oReference !== null) {
                            $oReference->setVolume($sData);
                        }
                        break;
                    case "REF_PAGES":
                        if ($oReference !== null) {
                            $oReference->setPages($sData);
                        }
                        break;
                    case "REF_YEAR":
                        if ($oReference !== null) {
                            $oReference->setYear($sData);
                        }
                        break;
                }
            }

            $this->closeReference($oReference, $sAuthors, $sTitle);
            $this->taxClass = $this->splitTaxonomy($sTaxonomy);
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }

    /**
     * Files the reference being filled, with the multiline fields it accumulated.
     * @param   GenomeReference|null    $oReference     The reference set, if one is open
     * @param   string                  $sAuthors       The accumulated REF_AUTHOR lines
     * @param   string                  $sTitle         The accumulated REF_TITLE lines
     */
    private function closeReference($oReference, string $sAuthors, string $sTitle) : void
    {
        if ($oReference === null) {
            return;
        }

        $aAuthors = [];
        foreach(preg_split("/;/", $sAuthors, -1, PREG_SPLIT_NO_EMPTY) as $sAuthor) {
            $sAuthor = trim($sAuthor);
            if ($sAuthor != "") {
                $aAuthors[] = $sAuthor;
            }
        }

        $oReference->setAuthors($aAuthors);
        $oReference->setTitle(trim($sTitle));

        $this->references[] = $oReference;
    }

    /**
     * The classification is a semicolon separated lineage which may run over several lines.
     * @param   string      $sTaxonomy      The accumulated CLASSIFICATION lines
     * @return  array
     */
    private function splitTaxonomy(string $sTaxonomy) : array
    {
        $aTaxonomy = [];
        foreach(preg_split("/;/", $sTaxonomy, -1, PREG_SPLIT_NO_EMPTY) as $sRank) {
            $sRank = trim($sRank);
            if ($sRank != "") {
                $aTaxonomy[] = $sRank;
            }
        }

        return $aTaxonomy;
    }

    /**
     * The scientific name of the organism.
     * @return string
     */
    public function getOrganism(): string
    {
        return $this->organism;
    }

    /**
     * @return string
     */
    public function getCommonName(): string
    {
        return $this->commonName;
    }

    /**
     * @return array
     */
    public function getTaxClass(): array
    {
        return $this->taxClass;
    }

    /**
     * Whether the genome has been completely sequenced, as written in the record.
     * @return string
     */
    public function getIsComplete(): string
    {
        return $this->isComplete;
    }

    /**
     * @return string
     */
    public function getGbRelease(): string
    {
        return $this->gbRelease;
    }

    /**
     * @return int
     */
    public function getGbEntries(): int
    {
        return $this->gbEntries;
    }

    /**
     * @return int
     */
    public function getGbBasepairs(): int
    {
        return $this->gbBasepairs;
    }

    /**
     * The haploid genome size, in base pairs.
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @return GenomeReference[]
     */
    public function getReferences(): array
    {
        return $this->references;
    }
}
