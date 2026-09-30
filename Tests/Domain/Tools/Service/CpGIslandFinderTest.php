<?php
namespace Tests\Domain\Tools\Service;

use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;
use Amelaye\BioPHP\Domain\Tools\Service\CpGIslandFinder;
use Amelaye\BioPHP\Domain\Tools\Service\SkewCalculator;
use PHPUnit\Framework\TestCase;

class CpGIslandFinderTest extends TestCase
{
    private $finder;

    public function setUp(): void
    {
        $this->finder = new CpGIslandFinder(new SkewCalculator());
    }

    /**
     * Sequence : 20 "A" + ("CG" repeated 20 times, 40 bases) + 20 "A" = 80 bases, a clean single
     * CpG-island-like region flanked by A-only DNA. Scanned with window size 20, step 5, the usual
     * 50% GC / 0.6 Obs-Exp thresholds. Independently re-derived twice before being written here :
     * first hand-traced window by window (which 0-based start positions 0,5,10,...,60 qualify, and
     * how the qualifying ones merge), then cross-checked with a standalone PHP script implementing
     * the exact same algorithm. Both agree on a single merged island, 0-based span [10,70) :
     * 1-based start=11, end=70, length=60 (10 leftover "A"s on each side of the CG region never
     * clear the 50% GC threshold once averaged into a 20-base window, so they're correctly excluded).
     * Recomputed over that full 60-base merged span (not inherited from one window) :
     * 20 A + 20x"CG" + 10 A = A=20, C=20, G=20 -> gcContent=(20+20)/60=2/3 ; CpG count=20 (the "CG"
     * repeat, no boundary pairs) -> Obs/Exp=(20*60)/(20*20)=3.0.
     */
    public function testFindsASingleMergedIslandInAGcRichRegionFlankedByAtOnlyDna()
    {
        $sSequence = str_repeat("A", 20) . str_repeat("CG", 20) . str_repeat("A", 20);

        $aIslands = $this->finder->findIslands(new DnaSequence($sSequence), 20, 5, 0.5, 0.6);

        $this->assertCount(1, $aIslands);
        $this->assertEquals(11, $aIslands[0]->getStart());
        $this->assertEquals(70, $aIslands[0]->getEnd());
        $this->assertEquals(60, $aIslands[0]->getLength());
        $this->assertEqualsWithDelta(2 / 3, $aIslands[0]->getGcContent(), 0.000001);
        $this->assertEqualsWithDelta(3.0, $aIslands[0]->getObservedToExpectedRatio(), 0.000001);
    }

    public function testAnAtOnlySequenceHasNoIsland()
    {
        $aIslands = $this->finder->findIslands(new DnaSequence(str_repeat("AT", 50)), 20, 5);

        $this->assertCount(0, $aIslands);
    }

    public function testASequenceShorterThanTheWindowProducesNoIsland()
    {
        $aIslands = $this->finder->findIslands(new DnaSequence("CGCGCGCG"), 20, 1);

        $this->assertCount(0, $aIslands);
    }

    public function testRejectsAWindowSizeBelowOne()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->finder->findIslands(new DnaSequence("ACGT"), 0, 1);
    }

    public function testRejectsAStepBelowOne()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->finder->findIslands(new DnaSequence("ACGT"), 4, 0);
    }
}
