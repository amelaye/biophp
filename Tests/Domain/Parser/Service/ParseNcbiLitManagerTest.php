<?php
namespace Tests\Domain\Parser\Service;

use Amelaye\BioPHP\Domain\Database\Entity\Collection;
use Amelaye\BioPHP\Domain\Database\Entity\CollectionElement;
use Amelaye\BioPHP\Domain\Database\Service\DatabaseManager;
use Amelaye\BioPHP\Domain\Parser\Service\ParseNcbiLitManager;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class ParseNcbiLitManagerTest extends TestCase
{
    /**
     * @return ParseNcbiLitManager
     */
    private function fetch()
    {
        $collection = new Collection();
        $collection->setId(1);
        $collection->setNomCollection("ncbilitdb");

        $collectionElement = new CollectionElement();
        $collectionElement->setIdElement("1");
        $collectionElement->setFileName("sample.ncbilit");
        $collectionElement->setDbFormat("NCBI_LIT");
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
            ->with(['idElement' => "1"])
            ->willReturn($collectionElement);

        $databaseManager = new DatabaseManager($mockedEm, "./data/");

        return $databaseManager->fetch("1");
    }

    public function testFormatMetadata()
    {
        $aFlines = file("./data/sample.ncbilit");

        $this->assertEquals("NCBI_LIT", ParseNcbiLitManager::getFormat());
        $this->assertTrue(ParseNcbiLitManager::isEntryStart($aFlines[0]));
        $this->assertEquals("1", ParseNcbiLitManager::getEntryId($aFlines, $aFlines[0]));
        $this->assertTrue(ParseNcbiLitManager::isEntryEnd("----------"));
        $this->assertFalse(ParseNcbiLitManager::isEntryEnd($aFlines[0]));
    }

    public function testFetch()
    {
        $oParser = $this->fetch();

        $this->assertInstanceOf(ParseNcbiLitManager::class, $oParser);
        $this->assertEquals("1", $oParser->getId());
        $this->assertEquals("AADE editors' journal.", $oParser->getTitle());
        $this->assertEquals("AADE Ed J", $oParser->getMedAbbr());
        $this->assertEquals("0160-6999", $oParser->getIssn());
        $this->assertEquals("---", $oParser->getEssn());
        $this->assertEquals("---", $oParser->getIsoAbbr());
        $this->assertEquals("7708172", $oParser->getNlmId());
    }

    /**
     * A journal title holding a colon of its own must not be cut at it.
     */
    public function testTitleHoldingASeparatorIsKeptWhole()
    {
        $oParser = new ParseNcbiLitManager();
        $oParser->parseDataFile([
            "JrId: 42",
            "JournalTitle: Nature: the journal of science.",
            "----------"
        ]);

        $this->assertEquals("Nature: the journal of science.", $oParser->getTitle());
    }

    /**
     * Reading stops at the row of dashes, so a second record does not bleed into the first.
     */
    public function testReadingStopsAtTheRecordSeparator()
    {
        $oParser = new ParseNcbiLitManager();
        $oParser->parseDataFile([
            "JrId: 1",
            "JournalTitle: First.",
            "----------",
            "JrId: 2",
            "JournalTitle: Second."
        ]);

        $this->assertEquals("1", $oParser->getId());
        $this->assertEquals("First.", $oParser->getTitle());
    }
}
