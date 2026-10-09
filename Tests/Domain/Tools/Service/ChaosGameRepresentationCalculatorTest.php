<?php
namespace Tests\Domain\Tools\Service;

use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;
use Amelaye\BioPHP\Domain\Tools\Service\ChaosGameRepresentationCalculator;
use PHPUnit\Framework\TestCase;

/**
 * The oracles are biotools' own computations, copied as they were : createCGRImage()'s loop on
 * pixels, and mapAreaData()'s offsets of an oligonucleotide in a 256 pixel square. The calculator
 * must put every point and every oligonucleotide where those put them.
 */
class ChaosGameRepresentationCalculatorTest extends TestCase
{
    private ChaosGameRepresentationCalculator $calculator;

    public function setUp(): void
    {
        $this->calculator = new ChaosGameRepresentationCalculator();
    }

    private static function randomSequence(int $iLength, int $iSeed): string
    {
        mt_srand($iSeed);
        $sSequence = "";
        for ($i = 0; $i < $iLength; $i++) {
            $sSequence .= "ACGT"[mt_rand(0, 3)];
        }

        return $sSequence;
    }

    /**
     * biotools ChaosGameRepresentationManager::createCGRImage(), the loop that fills $aPoints
     * @return  array   [[floor x, floor y], ...]
     */
    private static function biotoolsPixels(string $sSequence, int $iSize): array
    {
        $fX = round($iSize / 2);
        $fY = $fX;
        $aPoints = [];
        for ($i = 0; $i < strlen($sSequence); $i++) {
            $sW = substr($sSequence, $i, 1);
            if ($sW == "A") {
                $fX -= $fX / 2;
                $fY += ($iSize - $fY) / 2;
            }
            if ($sW == "C") {
                $fX -= $fX / 2;
                $fY -= $fY / 2;
            }
            if ($sW == "G") {
                $fX += ($iSize - $fX) / 2;
                $fY -= $fY / 2;
            }
            if ($sW == "T") {
                $fX += ($iSize - $fX) / 2;
                $fY += ($iSize - $fY) / 2;
            }
            $aPoints[] = [floor($fX), floor($fY)];
        }

        return $aPoints;
    }

    /**
     * biotools ChaosGameRepresentationManager::mapAreaData(), the offsets of one oligonucleotide
     * @return  array   [x, y] in pixels of a 256 pixel square
     */
    private static function biotoolsOffsets(string $sOligo): array
    {
        $fX = 0;
        $fY = 0;
        $iTt = 0;
        $iLen2 = strlen($sOligo);
        while ($iLen2 > 0) {
            $iLen2--;
            $fTtt = pow(2, $iTt);
            $iTt++;
            $sBase = substr($sOligo, $iLen2, 1);
            if ($sBase == "A" || $sBase == "T") {
                $fY += 128 / $fTtt;
            }
            if ($sBase == "G" || $sBase == "T") {
                $fX += 128 / $fTtt;
            }
        }

        return [$fX, $fY];
    }

    public function testThePointsAreWhereBiotoolsPutsThem()
    {
        foreach ([256, 512, 1024] as $iSize) {
            foreach ([1, 2, 3] as $iSeed) {
                $sSequence = self::randomSequence(300, $iSeed);

                $aExpected = self::biotoolsPixels($sSequence, $iSize);
                $aPoints = $this->calculator->points(new DnaSequence($sSequence));

                $this->assertCount(300, $aPoints);
                foreach ($aPoints as $i => [$fX, $fY]) {
                    $this->assertSame($aExpected[$i][0], floor($fX * $iSize), "size $iSize, seed $iSeed, point $i (x)");
                    $this->assertSame($aExpected[$i][1], floor($fY * $iSize), "size $iSize, seed $iSeed, point $i (y)");
                }
            }
        }
    }

    public function testEachBaseMovesTheRunningPointHalfWayToItsCorner()
    {
        $aPoints = $this->calculator->points(new DnaSequence("CGAT"));

        $this->assertSame([0.25, 0.25], $aPoints[0]);   // C, from the centre
        $this->assertSame([0.625, 0.125], $aPoints[1]); // G
        $this->assertSame([0.3125, 0.5625], $aPoints[2]); // A
        $this->assertSame([0.65625, 0.78125], $aPoints[3]); // T
    }

    public function testASymbolOtherThanACGTLeavesNoPointAndMovesNothing()
    {
        $this->assertSame(
            $this->calculator->points(new DnaSequence("ACGT")),
            $this->calculator->points(new DnaSequence("ACNRGT"))
        );
        $this->assertSame([], $this->calculator->points(new DnaSequence("")));
    }

    public function testTheOligonucleotidesStandWhereBiotoolsPutsThem()
    {
        foreach ([1, 2, 3, 4, 5] as $iLength) {
            $iCells = 1 << $iLength;
            foreach (array_keys($this->calculator->oligoCounts(new DnaSequence(""), $iLength)) as $sOligo) {
                [$iColumn, $iRow] = $this->calculator->cell($sOligo);
                [$fX, $fY] = self::biotoolsOffsets($sOligo);

                $this->assertSame($fX, $iColumn * (256 / $iCells), "$sOligo (column)");
                $this->assertSame($fY, $iRow * (256 / $iCells), "$sOligo (row)");
            }
        }
    }

    /**
     * An oligonucleotide's cell is where the chaos game, run on that word alone, ends : the grid is
     * the CGR cut in 2^k x 2^k squares.
     */
    public function testACellIsWhereTheChaosGameOfTheOligonucleotideEnds()
    {
        foreach ([1, 2, 3, 4] as $iLength) {
            foreach (array_keys($this->calculator->oligoCounts(new DnaSequence(""), $iLength)) as $sOligo) {
                $aPoints = $this->calculator->points(new DnaSequence($sOligo));
                [$fX, $fY] = $aPoints[count($aPoints) - 1];

                $this->assertSame([(int) floor($fX * (1 << $iLength)), (int) floor($fY * (1 << $iLength))], $this->calculator->cell($sOligo), $sOligo);
            }
        }
    }

    public function testTheCellsOfTheDinucleotidesFollowTheLayoutOfBiotoolsImage()
    {
        // the first row of the 4 x 4 image : CC GC CG GG ; the second : AC TC AG TG
        $this->assertSame([[0, 0], [1, 0], [2, 0], [3, 0]], array_map([$this->calculator, "cell"], ["CC", "GC", "CG", "GG"]));
        $this->assertSame([[0, 1], [1, 1], [2, 1], [3, 1]], array_map([$this->calculator, "cell"], ["AC", "TC", "AG", "TG"]));
        $this->assertSame([[0, 0], [0, 1], [1, 0], [1, 1]], array_map([$this->calculator, "cell"], ["C", "A", "G", "T"]));
    }

    public function testEveryOligonucleotideHasItsOwnCell()
    {
        foreach ([1, 2, 3, 4, 5] as $iLength) {
            $aCells = array_map(
                fn($sOligo) => implode(",", $this->calculator->cell($sOligo)),
                array_keys($this->calculator->oligoCounts(new DnaSequence(""), $iLength))
            );

            $this->assertCount(4 ** $iLength, array_unique($aCells));
        }
    }

    public function testTheOverlappingOligonucleotidesAreCounted()
    {
        $aCounts = $this->calculator->oligoCounts(new DnaSequence("AAAC"), 2);

        $this->assertCount(16, $aCounts);
        $this->assertSame(2, $aCounts["AA"]);
        $this->assertSame(1, $aCounts["AC"]);
        $this->assertSame(3, array_sum($aCounts));
    }

    public function testAnOligonucleotideHoldingAnotherSymbolIsLeftOut()
    {
        $aCounts = $this->calculator->oligoCounts(new DnaSequence("ACNGT"), 2);

        $this->assertSame(2, array_sum($aCounts));
        $this->assertSame(1, $aCounts["AC"]);
        $this->assertSame(1, $aCounts["GT"]);
    }

    /**
     * biotools counted the second strand as the sequence followed by its reverse complement, a
     * space between them : no oligonucleotide spans the join.
     */
    public function testBothStrandsAddTheReverseComplement()
    {
        $aOne = $this->calculator->oligoCounts(new DnaSequence("AACG"), 2);
        $aBoth = $this->calculator->oligoCounts(new DnaSequence("AACG"), 2, true);

        $this->assertSame(["AA" => 1, "AC" => 1, "CG" => 1], array_filter($aOne));
        // reverse complement CGTT : CG, GT, TT
        $this->assertSame(["AA" => 1, "AC" => 1, "CG" => 2, "GT" => 1, "TT" => 1], array_filter($aBoth));
    }

    public function testTheFrequencyMatrixPutsEachCountInItsCell()
    {
        $aMatrix = $this->calculator->frequencyMatrix(new DnaSequence("GCGCGC"), 2);

        $this->assertCount(4, $aMatrix);
        $this->assertCount(4, $aMatrix[0]);
        $this->assertSame(3, $aMatrix[0][1]); // GC : column 1, row 0
        $this->assertSame(2, $aMatrix[0][2]); // CG : column 2, row 0
        $this->assertSame(5, array_sum(array_map('array_sum', $aMatrix)));
    }

    public function testAnOligonucleotideLengthOutOfRangeIsRefused()
    {
        foreach ([0, 9, -1] as $iLength) {
            try {
                $this->calculator->oligoCounts(new DnaSequence("ACGT"), $iLength);
                $this->fail("A length of $iLength should be refused.");
            } catch (\InvalidArgumentException $ex) {
                $this->assertStringContainsString("1 to 8", $ex->getMessage());
            }
        }
    }

    public function testACellOfAWordThatIsNotMadeOfACGTIsRefused()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->calculator->cell("ACN");
    }
}
