<?php
namespace Tests\Domain\Model;

use Amelaye\BioPHP\Domain\Model\PdbAtom;
use PHPUnit\Framework\TestCase;

class PdbAtomTest extends TestCase
{
    public function testNewPdbAtom()
    {
        $oAtom = new PdbAtom();
        $oAtom->setSerial(1);
        $oAtom->setName("CA");
        $oAtom->setAltLoc("A");
        $oAtom->setResName("ALA");
        $oAtom->setChainId("A");
        $oAtom->setResSeq(1);
        $oAtom->setX(11.104);
        $oAtom->setY(6.134);
        $oAtom->setZ(-6.504);
        $oAtom->setOccupancy(1.0);
        $oAtom->setTempFactor(20.0);
        $oAtom->setElement("C");

        $this->assertEquals(1, $oAtom->getSerial());
        $this->assertEquals("CA", $oAtom->getName());
        $this->assertEquals("A", $oAtom->getAltLoc());
        $this->assertEquals("ALA", $oAtom->getResName());
        $this->assertEquals("A", $oAtom->getChainId());
        $this->assertEquals(1, $oAtom->getResSeq());
        $this->assertEquals(11.104, $oAtom->getX());
        $this->assertEquals(6.134, $oAtom->getY());
        $this->assertEquals(-6.504, $oAtom->getZ());
        $this->assertEquals(1.0, $oAtom->getOccupancy());
        $this->assertEquals(20.0, $oAtom->getTempFactor());
        $this->assertEquals("C", $oAtom->getElement());
    }

    /**
     * Getters must not throw a TypeError when the corresponding setter was never called.
     */
    public function testGettersDoNotThrowWhenFieldsAreNotSet()
    {
        $oAtom = new PdbAtom();

        $this->assertSame(0, $oAtom->getSerial());
        $this->assertSame("", $oAtom->getName());
        $this->assertSame("", $oAtom->getAltLoc());
        $this->assertSame("", $oAtom->getResName());
        $this->assertSame("", $oAtom->getChainId());
        $this->assertSame(0, $oAtom->getResSeq());
        $this->assertSame(0.0, $oAtom->getX());
        $this->assertSame(0.0, $oAtom->getY());
        $this->assertSame(0.0, $oAtom->getZ());
        $this->assertSame(0.0, $oAtom->getOccupancy());
        $this->assertSame(0.0, $oAtom->getTempFactor());
        $this->assertSame("", $oAtom->getElement());
    }
}
