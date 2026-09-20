<?php
/**
 * KEGG COMPOUND parsing
 * Freely inspired by BioPHP's project biophp.org
 * Created 12 September 2026
 * Last modified 18 September 2026
 */
namespace Amelaye\BioPHP\Domain\Parser\Service;

/**
 * Class ParseKeggCompoundManager
 * A compound record describes one metabolite : its formula, the reactions it takes part in, the
 * enzymes acting on it and the pathways it belongs to.
 * @package Amelaye\BioPHP\Domain\Parser\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class ParseKeggCompoundManager extends ParseKeggAbstractManager
{
    /**
     * @var string
     */
    private $formula = "";

    /**
     * @var array
     */
    private $reactions = [];

    /**
     * @var array
     */
    private $pathways = [];

    /**
     * EC numbers of the enzymes acting on the compound.
     * @var array
     */
    private $enzymes = [];

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
        return "KEGG_COMPOUND";
    }

    /**
     * Parses a KEGG compound data file and populates this manager's fields.
     * @param   array       $aFlines        The lines the script has to parse
     * @throws  \Exception
     */
    public function parseDataFile($aFlines)
    {
        $aFields = $this->readFields($aFlines);

        $this->formula   = isset($aFields["FORMULA"]) ? $this->joinLines($aFields["FORMULA"]) : "";
        $this->reactions = isset($aFields["REACTION"]) ? $this->splitTokens($aFields["REACTION"]) : [];
        $this->enzymes   = isset($aFields["ENZYME"]) ? $this->splitTokens($aFields["ENZYME"]) : [];
        $this->pathways  = isset($aFields["PATHWAY"]) ? $this->parsePathways($aFields["PATHWAY"]) : [];
        $this->dbLinks   = isset($aFields["DBLINKS"]) ? $this->parseDbLinks($aFields["DBLINKS"]) : [];
    }

    /**
     * @return string
     */
    public function getFormula(): string
    {
        return $this->formula;
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

    /**
     * @return array
     */
    public function getDbLinks(): array
    {
        return $this->dbLinks;
    }
}
