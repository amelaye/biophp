<?php
namespace Tests\Domain\Parser\Service;

use Amelaye\BioPHP\Domain\Database\Entity\Collection;
use Amelaye\BioPHP\Domain\Database\Entity\CollectionElement;
use Amelaye\BioPHP\Domain\Database\Service\DatabaseManager;
use Amelaye\BioPHP\Domain\Parser\Service\ParsePirManager;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class ParsePirManagerTest extends TestCase
{
    /**
     * @return ParsePirManager
     */
    private function fetch()
    {
        $collection = new Collection();
        $collection->setId(1);
        $collection->setNomCollection("pirdb");

        $collectionElement = new CollectionElement();
        $collectionElement->setIdElement("RHTDTO");
        $collectionElement->setFileName("sample.pir");
        $collectionElement->setDbFormat("PIR");
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
            ->with(['idElement' => "RHTDTO"])
            ->willReturn($collectionElement);

        $databaseManager = new DatabaseManager($mockedEm, "./data/");

        return $databaseManager->fetch("RHTDTO");
    }

    public function testFormatMetadata()
    {
        $aFlines = file("./data/sample.pir");

        $this->assertEquals("PIR", ParsePirManager::getFormat());
        $this->assertTrue(ParsePirManager::isEntryStart($aFlines[0]));
        $this->assertEquals("RHTDTO", ParsePirManager::getEntryId($aFlines, $aFlines[0]));
        $this->assertTrue(ParsePirManager::isEntryEnd("//"));
        $this->assertFalse(ParsePirManager::isEntryEnd($aFlines[0]));
    }

    public function testFetch()
    {
        $oParser = $this->fetch();

        $this->assertInstanceOf(ParsePirManager::class, $oParser);
        $this->assertEquals("RHTDTO", $oParser->getEntryName());
        $this->assertEquals("complete", $oParser->getEntryType());
        $this->assertEquals(
            "R-phycoerythrin alpha-1 chain - red alga (Gastroclonium coulteri)",
            $oParser->getTitle()
        );
    }

    /**
     * Most PIR fields qualify their value with "#key value" pairs.
     */
    public function testOrganismQualifiersAreSplit()
    {
        $oParser = $this->fetch();

        $this->assertEquals("domestic rabbit", $oParser->getOrganism());
        $this->assertEquals("Oryctolagus cuniculus", $oParser->getSpecies());
    }

    public function testDateQualifiersAreSplit()
    {
        $oParser = $this->fetch();

        $this->assertEquals("15-Jun-2001", $oParser->getCreateDate());
        $this->assertEquals("15-Jun-2001", $oParser->getSeqrevDate());
        $this->assertEquals("20-Jul-2002", $oParser->getTxtchgDate());
    }

    public function testSummaryQualifiersAreTyped()
    {
        $oParser = $this->fetch();

        $this->assertSame(3, $oParser->getLength());
        $this->assertSame(380.0, $oParser->getMolwt());
        $this->assertEquals("465", $oParser->getChecksum());
    }

    public function testSemicolonListsSpanContinuationLines()
    {
        $oParser = $this->fetch();

        $this->assertEquals(["PT0622", "PT0680", "PT0582", "PT0673"], $oParser->getAccessions());
        $this->assertEquals(
            ["amidated carboxyl end", "cutaneous gland", "hormone", "pyroglutamic acid"],
            $oParser->getKeywords()
        );
    }
}
