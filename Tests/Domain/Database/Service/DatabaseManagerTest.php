<?php
namespace Tests\Domain\Database\Service;

use Amelaye\BioPHP\Domain\Database\Entity\Collection;
use Amelaye\BioPHP\Domain\Database\Entity\CollectionElement;
use Amelaye\BioPHP\Domain\Database\Service\DatabaseManager;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class DatabaseManagerTest extends TestCase
{
    public function testFetchReturnsFalseWhenElementNotFound()
    {
        $mockedEm = $this->createMock(EntityManager::class);

        $repo = $this->createMock(EntityRepository::class);
        $mockedEm->expects($this->once())
            ->method('getRepository')
            ->with(CollectionElement::class)
            ->willReturn($repo);
        $repo->expects($this->once())->method('findOneBy')
            ->with(['idElement' => "unknown"])
            ->willReturn(null);

        $databaseManager = new DatabaseManager($mockedEm, './data/');

        $this->assertFalse($databaseManager->fetch("unknown"));
    }

    public function testFetchThrowsWhenFileDoesNotExist()
    {
        $collectionElement = new CollectionElement();
        $collectionElement->setIdElement("NM_031438");
        $collectionElement->setFileName("does_not_exist.seq");
        $collectionElement->setDbFormat("GENBANK");
        $collectionElement->setSeqCount(1);
        $collectionElement->setLineNo(0);

        $mockedEm = $this->createMock(EntityManager::class);

        $repo = $this->createMock(EntityRepository::class);
        $mockedEm->expects($this->once())
            ->method('getRepository')
            ->with(CollectionElement::class)
            ->willReturn($repo);
        $repo->expects($this->once())->method('findOneBy')
            ->with(['idElement' => "NM_031438"])
            ->willReturn($collectionElement);

        $databaseManager = new DatabaseManager($mockedEm, './data/');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/does_not_exist\.seq doesn\'t exist/');
        $databaseManager->fetch("NM_031438");
    }

    public function testRecordingThrowsWhenNoFilesProvided()
    {
        $mockedEm = $this->createMock(EntityManager::class);

        $repo = $this->createMock(EntityRepository::class);
        $mockedEm->expects($this->once())
            ->method('getRepository')
            ->with(Collection::class)
            ->willReturn($repo);
        $repo->expects($this->once())->method('findOneBy')
            ->with(['nomCollection' => "humandb"])
            ->willReturn(null);

        $databaseManager = new DatabaseManager($mockedEm, './data/');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/No files provided !/');
        $databaseManager->recording("humandb");
    }

    public function testRecordingCreatesNewCollectionAndElementWhenNoneExist()
    {
        $collectionRepo = $this->createMock(EntityRepository::class);
        $collectionRepo->expects($this->once())->method('findOneBy')
            ->with(['nomCollection' => "humandb"])
            ->willReturn(null);

        $collectionElementRepo = $this->createMock(EntityRepository::class);
        $collectionElementRepo->expects($this->once())->method('findOneBy')
            ->with(['idElement' => "NM_031438"])
            ->willReturn(null);

        $mockedEm = $this->createMock(EntityManager::class);
        $mockedEm->method('getRepository')->willReturnMap([
            [Collection::class, $collectionRepo],
            [CollectionElement::class, $collectionElementRepo],
        ]);

        $persisted = [];
        $mockedEm->expects($this->exactly(2))->method('persist')
            ->willReturnCallback(function ($entity) use (&$persisted) {
                $persisted[] = $entity;
            });
        $mockedEm->expects($this->exactly(2))->method('flush');

        $databaseManager = new DatabaseManager($mockedEm, './data/');
        $databaseManager->recording("humandb", "GENBANK", "human.seq");

        $this->assertCount(2, $persisted);

        $this->assertInstanceOf(Collection::class, $persisted[0]);
        $this->assertEquals("humandb", $persisted[0]->getNomCollection());

        $this->assertInstanceOf(CollectionElement::class, $persisted[1]);
        $this->assertEquals("NM_031438", $persisted[1]->getIdElement());
        $this->assertEquals("human.seq", $persisted[1]->getFileName());
        $this->assertEquals("GENBANK", $persisted[1]->getDbFormat());
        $this->assertEquals(0, $persisted[1]->getLineNo());
        $this->assertEquals(1, $persisted[1]->getSeqCount());
        $this->assertSame($persisted[0], $persisted[1]->getCollection());
    }

    public function testRecordingUsesExistingCollectionAndSkipsExistingElement()
    {
        $existingCollection = new Collection();
        $existingCollection->setId(5);
        $existingCollection->setNomCollection("humandb");

        $existingElement = new CollectionElement();
        $existingElement->setIdElement("NM_031438");
        $existingElement->setFileName("human.seq");
        $existingElement->setDbFormat("GENBANK");

        $collectionRepo = $this->createMock(EntityRepository::class);
        $collectionRepo->expects($this->once())->method('findOneBy')
            ->with(['nomCollection' => "humandb"])
            ->willReturn($existingCollection);

        $collectionElementRepo = $this->createMock(EntityRepository::class);
        $collectionElementRepo->expects($this->once())->method('findOneBy')
            ->with(['idElement' => "NM_031438"])
            ->willReturn($existingElement);

        $mockedEm = $this->createMock(EntityManager::class);
        $mockedEm->method('getRepository')->willReturnMap([
            [Collection::class, $collectionRepo],
            [CollectionElement::class, $collectionElementRepo],
        ]);

        $mockedEm->expects($this->never())->method('persist');
        $mockedEm->expects($this->never())->method('flush');

        $databaseManager = new DatabaseManager($mockedEm, './data/');
        $databaseManager->recording("humandb", "GENBANK", "human.seq");
    }

    /**
     * data/enzyme.dat is a real excerpt of the ExPASy ENZYME database : the copyright header it
     * opens with, then five records. Indexing it has to yield one row per record, each pointing
     * at the line its own record starts at, and none for the header, which is no record.
     */
    public function testRecordingIndexesEveryRecordOfARealDataFile()
    {
        $collectionRepo = $this->createMock(EntityRepository::class);
        $collectionRepo->method('findOneBy')->willReturn(null);

        $collectionElementRepo = $this->createMock(EntityRepository::class);
        $collectionElementRepo->method('findOneBy')->willReturn(null);

        $mockedEm = $this->createMock(EntityManager::class);
        $mockedEm->method('getRepository')->willReturnMap([
            [Collection::class, $collectionRepo],
            [CollectionElement::class, $collectionElementRepo],
        ]);

        $persisted = [];
        $mockedEm->method('persist')->willReturnCallback(function ($entity) use (&$persisted) {
            $persisted[] = $entity;
        });

        $databaseManager = new DatabaseManager($mockedEm, './data/');
        $databaseManager->recording("enzymedb", "EXPASY_ENZYME", "enzyme.dat");

        $aElements = array_values(array_filter($persisted, function ($oEntity) {
            return $oEntity instanceof CollectionElement;
        }));

        $aIndexed = [];
        foreach ($aElements as $oElement) {
            $aIndexed[$oElement->getIdElement()] = $oElement->getLineNo();
        }

        $this->assertEquals(
            ["1.1.1.1" => 24, "1.1.1.2" => 39, "1.1.1.5" => 52, "1.1.1.74" => 55, "1.14.14.1" => 58],
            $aIndexed
        );
    }

    /**
     * Reading a record of a real data file means reading that record, not the first one the
     * file happens to hold : EC 1.14.14.1 sits past four other records.
     */
    public function testFetchReadsTheRecordItsIndexPointsAt()
    {
        $collection = new Collection();
        $collection->setId(1);
        $collection->setNomCollection("enzymedb");

        $collectionElement = new CollectionElement();
        $collectionElement->setIdElement("1.14.14.1");
        $collectionElement->setFileName("enzyme.dat");
        $collectionElement->setDbFormat("EXPASY_ENZYME");
        $collectionElement->setSeqCount(5);
        $collectionElement->setLineNo(58);
        $collectionElement->setCollection($collection);

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findOneBy')->willReturn($collectionElement);

        $mockedEm = $this->createMock(EntityManager::class);
        $mockedEm->method('getRepository')->willReturn($repo);

        $databaseManager = new DatabaseManager($mockedEm, './data/');
        $oParser = $databaseManager->fetch("1.14.14.1");

        $this->assertEquals("1.14.14.1", $oParser->getId());
        $this->assertEquals("unspecific monooxygenase.", $oParser->getDescription());
    }

    /**
     * A record whose EC number was moved elsewhere keeps only the notice saying where to look.
     */
    public function testFetchReadsATransferredEntry()
    {
        $collectionElement = new CollectionElement();
        $collectionElement->setIdElement("1.1.1.5");
        $collectionElement->setFileName("enzyme.dat");
        $collectionElement->setDbFormat("EXPASY_ENZYME");
        $collectionElement->setSeqCount(5);
        $collectionElement->setLineNo(52);

        $repo = $this->createMock(EntityRepository::class);
        $repo->method('findOneBy')->willReturn($collectionElement);

        $mockedEm = $this->createMock(EntityManager::class);
        $mockedEm->method('getRepository')->willReturn($repo);

        $databaseManager = new DatabaseManager($mockedEm, './data/');
        $oParser = $databaseManager->fetch("1.1.1.5");

        $this->assertEquals("1.1.1.5", $oParser->getId());
        $this->assertEquals("Transferred entry: 1.1.1.303 and 1.1.1.304.", $oParser->getDescription());
    }
}
