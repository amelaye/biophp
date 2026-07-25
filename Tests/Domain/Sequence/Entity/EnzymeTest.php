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

    /**
     * Getters must not throw a TypeError when the corresponding setter was never called.
     */
    public function testGettersDoNotThrowWhenFieldsAreNotSet()
    {
        $oEnzyme = new Enzyme();

        $this->assertSame("", $oEnzyme->getName());
        $this->assertSame("", $oEnzyme->getPattern());
        $this->assertSame(0, $oEnzyme->getCutpos());
        $this->assertSame(0, $oEnzyme->getLength());
    }
}