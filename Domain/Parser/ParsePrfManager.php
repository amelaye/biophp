<?php
/**
 * PRF/SEQDB database parsing (Protein Research Foundation)
 * Freely inspired by BioPHP's project biophp.org
 * Created 25 August 2026
 * Last modified 12 September 2026
 */
namespace Amelaye\BioPHP\Domain\Parser;

use Amelaye\BioPHP\Domain\Database\Interfaces\ParseDatabaseInterface;

/**
 * Class ParsePrfManager
 * A PRF entry describes a published protein. Its layout is a twelve character label column, a
 * blank label meaning the line continues the one above, and indented sub-keys qualifying the
 * field they follow. This class exposes plain scalars rather than the Sequence/Feature entities
 * of ParseDbAbstractManager. The SOURCE, KEYWORD, CROSSREF and SEQUENCE fields, which the
 * original BioPHP parser declared without ever filling, are read here.
 * @package Amelaye\BioPHP\Domain\Parser
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class ParsePrfManager implements ParseDatabaseInterface
{
    /**
     * Width of the label column, the data starting right after it.
     */
    private const LABEL_WIDTH = 12;

    /**
     * @var string
     */
    private $entryCode = "";

    /**
     * @var string
     */
    private $entryName = "";

    /**
     * @var string
     */
    private $source = "";

    /**
     * @var string
     */
    private $commonName = "";

    /**
     * @var array
     */
    private $taxonomy = [];

    /**
     * @var string
     */
    private $journal = "";

    /**
     * @var array
     */
    private $authors = [];

    /**
     * @var string
     */
    private $title = "";

    /**
     * @var array
     */
    private $keywords = [];

    /**
     * @var string
     */
    private $comment = "";

    /**
     * @var array
     */
    private $crossRefs = [];

    /**
     * @var string
     */
    private $sequence = "";

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
        return "PRF";
    }

    /**
     * Tells whether a line opens a new PRF entry.
     * @param   string      $sLine          The line to analyze
     * @return  bool
     */
    public static function isEntryStart(string $sLine) : bool
    {
        return trim(substr($sLine, 0, self::LABEL_WIDTH)) == "CODE";
    }

    /**
     * Tells whether a line closes a PRF entry. PRF uses a triple slash, where the GenBank family
     * uses a double one.
     * @param   string      $sLine          The line to analyze
     * @return  bool
     */
    public static function isEntryEnd(string $sLine) : bool
    {
        return substr($sLine, 0, 3) == "///";
    }

    /**
     * Extracts the identifier uniquely naming a PRF entry.
     * @param   array       $aFlines        The whole file, buffered
     * @param   string      $sLine          The line opening the entry
     * @return  string
     */
    public static function getEntryId(array $aFlines, string $sLine) : string
    {
        return trim(substr($sLine, self::LABEL_WIDTH));
    }

    /**
     * Parses a PRF data file and populates this manager's fields.
     * @param   array       $aFlines        The lines the script has to parse
     * @throws  \Exception
     */
    public function parseDataFile($aFlines)
    {
        $aBuffers = [
            "JOURNAL" => "", "AUTHOR" => "", "TITLE" => "", "COMMENT" => "",
            "KEYWORD" => "", "SEQUENCE" => "", "taxon" => ""
        ];
        $sCurrent = "";

        foreach($aFlines as $sLine) {
            if (self::isEntryEnd($sLine)) {
                break;
            }

            $sLabel = trim(substr($sLine, 0, self::LABEL_WIDTH));
            $sData  = trim(substr($sLine, self::LABEL_WIDTH));

            if ($sLabel == "") {
                if ($sCurrent != "") {
                    $aBuffers[$sCurrent] .= $sData . self::separatorFor($sCurrent);
                }
                continue;
            }

            $sCurrent = "";

            switch($sLabel) {
                case "CODE":
                    $this->entryCode = $sData;
                    break;
                case "NAME":
                    $this->entryName = $sData;
                    break;
                case "SOURCE":
                    $this->source = $sData;
                    break;
                case "cname":
                    $this->commonName = $sData;
                    break;
                case "taxon":
                case "JOURNAL":
                case "AUTHOR":
                case "TITLE":
                case "KEYWORD":
                case "COMMENT":
                    $aBuffers[$sLabel] = $sData . self::separatorFor($sLabel);
                    $sCurrent = $sLabel;
                    break;
                case "SEQUENCE":
                    $aBuffers["SEQUENCE"] = $sData . " ";
                    $sCurrent = "SEQUENCE";
                    break;
                case "CROSSREF":
                    $this->parseCrossRefs($sData);
                    break;
            }
        }

        $this->journal  = trim($aBuffers["JOURNAL"]);
        $this->title    = trim($aBuffers["TITLE"]);
        $this->comment  = trim($aBuffers["COMMENT"]);
        $this->authors  = $this->splitAuthors($aBuffers["AUTHOR"]);
        $this->keywords = $this->splitKeywords($aBuffers["KEYWORD"]);
        $this->taxonomy = $this->splitTaxonomy($aBuffers["taxon"]);
        $this->sequence = (string) preg_replace('/\s+/', "", $aBuffers["SEQUENCE"]);
    }

    /**
     * What joins a field to its continuation line. Keywords are separated by a run of at least
     * two spaces, and the line break itself separates them too : joining those lines with a
     * single space would weld the last keyword of one line to the first of the next.
     * @param   string      $sField         The field being accumulated
     * @return  string
     */
    private static function separatorFor(string $sField) : string
    {
        return $sField == "KEYWORD" ? "  " : " ";
    }

    /**
     * Authors are written "Surname,Initial., Surname,Initial." : the separator is the period
     * closing the initial, which has to be put back on each name.
     * @param   string      $sAuthors       The accumulated AUTHOR lines
     * @return  array
     */
    private function splitAuthors(string $sAuthors) : array
    {
        if (trim($sAuthors) == "") {
            return [];
        }

        $aParts = preg_split("/\.\,/", $sAuthors, -1, PREG_SPLIT_NO_EMPTY);
        $sLast  = trim((string) array_pop($aParts));

        $aAuthors = [];
        foreach($aParts as $sAuthor) {
            $aAuthors[] = trim($sAuthor) . ".";
        }
        if ($sLast != "") {
            $aAuthors[] = $sLast;
        }

        return $aAuthors;
    }

    /**
     * Keywords sit on one line each, separated by a run of at least two spaces.
     * @param   string      $sKeywords      The accumulated KEYWORD lines
     * @return  array
     */
    private function splitKeywords(string $sKeywords) : array
    {
        if (trim($sKeywords) == "") {
            return [];
        }

        $aKeywords = [];
        foreach(preg_split("/\s{2,}/", trim($sKeywords), -1, PREG_SPLIT_NO_EMPTY) as $sKeyword) {
            $sKeyword = trim($sKeyword);
            if ($sKeyword != "") {
                $aKeywords[] = $sKeyword;
            }
        }

        return $aKeywords;
    }

    /**
     * The taxonomy is a semicolon separated lineage, which may span several lines.
     * @param   string      $sTaxonomy      The accumulated taxon lines
     * @return  array
     */
    private function splitTaxonomy(string $sTaxonomy) : array
    {
        if (trim($sTaxonomy) == "") {
            return [];
        }

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
     * Cross-references are semicolon separated "DATABASE=IDENTIFIER" pairs. The same database
     * may appear more than once, so they are returned as a list rather than keyed by name.
     * Example: CROSSREF    PIR=ICHU2;PIR=ICGI2
     * @param   string      $sData          The CROSSREF line, label stripped
     */
    private function parseCrossRefs(string $sData) : void
    {
        foreach(preg_split("/;/", $sData, -1, PREG_SPLIT_NO_EMPTY) as $sRef) {
            $aPair = explode("=", trim($sRef), 2);
            if (count($aPair) < 2) {
                continue;
            }

            $this->crossRefs[] = ["db" => trim($aPair[0]), "id" => trim($aPair[1])];
        }
    }

    /**
     * @return string
     */
    public function getEntryCode(): string
    {
        return $this->entryCode;
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
    public function getSource(): string
    {
        return $this->source;
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
    public function getTaxonomy(): array
    {
        return $this->taxonomy;
    }

    /**
     * @return string
     */
    public function getJournal(): string
    {
        return $this->journal;
    }

    /**
     * @return array
     */
    public function getAuthors(): array
    {
        return $this->authors;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
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
    public function getComment(): string
    {
        return $this->comment;
    }

    /**
     * @return array
     */
    public function getCrossRefs(): array
    {
        return $this->crossRefs;
    }

    /**
     * @return string
     */
    public function getSequence(): string
    {
        return $this->sequence;
    }
}
