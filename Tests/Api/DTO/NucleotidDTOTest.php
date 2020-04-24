<?php
namespace Tests\Api\DTO;

use Amelaye\BioPHP\Api\DTO\NucleotidDTO;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class NucleotidDTOTest extends WebTestCase
{
    public function testNewNucloeotidDTO()
    {
        $nucleotid = new NucleotidDTO();
        $nucleotid->setId("1");
        $nucleotid->setLetter("A");
        $nucleotid->setComplement("T");
        $nucleotid->setNature("DNA");
        $nucleotid->setWeight(313.245);

        $this->assertEquals("1", $nucleotid->getId());
        $this->assertEquals("A", $nucleotid->getLetter());
        $this->assertEquals("T", $nucleotid->getComplement());
        $this->assertEquals("DNA", $nucleotid->getNature());
        $this->assertEquals(313.245, $nucleotid->getWeight());
    }
}