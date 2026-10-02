<?php
/**
 * TRANSFAC gene.dat parsing
 * Freely inspired by BioPHP's project biophp.org
 * Created 12 September 2026
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Parser\Service;

/**
 * Class ParseTransfacGeneManager
 * A gene record names a gene whose regulation TRANSFAC describes, and points at the regulatory
 * sites and composite elements found around it.
 * @package Amelaye\BioPHP\Domain\Parser\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class ParseTransfacGeneManager extends ParseTransfacAbstractManager
{
    /**
     * @var string
     */
    private string $shortDescription = "";

    /**
     * @var string
     */
    private string $description = "";

    /**
     * Common name of the organism, e.g. "human".
     * @var string
     */
    private string $organism = "";

    /**
     * Scientific name of the organism, e.g. "homo sapiens".
     * @var string
     */
    private string $species = "";

    /**
     * @var array
     */
    private array $taxClass = [];

    /**
     * @var array
     */
    private array $compelAccessions = [];

    /**
     * @var string
     */
    private string $sTaxonomy = "";

    /**
     * The name this format is known by in the collection records and in DatabaseParserFactory.
     * @return string
     */
    public static function getFormat() : string
    {
        return "TRANSFAC_GENE";
    }

    /**
     * Parses a TRANSFAC gene data file and populates this manager's fields.
     * @param   array       $aFlines        The lines the script has to parse
     * @throws  \Exception
     */
    public function parseDataFile(array $aFlines) {
        foreach ($aFlines as $sLine) {
            $sLabel = self::readLabel($sLine);
            $sData  = self::readData($sLine);

            if (self::isEntryEnd($sLine)) {
                break;
            }
            if ($this->parseCommonField($sLabel, $sData)) {
                continue;
            }

            switch ($sLabel) {
                case "SD":
                    $this->shortDescription = $sData;
                    break;
                case "DE":
                    $this->description = $this->append($this->description, $sData);
                    break;
                case "OS":
                    $this->parseOrganism($sData);
                    break;
                case "OC":
                    $this->sTaxonomy = $this->append($this->sTaxonomy, $sData);
                    break;
                case "CO":
                    $this->compelAccessions[] = $sData;
                    break;
            }
        }

        $this->taxClass = $this->splitList($this->sTaxonomy);
    }

    /**
     * Parses the OS line, which names the organism twice : commonly, then scientifically.
     * Format : OS  human, homo sapiens
     * @param   string      $sData
     */
    private function parseOrganism(string $sData) {
        $aTokens = $this->splitList($sData, ",");

        $this->organism = $aTokens[0] ?? "";
        $this->species  = $aTokens[1] ?? "";
    }

    /**
     * @return string
     */
    public function getShortDescription(): string
    {
        return $this->shortDescription;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getOrganism(): string
    {
        return $this->organism;
    }

    /**
     * @return string
     */
    public function getSpecies(): string
    {
        return $this->species;
    }

    /**
     * @return array
     */
    public function getTaxClass(): array
    {
        return $this->taxClass;
    }

    /**
     * @return array
     */
    public function getCompelAccessions(): array
    {
        return $this->compelAccessions;
    }
}
