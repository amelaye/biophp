<?php
namespace Tests\Domain\Phylogenetics\ValueObject;

use Amelaye\BioPHP\Domain\Phylogenetics\Exception\InvalidDistanceMatrixException;
use Amelaye\BioPHP\Domain\Phylogenetics\ValueObject\DistanceMatrix;
use PHPUnit\Framework\TestCase;

class DistanceMatrixTest extends TestCase
{
    public function testExposesLabelsSizeAndDistances()
    {
        $oMatrix = new DistanceMatrix(
            ["A", "B", "C"],
            [
                [0, 3, 5],
                [3, 0, 6],
                [5, 6, 0],
            ]
        );

        $this->assertEquals(["A", "B", "C"], $oMatrix->getLabels());
        $this->assertEquals(3, $oMatrix->getSize());
        $this->assertEquals(6.0, $oMatrix->getDistance(1, 2));
        $this->assertEquals(
            [[0.0, 3.0, 5.0], [3.0, 0.0, 6.0], [5.0, 6.0, 0.0]],
            $oMatrix->getMatrix()
        );
    }

    public function testRejectsZeroTaxa()
    {
        $this->expectException(InvalidDistanceMatrixException::class);
        $this->expectExceptionMessage("at least one taxon");

        new DistanceMatrix([], []);
    }

    public function testRejectsAnEmptyLabel()
    {
        $this->expectException(InvalidDistanceMatrixException::class);
        $this->expectExceptionMessage("non-empty string");

        new DistanceMatrix(["A", ""], [[0, 1], [1, 0]]);
    }

    public function testRejectsADuplicateLabel()
    {
        $this->expectException(InvalidDistanceMatrixException::class);
        $this->expectExceptionMessage('"A" is given more than once');

        new DistanceMatrix(["A", "A"], [[0, 1], [1, 0]]);
    }

    public function testRejectsAMatrixWithTheWrongNumberOfRows()
    {
        $this->expectException(InvalidDistanceMatrixException::class);
        $this->expectExceptionMessage("must be 2 x 2");

        new DistanceMatrix(["A", "B"], [[0, 1]]);
    }

    public function testRejectsAMatrixWithTheWrongRowWidth()
    {
        $this->expectException(InvalidDistanceMatrixException::class);
        $this->expectExceptionMessage("must be 2 x 2");

        new DistanceMatrix(["A", "B"], [[0, 1, 9], [1, 0, 9]]);
    }

    public function testRejectsANonZeroDiagonal()
    {
        $this->expectException(InvalidDistanceMatrixException::class);
        $this->expectExceptionMessage("Distance of taxon 0 to itself must be 0");

        new DistanceMatrix(["A", "B"], [[1, 1], [1, 0]]);
    }

    public function testRejectsAnAsymmetricEntry()
    {
        $this->expectException(InvalidDistanceMatrixException::class);
        $this->expectExceptionMessage("not symmetric");

        new DistanceMatrix(["A", "B"], [[0, 1], [2, 0]]);
    }

    public function testRejectsANegativeDistance()
    {
        $this->expectException(InvalidDistanceMatrixException::class);
        $this->expectExceptionMessage("must not be negative");

        new DistanceMatrix(["A", "B"], [[0, -1], [-1, 0]]);
    }
}
