<?php


namespace Tests\AppBundle\Entity;


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
}