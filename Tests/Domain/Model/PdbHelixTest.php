<?php
namespace Tests\Domain\Model;

use Amelaye\BioPHP\Domain\Model\PdbHelix;
use PHPUnit\Framework\TestCase;

class PdbHelixTest extends TestCase
{
    public function testNewPdbHelix()
    {
        $oHelix = new PdbHelix();
        $oHelix->setHelixId("H1");
        $oHelix->setInitResName("ALA");
        $oHelix->setInitChainId("A");
        $oHelix->setInitSeqNum(1);
        $oHelix->setEndResName("VAL");
        $oHelix->setEndChainId("A");
        $oHelix->setEndSeqNum(3);
        $oHelix->setHelixClass(1);
        $oHelix->setLength(3);

        $this->assertEquals("H1", $oHelix->getHelixId());
        $this->assertEquals("ALA", $oHelix->getInitResName());
        $this->assertEquals("A", $oHelix->getInitChainId());
        $this->assertEquals(1, $oHelix->getInitSeqNum());
        $this->assertEquals("VAL", $oHelix->getEndResName());
        $this->assertEquals("A", $oHelix->getEndChainId());
        $this->assertEquals(3, $oHelix->getEndSeqNum());
        $this->assertEquals(1, $oHelix->getHelixClass());
        $this->assertEquals(3, $oHelix->getLength());
    }

    /**
     * Getters must not throw a TypeError when the corresponding setter was never called.
     */
    public function testGettersDoNotThrowWhenFieldsAreNotSet()
    {
        $oHelix = new PdbHelix();

        $this->assertSame("", $oHelix->getHelixId());
        $this->assertSame("", $oHelix->getInitResName());
        $this->assertSame("", $oHelix->getInitChainId());
        $this->assertSame(0, $oHelix->getInitSeqNum());
        $this->assertSame("", $oHelix->getEndResName());
        $this->assertSame("", $oHelix->getEndChainId());
        $this->assertSame(0, $oHelix->getEndSeqNum());
        $this->assertSame(0, $oHelix->getHelixClass());
        $this->assertSame(0, $oHelix->getLength());
    }
}
