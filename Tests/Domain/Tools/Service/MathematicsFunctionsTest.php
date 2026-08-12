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
}
