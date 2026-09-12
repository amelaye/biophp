<?php
namespace Tests\Domain\Parser;

use Amelaye\BioPHP\Domain\Database\Entity\Collection;
use Amelaye\BioPHP\Domain\Database\Entity\CollectionElement;
use Amelaye\BioPHP\Domain\Database\Service\DatabaseManager;
use Amelaye\BioPHP\Domain\Parser\ParseTransfacFactorManager;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class ParseTransfacFactorManagerTest extends TestCase
{
    /**
     * @return ParseTransfacFactorManager
     */
    private function fetch()
    {
        $collection = new Collection();
        $collection->setId(1);
        $collection->setNomCollection("transfacdb");

        $collectionElement = new CollectionElement();
        $collectionElement->setIdElement("T00526");
        $collectionElement->setFileName("sample.transfacfactor");
        $collectionElement->setDbFormat("TRANSFAC_FACTOR");
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
            ->with(['idElement' => "T00526"])
            ->willReturn($collectionElement);

        $databaseManager = new DatabaseManager($mockedEm, "./data/");

        return $databaseManager->fetch("T00526");
    }

    public function testFormatMetadata()
    {
        $this->assertEquals("TRANSFAC_FACTOR", ParseTransfacFactorManager::getFormat());
        $this->assertTrue(ParseTransfacFactorManager::isEntryStart("AC  T00526"));
        $this->assertFalse(ParseTransfacFactorManager::isEntryStart("ID  something"));
        $this->assertTrue(ParseTransfacFactorManager::isEntryEnd("//"));
        $this->assertEquals("T00526", ParseTransfacFactorManager::getEntryId(["AC  T00526", "ID  x"], "AC  T00526"));
    }

    public function testFetch()
    {
        $oParser = $this->fetch();

        $this->assertEquals("T00526", $oParser->getAccession());
        $this->assertEquals("MyoD", $oParser->getFactorName());
        $this->assertEquals("20.06.1990", $oParser->getDateCreated());
        $this->assertEquals("16.10.1995", $oParser->getDateUpdated());
        $this->assertEquals("mouse", $oParser->getOrganism());
        $this->assertEquals("mus musculus", $oParser->getSpecies());
        $this->assertEquals(["MYF3", "MyoD1", "myogenic differentiation 1"], $oParser->getSynonyms());
    }

    /**
     * Homologs are comma separated, where every other list of this format uses a semicolon.
     */
    public function testHomologsAreSeparatedByCommas()
    {
        $this->assertEquals(
            ["MyoD (human)", "CMD1 (chicken)", "XMyoD (xenopus)"],
            $this->fetch()->getHomologs()
        );
    }

    /**
     * The CL line points at the structural class of the factor by three of its names at once.
     */
    public function testTheClassLineIsSplitIntoItsThreeParts()
    {
        $oParser = $this->fetch();

        $this->assertEquals("C0010", $oParser->getClassAccession());
        $this->assertEquals("bHLH", $oParser->getClassId());
        $this->assertEquals("1.2.3.0.1", $oParser->getClassDecimalNo());
    }
}
