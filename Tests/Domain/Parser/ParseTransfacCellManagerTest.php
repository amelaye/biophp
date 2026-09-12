<?php
namespace Tests\Domain\Parser;

use Amelaye\BioPHP\Domain\Database\Entity\Collection;
use Amelaye\BioPHP\Domain\Database\Entity\CollectionElement;
use Amelaye\BioPHP\Domain\Database\Service\DatabaseManager;
use Amelaye\BioPHP\Domain\Parser\ParseTransfacCellManager;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class ParseTransfacCellManagerTest extends TestCase
{
    /**
     * @return ParseTransfacCellManager
     */
    private function fetch()
    {
        $collection = new Collection();
        $collection->setId(1);
        $collection->setNomCollection("transfacdb");

        $collectionElement = new CollectionElement();
        $collectionElement->setIdElement("C0001");
        $collectionElement->setFileName("sample.transfaccell");
        $collectionElement->setDbFormat("TRANSFAC_CELL");
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
            ->with(['idElement' => "C0001"])
            ->willReturn($collectionElement);

        $databaseManager = new DatabaseManager($mockedEm, "./data/");

        return $databaseManager->fetch("C0001");
    }

    public function testFormatMetadata()
    {
        $this->assertEquals("TRANSFAC_CELL", ParseTransfacCellManager::getFormat());
        $this->assertTrue(ParseTransfacCellManager::isEntryStart("AC  C0001"));
        $this->assertFalse(ParseTransfacCellManager::isEntryStart("ID  something"));
        $this->assertTrue(ParseTransfacCellManager::isEntryEnd("//"));
        $this->assertEquals("C0001", ParseTransfacCellManager::getEntryId(["AC  C0001", "ID  x"], "AC  C0001"));
    }

    public function testFetch()
    {
        $oParser = $this->fetch();

        $this->assertEquals("C0001", $oParser->getAccession());
        $this->assertEquals("HeLa", $oParser->getId());
        $this->assertEquals("human", $oParser->getOrganism());
        $this->assertEquals("cervix carcinoma", $oParser->getFactorSource());
        $this->assertEquals(
            "epithelial cell line established from a cervical adenocarcinoma, "
            . "the oldest and most widely used human cell line",
            $oParser->getDescription()
        );
    }

    /**
     * A creation date may carry a time of day where the update date carries none : both are
     * read, when the fixed offset Legacy used misses the marker on a four-digit year.
     */
    public function testADateCarryingATimeOfDayIsReadWhole()
    {
        $oParser = $this->fetch();

        $this->assertEquals("20.06.1990 11:00:03", $oParser->getDateCreated());
        $this->assertEquals("24.08.1995", $oParser->getDateUpdated());
    }
}
