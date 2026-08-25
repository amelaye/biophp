<?php
namespace Tests\Domain\Parser;

use Amelaye\BioPHP\Domain\Database\Entity\Collection;
use Amelaye\BioPHP\Domain\Database\Entity\CollectionElement;
use Amelaye\BioPHP\Domain\Database\Service\DatabaseManager;
use Amelaye\BioPHP\Domain\Parser\ParseEpdManager;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class ParseEpdManagerTest extends TestCase
{
    /**
     * @return ParseEpdManager
     */
    private function fetch()
    {
        $collection = new Collection();
        $collection->setId(1);
        $collection->setNomCollection("epddb");

        $collectionElement = new CollectionElement();
        $collectionElement->setIdElement("EP11001");
        $collectionElement->setFileName("sample.epd");
        $collectionElement->setDbFormat("EPD");
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
            ->with(['idElement' => "EP11001"])
            ->willReturn($collectionElement);

        $databaseManager = new DatabaseManager($mockedEm, "./data/");

        return $databaseManager->fetch("EP11001");
    }

    public function testFormatMetadata()
    {
        $aFlines = file("./data/sample.epd");

        $this->assertEquals("EPD", ParseEpdManager::getFormat());
        $this->assertTrue(ParseEpdManager::isEntryStart($aFlines[0]));
        $this->assertEquals("EP11001", ParseEpdManager::getEntryId($aFlines, $aFlines[0]));
        $this->assertTrue(ParseEpdManager::isEntryEnd("//"));
        $this->assertFalse(ParseEpdManager::isEntryEnd($aFlines[0]));
    }

    public function testFetch()
    {
        $oParser = $this->fetch();

        $this->assertInstanceOf(ParseEpdManager::class, $oParser);
        $this->assertEquals("HS_ACHA_1", $oParser->getEntryName());
        $this->assertEquals("standard", $oParser->getDataClass());
        $this->assertEquals("single", $oParser->getInsiteType());
        $this->assertEquals("HUM", $oParser->getTaxDiv());
        $this->assertEquals(["EP11001", "EP11002"], $oParser->getAccessions());
        $this->assertEquals(
            "Homo sapiens acetylcholine receptor alpha subunit promoter region.",
            $oParser->getDescription()
        );
        $this->assertEquals(
            "Transcription start site mapped by primer extension.",
            $oParser->getComments()
        );
    }

    /**
     * The three dated events the format defines are all read : the original BioPHP parser had
     * commented two of them out.
     */
    public function testAllThreeDatedEventsAreRead()
    {
        $oParser = $this->fetch();

        $this->assertEquals("15-JUN-2001", $oParser->getCreateDate());
        $this->assertEquals("75", $oParser->getCreateRel());
        $this->assertEquals("20-JUL-2002", $oParser->getSequpdDate());
        $this->assertEquals("80", $oParser->getSequpdRel());
        $this->assertEquals("05-JAN-2003", $oParser->getNotupdDate());
        $this->assertEquals("82", $oParser->getNotupdRel());
    }

    /**
     * A DT line whose event is not one of the three known ones is skipped, not misfiled.
     */
    public function testAnUnknownDatedEventIsIgnored()
    {
        $oParser = new ParseEpdManager();
        $oParser->parseDataFile(["ID   X  standard; single; HUM.", "DT   01-JAN-2000 (REL. 1, SOMETHING ELSE)", "//"]);

        $this->assertSame("", $oParser->getCreateDate());
        $this->assertSame("", $oParser->getSequpdDate());
    }
}
