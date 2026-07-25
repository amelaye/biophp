<?php
namespace Tests\Domain\Sequence\Entity;

use Amelaye\BioPHP\Domain\Sequence\Entity\GbSequence;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GbSequenceTest extends WebTestCase
{
    public function testNewGbSequence()
    {
        $oGbSequence = new GbSequence();
        $oGbSequence->setPrimAcc("NM_031438");
        $oGbSequence->setTopology("LINEAR");
        $oGbSequence->setDivision("PRI");
        $oGbSequence->setVersion("NM_031438.4");
        $oGbSequence->setStrands("test");
        $oGbSequence->setSegmentNo(1);
        $oGbSequence->setSegmentCount(10);
        $oGbSequence->setNcbiGiId("123");

        $this->assertEquals("NM_031438", $oGbSequence->getPrimAcc());
        $this->assertEquals("LINEAR", $oGbSequence->getTopology());
        $this->assertEquals("PRI", $oGbSequence->getDivision());
        $this->assertEquals("NM_031438.4", $oGbSequence->getVersion());
        $this->assertEquals("test", $oGbSequence->getStrands());
        $this->assertEquals(1, $oGbSequence->getSegmentNo());
        $this->assertEquals(10, $oGbSequence->getSegmentCount());
        $this->assertEquals("123", $oGbSequence->getNcbiGiId());
    }

    /**
     * Getters must not throw a TypeError when the corresponding setter was never called.
     * All fields except primAcc are nullable in the database, so they must stay null.
     */
    public function testGettersDoNotThrowWhenFieldsAreNotSet()
    {
        $oGbSequence = new GbSequence();

        $this->assertSame("", $oGbSequence->getPrimAcc());
        $this->assertNull($oGbSequence->getStrands());
        $this->assertNull($oGbSequence->getTopology());
        $this->assertNull($oGbSequence->getDivision());
        $this->assertNull($oGbSequence->getSegmentNo());
        $this->assertNull($oGbSequence->getSegmentCount());
        $this->assertNull($oGbSequence->getVersion());
        $this->assertNull($oGbSequence->getNcbiGiId());
    }
}