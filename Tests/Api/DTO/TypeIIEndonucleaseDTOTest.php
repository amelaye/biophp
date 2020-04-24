<?php
namespace Tests\Api\DTO;

use Amelaye\BioPHP\Api\DTO\TypeIIEndonucleaseDTO;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TypeIIEndonucleaseDTOTest extends WebTestCase
{
    public function testNewTypeIIEndonucleaseDTO()
    {
        $endonuclease = new TypeIIEndonucleaseDTO();
        $endonuclease->setId("ZraI");
        $endonuclease->setSamePattern(["ZraI"]);
        $endonuclease->setRecognitionPattern("GAC'GTC");
        $endonuclease->setComputingPattern("(GACGTC)");
        $endonuclease->setLengthRecognitionPattern(6);
        $endonuclease->setCleavagePosUpper(3);
        $endonuclease->setCleavagePosLower(0);
        $endonuclease->setNbNonNBases(6);

        $this->assertEquals("ZraI", $endonuclease->getId());
        $this->assertEquals(["ZraI"], $endonuclease->getSamePattern());
        $this->assertEquals("GAC'GTC", $endonuclease->getRecognitionPattern());
        $this->assertEquals("(GACGTC)", $endonuclease->getComputingPattern());
        $this->assertEquals(6, $endonuclease->getLengthRecognitionPattern());
        $this->assertEquals(3, $endonuclease->getCleavagePosUpper());
        $this->assertEquals(0, $endonuclease->getCleavagePosLower());
        $this->assertEquals(6, $endonuclease->getNbNonNBases());
    }
}