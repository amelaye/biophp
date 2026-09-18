<?php
/**
 * KEGG GENOME parsing
 * Freely inspired by BioPHP's project biophp.org
 * Created 12 September 2026
 * Last modified 12 September 2026
 */
namespace Amelaye\BioPHP\Domain\Parser\Service;

/**
 * Class ParseKeggGenomeManager
 * A genome record names one sequenced organism KEGG holds pathways for, and ties it to the NCBI
 * taxonomy. Not to be confused with ParseGenomeManager, which reads the sequencing statistics
 * of the Legacy "DOGS" records.
 * @package Amelaye\BioPHP\Domain\Parser\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class ParseKeggGenomeManager extends ParseKeggAbstractManager
{
    /**
     * @var string
     */
    private $definition = "";

    /**
     * NCBI taxonomy identifier, read out of the "TAX:" prefix the field writes it with.
     * @var string
     */
    private $taxonomy = "";

    /**
     * @var array
     */
    private $lineage = [];

    /**
     * The name this format is known by in the collection records and in DatabaseParserFactory.
     * @return string
     */
    public static function getFormat() : string
    {
        return "KEGG_GENOME";
    }

    /**
     * Parses a KEGG genome data file and populates this manager's fields.
     * @param   array       $aFlines        The lines the script has to parse
     * @throws  \Exception
     */
    public function parseDataFile($aFlines)
    {
        $aFields = $this->readFields($aFlines);

        $this->definition = isset($aFields["DEFINITION"]) ? $this->joinLines($aFields["DEFINITION"]) : "";
        $this->taxonomy   = isset($aFields["TAXONOMY"]) ? $this->readTaxonomy($aFields["TAXONOMY"][0]) : "";
        $this->lineage    = isset($aFields["LINEAGE"]) ? array_values(array_filter(array_map(
            'trim',
            explode(";", $this->joinLines($aFields["LINEAGE"]))
        ))) : [];
    }

    /**
     * Reads the TAXONOMY line, which carries the identifier behind a "TAX:" prefix.
     * Format : TAXONOMY    TAX:9606
     * @param   string      $sData
     * @return  string
     */
    private function readTaxonomy($sData)
    {
        $aTokens = preg_split("/:/", trim($sData), -1, PREG_SPLIT_NO_EMPTY);

        return trim($aTokens[1] ?? ($aTokens[0] ?? ""));
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
    public function getTaxonomy(): string
    {
        return $this->taxonomy;
    }

    /**
     * @return array
     */
    public function getLineage(): array
    {
        return $this->lineage;
    }
}
