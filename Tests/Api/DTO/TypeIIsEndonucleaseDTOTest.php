<?php


namespace Tests\AppBundle\API\DTO;

use Amelaye\BioPHP\Api\DTO\TypeIIsEndonucleaseDTO;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TypeIIsEndonucleaseDTOTest extends WebTestCase
{
    public function testNewTypeIIsEndonucleaseDTO()
    {
        $endonuclease = new TypeIIsEndonucleaseDTO();
        $endonuclease->setId("TspGWI");
        $endonuclease->setSamePattern(["TspGWI"]);
        $endonuclease->setRecognitionPattern("ACGGANNNNNNNNN_NN'");
        $endonuclease->setComputingPattern("(ACGGA...........)");
        $endonuclease->setLengthRecognitionPattern(16);
        $endonuclease->setCleavagePosUpper(16);
        $endonuclease->setCleavagePosLower(-2);
        $endonuclease->setNbNonNBases(5);

        $this->assertEquals("TspGWI", $endonuclease->getId());
        $this->assertEquals(["TspGWI"], $endonuclease->getSamePattern());
        $this->assertEquals("ACGGANNNNNNNNN_NN'", $endonuclease->getRecognitionPattern());
        $this->assertEquals("(ACGGA...........)", $endonuclease->getComputingPattern());
        $this->assertEquals(16, $endonuclease->getLengthRecognitionPattern());
        $this->assertEquals(16, $endonuclease->getCleavagePosUpper());
        $this->assertEquals(-2, $endonuclease->getCleavagePosLower());
        $this->assertEquals(5, $endonuclease->getNbNonNBases());
    }
}