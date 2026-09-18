<?php
namespace Tests\Domain\Parser\Service;

use Amelaye\BioPHP\Domain\Database\Entity\Collection;
use Amelaye\BioPHP\Domain\Database\Entity\CollectionElement;
use Amelaye\BioPHP\Domain\Database\Service\DatabaseManager;
use Amelaye\BioPHP\Domain\Parser\Service\ParsePrfManager;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class ParsePrfManagerTest extends TestCase
{
    /**
     * @return ParsePrfManager
     */
    private function fetch()
    {
        $collection = new Collection();
        $collection->setId(1);
        $collection->setNomCollection("prfdb");

        $collectionElement = new CollectionElement();
        $collectionElement->setIdElement("0904306A");
        $collectionElement->setFileName("sample.prf");
        $collectionElement->setDbFormat("PRF");
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
            ->with(['idElement' => "0904306A"])
            ->willReturn($collectionElement);

        $databaseManager = new DatabaseManager($mockedEm, "./data/");

        return $databaseManager->fetch("0904306A");
    }

    public function testFormatMetadata()
    {
        $aFlines = file("./data/sample.prf");

        $this->assertEquals("PRF", ParsePrfManager::getFormat());
        $this->assertTrue(ParsePrfManager::isEntryStart($aFlines[0]));
        $this->assertEquals("0904306A", ParsePrfManager::getEntryId($aFlines, $aFlines[0]));
        $this->assertTrue(ParsePrfManager::isEntryEnd("///"));
        $this->assertFalse(ParsePrfManager::isEntryEnd($aFlines[0]));
    }

    public function testFetch()
    {
        $oParser = $this->fetch();

        $this->assertInstanceOf(ParsePrfManager::class, $oParser);
        $this->assertEquals("0904306A", $oParser->getEntryCode());
        $this->assertEquals("interleukin 2", $oParser->getEntryName());
        $this->assertEquals("Homo sapiens", $oParser->getSource());
        $this->assertEquals("man", $oParser->getCommonName());
        $this->assertEquals("Nature(London), 302(5906),305-310(1983)", $oParser->getJournal());
        $this->assertEquals(
            "Structure and expression of a cloned cDNA for human interleukin-2.",
            $oParser->getTitle()
        );
    }

    public function testAuthorsKeepTheirTrailingInitial()
    {
        $oParser = $this->fetch();

        $this->assertEquals(
            [
                "Taniguchi,T.", "Matsui,H.", "Fujita,T.", "Takaoka,C.",
                "Kashima,N.", "Yoshimoto,R.", "Hamuro,J."
            ],
            $oParser->getAuthors()
        );
    }

    /**
     * The lineage is a semicolon separated list which may run over several lines.
     */
    public function testTaxonomySpansContinuationLines()
    {
        $oParser = $this->fetch();

        $aTaxonomy = $oParser->getTaxonomy();
        $this->assertCount(11, $aTaxonomy);
        $this->assertEquals("Eucarya", $aTaxonomy[0]);
        $this->assertEquals("Hominidae", $aTaxonomy[10]);
    }

    /**
     * Keywords are separated by a run of at least two spaces, and a line break separates them
     * too : the last keyword of a line must not weld onto the first of the next.
     */
    public function testKeywordsAreNotWeldedAcrossLines()
    {
        $oParser = $this->fetch();

        $this->assertEquals(
            [
                "Interleukin 2", "Human", "Cloning From cDNA Library",
                "Seq Determination", "812bp", "mRNA Hybridization Translation"
            ],
            $oParser->getKeywords()
        );
    }

    /**
     * The same database may be cross-referenced twice, so references are a list, not a map.
     */
    public function testCrossRefsKeepDuplicateDatabases()
    {
        $oParser = $this->fetch();

        $this->assertEquals(
            [
                ["db" => "PIR", "id" => "ICHU2"],
                ["db" => "PIR", "id" => "ICGI2"]
            ],
            $oParser->getCrossRefs()
        );
    }

    public function testSequenceIsStrippedOfItsLayout()
    {
        $oParser = $this->fetch();

        $this->assertStringStartsWith("MYRMQLLSCIALSLALVTNS", $oParser->getSequence());
        $this->assertStringEndsWith("WITFCQSIISTLT", $oParser->getSequence());
        $this->assertEquals(153, strlen($oParser->getSequence()));
    }
}
