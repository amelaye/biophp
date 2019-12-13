<?php


namespace Tests\AppBundle\Entity;


use AppBundle\Entity\Sequencing\SpDatabank;
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
}