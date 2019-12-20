<?php


namespace Tests\AppBundle\API\DTO;
use AppBundle\Api\DTO\ElementDTO;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class ElementDTOTest extends WebTestCase
{
    public function testNewElementDTO()
    {
        $element = new ElementDTO();
        $element->setName("carbone");
        $element->setWeight(12.01);

        $this->assertEquals("carbone", $element->getName());
        $this->assertEquals(12.01, $element->getWeight());
    }
}