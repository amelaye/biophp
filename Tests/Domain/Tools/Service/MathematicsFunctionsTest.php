<?php
namespace Tests\Domain\Tools\Service;

use Amelaye\BioPHP\Domain\Tools\Service\MathematicsFunctions;
use PHPUnit\Framework\TestCase;

class MathematicsFunctionsTest extends TestCase
{
    public function testMean()
    {
        $this->assertEquals(3, MathematicsFunctions::Mean([1, 2, 3, 4, 5]));
        $this->assertEquals(1.5, MathematicsFunctions::Mean([1, 2]));
        $this->assertEquals(2.333, MathematicsFunctions::Mean([1, 2, 4]));
    }

    public function testMeanIgnoresUnsetValues()
    {
        $this->assertEquals(2, MathematicsFunctions::Mean([1, null, 3]));
    }

    public function testMeanThrowsOnEmptyArray()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/Cannot calculate the mean of an empty data set !/');
        MathematicsFunctions::Mean([]);
    }

    public function testMeanThrowsWhenAllValuesAreNull()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/Cannot calculate the mean of an empty data set !/');
        MathematicsFunctions::Mean([null, null]);
    }

    public function testMeanWithSingleElement()
    {
        $this->assertEquals(5, MathematicsFunctions::Mean([5]));
    }

    public function testMedianOddCount()
    {
        $this->assertEquals(2, MathematicsFunctions::Median([3, 1, 2]));
    }

    public function testMedianEvenCount()
    {
        $this->assertEquals(2.5, MathematicsFunctions::Median([4, 3, 2, 1]));
    }

    public function testMedianSingleElement()
    {
        $this->assertEquals(5, MathematicsFunctions::Median([5]));
    }

    public function testVariance()
    {
        // Classic textbook dataset: mean = 5, sample variance (n-1) = 32/7
        $this->assertEquals(4.571, MathematicsFunctions::Variance([2, 4, 4, 4, 5, 5, 7, 9]));
    }

    public function testVarianceThrowsOnSingleElement()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/Cannot calculate the variance with fewer than 2 valid elements !/');
        MathematicsFunctions::Variance([5]);
    }

    public function testVarianceThrowsOnEmptyArray()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/Cannot calculate the mean of an empty data set !/');
        MathematicsFunctions::Variance([]);
    }

    public function testVarianceWithTwoElements()
    {
        $this->assertEquals(2, MathematicsFunctions::Variance([1, 3]));
    }

    public function testPearsonDistanceOfIdenticalSeriesIsZero()
    {
        $this->assertSame(0.0, MathematicsFunctions::PearsonDistance([1, 2, 3, 4], [1, 2, 3, 4]));
    }

    public function testPearsonDistanceOfPerfectlyAnticorrelatedSeriesIsTwo()
    {
        $this->assertEqualsWithDelta(2.0, MathematicsFunctions::PearsonDistance([1, 2, 3], [3, 2, 1]), 1e-12);
    }

    public function testPearsonDistanceHandComputed()
    {
        // x = 1,2,3,4 ; y = 1,3,2,4 : Sxy - SxSy/n = 29 - 25 = 4, Sxx = Syy = 30 - 25 = 5 => r = 4/5 = 0.8, distance 0.2
        $this->assertEqualsWithDelta(0.2, MathematicsFunctions::PearsonDistance([1, 2, 3, 4], [1, 3, 2, 4]), 1e-12);
    }

    public function testPearsonDistanceRefusesDifferentSizes()
    {
        $this->expectException(\InvalidArgumentException::class);
        MathematicsFunctions::PearsonDistance([1, 2, 3], [1, 2]);
    }

    public function testEuclideanDistance()
    {
        // length 1: scale = sqrt(2)/4 ; sqrt((3-0)^2 + (0-4)^2) = 5
        $this->assertEqualsWithDelta(
            sqrt(2) / 4 * 5,
            MathematicsFunctions::EuclideanDistance(['A' => 3, 'C' => 0], ['A' => 0, 'C' => 4], 1),
            1e-12
        );
    }

    public function testEuclideanDistanceOfIdenticalTablesIsZero()
    {
        $this->assertSame(0.0, MathematicsFunctions::EuclideanDistance(['A' => 2, 'C' => 5], ['A' => 2, 'C' => 5], 2));
    }

    public function testAlmeidaDistanceOfIdenticalTablesIsZero()
    {
        $this->assertEqualsWithDelta(
            0.0,
            MathematicsFunctions::AlmeidaDistance(['AA' => 1, 'AC' => 3, 'AG' => 2], ['AA' => 1, 'AC' => 3, 'AG' => 2]),
            1e-8
        );
    }

    public function testAlmeidaDistanceRefusesDifferentSizes()
    {
        $this->expectException(\InvalidArgumentException::class);
        MathematicsFunctions::AlmeidaDistance(['A' => 1, 'C' => 2], ['A' => 1]);
    }
}
