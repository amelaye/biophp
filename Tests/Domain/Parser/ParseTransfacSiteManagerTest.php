<?php
namespace Tests\Domain\Parser;

use Amelaye\BioPHP\Domain\Database\Entity\Collection;
use Amelaye\BioPHP\Domain\Database\Entity\CollectionElement;
use Amelaye\BioPHP\Domain\Database\Service\DatabaseManager;
use Amelaye\BioPHP\Domain\Parser\ParseTransfacSiteManager;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class ParseTransfacSiteManagerTest extends TestCase
{
    /**
     * @return ParseTransfacSiteManager
     */
    private function fetch()
    {
        $collection = new Collection();
        $collection->setId(1);
        $collection->setNomCollection("transfacdb");

        $collectionElement = new CollectionElement();
        $collectionElement->setIdElement("R00001");
        $collectionElement->setFileName("sample.transfacsite");
        $collectionElement->setDbFormat("TRANSFAC_SITE");
        $collectionElement->setSeqCount(1);
        $collectionElement->setLineNo(0);
        $collectionElement->setCollection($collection);

        $mockedEm = $this->createMock(EntityManager::class);

        $repo = $this->createMock(EntityRepository::class);
        $mockedEm->expects($this->once())
            ->method('getRepository')
            ->with(CollectionElement::class)
            ->willReturn($repo);
        $repo->expects($this->once())->method('findOneBy')
            ->with(['idElement' => "R00001"])
            ->willReturn($collectionElement);

        $databaseManager = new DatabaseManager($mockedEm, "./data/");

        return $databaseManager->fetch("R00001");
    }

    public function testFormatMetadata()
    {
        $this->assertEquals("TRANSFAC_SITE", ParseTransfacSiteManager::getFormat());
        $this->assertTrue(ParseTransfacSiteManager::isEntryStart("AC  R00001"));
        $this->assertFalse(ParseTransfacSiteManager::isEntryStart("ID  something"));
        $this->assertTrue(ParseTransfacSiteManager::isEntryEnd("//"));
        $this->assertEquals("R00001", ParseTransfacSiteManager::getEntryId(["AC  R00001", "ID  x"], "AC  R00001"));
    }

    public function testFetch()
    {
        $oParser = $this->fetch();

        $this->assertEquals("R00001", $oParser->getAccession());
        $this->assertEquals('MCK$MUS_01', $oParser->getId());
        $this->assertEquals("20.06.1990", $oParser->getDateCreated());
        $this->assertEquals("D", $oParser->getSeqType());
        $this->assertEquals(
            "muscle creatine kinase gene, right E-box of the enhancer",
            $oParser->getDescription()
        );
        $this->assertEquals("enhancer", $oParser->getGeneRegion());
        $this->assertEquals("CCCCAACACCTGCTGCCTGA", $oParser->getSequence());
        $this->assertEquals("mouse, mus musculus", $oParser->getOrganism());
        $this->assertEquals("DNase I footprinting", $oParser->getMethod());
        $this->assertEquals(
            ["T00526; MyoD; Species: mouse, Mus musculus."],
            $oParser->getBindingFactors()
        );
    }

    /**
     * A site sitting upstream of the transcription start site is numbered negatively, so the
     * sign has to survive the read.
     */
    public function testAnUpstreamPositionKeepsItsSign()
    {
        $this->assertEquals("-1195", $this->fetch()->getFirstPosition());
    }
}
