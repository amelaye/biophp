<?php
namespace Tests\Domain\Parser;

use Amelaye\BioPHP\Domain\Database\Entity\Collection;
use Amelaye\BioPHP\Domain\Database\Entity\CollectionElement;
use Amelaye\BioPHP\Domain\Database\Service\DatabaseManager;
use Amelaye\BioPHP\Domain\Parser\ParseBlocksManager;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class ParseBlocksManagerTest extends TestCase
{
    /**
     * @return ParseBlocksManager
     */
    private function fetch()
    {
        $collection = new Collection();
        $collection->setId(1);
        $collection->setNomCollection("blocksdb");

        $collectionElement = new CollectionElement();
        $collectionElement->setIdElement("IPB002128C");
        $collectionElement->setFileName("sample.blocks");
        $collectionElement->setDbFormat("BLOCKS");
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
            ->with(['idElement' => "IPB002128C"])
            ->willReturn($collectionElement);

        $databaseManager = new DatabaseManager($mockedEm, "./data/");

        return $databaseManager->fetch("IPB002128C");
    }

    public function testFormatMetadata()
    {
        $aFlines = file("./data/sample.blocks");

        $this->assertEquals("BLOCKS", ParseBlocksManager::getFormat());
        $this->assertTrue(ParseBlocksManager::isEntryStart($aFlines[0]));
        $this->assertEquals("IPB002128C", ParseBlocksManager::getEntryId($aFlines, $aFlines[0]));
    }

    public function testFetch()
    {
        $oParser = $this->fetch();

        $this->assertInstanceOf(ParseBlocksManager::class, $oParser);
        $this->assertEquals("TEST_FAMILY", $oParser->getId());
        $this->assertEquals("IPB002128C", $oParser->getAccession());
        $this->assertEquals("RDG", $oParser->getAaTriplet());
        $this->assertEquals(
            "A test protein family block used by the BioPHP unit tests.",
            $oParser->getDescription()
        );
    }

    public function testDistanceRangeIsReadFromTheAccessionLine()
    {
        $oParser = $this->fetch();

        $this->assertEquals(30, $oParser->getDistMin());
        $this->assertEquals(31, $oParser->getDistMax());
    }

    /**
     * An AC line without a distance range leaves the range at zero rather than failing.
     */
    public function testAccessionWithoutADistanceRange()
    {
        $oParser = new ParseBlocksManager();
        $oParser->parseDataFile(["ID   FAM; BLOCK", "AC   IPB000001A", "//"]);

        $this->assertEquals("IPB000001A", $oParser->getAccession());
        $this->assertEquals(0, $oParser->getDistMin());
        $this->assertEquals(0, $oParser->getDistMax());
    }
}
