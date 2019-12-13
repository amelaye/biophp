<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\IO\Collection;
use AppBundle\Entity\IO\CollectionElement;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CollectionElementTest extends WebTestCase
{
    public function testNewCollectionElement()
    {
        $collection = new Collection();
        $collection->setId(1);
        $collection->setNomCollection("humandb");

        $collectionElement = new CollectionElement();
        $collectionElement->setIdElement("NM_031438");
        $collectionElement->setFileName("human.seq");
        $collectionElement->setDbFormat("GENBANK");
        $collectionElement->setSeqCount(1);
        $collectionElement->setLineNo(0);
        $collectionElement->setCollection($collection);

        $this->assertEquals("NM_031438", $collectionElement->getIdElement());
        $this->assertEquals("human.seq", $collectionElement->getFileName());
        $this->assertEquals("GENBANK", $collectionElement->getDbFormat());
        $this->assertEquals(1, $collectionElement->getSeqCount());
        $this->assertEquals(0, $collectionElement->getLineNo());
        $this->assertEquals($collection, $collectionElement->getCollection());
    }
}