<?php

namespace Tests\AppBundle\API\DTO;

use AppBundle\Api\DTO\Pam250MatrixDigitDTO;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class Pam250MatrixDigitDTOTest extends WebTestCase
{
    public function testNewPam250MatrixDigitDTO()
    {
        $line = new Pam250MatrixDigitDTO();
        $line->setId("piou");
        $line->setValue(-5);

        $this->assertEquals("piou", $line->getId());
        $this->assertEquals(-5, $line->getValue());
    }
}