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

    /**
     * Rows keyed by label left [$i][$j] undefined : read as 0, the diagonal check passed and the
     * matrix held zeros instead of its distances.
     */
    public function testRowsKeyedByLabelAreReadInOrder()
    {
        $oMatrix = new DistanceMatrix(["A", "B"], [
            "A" => ["A" => 0, "B" => 3],
            "B" => ["A" => 3, "B" => 0],
        ]);

        $this->assertEquals(3.0, $oMatrix->getDistance(0, 1));
        $this->assertEquals(3.0, $oMatrix->getDistance(1, 0));
    }

    /**
     * An infinite distance was accepted : UPGMA then built branches of INF - INF, which
     * PhylogeneticNode refuses, so the tree builder failed far from the cause.
     */
    public function testRejectsAnInfiniteDistance()
    {
        $this->expectException(InvalidDistanceMatrixException::class);
        $this->expectExceptionMessage("finite");

        new DistanceMatrix(["A", "B"], [[0, INF], [INF, 0]]);
    }

    /**
     * The rows were read in their own order : a matrix keyed by label and not listed in the order of the
     * labels (symmetric, zero diagonal) was accepted, and d(A,C) read as d(B,C).
     */
    public function testAMatrixKeyedByLabelIsReadByLabelWhateverItsOrder()
    {
        $oMatrix = new DistanceMatrix(["A", "B", "C"], [
            "B" => ["B" => 0, "A" => 3, "C" => 6],
            "A" => ["B" => 3, "A" => 0, "C" => 5],
            "C" => ["B" => 6, "A" => 5, "C" => 0],
        ]);

        $this->assertEquals(3.0, $oMatrix->getDistance(0, 1));
        $this->assertEquals(5.0, $oMatrix->getDistance(0, 2));
        $this->assertEquals(6.0, $oMatrix->getDistance(1, 2));
    }

    public function testAListIsReadInOrderEvenWithNumericLabels()
    {
        $oMatrix = new DistanceMatrix(["1", "0"], [[0, 4], [4, 0]]);

        $this->assertEquals(4.0, $oMatrix->getDistance(0, 1));
    }

    /**
     * A distance that is no number was cast to 0 : two distinct taxa were taken for identical.
     */
    public function testRejectsADistanceThatIsNoNumber()
    {
        foreach (["n/a", null, "", [1]] as $mValue) {
            try {
                new DistanceMatrix(["A", "B"], [[0, $mValue], [$mValue, 0]]);
                $this->fail("A distance of " . var_export($mValue, true) . " was accepted.");
            } catch (InvalidDistanceMatrixException $oException) {
                $this->assertStringContainsString("is not a number", $oException->getMessage());
            }
        }

        $this->assertEquals(2.5, (new DistanceMatrix(["A", "B"], [[0, "2.5"], ["2.5", 0]]))->getDistance(0, 1));
    }
}
