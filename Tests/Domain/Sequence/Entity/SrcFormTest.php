<?php


namespace Tests\AppBundle\Entity;

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
}