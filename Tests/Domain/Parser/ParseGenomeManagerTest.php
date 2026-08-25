<?php
namespace Tests\Domain\Parser;

use Amelaye\BioPHP\Domain\Database\Entity\Collection;
use Amelaye\BioPHP\Domain\Database\Entity\CollectionElement;
use Amelaye\BioPHP\Domain\Database\Service\DatabaseManager;
use Amelaye\BioPHP\Domain\Parser\ParseGenomeManager;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class ParseGenomeManagerTest extends TestCase
{
    /**
     * @return ParseGenomeManager
     */
    private function fetch()
    {
        $collection = new Collection();
        $collection->setId(1);
        $collection->setNomCollection("genomedb");

        $collectionElement = new CollectionElement();
        $collectionElement->setIdElement("Homo sapiens");
        $collectionElement->setFileName("sample.genome");
        $collectionElement->setDbFormat("GENOME");
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
            ->with(['idElement' => "Homo sapiens"])
            ->willReturn($collectionElement);

        $databaseManager = new DatabaseManager($mockedEm, "./data/");

        return $databaseManager->fetch("Homo sapiens");
    }

    public function testFormatMetadata()
    {
        $aFlines = file("./data/sample.genome");

        $this->assertEquals("GENOME", ParseGenomeManager::getFormat());
        $this->assertTrue(ParseGenomeManager::isEntryStart($aFlines[0]));
        $this->assertEquals("Homo sapiens", ParseGenomeManager::getEntryId($aFlines, $aFlines[0]));
        $this->assertTrue(ParseGenomeManager::isEntryEnd("//"));
        $this->assertFalse(ParseGenomeManager::isEntryEnd($aFlines[0]));
    }

    public function testFetch()
    {
        $oParser = $this->fetch();

        $this->assertInstanceOf(ParseGenomeManager::class, $oParser);
        $this->assertEquals("Homo sapiens", $oParser->getOrganism());
        $this->assertEquals("human", $oParser->getCommonName());
        $this->assertEquals("NO", $oParser->getIsComplete());
        $this->assertEquals("134", $oParser->getGbRelease());
        $this->assertSame(1234567, $oParser->getGbEntries());
        $this->assertSame(9876543210, $oParser->getGbBasepairs());
        $this->assertSame(3200000000, $oParser->getSize());
    }

    /**
     * The classification runs over a tab indented continuation line.
     */
    public function testClassificationSpansContinuationLines()
    {
        $oParser = $this->fetch();

        $this->assertEquals(
            ["Eukaryota", "Metazoa", "Chordata", "Craniata", "Vertebrata", "Mammalia"],
            $oParser->getTaxClass()
        );
    }

    /**
     * A record may hold several reference sets, each opened by its REF_TYPE line.
     */
    public function testEachReferenceSetIsKeptApart()
    {
        $aReferences = $this->fetch()->getReferences();

        $this->assertCount(2, $aReferences);

        $this->assertEquals("journal", $aReferences[0]->getType());
        $this->assertEquals(
            "Initial sequencing and analysis of the human genome.",
            $aReferences[0]->getTitle()
        );
        $this->assertEquals(
            ["Lander E.S.", "Linton L.M.", "Birren B."],
            $aReferences[0]->getAuthors()
        );
        $this->assertEquals("Nature", $aReferences[0]->getJournal());
        $this->assertEquals("409", $aReferences[0]->getVolume());
        $this->assertEquals("860-921", $aReferences[0]->getPages());
        $this->assertEquals("2001", $aReferences[0]->getYear());

        $this->assertEquals("book", $aReferences[1]->getType());
        $this->assertEquals(["Venter J.C."], $aReferences[1]->getAuthors());
        $this->assertEquals("Science", $aReferences[1]->getJournal());
    }
}
