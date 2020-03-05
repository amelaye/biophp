<?php


namespace Tests\AppBundle\API\DTO;


use Amelaye\BioPHP\Api\DTO\PKDTO;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PKDTOTest extends WebTestCase
{
    public function testNewPKDTO()
    {
        $pkValue = new PKDTO();
        $pkValue->setId("EMBOSS");
        $pkValue->setNTerminus(8.6);
        $pkValue->setK(10.8);
        $pkValue->setR(12.5);
        $pkValue->setH(6.5);
        $pkValue->setCTerminus(3.6);
        $pkValue->setD(3.9);
        $pkValue->setE(4.1);
        $pkValue->setC(8.5);
        $pkValue->setY(10.1);

        $this->assertEquals("EMBOSS", $pkValue->getId());
        $this->assertEquals(8.6, $pkValue->getNTerminus());
        $this->assertEquals(10.8, $pkValue->getK());
        $this->assertEquals(12.5, $pkValue->getR());
        $this->assertEquals(6.5, $pkValue->getH());
        $this->assertEquals(3.6, $pkValue->getCTerminus());
        $this->assertEquals(3.9, $pkValue->getD());
        $this->assertEquals(4.1, $pkValue->getE());
        $this->assertEquals(8.5, $pkValue->getC());
        $this->assertEquals(10.1, $pkValue->getY());
    }
}