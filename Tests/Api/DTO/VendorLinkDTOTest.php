<?php


namespace Tests\AppBundle\API\DTO;

use Amelaye\BioPHP\Api\DTO\VendorLinkDTO;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class VendorLinkDTOTest extends WebTestCase
{
    public function testNewVendorLinkDTO()
    {
        $link = new VendorLinkDTO();
        $link->setId("C");
        $link->setName("Minotech Biotechnology");
        $link->setLink("http://www.minotech.gr");

        $this->assertEquals("C", $link->getId());
        $this->assertEquals("Minotech Biotechnology", $link->getName());
        $this->assertEquals("http://www.minotech.gr", $link->getLink());
    }
}