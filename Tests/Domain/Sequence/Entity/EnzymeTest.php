<?php
namespace Tests\Domain\Sequence\Entity;

use Amelaye\BioPHP\Domain\Sequence\Entity\Enzyme;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EnzymeTest extends WebTestCase
{
    /**
     * Tests for Protein Entity
     */
    public function testNewEnzyme()
    {
        $oEnzyme = new Enzyme();
        $oEnzyme->setName("bla");
        $oEnzyme->setLength(12);
        $oEnzyme->setCutpos(2);
        $oEnzyme->setPattern("I");


        $this->assertEquals("bla", $oEnzyme->getName());
        $this->assertEquals(12, $oEnzyme->getLength());
        $this->assertEquals(2, $oEnzyme->getCutpos());
        $this->assertEquals("I", $oEnzyme->getPattern());
    }
}