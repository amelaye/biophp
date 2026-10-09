<?php
/**
 * TRANSFAC site.dat parsing
 * Freely inspired by BioPHP's project biophp.org
 * Created 12 September 2026
 * Last modified 7 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Parser\Service;

/**
 * Class ParseTransfacSiteManager
 * A site record describes one regulatory element : the stretch of DNA a factor was found to
 * bind, where it sits relative to the transcription start site of its gene, and which factor
 * binds it.
 * @package Amelaye\BioPHP\Domain\Parser\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class ParseTransfacSiteManager extends ParseTransfacAbstractManager
{
    /**
     * Sequence type, e.g. "D" for a DNA element taken from a gene.
     * @var string
     */
    private string $seqType = "";

    /**
     * @var string
     */
    private string $description = "";

    /**
     * @var string
     */
    private string $geneRegion = "";

    /**
     * The sequence of the element itself.
     * @var string
     */
    private string $sequence = "";

    /**
     * Position of the first base, counted from the transcription start site, so a site sitting
     * upstream of it reads negative.
     * @var string
     */
    private string $firstPosition = "";

    /**
     * @var array
     */
    private array $bindingFactors = [];

    /**
     * @var string
     */
    private string $organism = "";

    /**
     * @var string
     */
    private string $method = "";

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
        return "TRANSFAC_SITE";
    }

    /**
     * Parses a TRANSFAC site data file and populates this manager's fields.
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
                case "TY":
                    $this->seqType = $sData;
                    break;
                case "DE":
                    $this->description = $this->append($this->description, $sData);
                    break;
                case "RE":
                    $this->geneRegion = $this->append($this->geneRegion, $sData);
                    break;
                case "SQ":
                    $this->sequence = $this->appendSequence($this->sequence, $sData);
                    break;
                case "SF":
                    $this->firstPosition = $sData;
                    break;
                case "BF":
                    $this->bindingFactors[] = $sData;
                    break;
                case "OS":
                    $this->organism = $sData;
                    break;
                case "MM":
                    $this->method = $this->append($this->method, $sData);
                    break;
                case "CC":
                    $this->comments = $this->append($this->comments, $sData);
                    break;
            }
        }
    }

    /**
     * @return string
     */
    public function getSeqType(): string
    {
        return $this->seqType;
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
    public function getGeneRegion(): string
    {
        return $this->geneRegion;
    }

    /**
     * @return string
     */
    public function getSequence(): string
    {
        return $this->sequence;
    }

    /**
     * @return string
     */
    public function getFirstPosition(): string
    {
        return $this->firstPosition;
    }

    /**
     * @return array
     */
    public function getBindingFactors(): array
    {
        return $this->bindingFactors;
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
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getComments(): string
    {
        return $this->comments;
    }
}
