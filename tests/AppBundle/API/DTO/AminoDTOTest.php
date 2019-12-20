<?php


namespace Tests\AppBundle\API\DTO;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use AppBundle\Api\DTO\AminoDTO;

class AminoDTOTest extends WebTestCase
{
    public function testNewAminoDTO()
    {
        $amino = new AminoDTO();
        $amino->setId('Z');
        $amino->setName("Glutamate or glutamine");
        $amino->setName1Letter('Z');
        $amino->setName3Letters('N/A');
        $amino->setWeight1(75.07);
        $amino->setWeight2(204.22);

        $this->assertEquals("Z", $amino->getId());
        $this->assertEquals("Glutamate or glutamine", $amino->getName());
        $this->assertEquals("Z", $amino->getName1Letter());
        $this->assertEquals('N/A', $amino->getName3Letters());
        $this->assertEquals(75.07, $amino->getWeight1());
        $this->assertEquals(204.22, $amino->getWeight2());
    }
}