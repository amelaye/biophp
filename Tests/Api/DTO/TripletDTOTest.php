<?php


namespace Tests\AppBundle\API\DTO;
use AppBundle\Api\DTO\TripletDTO;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class TripletDTOTest extends WebTestCase
{
    public function testNewTripletDTO()
    {
        $triplet = new TripletDTO();
        $triplet->setId("1");
        $triplet->setTriplet("TTC");

        $this->assertEquals("1", $triplet->getId());
        $this->assertEquals("TTC", $triplet->getTriplet());
    }
}