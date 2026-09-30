<?php
/**
 * Immutable value object describing the outcome of a pairwise sequence alignment
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 30 September 2026
 */
namespace Amelaye\BioPHP\Domain\Alignment\Result;

use Amelaye\BioPHP\Domain\Alignment\Exception\InvalidAlignmentInputException;

/**
 * alignedFirst and alignedSecond are the two input sequences with '-' gap symbols inserted so they
 * share the same length, column by column. first/secondStart and first/secondEnd are zero-based,
 * inclusive positions in the ORIGINAL, ungapped sequences that the aligned region covers : for a
 * global alignment (Needleman-Wunsch) this always spans the whole of both sequences, but the same
 * shape is reused by local alignment (Smith-Waterman), where it identifies the aligned subsequence.
 * An empty original sequence (aligned as all gaps) is represented as start=0, end=-1, the usual
 * empty-range convention ; it is never used for the aligned strings themselves, which are never empty.
 * Class PairwiseAlignmentResult
 * @package Amelaye\BioPHP\Domain\Alignment\Result
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class PairwiseAlignmentResult
{
    /**
     * @var     string
     */
    private $alignedFirst;

    /**
     * @var     string
     */
    private $alignedSecond;

    /**
     * @var     int
     */
    private $score;

    /**
     * @var     int
     */
    private $firstStart;

    /**
     * @var     int
     */
    private $firstEnd;

    /**
     * @var     int
     */
    private $secondStart;

    /**
     * @var     int
     */
    private $secondEnd;

    /**
     * PairwiseAlignmentResult constructor.
     * @param   string      $sAlignedFirst      First sequence, with '-' gaps inserted
     * @param   string      $sAlignedSecond     Second sequence, with '-' gaps inserted, same length
     * as $sAlignedFirst
     * @param   int         $iScore
     * @param   int         $iFirstStart        Zero-based, inclusive, in the original first sequence
     * @param   int         $iFirstEnd          Zero-based, inclusive, in the original first sequence
     * @param   int         $iSecondStart       Zero-based, inclusive, in the original second sequence
     * @param   int         $iSecondEnd         Zero-based, inclusive, in the original second sequence
     */
    public function __construct(
        string $sAlignedFirst,
        string $sAlignedSecond,
        int $iScore,
        int $iFirstStart,
        int $iFirstEnd,
        int $iSecondStart,
        int $iSecondEnd
    ) {
        if ($sAlignedFirst === "" || $sAlignedSecond === "") {
            throw InvalidAlignmentInputException::emptyAlignedSequence();
        }

        if (strlen($sAlignedFirst) !== strlen($sAlignedSecond)) {
            throw InvalidAlignmentInputException::mismatchedAlignedLength(strlen($sAlignedFirst), strlen($sAlignedSecond));
        }

        $this->alignedFirst = $sAlignedFirst;
        $this->alignedSecond = $sAlignedSecond;
        $this->score = $iScore;
        $this->firstStart = $iFirstStart;
        $this->firstEnd = $iFirstEnd;
        $this->secondStart = $iSecondStart;
        $this->secondEnd = $iSecondEnd;
    }

    /**
     * @return  string
     */
    public function getAlignedFirst(): string
    {
        return $this->alignedFirst;
    }

    /**
     * @return  string
     */
    public function getAlignedSecond(): string
    {
        return $this->alignedSecond;
    }

    /**
     * @return  int
     */
    public function getScore(): int
    {
        return $this->score;
    }

    /**
     * @return  int         Column count of the alignment, gaps included
     */
    public function getLength(): int
    {
        return strlen($this->alignedFirst);
    }

    /**
     * @return  int
     */
    public function getFirstStart(): int
    {
        return $this->firstStart;
    }

    /**
     * @return  int
     */
    public function getFirstEnd(): int
    {
        return $this->firstEnd;
    }

    /**
     * @return  int
     */
    public function getSecondStart(): int
    {
        return $this->secondStart;
    }

    /**
     * @return  int
     */
    public function getSecondEnd(): int
    {
        return $this->secondEnd;
    }

    /**
     * The fraction of columns, among those where neither side is a gap, where both symbols are
     * identical. Gap columns are excluded from both the numerator and the denominator, which is the
     * usual "percent identity over aligned positions" convention.
     * @return  float       Between 0 and 1 ; 0 when every column involves a gap
     */
    public function getIdentity(): float
    {
        $iCompared = 0;
        $iMatching = 0;
        $iLength = $this->getLength();

        for ($i = 0; $i < $iLength; $i++) {
            $sFirst = $this->alignedFirst[$i];
            $sSecond = $this->alignedSecond[$i];

            if ($sFirst === "-" || $sSecond === "-") {
                continue;
            }

            $iCompared++;
            if ($sFirst === $sSecond) {
                $iMatching++;
            }
        }

        return $iCompared === 0 ? 0.0 : $iMatching / $iCompared;
    }
}
