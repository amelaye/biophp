<?php
/**
 * KEGG REACTION parsing
 * Freely inspired by BioPHP's project biophp.org
 * Created 12 September 2026
 * Last modified 12 September 2026
 */
namespace Amelaye\BioPHP\Domain\Parser;

/**
 * Class ParseKeggReactionManager
 * A reaction record states one biochemical conversion : its equation, written with the compound
 * identifiers on either side of an arrow, and the enzymes that catalyse it.
 * @package Amelaye\BioPHP\Domain\Parser
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class ParseKeggReactionManager extends ParseKeggAbstractManager
{
    /**
     * The reaction written with the names of its compounds.
     * @var string
     */
    private $definition = "";

    /**
     * The reaction written with the identifiers of its compounds.
     * @var string
     */
    private $equation = "";

    /**
     * @var array
     */
    private $pathways = [];

    /**
     * EC numbers of the enzymes catalysing the reaction.
     * @var array
     */
    private $enzymes = [];

    /**
     * The name this format is known by in the collection records and in DatabaseParserFactory.
     * @return string
     */
    public static function getFormat() : string
    {
        return "KEGG_REACTION";
    }

    /**
     * Parses a KEGG reaction data file and populates this manager's fields.
     * @param   array       $aFlines        The lines the script has to parse
     * @throws  \Exception
     */
    public function parseDataFile($aFlines)
    {
        $aFields = $this->readFields($aFlines);

        $this->definition = isset($aFields["DEFINITION"]) ? $this->joinLines($aFields["DEFINITION"]) : "";
        $this->equation   = isset($aFields["EQUATION"]) ? $this->joinLines($aFields["EQUATION"]) : "";
        $this->enzymes    = isset($aFields["ENZYME"]) ? $this->splitTokens($aFields["ENZYME"]) : [];
        $this->pathways   = isset($aFields["PATHWAY"]) ? $this->parsePathways($aFields["PATHWAY"]) : [];
    }

    /**
     * @return string
     */
    public function getDefinition(): string
    {
        return $this->definition;
    }

    /**
     * @return string
     */
    public function getEquation(): string
    {
        return $this->equation;
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
    public function getEnzymes(): array
    {
        return $this->enzymes;
    }
}
