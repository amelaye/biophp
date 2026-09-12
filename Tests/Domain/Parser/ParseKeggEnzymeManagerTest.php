<?php
namespace Tests\Domain\Parser;

use Amelaye\BioPHP\Domain\Database\Entity\Collection;
use Amelaye\BioPHP\Domain\Database\Entity\CollectionElement;
use Amelaye\BioPHP\Domain\Database\Service\DatabaseManager;
use Amelaye\BioPHP\Domain\Parser\ParseKeggEnzymeManager;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class ParseKeggEnzymeManagerTest extends TestCase
{
    /**
     * @return ParseKeggEnzymeManager
     */
    private function fetch()
    {
        $collection = new Collection();
        $collection->setId(1);
        $collection->setNomCollection("keggdb");

        $collectionElement = new CollectionElement();
        $collectionElement->setIdElement("EC 2.7.1.1");
        $collectionElement->setFileName("sample.keggenzyme");
        $collectionElement->setDbFormat("KEGG_ENZYME");
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
            ->with(['idElement' => "EC 2.7.1.1"])
            ->willReturn($collectionElement);

        $databaseManager = new DatabaseManager($mockedEm, "./data/");

        return $databaseManager->fetch("EC 2.7.1.1");
    }

    public function testFormatMetadata()
    {
        $this->assertEquals("KEGG_ENZYME", ParseKeggEnzymeManager::getFormat());
        $this->assertTrue(ParseKeggEnzymeManager::isEntryStart("ENTRY       EC 2.7.1.1                  Enzyme"));
        $this->assertFalse(ParseKeggEnzymeManager::isEntryStart("NAME        something"));
        $this->assertTrue(ParseKeggEnzymeManager::isEntryEnd("///"));
        $this->assertFalse(ParseKeggEnzymeManager::isEntryEnd("//"));
        $this->assertEquals(
            "EC 2.7.1.1",
            ParseKeggEnzymeManager::getEntryId(["ENTRY       EC 2.7.1.1                  Enzyme"], "ENTRY       EC 2.7.1.1                  Enzyme")
        );
    }

    public function testFetch()
    {
        $oParser = $this->fetch();

        $this->assertEquals("EC 2.7.1.1", $oParser->getEntry());
        $this->assertEquals(["hexokinase", "glucose kinase"], $oParser->getNames());
        $this->assertEquals("ATP:D-hexose 6-phosphotransferase", $oParser->getSysname());
        $this->assertEquals(
            ["ATP + D-hexose = ADP + D-hexose 6-phosphate"],
            $oParser->getReactions()
        );
        $this->assertEquals(["ATP", "D-hexose"], $oParser->getSubstrates());
        $this->assertEquals(["ADP", "D-hexose 6-phosphate"], $oParser->getProducts());
        $this->assertEquals(["HSA: 3098 3099 3101", "MMU: 15275 15277"], $oParser->getGenes());
        $this->assertEquals(["MIM: 235700  Hexokinase deficiency"], $oParser->getDiseases());
        $this->assertEquals(["KO: K00844  hexokinase"], $oParser->getOrthologs());
    }

    /**
     * The ENTRY line of an enzyme names the record in two words, "EC" and the number, before
     * naming the kind of record : both words belong to the identifier.
     */
    public function testTheEcNumberKeepsItsPrefix()
    {
        $this->assertEquals("EC 2.7.1.1", $this->fetch()->getEntry());
    }

    /**
     * The enzyme classification reads from the broadest level down to the narrowest, over as
     * many lines as it takes.
     */
    public function testTheClassificationIsSplitIntoItsLevels()
    {
        $this->assertEquals(
            [
                "Transferases",
                "Transferring phosphorus-containing groups",
                "Phosphotransferases with an alcohol group as acceptor",
            ],
            $this->fetch()->getClassification()
        );
    }

    /**
     * A comment wrapping over two lines is joined back into one sentence.
     */
    public function testACommentSpanningTwoLinesIsJoined()
    {
        $this->assertEquals(
            "D-glucose, D-mannose, D-fructose and sorbitol can act as acceptors; "
            . "ITP and dATP can act as donors",
            $this->fetch()->getComment()
        );
    }

    /**
     * The STRUCTURES field names the database before listing the entries : only the entries
     * themselves are structure identifiers.
     */
    public function testTheStructureDatabaseNameIsNotAStructure()
    {
        $this->assertEquals(["1HKB", "1HKC", "1IG8"], $this->fetch()->getStructures());
    }
}
