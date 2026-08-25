<?php
namespace Tests\Domain\Parser;

use Amelaye\BioPHP\Domain\Database\Entity\Collection;
use Amelaye\BioPHP\Domain\Database\Entity\CollectionElement;
use Amelaye\BioPHP\Domain\Database\Service\DatabaseManager;
use Amelaye\BioPHP\Domain\Parser\ParsePmdManager;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class ParsePmdManagerTest extends TestCase
{
    /**
     * @return ParsePmdManager
     */
    private function fetch()
    {
        $collection = new Collection();
        $collection->setId(1);
        $collection->setNomCollection("pmddb");

        $collectionElement = new CollectionElement();
        $collectionElement->setIdElement("A000300");
        $collectionElement->setFileName("sample.pmd");
        $collectionElement->setDbFormat("PMD");
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
            ->with(['idElement' => "A000300"])
            ->willReturn($collectionElement);

        $databaseManager = new DatabaseManager($mockedEm, "./data/");

        return $databaseManager->fetch("A000300");
    }

    public function testFormatMetadata()
    {
        $aFlines = file("./data/sample.pmd");

        $this->assertEquals("PMD", ParsePmdManager::getFormat());
        $this->assertTrue(ParsePmdManager::isEntryStart($aFlines[0]));
        $this->assertEquals("A000300", ParsePmdManager::getEntryId($aFlines, $aFlines[0]));
        $this->assertTrue(ParsePmdManager::isEntryEnd("///"));
        $this->assertFalse(ParsePmdManager::isEntryEnd($aFlines[0]));
    }

    public function testFetch()
    {
        $oParser = $this->fetch();

        $this->assertInstanceOf(ParsePmdManager::class, $oParser);
        $this->assertEquals("A", $oParser->getEntryType());
        $this->assertEquals("000300", $oParser->getEntryNo());
        $this->assertEquals("Artificial", $oParser->getMutationType());
        $this->assertEquals("2607383", $oParser->getArticleNo());
        $this->assertEquals("10666322", $oParser->getMedlineNo());
        $this->assertEquals("Arch.Biochem.Biophys. (2000) 374(2), 389-394", $oParser->getJournal());
    }

    /**
     * Authors span two lines and are separated by commas, the last one by an ampersand.
     */
    public function testAuthorsAreSplitAcrossContinuationLines()
    {
        $oParser = $this->fetch();

        $this->assertEquals(
            ["Shoshani I.", "Bianchi G.", "Desaubry L.", "Dessauer C.W.", "Johnson R.A."],
            $oParser->getAuthors()
        );
    }

    public function testMultilineTitleIsJoined()
    {
        $oParser = $this->fetch();

        $this->assertEquals(
            "Lys-Ala mutations of type I adenylyl cyclase result in altered susceptibility "
            . "to inhibition by adenine nucleoside 3'-polyphosphates.",
            $oParser->getTitle()
        );
    }
}
