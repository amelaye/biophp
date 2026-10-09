<?php
namespace Tests\Domain\Alignment\Service;

use Amelaye\BioPHP\Domain\Alignment\Result\PairwiseAlignmentResult;
use Amelaye\BioPHP\Domain\Alignment\Service\NeedlemanWunschAligner;
use Amelaye\BioPHP\Domain\Alignment\Service\SemiGlobalAligner;
use Amelaye\BioPHP\Domain\Alignment\Service\SimpleMatchMismatchScoring;
use Amelaye\BioPHP\Domain\Alignment\Service\SmithWatermanAligner;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;
use PHPUnit\Framework\TestCase;

/**
 * What must hold for any pair of sequences, whatever the pair : the alignment is a faithful
 * rearrangement of its inputs, its score is what its columns add up to, and swapping the two
 * sequences or reverse-complementing both changes nothing. The pairs are random but seeded, so a
 * failure replays.
 */
class AlignerPropertiesTest extends TestCase
{
    private const GAP = -3;

    private SimpleMatchMismatchScoring $scoring;

    public function setUp(): void
    {
        $this->scoring = new SimpleMatchMismatchScoring(2, -1);
    }

    /**
     * @return  array   40 pairs of DNA strings, 1 to 14 bases each
     */
    private static function pairs(): array
    {
        mt_srand(20261009);
        $aPairs = [];
        for ($i = 0; $i < 40; $i++) {
            $aPair = [];
            foreach ([0, 1] as $iIndex) {
                $sSequence = "";
                for ($j = mt_rand(1, 14); $j > 0; $j--) {
                    $sSequence .= "ACGT"[mt_rand(0, 3)];
                }
                $aPair[] = $sSequence;
            }
            $aPairs[] = $aPair;
        }

        return $aPairs;
    }

    /**
     * A local alignment needs a pair scoring above zero : two sequences with no base in common have
     * none, and SmithWatermanAligner says so (see testLocalAlignmentOfSequencesWithNothingInCommonThrows).
     */
    private static function shareABase(string $sFirst, string $sSecond): bool
    {
        return array_intersect(str_split($sFirst), str_split($sSecond)) !== [];
    }

    private static function reverseComplement(string $sSequence): string
    {
        return strrev(strtr($sSequence, "ACGT", "TGCA"));
    }

    /**
     * @param   PairwiseAlignmentResult     $oResult
     * @return  int     The score the columns add up to : a gap costs GAP, a pair its substitution score
     */
    private function recomputeScore(PairwiseAlignmentResult $oResult): int
    {
        $sFirst = $oResult->getAlignedFirst();
        $sSecond = $oResult->getAlignedSecond();
        $iScore = 0;
        for ($i = 0; $i < strlen($sFirst); $i++) {
            $iScore += ($sFirst[$i] === "-" || $sSecond[$i] === "-")
                ? self::GAP
                : $this->scoring->score($sFirst[$i], $sSecond[$i]);
        }

        return $iScore;
    }

    private function align($oAligner, string $sFirst, string $sSecond): PairwiseAlignmentResult
    {
        return $oAligner->align(new DnaSequence($sFirst), new DnaSequence($sSecond), $this->scoring, self::GAP);
    }

    public function testGlobalAlignmentKeepsBothSequencesAndAddsUpToItsScore()
    {
        $oAligner = new NeedlemanWunschAligner();
        foreach (self::pairs() as [$sFirst, $sSecond]) {
            $oResult = $this->align($oAligner, $sFirst, $sSecond);

            $this->assertSame($sFirst, str_replace("-", "", $oResult->getAlignedFirst()), "$sFirst/$sSecond");
            $this->assertSame($sSecond, str_replace("-", "", $oResult->getAlignedSecond()), "$sFirst/$sSecond");
            $this->assertSame($this->recomputeScore($oResult), $oResult->getScore(), "$sFirst/$sSecond");
        }
    }

    public function testGlobalAndLocalScoresAreTheSameWhateverTheOrderOfTheSequences()
    {
        foreach ([new NeedlemanWunschAligner(), new SmithWatermanAligner(), new SemiGlobalAligner()] as $oAligner) {
            foreach (self::pairs() as [$sFirst, $sSecond]) {
                if (!self::shareABase($sFirst, $sSecond)) {
                    continue;
                }
                $this->assertSame(
                    $this->align($oAligner, $sFirst, $sSecond)->getScore(),
                    $this->align($oAligner, $sSecond, $sFirst)->getScore(),
                    get_class($oAligner) . " $sFirst/$sSecond"
                );
            }
        }
    }

    /**
     * The reverse complement of both strands is the same pair seen from the other strand : the
     * score of the best global alignment cannot depend on it.
     */
    public function testGlobalScoreIsTheSameOnTheReverseComplementStrand()
    {
        $oAligner = new NeedlemanWunschAligner();
        foreach (self::pairs() as [$sFirst, $sSecond]) {
            $this->assertSame(
                $this->align($oAligner, $sFirst, $sSecond)->getScore(),
                $this->align($oAligner, self::reverseComplement($sFirst), self::reverseComplement($sSecond))->getScore(),
                "$sFirst/$sSecond"
            );
        }
    }

    public function testLocalAlignmentIsAPieceOfBothSequencesAndAddsUpToItsScore()
    {
        $oAligner = new SmithWatermanAligner();
        foreach (self::pairs() as [$sFirst, $sSecond]) {
            if (!self::shareABase($sFirst, $sSecond)) {
                continue;
            }
            $oResult = $this->align($oAligner, $sFirst, $sSecond);

            $this->assertStringContainsString(str_replace("-", "", $oResult->getAlignedFirst()), $sFirst, "$sFirst/$sSecond");
            $this->assertStringContainsString(str_replace("-", "", $oResult->getAlignedSecond()), $sSecond, "$sFirst/$sSecond");
            $this->assertSame($this->recomputeScore($oResult), $oResult->getScore(), "$sFirst/$sSecond");
            $this->assertGreaterThan(0, $oResult->getScore());
        }
    }

    /**
     * A local alignment may stop wherever it likes and a semi-global one may leave its ends free,
     * so neither can score less than the global alignment of the same pair.
     */
    public function testLocalAndSemiGlobalScoresAreNeverBelowTheGlobalOne()
    {
        $oGlobal = new NeedlemanWunschAligner();
        foreach (self::pairs() as [$sFirst, $sSecond]) {
            if (!self::shareABase($sFirst, $sSecond)) {
                continue;
            }
            $iGlobal = $this->align($oGlobal, $sFirst, $sSecond)->getScore();

            $this->assertGreaterThanOrEqual($iGlobal, $this->align(new SmithWatermanAligner(), $sFirst, $sSecond)->getScore(), "$sFirst/$sSecond");
            $this->assertGreaterThanOrEqual($iGlobal, $this->align(new SemiGlobalAligner(), $sFirst, $sSecond)->getScore(), "$sFirst/$sSecond");
        }
    }

    /**
     * Against an empty sequence the global alignment is one run of gaps.
     */
    public function testGlobalAlignmentAgainstAnEmptySequenceIsOnlyGaps()
    {
        $oAligner = new NeedlemanWunschAligner();

        $oResult = $this->align($oAligner, "ACGT", "");
        $this->assertSame("ACGT", $oResult->getAlignedFirst());
        $this->assertSame("----", $oResult->getAlignedSecond());
        $this->assertSame(4 * self::GAP, $oResult->getScore());

        $this->assertSame(4 * self::GAP, $this->align($oAligner, "", "ACGT")->getScore());
    }

    /**
     * A ten-base stretch shared by two sequences whose flanks cannot pair (C against G) is found
     * whole, and scores 10 matches. Worked by hand : 10 x 2.
     */
    public function testLocalAlignmentFindsASingleTenBaseMatch()
    {
        $oResult = $this->align(new SmithWatermanAligner(), "CCCCCACGTTGCAAGCCCCC", "GGGGGACGTTGCAAGGGGGG");

        $this->assertSame("ACGTTGCAAG", $oResult->getAlignedFirst());
        $this->assertSame("ACGTTGCAAG", $oResult->getAlignedSecond());
        $this->assertSame(20, $oResult->getScore());
    }

    /**
     * A sequence wholly contained in the other is an overlap in full : its length times a match, and
     * no gap to pay for the other sequence's ends.
     */
    public function testSemiGlobalAlignmentOfAContainedSequenceCostsNoGap()
    {
        $oResult = $this->align(new SemiGlobalAligner(), "TTGC", "ACGTTGCA");

        $this->assertSame(4 * 2, $oResult->getScore());
        $this->assertSame("TTGC", $oResult->getAlignedFirst());
        $this->assertSame("TTGC", $oResult->getAlignedSecond());
    }

    /**
     * Two sequences with no base in common have no local alignment scoring above zero : there is
     * nothing to report, and the aligner says so instead of returning an empty alignment.
     */
    public function testLocalAlignmentOfSequencesWithNothingInCommonThrows()
    {
        $this->expectException(\Amelaye\BioPHP\Domain\Alignment\Exception\InvalidAlignmentInputException::class);

        $this->align(new SmithWatermanAligner(), "AAAA", "CCCC");
    }
}
