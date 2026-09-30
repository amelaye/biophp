<?php
/**
 * Immutable value object describing one open reading frame found in a DNA sequence
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 30 September 2026
 */
namespace Amelaye\BioPHP\Domain\Tools\ValueObject;

/**
 * frame follows the standard six-frame convention : +1/+2/+3 for the forward strand (the reading
 * frame's zero-based offset plus one), -1/-2/-3 for the reverse strand (the same, on the reverse
 * complement). start/end are always given as ascending 1-based inclusive positions in the ORIGINAL,
 * forward-strand sequence - even for a reverse-strand ORF, the same convention GenBank's own
 * complement(start..end) location uses, so a negative frame's coordinates stay directly comparable
 * to a positive one's. end includes the stop codon's three bases when hasStopCodon() is true ;
 * getPeptide() itself never includes the "*" stop symbol.
 * Class OpenReadingFrame
 * @package Amelaye\BioPHP\Domain\Tools\ValueObject
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class OpenReadingFrame
{
    /**
     * @var     int
     */
    private $frame;

    /**
     * @var     int         1-based inclusive, ascending, in the original forward-strand sequence
     */
    private $start;

    /**
     * @var     int         1-based inclusive, ascending, in the original forward-strand sequence
     */
    private $end;

    /**
     * @var     string
     */
    private $peptide;

    /**
     * @var     bool
     */
    private $hasStopCodon;

    /**
     * OpenReadingFrame constructor.
     * @param   int         $iFrame         One of -3, -2, -1, 1, 2, 3
     * @param   int         $iStart
     * @param   int         $iEnd
     * @param   string      $sPeptide
     * @param   bool        $bHasStopCodon  False for an ORF still open at the end of the sequence
     */
    public function __construct(int $iFrame, int $iStart, int $iEnd, string $sPeptide, bool $bHasStopCodon)
    {
        if (!in_array($iFrame, [-3, -2, -1, 1, 2, 3], true)) {
            throw new \InvalidArgumentException(
                sprintf('Frame must be one of -3, -2, -1, 1, 2, 3, got %d.', $iFrame)
            );
        }

        if ($iStart < 1) {
            throw new \InvalidArgumentException(sprintf('Start must be at least 1, got %d.', $iStart));
        }

        if ($iEnd < $iStart) {
            throw new \InvalidArgumentException(
                sprintf('End (%d) must not be before start (%d).', $iEnd, $iStart)
            );
        }

        $this->frame = $iFrame;
        $this->start = $iStart;
        $this->end = $iEnd;
        $this->peptide = $sPeptide;
        $this->hasStopCodon = $bHasStopCodon;
    }

    /**
     * @return  int
     */
    public function getFrame(): int
    {
        return $this->frame;
    }

    /**
     * @return  int
     */
    public function getStart(): int
    {
        return $this->start;
    }

    /**
     * @return  int
     */
    public function getEnd(): int
    {
        return $this->end;
    }

    /**
     * @return  string
     */
    public function getPeptide(): string
    {
        return $this->peptide;
    }

    /**
     * @return  int
     */
    public function getLength(): int
    {
        return strlen($this->peptide);
    }

    /**
     * @return  bool
     */
    public function hasStopCodon(): bool
    {
        return $this->hasStopCodon;
    }

    /**
     * @return  bool
     */
    public function isReverseStrand(): bool
    {
        return $this->frame < 0;
    }
}
