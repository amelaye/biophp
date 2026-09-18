<?php
namespace Tests\Domain\Parser\Service;

use Amelaye\BioPHP\Domain\Database\Entity\Collection;
use Amelaye\BioPHP\Domain\Database\Entity\CollectionElement;
use Amelaye\BioPHP\Domain\Database\Service\DatabaseManager;
use Amelaye\BioPHP\Domain\Parser\Service\ParseTransfacGeneManager;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class ParseTransfacGeneManagerTest extends TestCase
{
    /**
     * @return ParseTransfacGeneManager
     */
    private function fetch()
    {
        $collection = new Collection();
        $collection->setId(1);
        $collection->setNomCollection("transfacdb");

        $collectionElement = new CollectionElement();
        $collectionElement->setIdElement("G000234");
        $collectionElement->setFileName("sample.transfacgene");
        $collectionElement->setDbFormat("TRANSFAC_GENE");
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
            ->with(['idElement' => "G000234"])
            ->willReturn($collectionElement);

        $databaseManager = new DatabaseManager($mockedEm, "./data/");

        return $databaseManager->fetch("G000234");
    }

    public function testFormatMetadata()
    {
        $this->assertEquals("TRANSFAC_GENE", ParseTransfacGeneManager::getFormat());
        $this->assertTrue(ParseTransfacGeneManager::isEntryStart("AC  G000234"));
        $this->assertFalse(ParseTransfacGeneManager::isEntryStart("ID  something"));
        $this->assertTrue(ParseTransfacGeneManager::isEntryEnd("//"));
        $this->assertEquals("G000234", ParseTransfacGeneManager::getEntryId(["AC  G000234", "ID  x"], "AC  G000234"));
    }

    public function testFetch()
    {
        $oParser = $this->fetch();

        $this->assertEquals("G000234", $oParser->getAccession());
        $this->assertEquals('HS$MYOD1_01', $oParser->getId());
        $this->assertEquals("12.05.1994", $oParser->getDateCreated());
        $this->assertEquals("22.08.1996", $oParser->getDateUpdated());
        $this->assertEquals("MYOD1", $oParser->getShortDescription());
        $this->assertEquals("myogenic differentiation 1", $oParser->getDescription());
        $this->assertEquals("human", $oParser->getOrganism());
        $this->assertEquals("homo sapiens", $oParser->getSpecies());
        $this->assertEquals(["C00001", "C00005"], $oParser->getCompelAccessions());
    }

    /**
     * The lineage runs over two OC lines and is read as one list, broadest rank first.
     */
    public function testTheLineageSpanningTwoLinesIsReadAsOneList()
    {
        $this->assertEquals(
            [
                "eukaryota", "animalia", "metazoa", "chordata", "vertebrata",
                "tetrapoda", "mammalia", "eutheria", "primates"
            ],
            $this->fetch()->getTaxClass()
        );
    }
}
