<?php
/**
 * Immutable value object describing one restriction enzyme cut on a circular plasmid
 * Freely inspired by BioPHP's project biophp.org
 * Created 24 September 2026
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Cloning\ValueObject;

/**
 * All positions are zero-based, consistently with CircularDnaSequence, and already wrapped within
 * [0, plasmid length[. recognitionPosition is where the enzyme's recognition pattern was matched;
 * upperCutPosition and lowerCutPosition are the boundary positions where the upper and lower strands
 * are actually severed, which may differ from recognitionPosition for enzymes cutting outside their
 * own recognition sequence (Type IIb and IIs). reverseStrand tells whether the recognition sequence
 * was found on the plasmid's own reference strand (false) or only detectable as its reverse
 * complement, i.e. the enzyme actually binds the other strand (true) ; upperCutPosition and
 * lowerCutPosition are always expressed in the reference strand's coordinates either way.
 * Class RestrictionCut
 * @package Amelaye\BioPHP\Domain\Cloning\ValueObject
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class RestrictionCut
{
    /**
     * @var     string
     */
    private string $enzymeName;

    /**
     * @var     int
     */
    private int $recognitionPosition;

    /**
     * @var     int
     */
    private int $upperCutPosition;

    /**
     * @var     int
     */
    private int $lowerCutPosition;

    /**
     * @var     RestrictionEnd
     */
    private RestrictionEnd $end;

    /**
     * @var     bool
     */
    private bool $reverseStrand;

    /**
     * RestrictionCut constructor.
     * @param   string          $sEnzymeName
     * @param   int             $iRecognitionPosition       Zero-based
     * @param   int             $iUpperCutPosition          Zero-based
     * @param   int             $iLowerCutPosition          Zero-based
     * @param   RestrictionEnd  $oEnd
     * @param   bool            $bReverseStrand             True when the recognition sequence was only
     * found as the reverse complement of the reference strand
     */
    public function __construct(
        string $sEnzymeName,
        int $iRecognitionPosition,
        int $iUpperCutPosition,
        int $iLowerCutPosition,
        RestrictionEnd $oEnd,
        bool $bReverseStrand = false
    ) {
        if (trim($sEnzymeName) === "") {
            throw new \InvalidArgumentException("Restriction cut enzyme name must not be empty.");
        }

        $this->enzymeName = $sEnzymeName;
        $this->recognitionPosition = $iRecognitionPosition;
        $this->upperCutPosition = $iUpperCutPosition;
        $this->lowerCutPosition = $iLowerCutPosition;
        $this->end = $oEnd;
        $this->reverseStrand = $bReverseStrand;
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

    /**
     * @return  bool        True when the recognition sequence was only found as the reverse
     * complement of the reference strand, i.e. the enzyme actually binds the other strand
     */
    public function isReverseStrand(): bool
    {
        return $this->reverseStrand;
    }
}
