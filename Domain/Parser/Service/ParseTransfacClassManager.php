<?php
/**
 * TRANSFAC class.dat parsing
 * Freely inspired by BioPHP's project biophp.org
 * Created 12 September 2026
 * Last modified 18 September 2026
 */
namespace Amelaye\BioPHP\Domain\Parser\Service;

/**
 * Class ParseTransfacClassManager
 * A class record describes a structural family of transcription factors - zinc fingers, leucine
 * zippers, helix-turn-helix - and lists the factors belonging to it.
 * @package Amelaye\BioPHP\Domain\Parser\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class ParseTransfacClassManager extends ParseTransfacAbstractManager
{
    /**
     * The classification, from the broadest level down to the narrowest.
     * @var array
     */
    private $classification = [];

    /**
     * @var string
     */
    private $structuralDescription = "";

    /**
     * @var array
     */
    private $memberFactors = [];

    /**
     * @var string
     */
    private $comments = "";

    /**
     * @var string
     */
    private $sClass = "";

    /**
     * The name this format is known by in the collection records and in DatabaseParserFactory.
     * @return string
     */
    public static function getFormat() : string
    {
        return "TRANSFAC_CLASS";
    }

    /**
     * Parses a TRANSFAC class data file and populates this manager's fields.
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
                case "CL":
                    $this->sClass = $this->append($this->sClass, $sData);
                    break;
                case "SD":
                    $this->structuralDescription = $this->append($this->structuralDescription, $sData);
                    break;
                case "BF":
                    $this->memberFactors[] = $sData;
                    break;
                case "CC":
                    $this->comments = $this->append($this->comments, $sData);
                    break;
            }
        }

        $this->classification = $this->splitList($this->sClass);
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
    public function getStructuralDescription(): string
    {
        return $this->structuralDescription;
    }

    /**
     * @return array
     */
    public function getMemberFactors(): array
    {
        return $this->memberFactors;
    }

    /**
     * @return string
     */
    public function getComments(): string
    {
        return $this->comments;
    }
}
