<?php
/**
 * UniGene database parsing (clusters of expressed sequence tags)
 * Freely inspired by BioPHP's project biophp.org
 * Created 25 August 2026
 * Last modified 25 August 2026
 */
namespace Amelaye\BioPHP\Domain\Parser;

use Amelaye\BioPHP\Domain\Database\Interfaces\ParseDatabaseInterface;

/**
 * Class ParseUnigeneManager
 * A UniGene entry groups the sequences believed to come from one gene. It carries no sequence
 * of its own, so this class exposes plain scalars rather than the Sequence/Feature entities of
 * ParseDbAbstractManager. The PROTSIM lines are kept raw, as ParsePrositeManager does for its
 * matrix field: their layout varies and the original BioPHP parser never decomposed them.
 * @package Amelaye\BioPHP\Domain\Parser
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class ParseUnigeneManager implements ParseDatabaseInterface
{
    /**
     * @var string
     */
    private $clusterId = "";

    /**
     * @var string
     */
    private $title = "";

    /**
     * @var array
     */
    private $expression = [];

    /**
     * @var array
     */
    private $protSims = [];

    /**
     * @var int
     */
    private $seqCount = 0;

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
        return "UNIGENE";
    }

    /**
     * Tells whether a line opens a new UniGene entry.
     * @param   string      $sLine          The line to analyze
     * @return  bool
     */
    public static function isEntryStart(string $sLine) : bool
    {
        return trim(substr($sLine, 0, 12)) == "ID";
    }

    /**
     * Tells whether a line closes a UniGene entry.
     * @param   string      $sLine          The line to analyze
     * @return  bool
     */
    public static function isEntryEnd(string $sLine) : bool
    {
        return substr($sLine, 0, 2) == "//";
    }

    /**
     * Extracts the identifier uniquely naming a UniGene entry.
     * @param   array       $aFlines        The whole file, buffered
     * @param   string      $sLine          The line opening the entry
     * @return  string
     */
    public static function getEntryId(array $aFlines, string $sLine) : string
    {
        return trim(substr($sLine, 12));
    }

    /**
     * Parses a UniGene data file and populates this manager's fields.
     * @param   array       $aFlines        The lines the script has to parse
     * @throws  \Exception
     */
    public function parseDataFile($aFlines)
    {
        try {
            $sTitle = "";

            foreach($aFlines as $sLine) {
                $sLabel = trim(substr($sLine, 0, 12));
                $sData  = trim(substr($sLine, 12));

                switch($sLabel) {
                    case "ID":
                        $this->clusterId = $sData;
                        break;
                    case "TITLE":
                        $sTitle .= $sData . " ";
                        break;
                    case "EXPRESS":
                        foreach(preg_split("/;/", $sData, -1, PREG_SPLIT_NO_EMPTY) as $sTissue) {
                            $this->expression[] = trim($sTissue);
                        }
                        break;
                    case "PROTSIM":
                        $this->protSims[] = $sData;
                        break;
                    case "SCOUNT":
                        $this->seqCount = (int) $sData;
                        break;
                }

                if ($sLabel == "//") {
                    break;
                }
            }

            $this->title = trim($sTitle);
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }

    /**
     * @return string
     */
    public function getClusterId(): string
    {
        return $this->clusterId;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * The tissues the cluster was observed in, one entry per semicolon separated item.
     * @return array
     */
    public function getExpression(): array
    {
        return $this->expression;
    }

    /**
     * The PROTSIM lines, kept as written.
     * @return array
     */
    public function getProtSims(): array
    {
        return $this->protSims;
    }

    /**
     * @return int
     */
    public function getSeqCount(): int
    {
        return $this->seqCount;
    }
}
