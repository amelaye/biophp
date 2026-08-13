<?php
namespace Tests\Domain\Database\Service;

use Amelaye\BioPHP\Domain\Database\Entity\Collection;
use Amelaye\BioPHP\Domain\Database\Entity\CollectionElement;
use Amelaye\BioPHP\Domain\Database\Service\DatabaseManager;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class ParsePdbManagerTest extends TestCase
{
    public function testFetch()
    {
        $collection = new Collection();
        $collection->setId(1);
        $collection->setNomCollection("pdbdb");

        $collectionElement = new CollectionElement();
        $collectionElement->setIdElement("1TST");
        $collectionElement->setFileName("sample.pdb");
        $collectionElement->setDbFormat("PDB");
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
            ->with(['idElement' => "1TST"])
            ->willReturn($collectionElement);

        $databaseManager = new DatabaseManager($mockedEm, "./data/");
        $oParsePdbManager = $databaseManager->fetch("1TST");

        $this->assertEquals("1TST", $oParsePdbManager->getIdCode());
        $this->assertEquals("HYDROLASE", $oParsePdbManager->getClassification());
        $this->assertEquals("15-JAN-20", $oParsePdbManager->getDepositionDate());
        $this->assertEquals("CRYSTAL STRUCTURE OF TEST PROTEIN", $oParsePdbManager->getTitle());
        $this->assertEquals(
            ["MOL_ID: 1", "MOLECULE: TEST PROTEIN", "CHAIN: A"],
            $oParsePdbManager->getCompounds()
        );
        $this->assertEquals("MOL_ID: 1; ORGANISM_SCIENTIFIC: HOMO SAPIENS;", $oParsePdbManager->getSource());
        $this->assertEquals(["HYDROLASE", "TEST", "STRUCTURAL GENOMICS"], $oParsePdbManager->getKeywords());
        $this->assertEquals("X-RAY DIFFRACTION", $oParsePdbManager->getExperimentalTechnique());
        $this->assertEquals(["J.SMITH", "A.DOE"], $oParsePdbManager->getAuthors());
        $this->assertEquals(["A" => "AGVLI"], $oParsePdbManager->getSeqRes());

        $this->assertEquals(
            ["a" => 50.0, "b" => 60.0, "c" => 70.0, "alpha" => 90.0, "beta" => 90.0, "gamma" => 90.0,
                "spaceGroup" => "P 21 21 21", "z" => 4],
            $oParsePdbManager->getCryst1()
        );

        $aHelices = $oParsePdbManager->getHelices();
        $this->assertCount(1, $aHelices);
        $this->assertEquals("H1", $aHelices[0]->getHelixId());
        $this->assertEquals("ALA", $aHelices[0]->getInitResName());
        $this->assertEquals("A", $aHelices[0]->getInitChainId());
        $this->assertEquals(1, $aHelices[0]->getInitSeqNum());
        $this->assertEquals("VAL", $aHelices[0]->getEndResName());
        $this->assertEquals("A", $aHelices[0]->getEndChainId());
        $this->assertEquals(3, $aHelices[0]->getEndSeqNum());
        $this->assertEquals(1, $aHelices[0]->getHelixClass());
        $this->assertEquals(3, $aHelices[0]->getLength());

        $aSheets = $oParsePdbManager->getSheets();
        $this->assertCount(1, $aSheets);
        $this->assertEquals("S1", $aSheets[0]->getSheetId());
        $this->assertEquals(1, $aSheets[0]->getStrand());
        $this->assertEquals("LEU", $aSheets[0]->getInitResName());
        $this->assertEquals("A", $aSheets[0]->getInitChainId());
        $this->assertEquals(4, $aSheets[0]->getInitSeqNum());
        $this->assertEquals("ILE", $aSheets[0]->getEndResName());
        $this->assertEquals("A", $aSheets[0]->getEndChainId());
        $this->assertEquals(5, $aSheets[0]->getEndSeqNum());

        $aAtoms = $oParsePdbManager->getAtoms();
        $this->assertCount(4, $aAtoms);
        $this->assertEquals(1, $aAtoms[0]->getSerial());
        $this->assertEquals("N", $aAtoms[0]->getName());
        $this->assertEquals("ALA", $aAtoms[0]->getResName());
        $this->assertEquals("A", $aAtoms[0]->getChainId());
        $this->assertEquals(1, $aAtoms[0]->getResSeq());
        $this->assertEquals(11.104, $aAtoms[0]->getX());
        $this->assertEquals(6.134, $aAtoms[0]->getY());
        $this->assertEquals(-6.504, $aAtoms[0]->getZ());
        $this->assertEquals(1.0, $aAtoms[0]->getOccupancy());
        $this->assertEquals(20.0, $aAtoms[0]->getTempFactor());
        $this->assertEquals("N", $aAtoms[0]->getElement());
        $this->assertEquals("CA", $aAtoms[1]->getName());
        $this->assertEquals("C", $aAtoms[2]->getName());
        $this->assertEquals("O", $aAtoms[3]->getName());

        $aHetAtoms = $oParsePdbManager->getHetAtoms();
        $this->assertCount(1, $aHetAtoms);
        $this->assertEquals(5, $aHetAtoms[0]->getSerial());
        $this->assertEquals("HOH", $aHetAtoms[0]->getResName());
        $this->assertEquals(101, $aHetAtoms[0]->getResSeq());
        $this->assertEquals(20.0, $aHetAtoms[0]->getX());
    }
}
