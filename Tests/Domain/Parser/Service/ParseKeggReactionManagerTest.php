<?php
namespace Tests\Domain\Parser\Service;

use Amelaye\BioPHP\Domain\Database\Entity\Collection;
use Amelaye\BioPHP\Domain\Database\Entity\CollectionElement;
use Amelaye\BioPHP\Domain\Database\Service\DatabaseManager;
use Amelaye\BioPHP\Domain\Parser\Service\ParseKeggReactionManager;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class ParseKeggReactionManagerTest extends TestCase
{
    /**
     * @return ParseKeggReactionManager
     */
    private function fetch()
    {
        $collection = new Collection();
        $collection->setId(1);
        $collection->setNomCollection("keggdb");

        $collectionElement = new CollectionElement();
        $collectionElement->setIdElement("R00299");
        $collectionElement->setFileName("sample.keggreaction");
        $collectionElement->setDbFormat("KEGG_REACTION");
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
            ->with(['idElement' => "R00299"])
            ->willReturn($collectionElement);

        $databaseManager = new DatabaseManager($mockedEm, "./data/");

        return $databaseManager->fetch("R00299");
    }

    public function testFormatMetadata()
    {
        $this->assertEquals("KEGG_REACTION", ParseKeggReactionManager::getFormat());
        $this->assertTrue(ParseKeggReactionManager::isEntryStart("ENTRY       R00299                      Reaction"));
        $this->assertFalse(ParseKeggReactionManager::isEntryStart("NAME        something"));
        $this->assertTrue(ParseKeggReactionManager::isEntryEnd("///"));
        $this->assertFalse(ParseKeggReactionManager::isEntryEnd("//"));
        $this->assertEquals(
            "R00299",
            ParseKeggReactionManager::getEntryId(["ENTRY       R00299                      Reaction"], "ENTRY       R00299                      Reaction")
        );
    }

    public function testFetch()
    {
        $oParser = $this->fetch();

        $this->assertEquals("R00299", $oParser->getEntry());
        $this->assertEquals(["ATP:D-glucose 6-phosphotransferase"], $oParser->getNames());
        $this->assertEquals(["2.7.1.1", "2.7.1.2", "2.7.1.69"], $oParser->getEnzymes());
        $this->assertEquals(
            [["map00010", "Glycolysis / Gluconeogenesis"], ["map00052", "Galactose metabolism"]],
            $oParser->getPathways()
        );
    }

    /**
     * The reaction is written twice : DEFINITION names its compounds, EQUATION numbers them.
     * Here the hexokinase step, which phosphorylates glucose at the cost of one ATP.
     */
    public function testTheReactionIsReadBothNamedAndNumbered()
    {
        $oParser = $this->fetch();

        $this->assertEquals(
            "ATP + D-Glucose <=> ADP + D-Glucose 6-phosphate",
            $oParser->getDefinition()
        );
        $this->assertEquals("C00002 + C00031 <=> C00008 + C00092", $oParser->getEquation());
    }
}
