<?php
/**
 * PROSITE motif database parsing
 * Freely inspired by BioPHP's project biophp.org
 * Created 12 August 2026
 * Last modified 12 August 2026
 */
namespace Amelaye\BioPHP\Domain\Database\Service;

use Amelaye\BioPHP\Domain\Database\Interfaces\ParseDatabaseInterface;
use Amelaye\BioPHP\Domain\Model\PrositeDbRef;

/**
 * Class ParsePrositeManager
 * A PROSITE entry describes a motif (pattern, or matrix/profile), not an annotated
 * sequence, so - like ParsePdbManager - this class does not reuse the Sequence/Feature
 * entities of ParseDbAbstractManager. It uses the same 2-char-tag/3-space layout as
 * EMBL and Swiss-Prot, so multi-line fields are read with the same lookahead approach
 * as ParseEmblManager. The MA (matrix) field, which Legacy/motif.inc.php itself only
 * half-implemented (buggy 3-level nesting), is kept as a raw string here rather than
 * ported as-is.
 * @package Amelaye\BioPHP\Domain\Database\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class ParsePrositeManager implements ParseDatabaseInterface
{
    /**
     * @var string
     */
    private $entryName = "";

    /**
     * @var string
     */
    private $entryType = "";

    /**
     * @var string
     */
    private $accession = "";

    /**
     * @var array
     */
    private $dates = [];

    /**
     * @var string
     */
    private $description = "";

    /**
     * @var string
     */
    private $pattern = "";

    /**
     * @var string
     */
    private $matrix = "";

    /**
     * @var array
     */
    private $numericalResults = [];

    /**
     * @var array
     */
    private $comments = [];

    /**
     * @var string
     */
    private $rule = "";

    /**
     * @var array
     */
    private $pdbXrefs = [];

    /**
     * @var PrositeDbRef[]
     */
    private $dbRefs = [];

    /**
     * @var string
     */
    private $docXref = "";

    /**
     * Constructor.
     */
    public function __construct()
    {
    }

    /**
     * Parses a PROSITE data file and populates this manager's fields.
     * @param   array       $aFlines        The lines the script has to parse
     * @throws  \Exception
     */
    public function parseDataFile($aFlines)
    {
        try {
            $aLines = new \ArrayIterator($aFlines);

            foreach ($aLines as $lineno => $linestr) {
                switch (trim(substr($aLines->current(), 0, 2))) {
                    case "ID":
                        $this->parseId($aLines->current());
                        break;
                    case "AC":
                        $this->accession = rtrim(trim(substr($aLines->current(), 5)), ";");
                        break;
                    case "DT":
                        $this->parseDate($aLines->current());
                        break;
                    case "DE":
                        $this->description = $this->accumulate($aLines, $aFlines, "DE", " ");
                        break;
                    case "PA":
                        $this->pattern = $this->accumulate($aLines, $aFlines, "PA", "");
                        break;
                    case "MA":
                        $this->matrix = $this->accumulate($aLines, $aFlines, "MA", " ");
                        break;
                    case "NR":
                        $this->numericalResults = $this->parseQualifiers($this->accumulate($aLines, $aFlines, "NR", " "));
                        break;
                    case "CC":
                        $this->comments = $this->parseQualifiers($this->accumulate($aLines, $aFlines, "CC", " "));
                        break;
                    case "RU":
                        $this->rule = $this->accumulate($aLines, $aFlines, "RU", " ");
                        break;
                    case "3D":
                        $this->pdbXrefs = $this->parseList($this->accumulate($aLines, $aFlines, "3D", " "));
                        break;
                    case "DR":
                        $this->dbRefs = $this->parseDbRefs($this->accumulate($aLines, $aFlines, "DR", " "));
                        break;
                    case "DO":
                        $this->docXref = rtrim(trim(substr($aLines->current(), 5)), ";");
                        break;
                }
            }
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Accumulates a multi-line field: the current line's data, plus every following
     * line still tagged $sTag, joined with $sJoiner. Advances $aLines past what it reads.
     * @param   \ArrayIterator  $aLines
     * @param   array           $aFlines
     * @param   string          $sTag
     * @param   string          $sJoiner
     * @return  string
     */
    private function accumulate(\ArrayIterator $aLines, $aFlines, $sTag, $sJoiner)
    {
        $sResult = trim(substr($aLines->current(), 5));
        while (true) {
            $sNextLine = $aFlines[$aLines->key() + 1] ?? "";
            if (trim(substr($sNextLine, 0, 2)) != $sTag) {
                break;
            }
            $aLines->next();
            $sResult .= $sJoiner . trim(substr($aLines->current(), 5));
        }
        return $sResult;
    }

    /**
     * Parses the ID line.
     * Format : ID   ENTRYNAME; TYPE.
     * @param   string      $sLine
     */
    private function parseId($sLine)
    {
        $aParts = array_map('trim', explode(";", trim(substr($sLine, 5))));
        $this->entryName = $aParts[0];
        $this->entryType = rtrim($aParts[1] ?? "", ".");
    }

    /**
     * Parses the DT line.
     * Format : DT   MMM-YEAR (CREATED); MMM-YEAR (DATA UPDATE); MMM-YEAR (INFO UPDATE).
     * @param   string      $sLine
     */
    private function parseDate($sLine)
    {
        $sData = rtrim(trim(substr($sLine, 5)), ".");
        $aItems = array_values(array_filter(array_map('trim', explode(";", $sData))));
        foreach ($aItems as $sItem) {
            if (preg_match('/^(.*)\s+\((.*)\)$/', $sItem, $aMatches)) {
                $this->dates[trim($aMatches[2])] = trim($aMatches[1]);
            }
        }
    }

    /**
     * Turns a "/qualifier=value; /qualifier=value;" string into an associative array.
     * @param   string      $sText
     * @return  array
     */
    private function parseQualifiers($sText)
    {
        $aResult = [];
        $aItems = array_values(array_filter(array_map('trim', explode(";", $sText))));
        foreach ($aItems as $sItem) {
            $aKeyValue = explode("=", ltrim($sItem, "/"), 2);
            if (isset($aKeyValue[1])) {
                $aResult[$aKeyValue[0]] = $aKeyValue[1];
            }
        }
        return $aResult;
    }

    /**
     * Turns a "item1; item2; item3." string into a plain array.
     * @param   string      $sText
     * @return  array
     */
    private function parseList($sText)
    {
        return array_values(array_filter(array_map('trim', explode(";", $sText))));
    }

    /**
     * Turns the DR field into a list of PrositeDbRef.
     * Format : DR   ACCESSION, ENTRY_NAME, T|F|N; ACCESSION, ENTRY_NAME, T|F|N; ...
     * @param   string      $sText
     * @return  PrositeDbRef[]
     */
    private function parseDbRefs($sText)
    {
        $aResult = [];
        $aItems = array_values(array_filter(array_map('trim', explode(";", $sText))));
        foreach ($aItems as $sItem) {
            $aFields = array_map('trim', explode(",", $sItem));
            if (count($aFields) < 3) {
                continue;
            }
            $oDbRef = new PrositeDbRef();
            $oDbRef->setAccession($aFields[0]);
            $oDbRef->setEntryName($aFields[1]);
            $oDbRef->setTruePositive($aFields[2] == "T");
            $aResult[] = $oDbRef;
        }
        return $aResult;
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
    public function getEntryType(): string
    {
        return $this->entryType;
    }

    /**
     * @return string
     */
    public function getAccession(): string
    {
        return $this->accession;
    }

    /**
     * @return array
     */
    public function getDates(): array
    {
        return $this->dates;
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
    public function getPattern(): string
    {
        return $this->pattern;
    }

    /**
     * @return string
     */
    public function getMatrix(): string
    {
        return $this->matrix;
    }

    /**
     * @return array
     */
    public function getNumericalResults(): array
    {
        return $this->numericalResults;
    }

    /**
     * @return array
     */
    public function getComments(): array
    {
        return $this->comments;
    }

    /**
     * @return string
     */
    public function getRule(): string
    {
        return $this->rule;
    }

    /**
     * @return array
     */
    public function getPdbXrefs(): array
    {
        return $this->pdbXrefs;
    }

    /**
     * @return PrositeDbRef[]
     */
    public function getDbRefs(): array
    {
        return $this->dbRefs;
    }

    /**
     * @return string
     */
    public function getDocXref(): string
    {
        return $this->docXref;
    }
}
