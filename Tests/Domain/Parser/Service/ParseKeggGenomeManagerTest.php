<?php
namespace Tests\Domain\Parser\Service;

use Amelaye\BioPHP\Domain\Database\Entity\Collection;
use Amelaye\BioPHP\Domain\Database\Entity\CollectionElement;
use Amelaye\BioPHP\Domain\Database\Service\DatabaseManager;
use Amelaye\BioPHP\Domain\Parser\Service\ParseKeggGenomeManager;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class ParseKeggGenomeManagerTest extends TestCase
{
    /**
     * @return ParseKeggGenomeManager
     */
    private function fetch()
    {
        $collection = new Collection();
        $collection->setId(1);
        $collection->setNomCollection("keggdb");

        $collectionElement = new CollectionElement();
        $collectionElement->setIdElement("T01001");
        $collectionElement->setFileName("sample.kegggenome");
        $collectionElement->setDbFormat("KEGG_GENOME");
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
            ->with(['idElement' => "T01001"])
            ->willReturn($collectionElement);

        $databaseManager = new DatabaseManager($mockedEm, "./data/");

        return $databaseManager->fetch("T01001");
    }

    public function testFormatMetadata()
    {
        $this->assertEquals("KEGG_GENOME", ParseKeggGenomeManager::getFormat());
        $this->assertTrue(ParseKeggGenomeManager::isEntryStart("ENTRY       T01001                      Genome"));
        $this->assertFalse(ParseKeggGenomeManager::isEntryStart("NAME        something"));
        $this->assertTrue(ParseKeggGenomeManager::isEntryEnd("///"));
        $this->assertFalse(ParseKeggGenomeManager::isEntryEnd("//"));
        $this->assertEquals(
            "T01001",
            ParseKeggGenomeManager::getEntryId(["ENTRY       T01001                      Genome"], "ENTRY       T01001                      Genome")
        );
    }

    public function testFetch()
    {
        $oParser = $this->fetch();

        $this->assertEquals("T01001", $oParser->getEntry());
        $this->assertEquals(["hsa, HUMAN, 9606"], $oParser->getNames());
        $this->assertEquals("Homo sapiens (human)", $oParser->getDefinition());
    }

    /**
     * The taxonomy identifier is written behind a "TAX:" prefix naming the authority; what is
     * wanted is the number itself, which is the NCBI taxon of the organism.
     */
    public function testTheTaxonomyIdentifierIsReadWithoutItsPrefix()
    {
        $this->assertEquals("9606", $this->fetch()->getTaxonomy());
    }

    /**
     * The lineage runs over three lines and is read as one list, broadest rank first.
     */
    public function testTheLineageSpanningSeveralLinesIsReadAsOneList()
    {
        $aLineage = $this->fetch()->getLineage();

        $this->assertEquals("Eukaryota", $aLineage[0]);
        $this->assertEquals("Homo", $aLineage[count($aLineage) - 1]);
        $this->assertContains("Primates", $aLineage);
        $this->assertCount(14, $aLineage);
    }
}
