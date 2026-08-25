<?php
namespace Tests\Domain\Parser;

use Amelaye\BioPHP\Domain\Database\Entity\Collection;
use Amelaye\BioPHP\Domain\Database\Entity\CollectionElement;
use Amelaye\BioPHP\Domain\Database\Service\DatabaseManager;
use Amelaye\BioPHP\Domain\Parser\ParsePdbstrManager;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class ParsePdbstrManagerTest extends TestCase
{
    /**
     * @return ParsePdbstrManager
     */
    private function fetch()
    {
        $collection = new Collection();
        $collection->setId(1);
        $collection->setNomCollection("pdbstrdb");

        $collectionElement = new CollectionElement();
        $collectionElement->setIdElement("1YIC_01");
        $collectionElement->setFileName("sample.pdbstr");
        $collectionElement->setDbFormat("PDBSTR");
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
            ->with(['idElement' => "1YIC_01"])
            ->willReturn($collectionElement);

        $databaseManager = new DatabaseManager($mockedEm, "./data/");

        return $databaseManager->fetch("1YIC_01");
    }

    public function testFormatMetadata()
    {
        $aFlines = file("./data/sample.pdbstr");

        $this->assertEquals("PDBSTR", ParsePdbstrManager::getFormat());
        $this->assertTrue(ParsePdbstrManager::isEntryStart($aFlines[0]));
        $this->assertEquals("1YIC_01", ParsePdbstrManager::getEntryId($aFlines, $aFlines[0]));
    }

    public function testFetch()
    {
        $oParser = $this->fetch();

        $this->assertInstanceOf(ParsePdbstrManager::class, $oParser);
        $this->assertEquals("1YIC_01", $oParser->getMemberId());
        $this->assertEquals(108, $oParser->getLength());
        $this->assertEquals("PROTEIN", $oParser->getMolType());
        $this->assertEquals("1YIC", $oParser->getEntryGroup());
        $this->assertEquals("97/02/18", $oParser->getCreateDate());
        $this->assertEquals("97/07/23", $oParser->getUpdDate());
    }

    /**
     * A MEMBER line missing its trailing items must not leave the parser with unset fields.
     */
    public function testPartialMemberLineFallsBackOnEmptyValues()
    {
        $oParser = new ParsePdbstrManager();
        $oParser->parseDataFile(["MEMBER      1ABC_01       42", "//"]);

        $this->assertEquals("1ABC_01", $oParser->getMemberId());
        $this->assertEquals(42, $oParser->getLength());
        $this->assertSame("", $oParser->getMolType());
        $this->assertSame("", $oParser->getUpdDate());
    }
}
