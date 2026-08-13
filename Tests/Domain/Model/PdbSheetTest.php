<?php
namespace Tests\Domain\Model;

use Amelaye\BioPHP\Domain\Model\PdbSheet;
use PHPUnit\Framework\TestCase;

class PdbSheetTest extends TestCase
{
    public function testNewPdbSheet()
    {
        $oSheet = new PdbSheet();
        $oSheet->setSheetId("S1");
        $oSheet->setStrand(1);
        $oSheet->setInitResName("LEU");
        $oSheet->setInitChainId("A");
        $oSheet->setInitSeqNum(4);
        $oSheet->setEndResName("ILE");
        $oSheet->setEndChainId("A");
        $oSheet->setEndSeqNum(5);

        $this->assertEquals("S1", $oSheet->getSheetId());
        $this->assertEquals(1, $oSheet->getStrand());
        $this->assertEquals("LEU", $oSheet->getInitResName());
        $this->assertEquals("A", $oSheet->getInitChainId());
        $this->assertEquals(4, $oSheet->getInitSeqNum());
        $this->assertEquals("ILE", $oSheet->getEndResName());
        $this->assertEquals("A", $oSheet->getEndChainId());
        $this->assertEquals(5, $oSheet->getEndSeqNum());
    }

    /**
     * Getters must not throw a TypeError when the corresponding setter was never called.
     */
    public function testGettersDoNotThrowWhenFieldsAreNotSet()
    {
        $oSheet = new PdbSheet();

        $this->assertSame("", $oSheet->getSheetId());
        $this->assertSame(0, $oSheet->getStrand());
        $this->assertSame("", $oSheet->getInitResName());
        $this->assertSame("", $oSheet->getInitChainId());
        $this->assertSame(0, $oSheet->getInitSeqNum());
        $this->assertSame("", $oSheet->getEndResName());
        $this->assertSame("", $oSheet->getEndChainId());
        $this->assertSame(0, $oSheet->getEndSeqNum());
    }
}
