<?php
/**
 * ExPASy ENZYME database parsing (EC nomenclature)
 * Freely inspired by BioPHP's project biophp.org
 * Created 12 August 2026
 * Last modified 12 August 2026
 */
namespace Amelaye\BioPHP\Domain\Database\Service;

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
     * @var string
     */
    private $catalyticActivity = "";

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
     * Parses an ExPASy ENZYME data file and populates this manager's fields.
     * @param   array       $aFlines        The lines the script has to parse
     * @throws  \Exception
     */
    public function parseDataFile($aFlines)
    {
        try {
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
                        $this->alternateNames[] = rtrim($this->accumulate($aLines, $aFlines, "AN", " "), ".");
                        break;
                    case "CA":
                        $this->catalyticActivity = rtrim($this->accumulate($aLines, $aFlines, "CA", " "), ".");
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
     * @return string
     */
    public function getCatalyticActivity(): string
    {
        return $this->catalyticActivity;
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
