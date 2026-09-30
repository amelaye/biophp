<?php
namespace Tests\Domain\Alignment\Service;

use Amelaye\BioPHP\Domain\Alignment\Service\SimpleMatchMismatchScoring;
use PHPUnit\Framework\TestCase;

class SimpleMatchMismatchScoringTest extends TestCase
{
    public function testIdenticalSymbolsScoreAsAMatch()
    {
        $oScoring = new SimpleMatchMismatchScoring(1, -1);

        $this->assertEquals(1, $oScoring->score("A", "A"));
    }

    public function testDifferentSymbolsScoreAsAMismatch()
    {
        $oScoring = new SimpleMatchMismatchScoring(1, -1);

        $this->assertEquals(-1, $oScoring->score("A", "C"));
    }

    public function testComparisonIsCaseInsensitive()
    {
        $oScoring = new SimpleMatchMismatchScoring(1, -1);

        $this->assertEquals(1, $oScoring->score("a", "A"));
    }

    public function testCustomScoresAreHonored()
    {
        $oScoring = new SimpleMatchMismatchScoring(5, -4);

        $this->assertEquals(5, $oScoring->score("G", "G"));
        $this->assertEquals(-4, $oScoring->score("G", "T"));
    }
}
