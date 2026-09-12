<?php
/**
 * ExPASy ENZYME database parsing (EC nomenclature)
 * Freely inspired by BioPHP's project biophp.org
 * Created 12 August 2026
 * Last modified 12 September 2026
 */
namespace Amelaye\BioPHP\Domain\Parser;

use Amelaye\BioPHP\Domain\Database\Interfaces\ParseDatabaseInterface;
use Amelaye\BioPHP\Domain\Model\ExpasyDisease;

/**
 * Class ParseExpasyEnzymeManager
 * ExPASy ENZYME entries describe an enzyme by its EC number, not a sequence - like
 * ParsePdbManager and ParsePrositeManager, this class does not reuse the Sequence/Feature
 * entities of ParseDbAbstractManager. Not to be confused with RestrictionEnzymeManager,
 * which covers restriction endonucleases (a completely different Legacy/ExPASy database).
 * @package Amelaye\BioPHP\Domain\Database\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class ParseExpasyEnzymeManager implements ParseDatabaseInterface
{
    /**
     * @var string
     */
    private $id = "";

    /**
     * @var string
     */
    private $description = "";

    /**
     * @var array
     */
    private $alternateNames = [];

    /**
     * One entry per reaction the enzyme catalyses : an enzyme acting on several substrates has
     * as many, and the file numbers them "(1)", "(2)".
     * @var array
     */
    private $catalyticActivities = [];

    /**
     * @var array
     */
    private $aCaLines = [];

    /**
     * @var array
     */
    private $cofactors = [];

    /**
     * @var string
     */
    private $comments = "";

    /**
     * @var ExpasyDisease[]
     */
    private $diseases = [];

    /**
     * @var array
     */
    private $prositeRefs = [];

    /**
     * @var array
     */
    private $swissprotRefs = [];

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
        return "EXPASY_ENZYME";
    }

    /**
     * Tells whether a line opens a new ExPASy ENZYME entry.
     * @param   string      $sLine          The line to analyze
     * @return  bool
     */
    public static function isEntryStart(string $sLine) : bool
    {
        return substr($sLine, 0, 2) == "ID";
    }

    /**
     * Tells whether a line closes a ExPASy ENZYME entry.
     * @param   string      $sLine          The line to analyze
     * @return  bool
     */
    public static function isEntryEnd(string $sLine) : bool
    {
        return substr($sLine, 0, 2) == "//";
    }

    /**
     * Extracts the identifier uniquely naming a ExPASy ENZYME entry.
     * @param   array       $aFlines        The whole file, buffered
     * @param   string      $sLine          The line opening the entry
     * @return  string
     */
    public static function getEntryId(array $aFlines, string $sLine) : string
    {
        return trim(substr($sLine, 5));
    }

    /**
     * Parses an ExPASy ENZYME data file and populates this manager's fields.
     * @param   array       $aFlines        The lines the script has to parse
     * @throws  \Exception
     */
    public function parseDataFile($aFlines)
    {
        $aLines = new \ArrayIterator($aFlines);
        $sComments = "";

        foreach ($aLines as $lineno => $linestr) {
            switch (trim(substr($aLines->current(), 0, 2))) {
                case "ID":
                    $this->id = trim(substr($aLines->current(), 5));
                    break;
                case "DE":
                    $this->description = $this->accumulate($aLines, $aFlines, "DE", " ");
                    break;
                case "AN":
                    // One synonym per line: unlike DE, CA or CF, consecutive AN lines are
                    // separate names, not one name wrapped over several lines.
                    $this->alternateNames[] = rtrim(trim(substr($aLines->current(), 5)), ".");
                    break;
                case "CA":
                    $this->aCaLines[] = trim(substr($aLines->current(), 5));
                    break;
                case "CF":
                    $this->cofactors = $this->parseCofactors($this->accumulate($aLines, $aFlines, "CF", " "));
                    break;
                case "CC":
                    $sComments .= substr(rtrim($aLines->current(), "\r\n"), 5) . "\n";
                    break;
                case "DI":
                    $this->diseases[] = $this->parseDisease(trim(substr($aLines->current(), 5)));
                    break;
                case "PR":
                    $this->prositeRefs[] = $this->parsePrositeRef(trim(substr($aLines->current(), 5)));
                    break;
                case "DR":
                    $this->swissprotRefs = array_merge(
                        $this->swissprotRefs,
                        $this->parseSwissprotRefs($this->accumulate($aLines, $aFlines, "DR", ""))
                    );
                    break;
            }
        }

        $this->comments = rtrim($sComments, "\n");
        $this->catalyticActivities = $this->parseCatalyticActivities($this->aCaLines);
    }

    /**
     * Reads the CA field into one entry per reaction. An enzyme acting on several substrates
     * has several, which the file numbers "(1)", "(2)"; an unnumbered line continues the
     * reaction above it, a long reaction being wrapped rather than repeated.
     * @param   array       $aLines
     * @return  array
     */
    private function parseCatalyticActivities($aLines)
    {
        $aActivities = [];

        foreach ($aLines as $sLine) {
            if (preg_match('/^\(\d+\)\s*(.*)$/', $sLine, $aMatch)) {
                $aActivities[] = $aMatch[1];
                continue;
            }
            if ($aActivities == []) {
                $aActivities[] = $sLine;
                continue;
            }
            $aActivities[count($aActivities) - 1] .= " " . $sLine;
        }

        return array_values(array_filter(array_map(function ($sActivity) {
            return rtrim(trim($sActivity), ".");
        }, $aActivities)));
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
     * Parses the CF field.
     * Format : CF   Cofactor1; Cofactor2.
     * @param   string      $sText
     * @return  array
     */
    private function parseCofactors($sText)
    {
        return array_values(array_filter(array_map(function ($sItem) {
            return rtrim(trim($sItem), ".");
        }, explode(";", $sText))));
    }

    /**
     * Parses one DI line.
     * Format : DI   Disease name; MIM: 123456.
     * @param   string      $sLine
     * @return  ExpasyDisease
     */
    private function parseDisease($sLine)
    {
        $aTokens = array_map('trim', explode(";", $sLine));
        $oDisease = new ExpasyDisease();
        $oDisease->setDisease($aTokens[0] ?? "");

        $aLitTokens = array_map('trim', explode(":", $aTokens[1] ?? ""));
        $oDisease->setReference(rtrim($aLitTokens[1] ?? "", "."));
        return $oDisease;
    }

    /**
     * Parses one PR line.
     * Format : PR   PROSITE; PDOC00061;
     * @param   string      $sLine
     * @return  string
     */
    private function parsePrositeRef($sLine)
    {
        $aTokens = array_values(array_filter(array_map('trim', explode(";", $sLine))));
        return $aTokens[1] ?? "";
    }

    /**
     * Parses the DR field into an accession => entry name map.
     * Format : DR   ACCESSION, ENTRY_NAME; ACCESSION, ENTRY_NAME; ...
     * @param   string      $sText
     * @return  array
     */
    private function parseSwissprotRefs($sText)
    {
        $aResult = [];
        $aItems = array_values(array_filter(array_map('trim', explode(";", $sText))));
        foreach ($aItems as $sItem) {
            $aFields = array_map('trim', explode(",", $sItem));
            if (count($aFields) < 2) {
                continue;
            }
            $aResult[$aFields[0]] = $aFields[1];
        }
        return $aResult;
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
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return array
     */
    public function getAlternateNames(): array
    {
        return $this->alternateNames;
    }

    /**
     * @return array
     */
    public function getCatalyticActivities(): array
    {
        return $this->catalyticActivities;
    }

    /**
     * @return array
     */
    public function getCofactors(): array
    {
        return $this->cofactors;
    }

    /**
     * @return string
     */
    public function getComments(): string
    {
        return $this->comments;
    }

    /**
     * @return ExpasyDisease[]
     */
    public function getDiseases(): array
    {
        return $this->diseases;
    }

    /**
     * @return array
     */
    public function getPrositeRefs(): array
    {
        return $this->prositeRefs;
    }

    /**
     * @return array
     */
    public function getSwissprotRefs(): array
    {
        return $this->swissprotRefs;
    }
}
