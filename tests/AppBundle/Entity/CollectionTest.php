<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\IO\Collection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CollectionTest extends WebTestCase
{
    public function testNewCollection()
    {
        $collection = new Collection();
        $collection->setId(1);
        $collection->setNomCollection("humandb");

        $this->assertEquals(1, $collection->getId());
        $this->assertEquals("humandb", $collection->getNomCollection());
    }
}