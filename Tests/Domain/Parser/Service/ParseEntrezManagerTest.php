<?php
namespace Tests\Domain\Parser\Service;

use Amelaye\BioPHP\Domain\Database\Entity\Collection;
use Amelaye\BioPHP\Domain\Database\Entity\CollectionElement;
use Amelaye\BioPHP\Domain\Database\Service\DatabaseManager;
use Amelaye\BioPHP\Domain\Parser\Service\ParseEntrezManager;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class ParseEntrezManagerTest extends TestCase
{
    public function testFetch()
    {
        $collection = new Collection();
        $collection->setId(1);
        $collection->setNomCollection("entrezdb");

        $collectionElement = new CollectionElement();
        $collectionElement->setIdElement("NC_001416");
        $collectionElement->setFileName("sample.entrez");
        $collectionElement->setDbFormat("ENTREZ");
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
            ->with(['idElement' => "NC_001416"])
            ->willReturn($collectionElement);

        $databaseManager = new DatabaseManager($mockedEm, "./data/");
        $oParser = $databaseManager->fetch("NC_001416");

        // LOCUS, read at fixed columns
        $this->assertEquals("NC_001416", $oParser->getEntryName());
        $this->assertEquals(48502, $oParser->getLength());
        $this->assertEquals("DNA", $oParser->getMolType());
        $this->assertEquals("DOUBLE", $oParser->getStrands());
        $this->assertEquals("LINEAR", $oParser->getTopology());
        $this->assertEquals("PHG", $oParser->getDivision());
        $this->assertEquals("06-JUL-2016", $oParser->getEntryDate());

        $this->assertEquals("Escherichia phage Lambda, complete genome.", $oParser->getDefinition());
        $this->assertEquals(["NC_001416", "J02459"], $oParser->getAccession());
        $this->assertEquals("NC_001416", $oParser->getPrimAcc());
        $this->assertEquals("NC_001416.1", $oParser->getVersion());
        $this->assertEquals("GI:9626243", $oParser->getNcbiGiId());
        $this->assertEquals(["complete genome", "bacteriophage"], $oParser->getKeywords());
        $this->assertEquals("Escherichia phage Lambda", $oParser->getSource());
        $this->assertEquals("Escherichia phage Lambda", $oParser->getOrganism());
        $this->assertEquals(
            ["Viruses", "Duplodnaviria", "Heunggongvirae", "Uroviricota", "Caudoviricetes", "Lambdavirus"],
            $oParser->getTaxonomy()
        );

        $aReferences = $oParser->getReferences();
        $this->assertCount(2, $aReferences);

        $this->assertEquals("1", $aReferences[0]->getRefNo());
        $this->assertEquals("(bases 1 to 48502)", $aReferences[0]->getBaseRange());
        $this->assertEquals(
            ["Sanger,F.", "Coulson,A.R.", "Hong,G.F.", "Hill,D.F.", "Petersen,G.B."],
            $aReferences[0]->getAuthors()
        );
        $this->assertEquals(
            "Nucleotide sequence of bacteriophage lambda DNA",
            $aReferences[0]->getTitle()
        );
        $this->assertEquals("J. Mol. Biol. 162 (4), 729-773 (1982)", $aReferences[0]->getJournal());
        $this->assertEquals("6221115", $aReferences[0]->getPubmed());
        $this->assertEquals("", $aReferences[0]->getRemark());

        $this->assertEquals("2", $aReferences[1]->getRefNo());
        $this->assertEquals(["Hendrix,R.W.", "Duda,R.L."], $aReferences[1]->getAuthors());
        $this->assertEquals(
            "Bacteriophage lambda PaPa: not the mother of all lambda phages",
            $aReferences[1]->getTitle()
        );
        $this->assertEquals("Science 258 (5085), 1145-1148 (1992)", $aReferences[1]->getJournal());
        $this->assertEquals("1439823", $aReferences[1]->getPubmed());
        $this->assertEquals("Review of the reference strain", $aReferences[1]->getRemark());
    }

    public function testFormatMetadata()
    {
        $this->assertEquals("ENTREZ", ParseEntrezManager::getFormat());
        $this->assertTrue(ParseEntrezManager::isEntryStart("LOCUS       NC_001416"));
        $this->assertFalse(ParseEntrezManager::isEntryStart("DEFINITION  Something."));
        $this->assertTrue(ParseEntrezManager::isEntryEnd("//"));
        $this->assertFalse(ParseEntrezManager::isEntryEnd("PUBMED      6221115"));
    }

    /**
     * The identifier is the first accession number, wherever the ACCESSION line sits.
     */
    public function testGetEntryIdReadsTheFirstAccession()
    {
        $aFlines = [
            "LOCUS       NC_001416",
            "ACCESSION   NC_001416 J02459",
        ];

        $this->assertEquals("NC_001416", ParseEntrezManager::getEntryId($aFlines, $aFlines[0]));
    }

    /**
     * A record short of an ACCESSION line falls back on the name its LOCUS line carries.
     */
    public function testGetEntryIdFallsBackOnTheLocusName()
    {
        $aFlines = ["LOCUS       NC_001416              48502 bp ds-DNA"];

        $this->assertEquals("NC_001416", ParseEntrezManager::getEntryId($aFlines, $aFlines[0]));
    }

    /**
     * A KEYWORDS field holding just a period holds no keyword : NCBI writes it that way when
     * an entry has none.
     */
    public function testAKeywordsFieldHoldingOnlyAPeriodIsEmpty()
    {
        $oParser = new ParseEntrezManager();
        $oParser->parseDataFile(["LOCUS       NC_001416", "KEYWORDS    .", "//"]);

        $this->assertEquals([], $oParser->getKeywords());
    }

    /**
     * A single-strand record reads as such : the strand count sits in its own LOCUS columns,
     * just before the molecule type.
     */
    public function testASingleStrandedRecordIsReadAsSuch()
    {
        $oParser = new ParseEntrezManager();
        $oParser->parseDataFile(["LOCUS       NC_001422               5386 bp ss-DNA     circular PHG 06-JUL-2016"]);

        $this->assertEquals("SINGLE", $oParser->getStrands());
        $this->assertEquals("DNA", $oParser->getMolType());
        $this->assertEquals("CIRCULAR", $oParser->getTopology());
        $this->assertEquals(5386, $oParser->getLength());
    }
}
