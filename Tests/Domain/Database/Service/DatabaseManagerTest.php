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
            ->with(['fileName' => "human.seq"])
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
            ->with(['fileName' => "human.seq"])
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
}
