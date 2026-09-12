<?php
/**
 * TRANSFAC matrix.dat parsing
 * Freely inspired by BioPHP's project biophp.org
 * Created 12 September 2026
 * Last modified 12 September 2026
 */
namespace Amelaye\BioPHP\Domain\Parser;

/**
 * Class ParseTransfacMatrixManager
 * A matrix record holds the nucleotide weight matrix describing what a transcription factor
 * binds : one row per position of the binding site, counting how often each of A, C, G and T
 * was observed there.
 * @package Amelaye\BioPHP\Domain\Parser
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class ParseTransfacMatrixManager extends ParseTransfacAbstractManager
{
    /**
     * @var string
     */
    private $name = "";

    /**
     * @var string
     */
    private $description = "";

    /**
     * One row per position : ["A" => 1, "C" => 2, "G" => 2, "T" => 0, "consensus" => "N"].
     * @var array
     */
    private $matrix = [];

    /**
     * @var string
     */
    private $basis = "";

    /**
     * @var string
     */
    private $comments = "";

    /**
     * The name this format is known by in the collection records and in DatabaseParserFactory.
     * @return string
     */
    public static function getFormat() : string
    {
        return "TRANSFAC_MATRIX";
    }

    /**
     * Parses a TRANSFAC matrix data file and populates this manager's fields.
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
                case "NA":
                    $this->name = $sData;
                    break;
                case "DE":
                    $this->description = $this->append($this->description, $sData);
                    break;
                case "BA":
                    $this->basis = $this->append($this->basis, $sData);
                    break;
                case "CC":
                    $this->comments = $this->append($this->comments, $sData);
                    break;
                default:
                    if (is_numeric($sLabel)) {
                        $this->matrix[] = $this->parseMatrixRow($sData);
                    }
                    break;
            }
        }
    }

    /**
     * Parses one row of the weight matrix, the label being the position it describes.
     * Format : 01      1      2      2      0      N
     * @param   string      $sData
     * @return  array
     */
    private function parseMatrixRow($sData)
    {
        $aTokens = preg_split("/\s+/", $sData, -1, PREG_SPLIT_NO_EMPTY);

        return [
            "A" => (int) ($aTokens[0] ?? 0),
            "C" => (int) ($aTokens[1] ?? 0),
            "G" => (int) ($aTokens[2] ?? 0),
            "T" => (int) ($aTokens[3] ?? 0),
            "consensus" => $aTokens[4] ?? "",
        ];
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return array
     */
    public function getMatrix(): array
    {
        return $this->matrix;
    }

    /**
     * @return string
     */
    public function getBasis(): string
    {
        return $this->basis;
    }

    /**
     * @return string
     */
    public function getComments(): string
    {
        return $this->comments;
    }
}
