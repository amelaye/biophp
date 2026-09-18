<?php
namespace Tests\Domain\Parser\Service;

use Amelaye\BioPHP\Domain\Database\Entity\Collection;
use Amelaye\BioPHP\Domain\Database\Entity\CollectionElement;
use Amelaye\BioPHP\Domain\Database\Service\DatabaseManager;
use Amelaye\BioPHP\Domain\Parser\Service\ParseTransfacMatrixManager;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class ParseTransfacMatrixManagerTest extends TestCase
{
    /**
     * @return ParseTransfacMatrixManager
     */
    private function fetch()
    {
        $collection = new Collection();
        $collection->setId(1);
        $collection->setNomCollection("transfacdb");

        $collectionElement = new CollectionElement();
        $collectionElement->setIdElement("M00001");
        $collectionElement->setFileName("sample.transfacmatrix");
        $collectionElement->setDbFormat("TRANSFAC_MATRIX");
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
            ->with(['idElement' => "M00001"])
            ->willReturn($collectionElement);

        $databaseManager = new DatabaseManager($mockedEm, "./data/");

        return $databaseManager->fetch("M00001");
    }

    public function testFormatMetadata()
    {
        $this->assertEquals("TRANSFAC_MATRIX", ParseTransfacMatrixManager::getFormat());
        $this->assertTrue(ParseTransfacMatrixManager::isEntryStart("AC  M00001"));
        $this->assertFalse(ParseTransfacMatrixManager::isEntryStart("ID  something"));
        $this->assertTrue(ParseTransfacMatrixManager::isEntryEnd("//"));
        $this->assertEquals("M00001", ParseTransfacMatrixManager::getEntryId(["AC  M00001", "ID  x"], "AC  M00001"));
    }

    public function testFetch()
    {
        $oParser = $this->fetch();

        $this->assertEquals("M00001", $oParser->getAccession());
        $this->assertEquals('V$MYOD_01', $oParser->getId());
        $this->assertEquals("19.10.1992", $oParser->getDateCreated());
        $this->assertEquals("16.10.1995", $oParser->getDateUpdated());
        $this->assertEquals("MyoD", $oParser->getName());
        $this->assertEquals("myoblast determination gene product", $oParser->getDescription());
        $this->assertEquals("5 functional elements in 3 genes", $oParser->getBasis());
        $this->assertEquals(
            "the canonical E-box CAGCTG bound by myogenic bHLH factors",
            $oParser->getComments()
        );
    }

    /**
     * The matrix is the record : one row per position of the site, counting how often each base
     * was seen there. Read down the consensus column and the E-box CAGCTG spells itself out.
     */
    public function testTheWeightMatrixIsReadRowByRow()
    {
        $aMatrix = $this->fetch()->getMatrix();

        $this->assertCount(6, $aMatrix);
        $this->assertEquals(["A" => 0, "C" => 5, "G" => 0, "T" => 0, "consensus" => "C"], $aMatrix[0]);
        $this->assertEquals(["A" => 0, "C" => 4, "G" => 0, "T" => 1, "consensus" => "C"], $aMatrix[3]);

        $sConsensus = "";
        foreach ($aMatrix as $aRow) {
            $sConsensus .= $aRow["consensus"];
        }
        $this->assertEquals("CAGCTG", $sConsensus);
    }

    /**
     * The header naming the base columns is not a position and must not become a row.
     */
    public function testTheP0HeaderIsNotReadAsAPosition()
    {
        $oParser = new ParseTransfacMatrixManager();
        $oParser->parseDataFile([
            "AC  M00001",
            "P0      A      C      G      T",
            "01      0      5      0      0      C",
            "//"
        ]);

        $this->assertCount(1, $oParser->getMatrix());
    }
}
