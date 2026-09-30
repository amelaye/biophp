<?php
/**
 * Immutable value object describing one restriction enzyme cut on a circular plasmid
 * Freely inspired by BioPHP's project biophp.org
 * Created 24 September 2026
 * Last modified 24 September 2026
 */
namespace Amelaye\BioPHP\Domain\Cloning\ValueObject;

/**
 * All positions are zero-based, consistently with CircularDnaSequence, and already wrapped within
 * [0, plasmid length[. recognitionPosition is where the enzyme's recognition pattern was matched;
 * upperCutPosition and lowerCutPosition are the boundary positions where the upper and lower strands
 * are actually severed, which may differ from recognitionPosition for enzymes cutting outside their
 * own recognition sequence (Type IIb and IIs).
 * Class RestrictionCut
 * @package Amelaye\BioPHP\Domain\Cloning\ValueObject
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class RestrictionCut
{
    /**
     * @var     string
     */
    private $enzymeName;

    /**
     * @var     int
     */
    private $recognitionPosition;

    /**
     * @var     int
     */
    private $upperCutPosition;

    /**
     * @var     int
     */
    private $lowerCutPosition;

    /**
     * @var     RestrictionEnd
     */
    private $end;

    /**
     * RestrictionCut constructor.
     * @param   string          $sEnzymeName
     * @param   int             $iRecognitionPosition       Zero-based
     * @param   int             $iUpperCutPosition          Zero-based
     * @param   int             $iLowerCutPosition          Zero-based
     * @param   RestrictionEnd  $oEnd
     */
    public function __construct(
        string $sEnzymeName,
        int $iRecognitionPosition,
        int $iUpperCutPosition,
        int $iLowerCutPosition,
        RestrictionEnd $oEnd
    ) {
        if (trim($sEnzymeName) === "") {
            throw new \InvalidArgumentException("Restriction cut enzyme name must not be empty.");
        }

        $this->enzymeName = $sEnzymeName;
        $this->recognitionPosition = $iRecognitionPosition;
        $this->upperCutPosition = $iUpperCutPosition;
        $this->lowerCutPosition = $iLowerCutPosition;
        $this->end = $oEnd;
    }

    /**
     * @return  string
     */
    public function getEnzymeName(): string
    {
        return $this->enzymeName;
    }

    /**
     * @return  int
     */
    public function getRecognitionPosition(): int
    {
        return $this->recognitionPosition;
    }

    /**
     * @return  int
     */
    public function getUpperCutPosition(): int
    {
        return $this->upperCutPosition;
    }

    /**
     * @return  int
     */
    public function getLowerCutPosition(): int
    {
        return $this->lowerCutPosition;
    }

    /**
     * @return  RestrictionEnd
     */
    public function getEnd(): RestrictionEnd
    {
        return $this->end;
    }
}
