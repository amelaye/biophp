<?php
namespace Tests\Domain\Tools\Service;

use Amelaye\BioPHP\Api\DTO\TmBaseStackingDTO;
use Amelaye\BioPHP\Api\Interfaces\TmBaseStackingApiAdapter;
use Amelaye\BioPHP\Domain\Tools\Service\MeltingTemperatureCalculator;
use PHPUnit\Framework\TestCase;

/**
 * A minimal stand-in for the real TmBaseStackingApi, carrying the published SantaLucia (1998)
 * unified nearest-neighbor parameters (http://www.ncbi.nlm.nih.gov/pmc/articles/PMC19045/table/T2/),
 * the same values TmBaseStackingDTO's own docblock cites - no live HTTP call involved.
 */
class StubTmBaseStackingApiAdapter implements TmBaseStackingApiAdapter
{
    public function getTmBaseStackings(): array
    {
        $aValues = [
            "AA" => [-7.9, -22.2], "AC" => [-8.4, -22.4], "AG" => [-7.8, -21.0], "AT" => [-7.2, -20.4],
            "CA" => [-8.5, -22.7], "CC" => [-8.0, -19.9], "CG" => [-10.6, -27.2], "CT" => [-7.8, -21.0],
            "GA" => [-8.2, -22.2], "GC" => [-9.8, -24.4], "GG" => [-8.0, -19.9], "GT" => [-8.4, -22.4],
            "TA" => [-7.2, -21.3], "TC" => [-8.2, -22.2], "TG" => [-8.5, -22.7], "TT" => [-7.9, -22.2],
        ];

        $aDtos = [];
        foreach ($aValues as $sId => $aPair) {
            $oDto = new TmBaseStackingDTO();
            $oDto->setId($sId);
            $oDto->setTemperatureEnthalpy($aPair[0]);
            $oDto->setTemperatureEnthropy($aPair[1]);
            $aDtos[] = $oDto;
        }

        return $aDtos;
    }

    public static function GetEnthropyValues(array $aTmBaseStackings): array
    {
        $aValues = [];
        foreach ($aTmBaseStackings as $oDto) {
            $aValues[$oDto->getId()] = $oDto->getTemperatureEnthropy();
        }
        return $aValues;
    }

    public static function GetEnthalpyValues(array $aTmBaseStackings): array
    {
        $aValues = [];
        foreach ($aTmBaseStackings as $oDto) {
            $aValues[$oDto->getId()] = $oDto->getTemperatureEnthalpy();
        }
        return $aValues;
    }
}

class MeltingTemperatureCalculatorTest extends TestCase
{
    private $calculator;

    public function setUp(): void
    {
        $this->calculator = new MeltingTemperatureCalculator(new StubTmBaseStackingApiAdapter());
    }

    public function testCalculatesGcPercent()
    {
        $this->assertEquals(50.0, $this->calculator->calculateGcPercent("ACGT"));
    }

    public function testGcPercentOfAnEmptyPrimerIsZero()
    {
        $this->assertEquals(0.0, $this->calculator->calculateGcPercent(""));
    }

    /**
     * "ACGT" has no degenerate symbol, so its weakest and strongest possible readings collapse to
     * the same normalized string "AGGA" (A/T -> "A", C/G -> "G") ; both bounds must agree :
     * 2 weak ("A") + 2 strong ("G") -> 2*2 + 4*2 = 12.
     */
    public function testMinimumAndMaximumTmAgreeWhenThePrimerHasNoDegenerateSymbol()
    {
        $this->assertEquals(12.0, $this->calculator->calculateMinimumTm("ACGT"));
        $this->assertEquals(12.0, $this->calculator->calculateMaximumTm("ACGT"));
    }

    /**
     * "ACGN" : minimum reading resolves "N" to the weak class (-> "A"), normalized "AGGA" ;
     * 2 weak + 2 strong -> 2*2+4*2=12. Maximum reading resolves "N" to the strong class (-> "G"),
     * normalized "AGGG" ; 1 weak + 3 strong -> 2*1+4*3=14.
     */
    public function testMinimumAndMaximumTmDivergeForADegeneratePrimer()
    {
        $this->assertEquals(12.0, $this->calculator->calculateMinimumTm("ACGN"));
        $this->assertEquals(14.0, $this->calculator->calculateMaximumTm("ACGN"));
    }

    public function testTmOfAnEmptyPrimerIsZero()
    {
        $this->assertEquals(0.0, $this->calculator->calculateMinimumTm(""));
        $this->assertEquals(0.0, $this->calculator->calculateMaximumTm(""));
    }

    /**
     * Independently re-derived with a standalone PHP script using the same published SantaLucia
     * values before being written here (see the commit's own verification) :
     * H=-22.80 kcal/mol, S=-67.11 cal/(mol*K), Tm=-42.1 degrees C, for primer "ACGT",
     * 250 nM primer / 50 mM salt / 0 mM Mg2+. A very short 4-mer's nearest-neighbor Tm coming out
     * negative is an expected edge case of the model (meant for much longer primers), not a bug.
     */
    public function testCalculatesNearestNeighborTmEnthalpyAndEntropy()
    {
        $oResult = $this->calculator->calculateNearestNeighborTm("ACGT", 250, 50, 0);

        $this->assertEqualsWithDelta(-22.80, $oResult->getEnthalpy(), 0.001);
        $this->assertEqualsWithDelta(-67.11, $oResult->getEntropy(), 0.001);
        $this->assertEqualsWithDelta(-42.1, $oResult->getTm(), 0.001);
    }

    public function testRejectsADegeneratePrimerForNearestNeighborTm()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("degenerate nucleotides");

        $this->calculator->calculateNearestNeighborTm("ACGN", 250, 50, 0);
    }
}
