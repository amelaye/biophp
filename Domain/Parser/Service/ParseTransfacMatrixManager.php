<?php
/**
 * TRANSFAC matrix.dat parsing
 * Freely inspired by BioPHP's project biophp.org
 * Created 12 September 2026
 * Last modified 7 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Parser\Service;

/**
 * Class ParseTransfacMatrixManager
 * A matrix record holds the nucleotide weight matrix describing what a transcription factor
 * binds : one row per position of the binding site, counting how often each of A, C, G and T
 * was observed there.
 * @package Amelaye\BioPHP\Domain\Parser\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class ParseTransfacMatrixManager extends ParseTransfacAbstractManager
{
    /**
     * @var string
     */
    private string $name = "";

    /**
     * @var string
     */
    private string $description = "";

    /**
     * One row per position : ["A" => 1, "C" => 2, "G" => 2, "T" => 0, "consensus" => "N"].
     * @var array
     */
    private array $matrix = [];

    /**
     * @var string
     */
    private string $basis = "";

    /**
     * @var string
     */
    private string $comments = "";

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
     * Parses one row of the weight matrix, the label being the position it describes. A matrix
     * holds counts (integers) or frequencies (0.25) : a value is an int or a float as written.
     * Format : 01      1      2      2      0      N
     * @param   string      $sData
     * @return  array
     */
    private function parseMatrixRow(string $sData) : array {
        $aTokens = preg_split("/\s+/", $sData, -1, PREG_SPLIT_NO_EMPTY);

        return [
            "A" => $this->readWeight($aTokens[0] ?? "0"),
            "C" => $this->readWeight($aTokens[1] ?? "0"),
            "G" => $this->readWeight($aTokens[2] ?? "0"),
            "T" => $this->readWeight($aTokens[3] ?? "0"),
            "consensus" => $aTokens[4] ?? "",
        ];
    }

    /**
     * @param   string      $sToken
     * @return  int|float   0 when the token is not a number
     */
    private function readWeight(string $sToken) {
        if (!is_numeric($sToken)) {
            return 0;
        }

        return $sToken + 0;
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
