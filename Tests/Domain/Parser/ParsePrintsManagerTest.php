<?php
namespace Tests\Domain\Parser;

use Amelaye\BioPHP\Domain\Database\Entity\Collection;
use Amelaye\BioPHP\Domain\Database\Entity\CollectionElement;
use Amelaye\BioPHP\Domain\Database\Service\DatabaseManager;
use Amelaye\BioPHP\Domain\Parser\ParsePrintsManager;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class ParsePrintsManagerTest extends TestCase
{
    /**
     * @return ParsePrintsManager
     */
    private function fetch()
    {
        $collection = new Collection();
        $collection->setId(1);
        $collection->setNomCollection("printsdb");

        $collectionElement = new CollectionElement();
        $collectionElement->setIdElement("TEST_FINGERPRINT");
        $collectionElement->setFileName("sample.prints");
        $collectionElement->setDbFormat("PRINTS");
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
            ->with(['idElement' => "TEST_FINGERPRINT"])
            ->willReturn($collectionElement);

        $databaseManager = new DatabaseManager($mockedEm, "./data/");

        return $databaseManager->fetch("TEST_FINGERPRINT");
    }

    public function testFormatMetadata()
    {
        $aFlines = file("./data/sample.prints");

        $this->assertEquals("PRINTS", ParsePrintsManager::getFormat());
        $this->assertTrue(ParsePrintsManager::isEntryStart($aFlines[0]));
        $this->assertEquals("TEST_FINGERPRINT", ParsePrintsManager::getEntryId($aFlines, $aFlines[0]));
    }

    public function testFetch()
    {
        $oParser = $this->fetch();

        $this->assertInstanceOf(ParsePrintsManager::class, $oParser);
        $this->assertEquals("TEST_FINGERPRINT", $oParser->getEntryName());
        $this->assertEquals("COMPOUND(3)", $oParser->getEntryType());
        $this->assertEquals("16-NOV-1995", $oParser->getCreateDate());
        $this->assertEquals("06-JUN-1999", $oParser->getUpdDate());
        $this->assertEquals(
            "A test fingerprint used by the BioPHP unit tests, describing "
            . "a conserved motif found in a fictitious protein family.",
            $oParser->getDescription()
        );
    }

    /**
     * The ga; line carries the creation date alone when no UPDATE pair follows it.
     */
    public function testCreationDateWithoutAnUpdate()
    {
        $oParser = new ParsePrintsManager();
        $oParser->parseDataFile(["gc; X", "ga; 16-NOV-1995"]);

        $this->assertEquals("16-NOV-1995", $oParser->getCreateDate());
        $this->assertSame("", $oParser->getUpdDate());
    }

    /**
     * The description may come back in a second group of gd; lines further down the entry.
     * Both groups belong to the same description and are kept, where the Legacy parser reset
     * its buffer on the second and returned only the last group.
     */
    public function testTwoSeparateDescriptionGroupsAreBothKept()
    {
        $oParser = new ParsePrintsManager();
        $oParser->parseDataFile([
            "gc; TESTPRINT",
            "gd; a conserved motif",
            "gx; PR00001",
            "gd; found in a fictitious family",
        ]);

        $this->assertEquals("a conserved motif found in a fictitious family", $oParser->getDescription());
    }
}
