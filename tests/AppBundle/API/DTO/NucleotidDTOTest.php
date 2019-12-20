<?php


namespace Tests\AppBundle\API\DTO;

use AppBundle\Api\DTO\NucleotidDTO;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class NucleotidDTOTest extends WebTestCase
{
    public function testNewNucloeotidDTO()
    {
        $nucleotid = new NucleotidDTO();
        $nucleotid->setLetter("A");
        $nucleotid->setComplement("T");
        $nucleotid->setNature("DNA");
        $nucleotid->setWeigth(313.245);

        $this->assertEquals("A", $nucleotid->getLetter());
        $this->assertEquals("T", $nucleotid->getComplement());
        $this->assertEquals("DNA", $nucleotid->getNature());
        $this->assertEquals(313.245, $nucleotid->getWeigth());
    }
}