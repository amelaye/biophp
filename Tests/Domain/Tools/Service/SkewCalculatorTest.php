<?php
namespace Tests\Domain\Tools\Service;

use Amelaye\BioPHP\Domain\Tools\Service\SkewCalculator;
use PHPUnit\Framework\TestCase;

class SkewCalculatorTest extends TestCase
{
    private $calculator;

    public function setUp(): void
    {
        $this->calculator = new SkewCalculator();
    }

    /**
     * "GGGGCC" : A=0,C=2,G=4,T=0. gcSkew=(4-2)/6=1/3. atSkew=0 (guarded, A+T=0).
     * ketoSkew=(4+2-0-0)/6=1.0. gcContent=6/6=1.0.
     */
    public function testCalculatesAllFourMetricsForAGcRichWindow()
    {
        $oResult = $this->calculator->calculate("GGGGCC");

        $this->assertEqualsWithDelta(1 / 3, $oResult->getGcSkew(), 0.0000001);
        $this->assertEquals(0.0, $oResult->getAtSkew());
        $this->assertEquals(1.0, $oResult->getKetoSkew());
        $this->assertEquals(1.0, $oResult->getGcContent());
    }

    /**
     * An all-A/T window has an undefined GC-skew (0 G, 0 C) - guarded to 0.0 rather than a
     * PHP 8 DivisionByZeroError.
     */
    public function testGcSkewOfAnAllAtWindowIsZeroRatherThanDividingByZero()
    {
        $oResult = $this->calculator->calculate("AATT");

        $this->assertEquals(0.0, $oResult->getGcSkew());
        $this->assertEqualsWithDelta(0.0, $oResult->getAtSkew(), 0.0000001);
        $this->assertEquals(0.0, $oResult->getGcContent());
    }

    public function testAnEmptyWindowIsAllZeroesRatherThanDividingByZero()
    {
        $oResult = $this->calculator->calculate("");

        $this->assertEquals(0.0, $oResult->getGcSkew());
        $this->assertEquals(0.0, $oResult->getAtSkew());
        $this->assertEquals(0.0, $oResult->getKetoSkew());
        $this->assertEquals(0.0, $oResult->getGcContent());
    }

    /**
     * "AAAAGGGG" (8 bases), window size 4, step 2 : windows at 0,2,4 -> "AAAA","AAGG","GGGG".
     */
    public function testSlidingWindowScansAtTheGivenStep()
    {
        $aResults = $this->calculator->calculateSlidingWindow("AAAAGGGG", 4, 2);

        $this->assertEquals([0, 2, 4], array_keys($aResults));
        $this->assertEquals(0.0, $aResults[0]->getGcContent());
        $this->assertEquals(0.5, $aResults[2]->getGcContent());
        $this->assertEquals(1.0, $aResults[4]->getGcContent());
    }

    public function testRejectsAWindowSizeBelowOne()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->calculator->calculateSlidingWindow("ACGT", 0, 1);
    }

    public function testRejectsAStepBelowOne()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->calculator->calculateSlidingWindow("ACGT", 2, 0);
    }
}
