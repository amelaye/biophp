<?php
namespace Tests\Domain\Parser\Service;

use Amelaye\BioPHP\Domain\Database\Entity\Collection;
use Amelaye\BioPHP\Domain\Database\Entity\CollectionElement;
use Amelaye\BioPHP\Domain\Database\Service\DatabaseManager;
use Amelaye\BioPHP\Domain\Parser\Service\ParseKeggOrthologManager;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class ParseKeggOrthologManagerTest extends TestCase
{
    /**
     * @return ParseKeggOrthologManager
     */
    private function fetch()
    {
        $collection = new Collection();
        $collection->setId(1);
        $collection->setNomCollection("keggdb");

        $collectionElement = new CollectionElement();
        $collectionElement->setIdElement("K00844");
        $collectionElement->setFileName("sample.keggortholog");
        $collectionElement->setDbFormat("KEGG_ORTHOLOG");
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
            ->with(['idElement' => "K00844"])
            ->willReturn($collectionElement);

        $databaseManager = new DatabaseManager($mockedEm, "./data/");

        return $databaseManager->fetch("K00844");
    }

    public function testFormatMetadata()
    {
        $this->assertEquals("KEGG_ORTHOLOG", ParseKeggOrthologManager::getFormat());
        $this->assertTrue(ParseKeggOrthologManager::isEntryStart("ENTRY       K00844                      KO"));
        $this->assertFalse(ParseKeggOrthologManager::isEntryStart("NAME        something"));
        $this->assertTrue(ParseKeggOrthologManager::isEntryEnd("///"));
        $this->assertFalse(ParseKeggOrthologManager::isEntryEnd("//"));
        $this->assertEquals(
            "K00844",
            ParseKeggOrthologManager::getEntryId(["ENTRY       K00844                      KO"], "ENTRY       K00844                      KO")
        );
    }

    public function testFetch()
    {
        $oParser = $this->fetch();

        $this->assertEquals("K00844", $oParser->getEntry());
        $this->assertEquals(["HK", "hexokinase"], $oParser->getNames());
        $this->assertEquals("hexokinase [EC:2.7.1.1]", $oParser->getDefinition());
        $this->assertEquals([["GO", "0004396"]], $oParser->getDbLinks());
    }

    /**
     * An ortholog group gathers the genes of several organisms, one line per organism : this is
     * what carries a pathway from one species to another.
     */
    public function testGenesAreListedOneOrganismPerLine()
    {
        $this->assertEquals(
            ["HSA: 3098 3099 3101", "MMU: 15275 15277"],
            $this->fetch()->getGenes()
        );
    }

    /**
     * The classification wraps over two lines and is read as one list.
     */
    public function testTheClassificationSpanningTwoLinesIsReadAsOneList()
    {
        $this->assertEquals(
            ["Metabolism", "Carbohydrate Metabolism", "Glycolysis / Gluconeogenesis"],
            $this->fetch()->getClassification()
        );
    }
}
