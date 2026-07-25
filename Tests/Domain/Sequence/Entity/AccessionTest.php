<?php
namespace Tests\Domain\Sequence\Entity;

use Amelaye\BioPHP\Domain\Sequence\Entity\Accession;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AccessionTest extends WebTestCase
{
    public function testNewAccession()
    {
        $oAccession = new Accession();
        $oAccession->setPrimAcc("primAcc");
        $oAccession->setAccession("test");

        $this->assertEquals("primAcc", $oAccession->getPrimAcc());
        $this->assertEquals("test", $oAccession->getAccession());
    }

    /**
     * Getters must not throw a TypeError when the corresponding setter was never called.
     */
    public function testGettersDoNotThrowWhenFieldsAreNotSet()
    {
        $oAccession = new Accession();

        $this->assertSame("", $oAccession->getPrimAcc());
        $this->assertSame("", $oAccession->getAccession());
    }
}