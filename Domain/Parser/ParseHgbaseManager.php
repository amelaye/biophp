<?php
/**
 * HGBase database parsing (human genic bi-allelic sequences)
 * Freely inspired by BioPHP's project biophp.org
 * Created 25 August 2026
 * Last modified 25 August 2026
 */
namespace Amelaye\BioPHP\Domain\Parser;

use Amelaye\BioPHP\Domain\Database\Interfaces\ParseDatabaseInterface;

/**
 * Class ParseHgbaseManager
 * An HGBase entry describes a human mutation and the population it was observed in, not a
 * sequence, so this class exposes plain scalars rather than the Sequence/Feature entities of
 * ParseDbAbstractManager. Its lines are a label, a tab, then the data.
 * @package Amelaye\BioPHP\Domain\Parser
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class ParseHgbaseManager implements ParseDatabaseInterface
{
    /**
     * @var string
     */
    private $haplotypeId = "";

    /**
     * @var string
     */
    private $allele = "";

    /**
     * @var string
     */
    private $isInBlock = "";

    /**
     * @var string
     */
    private $populationId = "";

    /**
     * @var string
     */
    private $popName = "";

    /**
     * @var int
     */
    private $popIndiv = 0;

    /**
     * @var float
     */
    private $freqPerc = 0.0;

    /**
     * @var int
     */
    private $freqIndiv = 0;

    /**
     * @var string
     */
    private $sourceId = "";

    /**
     * @var string
     */
    private $citation = "";

    /**
     * @var string
     */
    private $submitterName = "";

    /**
     * @var string
     */
    private $submissionId = "";

    /**
     * @var string
     */
    private $sourceComment = "";

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
        return "HGBASE";
    }

    /**
     * Tells whether a line opens a new HGBase entry.
     * @param   string      $sLine          The line to analyze
     * @return  bool
     */
    public static function isEntryStart(string $sLine) : bool
    {
        return self::readLabel($sLine) == "HAPLOTYPEID";
    }

    /**
     * Tells whether a line closes an HGBase entry.
     * @param   string      $sLine          The line to analyze
     * @return  bool
     */
    public static function isEntryEnd(string $sLine) : bool
    {
        return substr($sLine, 0, 2) == "//";
    }

    /**
     * Extracts the identifier uniquely naming an HGBase entry.
     * @param   array       $aFlines        The whole file, buffered
     * @param   string      $sLine          The line opening the entry
     * @return  string
     */
    public static function getEntryId(array $aFlines, string $sLine) : string
    {
        return self::readData($sLine);
    }

    /**
     * The label opening a line, upper cased. Labels are separated from their data by a tab or a
     * run of spaces.
     * @param   string      $sLine          The line to analyze
     * @return  string
     */
    private static function readLabel(string $sLine) : string
    {
        $aTokens = preg_split("/\s+/", trim($sLine), 2, PREG_SPLIT_NO_EMPTY);

        return isset($aTokens[0]) ? strtoupper($aTokens[0]) : "";
    }

    /**
     * Everything a line carries after its label.
     * @param   string      $sLine          The line to analyze
     * @return  string
     */
    private static function readData(string $sLine) : string
    {
        $aTokens = preg_split("/\s+/", trim($sLine), 2, PREG_SPLIT_NO_EMPTY);

        return isset($aTokens[1]) ? trim($aTokens[1]) : "";
    }

    /**
     * Parses an HGBase data file and populates this manager's fields.
     * The label is matched whole rather than by its first characters : POPULATION is a prefix of
     * POPULATIONID, and the original BioPHP parser read a POPULATIONID line as both.
     * @param   array       $aFlines        The lines the script has to parse
     * @throws  \Exception
     */
    public function parseDataFile($aFlines)
    {
        try {
            $sCitation = "";

            foreach($aFlines as $sLine) {
                if (self::isEntryEnd($sLine)) {
                    break;
                }

                $sLabel = self::readLabel($sLine);
                $sData  = self::readData($sLine);

                switch($sLabel) {
                    case "HAPLOTYPEID":
                        $this->haplotypeId = $sData;
                        break;
                    case "ALLELE":
                        $this->allele = $sData;
                        break;
                    case "ISINBLOCK":
                        $this->isInBlock = $sData;
                        break;
                    case "POPULATIONID":
                        $this->populationId = $sData;
                        break;
                    case "POPULATION":
                        $this->parsePopulation($sData);
                        break;
                    case "FREQUENCY":
                        $this->parseFrequency($sData);
                        break;
                    case "SOURCEID":
                        $this->sourceId = $sData;
                        break;
                    case "CITATION":
                        $sCitation .= $sData . " ";
                        break;
                    case "SUBMITTER":
                        $this->parseSubmitter($sData);
                        break;
                    case "SOURCECOMMENT":
                        $this->sourceComment = $sData;
                        break;
                }
            }

            $this->citation = trim($sCitation);
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }

    /**
     * Reads a POPULATION line, which names the population then counts its individuals.
     * Example: Caucasian (USA) (216 individuals)
     * @param   string      $sData          The line, label stripped
     */
    private function parsePopulation(string $sData) : void
    {
        if (preg_match("/^(.*)\((\d+)\s+individuals?\)\s*$/i", $sData, $aMatch)) {
            $this->popName  = trim($aMatch[1]);
            $this->popIndiv = (int) $aMatch[2];

            return;
        }

        $this->popName = trim($sData);
    }

    /**
     * Reads a FREQUENCY line, which gives a percentage then the population it was measured on.
     * Example: 2% (1039 individuals)
     * @param   string      $sData          The line, label stripped
     */
    private function parseFrequency(string $sData) : void
    {
        if (preg_match("/^\s*([0-9.]+)\s*%/", $sData, $aMatch)) {
            $this->freqPerc = (float) $aMatch[1];
        }

        if (preg_match("/\((\d+)\s+individuals?\)/i", $sData, $aMatch)) {
            $this->freqIndiv = (int) $aMatch[1];
        }
    }

    /**
     * Reads a SUBMITTER line, which names the submitter then gives the submission identifier.
     * Example: Jan. W. Koper (SUB0001234)
     * @param   string      $sData          The line, label stripped
     */
    private function parseSubmitter(string $sData) : void
    {
        if (preg_match("/^(.*)\(([^()]*)\)\s*$/", $sData, $aMatch)) {
            $this->submitterName = trim($aMatch[1]);
            $this->submissionId  = trim($aMatch[2]);

            return;
        }

        $this->submitterName = trim($sData);
    }

    /**
     * @return string
     */
    public function getHaplotypeId(): string
    {
        return $this->haplotypeId;
    }

    /**
     * @return string
     */
    public function getAllele(): string
    {
        return $this->allele;
    }

    /**
     * @return string
     */
    public function getIsInBlock(): string
    {
        return $this->isInBlock;
    }

    /**
     * @return string
     */
    public function getPopulationId(): string
    {
        return $this->populationId;
    }

    /**
     * @return string
     */
    public function getPopName(): string
    {
        return $this->popName;
    }

    /**
     * @return int
     */
    public function getPopIndiv(): int
    {
        return $this->popIndiv;
    }

    /**
     * @return float
     */
    public function getFreqPerc(): float
    {
        return $this->freqPerc;
    }

    /**
     * @return int
     */
    public function getFreqIndiv(): int
    {
        return $this->freqIndiv;
    }

    /**
     * @return string
     */
    public function getSourceId(): string
    {
        return $this->sourceId;
    }

    /**
     * @return string
     */
    public function getCitation(): string
    {
        return $this->citation;
    }

    /**
     * @return string
     */
    public function getSubmitterName(): string
    {
        return $this->submitterName;
    }

    /**
     * @return string
     */
    public function getSubmissionId(): string
    {
        return $this->submissionId;
    }

    /**
     * @return string
     */
    public function getSourceComment(): string
    {
        return $this->sourceComment;
    }
}
