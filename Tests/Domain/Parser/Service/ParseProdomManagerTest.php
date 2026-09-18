<?php
namespace Tests\Domain\Parser\Service;

use Amelaye\BioPHP\Domain\Database\Entity\Collection;
use Amelaye\BioPHP\Domain\Database\Entity\CollectionElement;
use Amelaye\BioPHP\Domain\Database\Service\DatabaseManager;
use Amelaye\BioPHP\Domain\Parser\Service\ParseProdomManager;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class ParseProdomManagerTest extends TestCase
{
    /**
     * @return ParseProdomManager
     */
    private function fetch()
    {
        $collection = new Collection();
        $collection->setId(1);
        $collection->setNomCollection("prodomdb");

        $collectionElement = new CollectionElement();
        $collectionElement->setIdElement("PD266930");
        $collectionElement->setFileName("sample.prodom");
        $collectionElement->setDbFormat("PRODOM");
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
            ->with(['idElement' => "PD266930"])
            ->willReturn($collectionElement);

        $databaseManager = new DatabaseManager($mockedEm, "./data/");

        return $databaseManager->fetch("PD266930");
    }

    public function testFormatMetadata()
    {
        $aFlines = file("./data/sample.prodom");

        $this->assertEquals("PRODOM", ParseProdomManager::getFormat());
        $this->assertTrue(ParseProdomManager::isEntryStart($aFlines[0]));
        $this->assertEquals("PD266930", ParseProdomManager::getEntryId($aFlines, $aFlines[0]));
        $this->assertTrue(ParseProdomManager::isEntryEnd("//"));
        $this->assertFalse(ParseProdomManager::isEntryEnd($aFlines[0]));
    }

    public function testFetch()
    {
        $oParser = $this->fetch();

        $this->assertInstanceOf(ParseProdomManager::class, $oParser);
        $this->assertEquals("20167", $oParser->getEntryNo());
        $this->assertEquals("PD266930", $oParser->getAccession());
        $this->assertEquals(10, $oParser->getDomainCount());
    }

    /**
     * The release is written prefixed with a "p", which is not part of its name.
     */
    public function testReleasePrefixIsStripped()
    {
        $oParser = $this->fetch();

        $this->assertEquals("2002.1", $oParser->getRelease());
    }

    /**
     * The KW line holds the frequent names and their counts, then the keywords, the two halves
     * being separated by a double slash.
     */
    public function testKeywordLineIsSplitInTwoHalves()
    {
        $oParser = $this->fetch();

        $this->assertEquals(["FADR" => 2, "Y586" => 1], $oParser->getFreqNames());
        $this->assertEquals(
            ["COMPLETE", "PROTEOME", "DNA-BINDING", "FATTY", "TRANSCRIPTION", "REGULATION"],
            $oParser->getKeywords()
        );
    }

    /**
     * A frequent name is written with the count of sequences carrying it. One written without
     * is kept all the same, counted zero : the name is part of the entry either way.
     */
    public function testAFrequentNameWithoutItsCountIsStillKept()
    {
        $oParser = new ParseProdomManager();
        $oParser->parseDataFile([
            "ID   20167 p2002.1                           10 seq.",
            "KW   FADR(2) Y586 // COMPLETE PROTEOME",
            "//",
        ]);

        $this->assertEquals(["FADR" => 2, "Y586" => 0], $oParser->getFreqNames());
    }
}
