<?php
/**
 * KEGG ORTHOLOG parsing
 * Freely inspired by BioPHP's project biophp.org
 * Created 12 September 2026
 * Last modified 12 September 2026
 */
namespace Amelaye\BioPHP\Domain\Parser;

/**
 * Class ParseKeggOrthologManager
 * An ortholog record groups the genes of different organisms that descend from one common
 * ancestral gene and do the same job, which is what lets a pathway be carried from one species
 * to another.
 * @package Amelaye\BioPHP\Domain\Parser
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class ParseKeggOrthologManager extends ParseKeggAbstractManager
{
    /**
     * @var string
     */
    private $definition = "";

    /**
     * @var array
     */
    private $classification = [];

    /**
     * One entry per organism, e.g. "HSA: 3101 3098".
     * @var array
     */
    private $genes = [];

    /**
     * @var array
     */
    private $dbLinks = [];

    /**
     * The name this format is known by in the collection records and in DatabaseParserFactory.
     * @return string
     */
    public static function getFormat() : string
    {
        return "KEGG_ORTHOLOG";
    }

    /**
     * Parses a KEGG ortholog data file and populates this manager's fields.
     * @param   array       $aFlines        The lines the script has to parse
     * @throws  \Exception
     */
    public function parseDataFile($aFlines)
    {
        $aFields = $this->readFields($aFlines);

        $this->definition = isset($aFields["DEFINITION"]) ? $this->joinLines($aFields["DEFINITION"]) : "";
        $this->dbLinks    = isset($aFields["DBLINKS"]) ? $this->parseDbLinks($aFields["DBLINKS"]) : [];
        $this->genes      = isset($aFields["GENES"]) ? array_values(array_filter(array_map(
            'trim',
            $aFields["GENES"]
        ))) : [];
        $this->classification = isset($aFields["CLASS"]) ? array_values(array_filter(array_map(
            'trim',
            explode(";", $this->joinLines($aFields["CLASS"]))
        ))) : [];
    }

    /**
     * @return string
     */
    public function getDefinition(): string
    {
        return $this->definition;
    }

    /**
     * @return array
     */
    public function getClassification(): array
    {
        return $this->classification;
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
    public function getDbLinks(): array
    {
        return $this->dbLinks;
    }
}
