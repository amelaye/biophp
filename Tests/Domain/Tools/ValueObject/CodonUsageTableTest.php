<?php
namespace Tests\Domain\Tools\ValueObject;

use Amelaye\BioPHP\Domain\Tools\ValueObject\CodonUsageTable;
use PHPUnit\Framework\TestCase;

class CodonUsageTableTest extends TestCase
{
    public function testReturnsTheCountOfAKnownCodon()
    {
        $oTable = new CodonUsageTable(["TTT" => 30, "TTC" => 10]);

        $this->assertEquals(30, $oTable->getCount("TTT"));
    }

    public function testReturnsZeroForAnUnknownCodon()
    {
        $oTable = new CodonUsageTable(["TTT" => 30]);

        $this->assertEquals(0, $oTable->getCount("GGG"));
    }

    public function testLookupIsCaseInsensitiveAndNormalizesStorage()
    {
        $oTable = new CodonUsageTable(["ttt" => 30]);

        $this->assertEquals(30, $oTable->getCount("TTT"));
        $this->assertEquals(["TTT"], $oTable->getCodons());
    }

    public function testRejectsACodonOfTheWrongLength()
    {
        $this->expectException(\InvalidArgumentException::class);

        new CodonUsageTable(["TT" => 10]);
    }

    public function testRejectsACodonWithASymbolOutsideAcgt()
    {
        $this->expectException(\InvalidArgumentException::class);

        new CodonUsageTable(["TTN" => 10]);
    }

    public function testRejectsANegativeCount()
    {
        $this->expectException(\InvalidArgumentException::class);

        new CodonUsageTable(["TTT" => -1]);
    }
}
