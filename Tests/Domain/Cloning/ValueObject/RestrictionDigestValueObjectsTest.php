<?php
namespace Tests\Domain\Cloning\ValueObject;

use Amelaye\BioPHP\Domain\Cloning\Aggregate\Plasmid;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\RestrictionCut;
use Amelaye\BioPHP\Domain\Cloning\Result\RestrictionDigestResult;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\RestrictionEnd;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\RestrictionFragment;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\CircularDnaSequence;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;
use PHPUnit\Framework\TestCase;

class RestrictionDigestValueObjectsTest extends TestCase
{
    public function testRestrictionCutExposesItsProperties()
    {
        $oEnd = RestrictionEnd::fivePrime("AATT");
        $oCut = new RestrictionCut("EcoRI", 4, 5, 9, $oEnd);

        $this->assertEquals("EcoRI", $oCut->getEnzymeName());
        $this->assertEquals(4, $oCut->getRecognitionPosition());
        $this->assertEquals(5, $oCut->getUpperCutPosition());
        $this->assertEquals(9, $oCut->getLowerCutPosition());
        $this->assertSame($oEnd, $oCut->getEnd());
    }

    public function testRestrictionCutRejectsAnEmptyEnzymeName()
    {
        $this->expectException(\InvalidArgumentException::class);

        new RestrictionCut("  ", 0, 0, 0, RestrictionEnd::blunt());
    }

    public function testRestrictionFragmentExposesItsProperties()
    {
        $oSequence = new DnaSequence("AATT");
        $oLeft = RestrictionEnd::fivePrime("AATT");
        $oRight = RestrictionEnd::blunt();

        $oFragment = new RestrictionFragment($oSequence, $oLeft, $oRight);

        $this->assertSame($oSequence, $oFragment->getSequence());
        $this->assertEquals(4, $oFragment->getLength());
        $this->assertSame($oLeft, $oFragment->getLeftEnd());
        $this->assertSame($oRight, $oFragment->getRightEnd());
    }

    public function testRestrictionDigestResultExposesItsProperties()
    {
        $oPlasmid = new Plasmid("p1", new CircularDnaSequence("ACGTACGTAC"));
        $oCut = new RestrictionCut("EcoRI", 0, 1, 5, RestrictionEnd::fivePrime("AATT"));
        $oFragment = new RestrictionFragment(new DnaSequence("ACGT"), RestrictionEnd::blunt(), RestrictionEnd::blunt());

        $oResult = new RestrictionDigestResult($oPlasmid, ["EcoRI"], [$oCut], [$oFragment], ["a warning"]);

        $this->assertSame($oPlasmid, $oResult->getPlasmid());
        $this->assertEquals(["EcoRI"], $oResult->getEnzymeNames());
        $this->assertSame([$oCut], $oResult->getCuts());
        $this->assertSame([$oFragment], $oResult->getFragments());
        $this->assertEquals(["a warning"], $oResult->getWarnings());
    }

    public function testRestrictionDigestResultDefaultsToNoWarnings()
    {
        $oPlasmid = new Plasmid("p1", new CircularDnaSequence("ACGTACGTAC"));

        $oResult = new RestrictionDigestResult($oPlasmid, [], [], []);

        $this->assertEquals([], $oResult->getWarnings());
    }

    public function testRestrictionDigestResultRejectsANonRestrictionCut()
    {
        $oPlasmid = new Plasmid("p1", new CircularDnaSequence("ACGTACGTAC"));

        $this->expectException(\InvalidArgumentException::class);

        new RestrictionDigestResult($oPlasmid, [], ["not a cut"], []);
    }

    public function testRestrictionDigestResultRejectsANonRestrictionFragment()
    {
        $oPlasmid = new Plasmid("p1", new CircularDnaSequence("ACGTACGTAC"));

        $this->expectException(\InvalidArgumentException::class);

        new RestrictionDigestResult($oPlasmid, [], [], ["not a fragment"]);
    }
}
