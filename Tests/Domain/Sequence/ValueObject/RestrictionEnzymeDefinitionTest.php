<?php
namespace Tests\Domain\Sequence\ValueObject;

use Amelaye\BioPHP\Domain\Sequence\ValueObject\RestrictionEnzymeDefinition;
use PHPUnit\Framework\TestCase;

class RestrictionEnzymeDefinitionTest extends TestCase
{
    private function makeDefinition(array $aOverrides = []): RestrictionEnzymeDefinition
    {
        $aArgs = array_merge(
            [
                "name" => "EcoRI",
                "aliases" => ["EcoRI"],
                "family" => RestrictionEnzymeDefinition::TYPE_II,
                "recognitionPattern" => "G'AATT_C",
                "computingPattern" => "(GAATTC)",
                "recognitionLength" => 6,
                "cleavagePositionUpper" => 1,
                "cleavagePositionLower" => 4,
                "nonAmbiguousBaseCount" => 6,
            ],
            $aOverrides
        );

        return new RestrictionEnzymeDefinition(
            $aArgs["name"],
            $aArgs["aliases"],
            $aArgs["family"],
            $aArgs["recognitionPattern"],
            $aArgs["computingPattern"],
            $aArgs["recognitionLength"],
            $aArgs["cleavagePositionUpper"],
            $aArgs["cleavagePositionLower"],
            $aArgs["nonAmbiguousBaseCount"]
        );
    }

    public function testExposesAllItsProperties()
    {
        $oDefinition = $this->makeDefinition();

        $this->assertEquals("EcoRI", $oDefinition->getName());
        $this->assertEquals(RestrictionEnzymeDefinition::TYPE_II, $oDefinition->getFamily());
        $this->assertEquals("G'AATT_C", $oDefinition->getRecognitionPattern());
        $this->assertEquals("(GAATTC)", $oDefinition->getComputingPattern());
        $this->assertEquals(6, $oDefinition->getRecognitionLength());
        $this->assertEquals(1, $oDefinition->getCleavagePositionUpper());
        $this->assertEquals(4, $oDefinition->getCleavagePositionLower());
        $this->assertEquals(6, $oDefinition->getNonAmbiguousBaseCount());
    }

    public function testPreservesBothStrandCleavagePositionsIncludingNegativeOnes()
    {
        // AasI-like Type II enzyme: cuts upstream of its own recognition sequence.
        $oDefinition = $this->makeDefinition([
            "cleavagePositionUpper" => 7,
            "cleavagePositionLower" => -2,
        ]);

        $this->assertEquals(7, $oDefinition->getCleavagePositionUpper());
        $this->assertEquals(-2, $oDefinition->getCleavagePositionLower());
    }

    public function testStripsCleavageMarksFromTheCleanRecognitionSequence()
    {
        $oDefinition = $this->makeDefinition(["recognitionPattern" => "G_ACGT'C"]);

        $this->assertEquals("GACGTC", $oDefinition->getCleanRecognitionSequence());
    }

    public function testDeduplicatesTrimsAndDropsEmptyAliases()
    {
        $oDefinition = $this->makeDefinition(["aliases" => [" AatI", "Eco147I", "AatI", "", "  "]]);

        $this->assertEquals(["AatI", "Eco147I"], $oDefinition->getAliases());
    }

    public function testDetectsAmbiguousIupacBases()
    {
        // AasI: GACNN_NN'NNGTC, contains N (any base).
        $oDefinition = $this->makeDefinition(["recognitionPattern" => "GACNN_NN'NNGTC"]);

        $this->assertTrue($oDefinition->hasAmbiguousBases());
    }

    public function testDoesNotFlagAStrictAcgtPatternAsAmbiguous()
    {
        // AatI: AGG'CCT, only A, G, C and T.
        $oDefinition = $this->makeDefinition(["recognitionPattern" => "AGG'CCT"]);

        $this->assertFalse($oDefinition->hasAmbiguousBases());
    }

    public function testDoesNotMistakeTheLowercaseOrSeparatorForAnAmbiguousRBase()
    {
        // AciI: two alternative recognition sites joined by " or ", none of them ambiguous.
        $oDefinition = $this->makeDefinition(["recognitionPattern" => "C'CG_C or G'CG_G"]);

        $this->assertFalse($oDefinition->hasAmbiguousBases());
    }

    public function testRejectsAnEmptyName()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->makeDefinition(["name" => "  "]);
    }

    public function testRejectsAnInvalidFamily()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('EcoRI');

        $this->makeDefinition(["family" => "TYPE_III"]);
    }

    public function testRejectsAZeroRecognitionLength()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->makeDefinition(["recognitionLength" => 0]);
    }

    public function testRejectsANegativeRecognitionLength()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->makeDefinition(["recognitionLength" => -1]);
    }

    public function testRejectsANegativeNonAmbiguousBaseCount()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->makeDefinition(["nonAmbiguousBaseCount" => -1]);
    }

    public function testAcceptsEveryValidFamily()
    {
        foreach (RestrictionEnzymeDefinition::VALID_FAMILIES as $sFamily) {
            $oDefinition = $this->makeDefinition(["family" => $sFamily]);

            $this->assertEquals($sFamily, $oDefinition->getFamily());
        }
    }
}
