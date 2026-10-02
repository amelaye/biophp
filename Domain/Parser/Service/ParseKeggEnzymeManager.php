<?php
/**
 * KEGG ENZYME parsing
 * Freely inspired by BioPHP's project biophp.org
 * Created 12 September 2026
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Parser\Service;

/**
 * Class ParseKeggEnzymeManager
 * An enzyme record gathers what KEGG knows of one EC number : the reaction it catalyses, what it
 * consumes and produces, the genes coding for it and the diseases a defect in it causes.
 * @package Amelaye\BioPHP\Domain\Parser\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class ParseKeggEnzymeManager extends ParseKeggAbstractManager
{
    /**
     * @var array
     */
    private array $classification = [];

    /**
     * The systematic name, which spells out the chemistry the enzyme performs.
     * @var string
     */
    private string $sysname = "";

    /**
     * @var array
     */
    private array $reactions = [];

    /**
     * @var array
     */
    private array $substrates = [];

    /**
     * @var array
     */
    private array $products = [];

    /**
     * @var string
     */
    private string $comment = "";

    /**
     * @var array
     */
    private array $pathways = [];

    /**
     * @var array
     */
    private array $orthologs = [];

    /**
     * @var array
     */
    private array $genes = [];

    /**
     * @var array
     */
    private array $diseases = [];

    /**
     * @var array
     */
    private array $motifs = [];

    /**
     * @var array
     */
    private array $structures = [];

    /**
     * @var array
     */
    private array $dbLinks = [];

    /**
     * The name this format is known by in the collection records and in DatabaseParserFactory.
     * @return string
     */
    public static function getFormat() : string
    {
        return "KEGG_ENZYME";
    }

    /**
     * Parses a KEGG enzyme data file and populates this manager's fields.
     * @param   array       $aFlines        The lines the script has to parse
     * @throws  \Exception
     */
    public function parseDataFile(array $aFlines) {
        $aFields = $this->readFields($aFlines);

        $this->classification = isset($aFields["CLASS"]) ? $this->splitClasses($aFields["CLASS"]) : [];
        $this->sysname        = isset($aFields["SYSNAME"]) ? $this->joinLines($aFields["SYSNAME"]) : "";
        $this->comment        = isset($aFields["COMMENT"]) ? $this->joinLines($aFields["COMMENT"]) : "";
        $this->reactions      = isset($aFields["REACTION"]) ? $this->splitLines($aFields["REACTION"]) : [];
        $this->substrates     = isset($aFields["SUBSTRATE"]) ? $this->splitLines($aFields["SUBSTRATE"]) : [];
        $this->products       = isset($aFields["PRODUCT"]) ? $this->splitLines($aFields["PRODUCT"]) : [];
        $this->genes          = isset($aFields["GENES"]) ? $this->splitLines($aFields["GENES"]) : [];
        $this->diseases       = isset($aFields["DISEASE"]) ? $this->splitLines($aFields["DISEASE"]) : [];
        $this->orthologs      = isset($aFields["ORTHOLOG"]) ? $this->splitLines($aFields["ORTHOLOG"]) : [];
        $this->motifs         = isset($aFields["MOTIF"]) ? $this->splitLines($aFields["MOTIF"]) : [];
        $this->structures     = isset($aFields["STRUCTURES"]) ? $this->splitStructures($aFields["STRUCTURES"]) : [];
        $this->pathways       = isset($aFields["PATHWAY"]) ? $this->parsePathways($aFields["PATHWAY"]) : [];
        $this->dbLinks        = isset($aFields["DBLINKS"]) ? $this->parseDbLinks($aFields["DBLINKS"]) : [];
    }

    /**
     * Reads a field listing one item per line, a long item wrapping onto the next : a wrapped
     * line is indented past the column the items start at.
     * @param   array       $aLines
     * @return  array
     */
    private function splitLines(array $aLines) : array {
        return array_values(array_filter(array_map(function ($sLine) {
            return rtrim(trim($sLine), ";");
        }, $aLines)));
    }

    /**
     * Reads the STRUCTURES field, which names the database holding the structures before
     * listing them : only the identifiers are kept.
     * Format : STRUCTURES  PDB: 1HKB 1HKC 1IG8
     * @param   array       $aLines
     * @return  array
     */
    private function splitStructures(array $aLines) : array {
        $aTokens = $this->splitTokens($aLines);

        return array_values(array_filter($aTokens, function ($sToken) {
            return substr($sToken, -1) != ":";
        }));
    }

    /**
     * Reads the CLASS field, whose levels are separated by semicolons and may wrap over lines.
     * @param   array       $aLines
     * @return  array
     */
    private function splitClasses(array $aLines) : array {
        return array_values(array_filter(array_map(
            'trim',
            explode(";", $this->joinLines($aLines))
        )));
    }

    /**
     * @return array
     */
    public function getClassification(): array
    {
        return $this->classification;
    }

    /**
     * @return string
     */
    public function getSysname(): string
    {
        return $this->sysname;
    }

    /**
     * @return array
     */
    public function getReactions(): array
    {
        return $this->reactions;
    }

    /**
     * @return array
     */
    public function getSubstrates(): array
    {
        return $this->substrates;
    }

    /**
     * @return array
     */
    public function getProducts(): array
    {
        return $this->products;
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
    public function getPathways(): array
    {
        return $this->pathways;
    }

    /**
     * @return array
     */
    public function getOrthologs(): array
    {
        return $this->orthologs;
    }

    /**
     * @return array
     */
    public function getGenes(): array
    {
        return $this->genes;
    }

    /**
     * @return array
     */
    public function getDiseases(): array
    {
        return $this->diseases;
    }

    /**
     * @return array
     */
    public function getMotifs(): array
    {
        return $this->motifs;
    }

    /**
     * @return array
     */
    public function getStructures(): array
    {
        return $this->structures;
    }

    /**
     * @return array
     */
    public function getDbLinks(): array
    {
        return $this->dbLinks;
    }
}
