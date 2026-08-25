<?php
/**
 * NCBI biomedical literature parsing (journal list)
 * Freely inspired by BioPHP's project biophp.org
 * Created 25 August 2026
 * Last modified 25 August 2026
 */
namespace Amelaye\BioPHP\Domain\Parser;

use Amelaye\BioPHP\Domain\Database\Interfaces\ParseDatabaseInterface;

/**
 * Class ParseNcbiLitManager
 * The NCBI journal list describes periodicals, not sequences, so this class exposes plain
 * scalars rather than the Sequence/Feature entities of ParseDbAbstractManager. It is the only
 * format the library reads whose fields are "Label: value" pairs and whose entries are closed
 * by a row of dashes rather than a double slash.
 * @package Amelaye\BioPHP\Domain\Parser
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class ParseNcbiLitManager implements ParseDatabaseInterface
{
    /**
     * @var string
     */
    private $id = "";

    /**
     * @var string
     */
    private $title = "";

    /**
     * @var string
     */
    private $medAbbr = "";

    /**
     * @var string
     */
    private $issn = "";

    /**
     * @var string
     */
    private $essn = "";

    /**
     * @var string
     */
    private $isoAbbr = "";

    /**
     * @var string
     */
    private $nlmId = "";

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
        return "NCBI_LIT";
    }

    /**
     * Tells whether a line opens a new journal entry.
     * @param   string      $sLine          The line to analyze
     * @return  bool
     */
    public static function isEntryStart(string $sLine) : bool
    {
        return substr($sLine, 0, 5) == "JrId:";
    }

    /**
     * Tells whether a line closes a journal entry. The NCBI list separates its records with a
     * row of dashes.
     * @param   string      $sLine          The line to analyze
     * @return  bool
     */
    public static function isEntryEnd(string $sLine) : bool
    {
        return substr($sLine, 0, 10) == "----------";
    }

    /**
     * Extracts the identifier uniquely naming a journal entry.
     * @param   array       $aFlines        The whole file, buffered
     * @param   string      $sLine          The line opening the entry
     * @return  string
     */
    public static function getEntryId(array $aFlines, string $sLine) : string
    {
        return trim(substr($sLine, 5));
    }

    /**
     * Parses an NCBI journal list and populates this manager's fields.
     * @param   array       $aFlines        The lines the script has to parse
     * @throws  \Exception
     */
    public function parseDataFile($aFlines)
    {
        try {
            foreach($aFlines as $sLine) {
                if (self::isEntryEnd($sLine)) {
                    break;
                }

                // Split on the first separator only : a journal title may hold one of its own.
                $aParts = explode(":", $sLine, 2);
                if (count($aParts) < 2) {
                    continue;
                }

                $sLabel = trim($aParts[0]);
                $sValue = trim($aParts[1]);

                switch($sLabel) {
                    case "JrId":
                        $this->id = $sValue;
                        break;
                    case "JournalTitle":
                        $this->title = $sValue;
                        break;
                    case "MedAbbr":
                        $this->medAbbr = $sValue;
                        break;
                    case "ISSN":
                        $this->issn = $sValue;
                        break;
                    case "ESSN":
                        $this->essn = $sValue;
                        break;
                    case "IsoAbbr":
                        $this->isoAbbr = $sValue;
                        break;
                    case "NlmId":
                        $this->nlmId = $sValue;
                        break;
                }
            }
        } catch (\Exception $ex) {
            throw new \Exception($ex);
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
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getMedAbbr(): string
    {
        return $this->medAbbr;
    }

    /**
     * @return string
     */
    public function getIssn(): string
    {
        return $this->issn;
    }

    /**
     * @return string
     */
    public function getEssn(): string
    {
        return $this->essn;
    }

    /**
     * @return string
     */
    public function getIsoAbbr(): string
    {
        return $this->isoAbbr;
    }

    /**
     * @return string
     */
    public function getNlmId(): string
    {
        return $this->nlmId;
    }
}
