<?php
namespace Tests\Domain\Parser\Service;

use Amelaye\BioPHP\Domain\Database\Entity\Collection;
use Amelaye\BioPHP\Domain\Database\Entity\CollectionElement;
use Amelaye\BioPHP\Domain\Database\Service\DatabaseManager;
use Amelaye\BioPHP\Domain\Parser\Service\ParseHgbaseManager;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class ParseHgbaseManagerTest extends TestCase
{
    /**
     * @return ParseHgbaseManager
     */
    private function fetch()
    {
        $collection = new Collection();
        $collection->setId(1);
        $collection->setNomCollection("hgbasedb");

        $collectionElement = new CollectionElement();
        $collectionElement->setIdElement("HGBASE:000123");
        $collectionElement->setFileName("sample.hgbase");
        $collectionElement->setDbFormat("HGBASE");
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
            ->with(['idElement' => "HGBASE:000123"])
            ->willReturn($collectionElement);

        $databaseManager = new DatabaseManager($mockedEm, "./data/");

        return $databaseManager->fetch("HGBASE:000123");
    }

    public function testFormatMetadata()
    {
        $aFlines = file("./data/sample.hgbase");

        $this->assertEquals("HGBASE", ParseHgbaseManager::getFormat());
        $this->assertTrue(ParseHgbaseManager::isEntryStart($aFlines[0]));
        $this->assertEquals("HGBASE:000123", ParseHgbaseManager::getEntryId($aFlines, $aFlines[0]));
        $this->assertTrue(ParseHgbaseManager::isEntryEnd("//"));
        $this->assertFalse(ParseHgbaseManager::isEntryEnd($aFlines[0]));
    }

    public function testFetch()
    {
        $oParser = $this->fetch();

        $this->assertInstanceOf(ParseHgbaseManager::class, $oParser);
        $this->assertEquals("HGBASE:000123", $oParser->getHaplotypeId());
        $this->assertEquals("C/T", $oParser->getAllele());
        $this->assertEquals("yes", $oParser->getIsInBlock());
        $this->assertEquals("POP0000456", $oParser->getPopulationId());
        $this->assertEquals("SRC0000789", $oParser->getSourceId());
        $this->assertEquals("Genotyped by direct sequencing.", $oParser->getSourceComment());
    }

    public function testPopulationNameAndSizeAreSplit()
    {
        $oParser = $this->fetch();

        $this->assertEquals("Caucasian (USA)", $oParser->getPopName());
        $this->assertEquals(216, $oParser->getPopIndiv());
    }

    public function testFrequencyAndCohortAreSplit()
    {
        $oParser = $this->fetch();

        $this->assertEquals(2.0, $oParser->getFreqPerc());
        $this->assertEquals(1039, $oParser->getFreqIndiv());
    }

    public function testSubmitterNameAndSubmissionIdAreSplit()
    {
        $oParser = $this->fetch();

        $this->assertEquals("Jan. W. Koper", $oParser->getSubmitterName());
        $this->assertEquals("SUB0001234", $oParser->getSubmissionId());
    }

    /**
     * POPULATION is a prefix of POPULATIONID : matching the label whole keeps an identifier line
     * from also being read as a population description, as the original BioPHP parser did.
     */
    public function testPopulationIdIsNotReadAsAPopulationName()
    {
        $oParser = new ParseHgbaseManager();
        $oParser->parseDataFile(["populationid\tPOP0000456", "//"]);

        $this->assertEquals("POP0000456", $oParser->getPopulationId());
        $this->assertSame("", $oParser->getPopName());
        $this->assertEquals(0, $oParser->getPopIndiv());
    }
}
