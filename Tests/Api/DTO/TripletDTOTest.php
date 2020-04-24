<?php
namespace Tests\Api\DTO;

use Amelaye\BioPHP\Api\DTO\TripletDTO;
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