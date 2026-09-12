<?php
namespace Tests\Domain\Parser;

use Amelaye\BioPHP\Domain\Database\Entity\Collection;
use Amelaye\BioPHP\Domain\Database\Entity\CollectionElement;
use Amelaye\BioPHP\Domain\Database\Service\DatabaseManager;
use Amelaye\BioPHP\Domain\Parser\ParseKeggCompoundManager;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class ParseKeggCompoundManagerTest extends TestCase
{
    /**
     * @return ParseKeggCompoundManager
     */
    private function fetch()
    {
        $collection = new Collection();
        $collection->setId(1);
        $collection->setNomCollection("keggdb");

        $collectionElement = new CollectionElement();
        $collectionElement->setIdElement("C00031");
        $collectionElement->setFileName("sample.keggcompound");
        $collectionElement->setDbFormat("KEGG_COMPOUND");
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
            ->with(['idElement' => "C00031"])
            ->willReturn($collectionElement);

        $databaseManager = new DatabaseManager($mockedEm, "./data/");

        return $databaseManager->fetch("C00031");
    }

    public function testFormatMetadata()
    {
        $this->assertEquals("KEGG_COMPOUND", ParseKeggCompoundManager::getFormat());
        $this->assertTrue(ParseKeggCompoundManager::isEntryStart("ENTRY       C00031                      Compound"));
        $this->assertFalse(ParseKeggCompoundManager::isEntryStart("NAME        something"));
        $this->assertTrue(ParseKeggCompoundManager::isEntryEnd("///"));
        $this->assertFalse(ParseKeggCompoundManager::isEntryEnd("//"));
        $this->assertEquals(
            "C00031",
            ParseKeggCompoundManager::getEntryId(["ENTRY       C00031                      Compound"], "ENTRY       C00031                      Compound")
        );
    }

    public function testFetch()
    {
        $oParser = $this->fetch();

        $this->assertEquals("C00031", $oParser->getEntry());
        $this->assertEquals("C6H12O6", $oParser->getFormula());
        $this->assertEquals(
            ["D-Glucose", "Grape sugar", "Dextrose", "D-Glucopyranose"],
            $oParser->getNames()
        );
        $this->assertEquals(
            [["CAS", "50-99-7"], ["PubChem", "3333"]],
            $oParser->getDbLinks()
        );
    }

    /**
     * Reaction and enzyme identifiers are listed across as many lines as they need, several to
     * a line, and read back as one flat list.
     */
    public function testIdentifiersSpanningSeveralLinesAreReadAsOneList()
    {
        $oParser = $this->fetch();

        $this->assertEquals(
            ["R00010", "R00015", "R00026", "R00028", "R00299", "R01600"],
            $oParser->getReactions()
        );
        $this->assertEquals(
            ["1.1.1.118", "1.1.1.119", "2.7.1.1", "2.7.1.2"],
            $oParser->getEnzymes()
        );
    }

    /**
     * A pathway is named twice : by the identifier of its map and by its title, which the
     * fixed-width column between them keeps apart even though the title holds spaces.
     */
    public function testEachPathwayKeepsItsMapIdentifierAndItsTitle()
    {
        $this->assertEquals(
            [
                ["map00010", "Glycolysis / Gluconeogenesis"],
                ["map00052", "Galactose metabolism"],
                ["map00500", "Starch and sucrose metabolism"],
            ],
            $this->fetch()->getPathways()
        );
    }
}
