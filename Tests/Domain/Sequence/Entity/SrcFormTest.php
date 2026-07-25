<?php
namespace Tests\Domain\Sequence\Entity;

use Amelaye\BioPHP\Domain\Sequence\Entity\SrcForm;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SrcFormTest extends WebTestCase
{
    public function testNewSrcForm()
    {
        $oSrcForm = new SrcForm();
        $oSrcForm->setPrimAcc("P01375");
        $oSrcForm->setEntry("Test entry.");

        $this->assertEquals("P01375", $oSrcForm->getPrimAcc());
        $this->assertEquals("Test entry.", $oSrcForm->getEntry());
    }

    /**
     * Getters must not throw a TypeError when the corresponding setter was never called.
     */
    public function testGettersDoNotThrowWhenFieldsAreNotSet()
    {
        $oSrcForm = new SrcForm();

        $this->assertSame("", $oSrcForm->getPrimAcc());
        $this->assertSame("", $oSrcForm->getEntry());
    }
}