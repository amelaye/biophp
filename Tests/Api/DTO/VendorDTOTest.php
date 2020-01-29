<?php


namespace Tests\AppBundle\API\DTO;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use AppBundle\Api\DTO\VendorDTO;

class VendorDTOTest extends WebTestCase
{
    public function testNewVendorDTO()
    {
        $vendor = new VendorDTO();
        $vendor->setId("Zsp2I");
        $vendor->setVendor("IV");

        $this->assertEquals("Zsp2I", $vendor->getId());
        $this->assertEquals("IV", $vendor->getVendor());
    }
}