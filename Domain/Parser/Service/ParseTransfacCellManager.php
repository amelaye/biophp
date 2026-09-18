<?php
/**
 * TRANSFAC cell.dat parsing
 * Freely inspired by BioPHP's project biophp.org
 * Created 12 September 2026
 * Last modified 12 September 2026
 */
namespace Amelaye\BioPHP\Domain\Parser\Service;

/**
 * Class ParseTransfacCellManager
 * A cell record describes the cell type or cell line a transcription factor was obtained from,
 * which is what ties a binding observation to the tissue it was seen in.
 * @package Amelaye\BioPHP\Domain\Parser\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class ParseTransfacCellManager extends ParseTransfacAbstractManager
{
    /**
     * Common name of the organism, e.g. "human".
     * @var string
     */
    private $organism = "";

    /**
     * @var string
     */
    private $factorSource = "";

    /**
     * @var string
     */
    private $description = "";

    /**
     * The name this format is known by in the collection records and in DatabaseParserFactory.
     * @return string
     */
    public static function getFormat() : string
    {
        return "TRANSFAC_CELL";
    }

    /**
     * Parses a TRANSFAC cell data file and populates this manager's fields.
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
                case "OS":
                    $this->organism = $sData;
                    break;
                case "SO":
                    $this->factorSource = $sData;
                    break;
                case "CD":
                    $this->description = $this->append($this->description, $sData);
                    break;
            }
        }
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
    public function getFactorSource(): string
    {
        return $this->factorSource;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }
}
