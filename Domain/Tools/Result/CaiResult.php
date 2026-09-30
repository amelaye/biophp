<?php
/**
 * Immutable value object holding the outcome of a Codon Adaptation Index calculation
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 30 September 2026
 */
namespace Amelaye\BioPHP\Domain\Tools\Result;

/**
 * The score is the geometric mean of relative synonymous codon usage across every SCORED codon of the
 * coding sequence, ranging from just above 0 (uses almost nothing but rare codons) to 1 (uses the most
 * frequent codon for every amino acid). codonsScored is how many codons that geometric mean was
 * actually taken over, after excluding stop codons and every amino acid with no synonym (Met, Trp
 * under the standard genetic code) - useful to judge how statistically meaningful the score is for a
 * short coding sequence.
 * Class CaiResult
 * @package Amelaye\BioPHP\Domain\Tools\Result
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class CaiResult
{
    /**
     * @var     float
     */
    private $score;

    /**
     * @var     int
     */
    private $codonsScored;

    /**
     * CaiResult constructor.
     * @param   float   $fScore
     * @param   int     $iCodonsScored
     */
    public function __construct(float $fScore, int $iCodonsScored)
    {
        $this->score = $fScore;
        $this->codonsScored = $iCodonsScored;
    }

    /**
     * @return  float
     */
    public function getScore(): float
    {
        return $this->score;
    }

    /**
     * @return  int
     */
    public function getCodonsScored(): int
    {
        return $this->codonsScored;
    }
}
