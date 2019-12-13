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

        $this->assertEquals("NM_031438", $oGbSequence->getPrimAcc());
        $this->assertEquals("LINEAR", $oGbSequence->getTopology());
        $this->assertEquals("PRI", $oGbSequence->getDivision());
        $this->assertEquals("NM_031438.4", $oGbSequence->getVersion());
    }
}