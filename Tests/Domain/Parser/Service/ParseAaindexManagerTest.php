<?php
namespace Tests\Domain\Parser\Service;

use Amelaye\BioPHP\Domain\Database\Entity\Collection;
use Amelaye\BioPHP\Domain\Database\Entity\CollectionElement;
use Amelaye\BioPHP\Domain\Database\Service\DatabaseManager;
use Amelaye\BioPHP\Domain\Parser\Service\ParseAaindexManager;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class ParseAaindexManagerTest extends TestCase
{
    /**
     * @return ParseAaindexManager
     */
    private function fetch()
    {
        $collection = new Collection();
        $collection->setId(1);
        $collection->setNomCollection("aaindexdb");

        $collectionElement = new CollectionElement();
        $collectionElement->setIdElement("ANDN920101");
        $collectionElement->setFileName("sample.aaindex");
        $collectionElement->setDbFormat("AAINDEX");
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
            ->with(['idElement' => "ANDN920101"])
            ->willReturn($collectionElement);

        $databaseManager = new DatabaseManager($mockedEm, "./data/");

        return $databaseManager->fetch("ANDN920101");
    }

    public function testFormatMetadata()
    {
        $aFlines = file("./data/sample.aaindex");

        $this->assertEquals("AAINDEX", ParseAaindexManager::getFormat());
        $this->assertTrue(ParseAaindexManager::isEntryStart($aFlines[0]));
        $this->assertEquals("ANDN920101", ParseAaindexManager::getEntryId($aFlines, $aFlines[0]));
        $this->assertTrue(ParseAaindexManager::isEntryEnd("//"));
        $this->assertFalse(ParseAaindexManager::isEntryEnd($aFlines[0]));
    }

    public function testFetch()
    {
        $oParser = $this->fetch();

        $this->assertInstanceOf(ParseAaindexManager::class, $oParser);
        $this->assertEquals("ANDN920101", $oParser->getAccession());
        $this->assertEquals(
            "alpha-CH chemical shifts (Andersen et al., 1992)",
            $oParser->getDescription()
        );
        $this->assertEquals("Andersen, N.H., Cao, B. and Chen, C.", $oParser->getAuthor());
        $this->assertEquals(
            "Biochem. and Biophys. Res. Comm. 184, 1008-1014 (1992)",
            $oParser->getJournal()
        );
    }

    /**
     * A continuation line starts with a space and belongs to the field above it.
     */
    public function testMultilineTitleIsJoined()
    {
        $oParser = $this->fetch();

        $this->assertEquals(
            "Peptide/protein structure analysis using the chemical shift index method: "
            . "upfield alpha-CH values reveal dynamic helices and aL sites",
            $oParser->getTitle()
        );
    }

    public function testLitRefsAreKeyedByDatabase()
    {
        $oParser = $this->fetch();

        $this->assertEquals(["LIT" => "1810048b", "PMID" => "1575719"], $oParser->getLitRefs());
    }

    /**
     * The R field is allowed to carry nothing at all.
     */
    public function testEmptyReferenceLineIsIgnored()
    {
        $oParser = new ParseAaindexManager();
        $oParser->parseDataFile(["H TEST000001", "R ", "//"]);

        $this->assertEquals("TEST000001", $oParser->getAccession());
        $this->assertEquals([], $oParser->getLitRefs());
    }

    /**
     * The C and I fields, the correlated entries and the index values themselves, were never
     * read : the entry gave its references but not its data.
     */
    public function testReadsTheCorrelationsAndTheIndexValues()
    {
        $oParser = new ParseAaindexManager();
        $oParser->parseDataFile([
            "H TEST000001\n",
            "D A test index\n",
            "C    BUNA790101    0.949  ZIMJ680102    0.917\n",
            "     TEST000002   -0.812\n",
            "I    A/L     R/K     N/M     D/F     C/P     Q/S     E/T     G/W     H/Y     I/V\n",
            "      4.35    4.38    4.75    4.76    4.65    4.37    4.29    3.97    4.63    3.95\n",
            "      4.17    4.36    4.52    4.66    4.44    4.50    4.35    4.70      NA    3.95\n",
            "//\n",
        ]);

        $this->assertEquals(["BUNA790101" => 0.949, "ZIMJ680102" => 0.917, "TEST000002" => -0.812], $oParser->getCorrelations());
        $aIndex = $oParser->getIndex();
        $this->assertCount(20, $aIndex);
        $this->assertEquals(4.35, $aIndex["A"]);
        $this->assertEquals(4.17, $aIndex["L"]);
        $this->assertEquals(3.95, $aIndex["V"]);
        $this->assertNull($aIndex["Y"]);
        $this->assertEquals("A test index", $oParser->getDescription());
    }
}
