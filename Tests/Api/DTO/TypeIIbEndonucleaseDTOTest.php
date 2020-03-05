<?php


namespace Tests\AppBundle\API\DTO;

use Amelaye\BioPHP\Api\DTO\TypeIIbEndonucleaseDTO;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TypeIIbEndonucleaseDTOTest extends WebTestCase
{
    public function testNewTypeIIbEndonucleaseDTO()
    {
        $endonuclease = new TypeIIbEndonucleaseDTO();
        $endonuclease->setId("AjuI#");
        $endonuclease->setSamePattern(["AjuI"]);
        $endonuclease->setRecognitionPattern("_NNNNN'NNNNNNNGAANNNNNNNTTGGNNNNNN_NNNNN_'");
        $endonuclease->setComputingPattern("(............GAA.......TTGG...........|...........CCAA.......TTC............)");
        $endonuclease->setLengthRecognitionPattern(37);
        $endonuclease->setCleavagePosUpper(5);
        $endonuclease->setCleavagePosLower(-5);
        $endonuclease->setNbNonNBases(7);

        $this->assertEquals("AjuI#", $endonuclease->getId());
        $this->assertEquals(["AjuI"], $endonuclease->getSamePattern());
        $this->assertEquals("_NNNNN'NNNNNNNGAANNNNNNNTTGGNNNNNN_NNNNN_'", $endonuclease->getRecognitionPattern());
        $this->assertEquals("(............GAA.......TTGG...........|...........CCAA.......TTC............)", $endonuclease->getComputingPattern());
        $this->assertEquals(37, $endonuclease->getLengthRecognitionPattern());
        $this->assertEquals(5, $endonuclease->getCleavagePosUpper());
        $this->assertEquals(-5, $endonuclease->getCleavagePosLower());
        $this->assertEquals(7, $endonuclease->getNbNonNBases());
    }
}