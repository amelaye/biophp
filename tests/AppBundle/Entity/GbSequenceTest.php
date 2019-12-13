<?php


namespace Tests\AppBundle\Entity;


use AppBundle\Entity\Sequencing\GbSequence;
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
}