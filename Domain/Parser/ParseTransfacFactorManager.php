<?php
/**
 * TRANSFAC factor.dat parsing
 * Freely inspired by BioPHP's project biophp.org
 * Created 12 September 2026
 * Last modified 12 September 2026
 */
namespace Amelaye\BioPHP\Domain\Parser;

/**
 * Class ParseTransfacFactorManager
 * A factor record describes one transcription factor : its name and synonyms, the organism it
 * comes from, the structural class it belongs to and its homologs in other species.
 * @package Amelaye\BioPHP\Domain\Parser
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class ParseTransfacFactorManager extends ParseTransfacAbstractManager
{
    /**
     * @var string
     */
    private $factorName = "";

    /**
     * @var array
     */
    private $synonyms = [];

    /**
     * Common name of the organism, e.g. "human".
     * @var string
     */
    private $organism = "";

    /**
     * Scientific name of the organism, e.g. "homo sapiens".
     * @var string
     */
    private $species = "";

    /**
     * @var array
     */
    private $taxClass = [];

    /**
     * @var array
     */
    private $homologs = [];

    /**
     * @var string
     */
    private $classAccession = "";

    /**
     * @var string
     */
    private $classId = "";

    /**
     * @var string
     */
    private $classDecimalNo = "";

    /**
     * @var string
     */
    private $sequence = "";

    /**
     * @var string
     */
    private $sSynonyms = "";

    /**
     * @var string
     */
    private $sTaxonomy = "";

    /**
     * @var string
     */
    private $sHomologs = "";

    /**
     * The name this format is known by in the collection records and in DatabaseParserFactory.
     * @return string
     */
    public static function getFormat() : string
    {
        return "TRANSFAC_FACTOR";
    }

    /**
     * Parses a TRANSFAC factor data file and populates this manager's fields.
     * @param   array       $aFlines        The lines the script has to parse
     * @throws  \Exception
     */
    public function parseDataFile($aFlines)
    {
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
                case "FA":
                    $this->factorName = $sData;
                    break;
                case "SY":
                    $this->sSynonyms = $this->append($this->sSynonyms, $sData);
                    break;
                case "OS":
                    $this->parseOrganism($sData);
                    break;
                case "OC":
                    $this->sTaxonomy = $this->append($this->sTaxonomy, $sData);
                    break;
                case "HO":
                    $this->sHomologs = $this->append($this->sHomologs, $sData);
                    break;
                case "CL":
                    $this->parseClass($sData);
                    break;
                case "SQ":
                    $this->sequence = $this->append($this->sequence, $sData);
                    break;
            }
        }

        $this->synonyms = $this->splitList($this->sSynonyms);
        $this->taxClass = $this->splitList($this->sTaxonomy);
        $this->homologs = $this->splitList($this->sHomologs, ",");
    }

    /**
     * Parses the OS line, which names the organism twice : commonly, then scientifically.
     * Format : OS  human, homo sapiens
     * @param   string      $sData
     */
    private function parseOrganism($sData)
    {
        $aTokens = $this->splitList($sData, ",");

        $this->organism = $aTokens[0] ?? "";
        $this->species  = $aTokens[1] ?? "";
    }

    /**
     * Parses the CL line, which points at the structural class of the factor.
     * Format : CL  C0001; CH; 2.3.3.0.1.
     * @param   string      $sData
     */
    private function parseClass($sData)
    {
        $aTokens = $this->splitList($sData);

        $this->classAccession = $aTokens[0] ?? "";
        $this->classId        = $aTokens[1] ?? "";
        $this->classDecimalNo = $aTokens[2] ?? "";
    }

    /**
     * @return string
     */
    public function getFactorName(): string
    {
        return $this->factorName;
    }

    /**
     * @return array
     */
    public function getSynonyms(): array
    {
        return $this->synonyms;
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
    public function getHomologs(): array
    {
        return $this->homologs;
    }

    /**
     * @return string
     */
    public function getClassAccession(): string
    {
        return $this->classAccession;
    }

    /**
     * @return string
     */
    public function getClassId(): string
    {
        return $this->classId;
    }

    /**
     * @return string
     */
    public function getClassDecimalNo(): string
    {
        return $this->classDecimalNo;
    }

    /**
     * @return string
     */
    public function getSequence(): string
    {
        return $this->sequence;
    }
}
