<?php
namespace Tests\Domain\Sequence\Entity;

use Amelaye\BioPHP\Domain\Sequence\Entity\SpDatabank;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SpDataBankTest extends WebTestCase
{
    public function testNewSpDatabank()
    {
        $oSpdatabank = new SpDatabank();
        $oSpdatabank->setPrimAcc("P01375");
        $oSpdatabank->setDbName("EMBL");
        $oSpdatabank->setPid1("X02910");
        $oSpdatabank->setPid2("HSTNFA");

        $this->assertEquals("P01375", $oSpdatabank->getPrimAcc());
        $this->assertEquals("EMBL", $oSpdatabank->getDbName());
        $this->assertEquals("X02910", $oSpdatabank->getPid1());
        $this->assertEquals("HSTNFA", $oSpdatabank->getPid2());
    }

    /**
     * Getters must not throw a TypeError when the corresponding setter was never called.
     * dbName/pid1/pid2 are nullable in the database, so they must stay null.
     */
    public function testGettersDoNotThrowWhenFieldsAreNotSet()
    {
        $oSpdatabank = new SpDatabank();

        $this->assertSame("", $oSpdatabank->getPrimAcc());
        $this->assertNull($oSpdatabank->getDbName());
        $this->assertNull($oSpdatabank->getPid1());
        $this->assertNull($oSpdatabank->getPid2());
    }
}