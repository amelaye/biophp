<?php
namespace Tests\Domain\Parser;

use Amelaye\BioPHP\Domain\Database\Entity\Collection;
use Amelaye\BioPHP\Domain\Database\Entity\CollectionElement;
use Amelaye\BioPHP\Domain\Database\Service\DatabaseManager;
use Amelaye\BioPHP\Domain\Parser\ParseTransfacClassManager;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class ParseTransfacClassManagerTest extends TestCase
{
    /**
     * @return ParseTransfacClassManager
     */
    private function fetch()
    {
        $collection = new Collection();
        $collection->setId(1);
        $collection->setNomCollection("transfacdb");

        $collectionElement = new CollectionElement();
        $collectionElement->setIdElement("C0010");
        $collectionElement->setFileName("sample.transfacclass");
        $collectionElement->setDbFormat("TRANSFAC_CLASS");
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
            ->with(['idElement' => "C0010"])
            ->willReturn($collectionElement);

        $databaseManager = new DatabaseManager($mockedEm, "./data/");

        return $databaseManager->fetch("C0010");
    }

    public function testFormatMetadata()
    {
        $this->assertEquals("TRANSFAC_CLASS", ParseTransfacClassManager::getFormat());
        $this->assertTrue(ParseTransfacClassManager::isEntryStart("AC  C0010"));
        $this->assertFalse(ParseTransfacClassManager::isEntryStart("ID  something"));
        $this->assertTrue(ParseTransfacClassManager::isEntryEnd("//"));
        $this->assertEquals("C0010", ParseTransfacClassManager::getEntryId(["AC  C0010", "ID  x"], "AC  C0010"));
    }

    public function testFetch()
    {
        $oParser = $this->fetch();

        $this->assertEquals("C0010", $oParser->getAccession());
        $this->assertEquals("bHLH", $oParser->getId());
        $this->assertEquals("15.03.1993", $oParser->getDateCreated());
        $this->assertEquals("", $oParser->getDateUpdated());
        $this->assertEquals("basic helix-loop-helix", $oParser->getStructuralDescription());
        $this->assertEquals(["T00526; MyoD", "T00029; c-Myc"], $oParser->getMemberFactors());
        $this->assertEquals(
            "two amphipathic helices joined by a loop; the basic region contacts "
            . "the E-box while the helices mediate dimerisation",
            $oParser->getComments()
        );
    }

    /**
     * The classification reads from the broadest level down to the narrowest.
     */
    public function testTheClassificationIsSplitIntoItsLevels()
    {
        $this->assertEquals(
            ["basic domains", "helix-loop-helix factors", "bHLH"],
            $this->fetch()->getClassification()
        );
    }
}
