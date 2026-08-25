<?php
namespace Tests\Domain\Parser;

use Amelaye\BioPHP\Domain\Database\Entity\Collection;
use Amelaye\BioPHP\Domain\Database\Entity\CollectionElement;
use Amelaye\BioPHP\Domain\Database\Service\DatabaseManager;
use Amelaye\BioPHP\Domain\Parser\ParsePrositeManager;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class ParsePrositeManagerTest extends TestCase
{
    public function testFetch()
    {
        $collection = new Collection();
        $collection->setId(1);
        $collection->setNomCollection("prositedb");

        $collectionElement = new CollectionElement();
        $collectionElement->setIdElement("PS90001");
        $collectionElement->setFileName("sample.prosite");
        $collectionElement->setDbFormat("PROSITE");
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
            ->with(['idElement' => "PS90001"])
            ->willReturn($collectionElement);

        $databaseManager = new DatabaseManager($mockedEm, "./data/");
        $oParsePrositeManager = $databaseManager->fetch("PS90001");

        $this->assertEquals("TEST_MOTIF", $oParsePrositeManager->getEntryName());
        $this->assertEquals("PATTERN", $oParsePrositeManager->getEntryType());
        $this->assertEquals("PS90001", $oParsePrositeManager->getAccession());
        $this->assertEquals(
            ["CREATED" => "JAN-2020", "DATA UPDATE" => "JAN-2020", "INFO UPDATE" => "FEB-2021"],
            $oParsePrositeManager->getDates()
        );
        $this->assertEquals("Test motif signature.", $oParsePrositeManager->getDescription());
        $this->assertEquals("[AG]-x-[ST]-x(2)-[DE].", $oParsePrositeManager->getPattern());
        $this->assertEquals("", $oParsePrositeManager->getMatrix());
        $this->assertEquals(
            ["RELEASE" => "1,100", "TOTAL" => "10(10)", "POSITIVE" => "10(10)", "UNKNOWN" => "0(0)",
                "FALSE_POS" => "0(0)", "FALSE_NEG" => "0", "PARTIAL" => "0"],
            $oParsePrositeManager->getNumericalResults()
        );
        $this->assertEquals(
            ["TAXO-RANGE" => "??EPV", "MAX-REPEAT" => "1", "SITE" => "1,test_site"],
            $oParsePrositeManager->getComments()
        );
        $this->assertEquals("This is a test rule describing the motif in free text.", $oParsePrositeManager->getRule());
        $this->assertEquals(["1ABC", "2XYZ"], $oParsePrositeManager->getPdbXrefs());
        $this->assertEquals("PDOC90001", $oParsePrositeManager->getDocXref());

        $aDbRefs = $oParsePrositeManager->getDbRefs();
        $this->assertCount(3, $aDbRefs);
        $this->assertEquals("P00001", $aDbRefs[0]->getAccession());
        $this->assertEquals("TEST1_HUMAN", $aDbRefs[0]->getEntryName());
        $this->assertTrue($aDbRefs[0]->isTruePositive());
        $this->assertEquals("P00002", $aDbRefs[1]->getAccession());
        $this->assertTrue($aDbRefs[1]->isTruePositive());
        $this->assertEquals("P00003", $aDbRefs[2]->getAccession());
        $this->assertFalse($aDbRefs[2]->isTruePositive());
    }

    /**
     * The identifier is read from the AC line, wherever it sits in the entry.
     */
    public function testGetEntryIdReadsTheAccessionLine()
    {
        $aFlines = ["ID   TEST_PATTERN; PATTERN.", "AC   P01375;", "DE   Something."];

        $this->assertEquals("P01375", ParsePrositeManager::getEntryId($aFlines, $aFlines[0]));
    }

    /**
     * An entry without an AC line has no identifier to give : it yields an empty string rather
     * than falling off the end of the method.
     */
    public function testGetEntryIdReturnsAnEmptyStringWithoutAnAccessionLine()
    {
        $aFlines = ["ID   TEST_PATTERN; PATTERN.", "DE   Something."];

        $this->assertSame("", ParsePrositeManager::getEntryId($aFlines, $aFlines[0]));
    }

    public function testFormatMetadata()
    {
        $this->assertEquals("PROSITE", ParsePrositeManager::getFormat());
        $this->assertTrue(ParsePrositeManager::isEntryStart("ID   TEST_PATTERN; PATTERN."));
        $this->assertFalse(ParsePrositeManager::isEntryStart("DE   Something."));
    }
}
