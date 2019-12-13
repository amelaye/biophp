<?php


namespace Tests\AppBundle\Entity;

use AppBundle\Entity\Sequencing\Reference;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ReferenceTest extends WebTestCase
{
    public function testNewReference()
    {
        $oReference = new Reference();
        $oReference->setPrimAcc("NM_031438");
        $oReference->setRefno("1");
        $oReference->setBaseRange("1 to 3488");
        $oReference->setTitle("Widespread macromolecular interaction perturbations in human genetic disorders");
        $oReference->setPubmed("25910212");
        $oReference->setJournal("Cell 161 (3), 647-660 (2015)");

        $this->assertEquals("NM_031438", $oReference->getPrimAcc());
        $this->assertEquals("1", $oReference->getRefno());
        $this->assertEquals("1 to 3488", $oReference->getBaseRange());
        $this->assertEquals("Widespread macromolecular interaction perturbations in human genetic disorders", $oReference->getTitle());
        $this->assertEquals("25910212", $oReference->getPubmed());
        $this->assertEquals("Cell 161 (3), 647-660 (2015)", $oReference->getJournal());
    }
}