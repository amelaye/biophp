<?php
namespace Tests\Domain\Parser\Service;

use Amelaye\BioPHP\Domain\Database\Entity\Collection;
use Amelaye\BioPHP\Domain\Database\Entity\CollectionElement;
use Amelaye\BioPHP\Domain\Database\Service\DatabaseManager;
use Amelaye\BioPHP\Domain\Sequence\Entity\Author;
use Amelaye\BioPHP\Domain\Sequence\Entity\Feature;
use Amelaye\BioPHP\Domain\Sequence\Entity\GbSequence;
use Amelaye\BioPHP\Domain\Sequence\Entity\Keyword;
use Amelaye\BioPHP\Domain\Sequence\Entity\Reference;
use Amelaye\BioPHP\Domain\Sequence\Entity\Sequence;
use Amelaye\BioPHP\Domain\Sequence\Entity\SrcForm;
use Amelaye\BioPHP\Domain\Parser\Service\ParseEmblManager;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class ParseEmblManagerTest extends TestCase
{
    public function testFetch()
    {
        $collection = new Collection();
        $collection->setId(1);
        $collection->setNomCollection("embldb");

        $collectionElement = new CollectionElement();
        $collectionElement->setIdElement("AB012345");
        $collectionElement->setFileName("sample.embl");
        $collectionElement->setDbFormat("EMBL");
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
            ->with(['idElement' => "AB012345"])
            ->willReturn($collectionElement);

        $databaseManager = new DatabaseManager($mockedEm, "./data/");
        $oParseEmblManager = $databaseManager->fetch("AB012345");

        $this->assertEquals([], $oParseEmblManager->getAccession());

        $oExpectedSequence = new Sequence();
        $oExpectedSequence->setPrimAcc("AB012345");
        $oExpectedSequence->setSeqLength(120);
        $oExpectedSequence->setMolType("mRNA");
        $oExpectedSequence->setDate("15-JAN-2020");
        $oExpectedSequence->setSource("Homo sapiens (human)");
        $oExpectedSequence->setSequence(str_repeat("a", 30) . str_repeat("c", 30) . str_repeat("g", 30) . str_repeat("t", 30));
        $oExpectedSequence->setDescription("Homo sapiens mRNA for test gene, complete cds.");
        $organism = ['Homo sapiens (human)', 'Eukaryota', 'Metazoa', 'Chordata', 'Craniata', 'Vertebrata', 'Euteleostomi',
            'Mammalia', 'Eutheria', 'Euarchontoglires', 'Primates', 'Haplorrhini', 'Catarrhini', 'Hominidae', 'Homo'];
        $oExpectedSequence->setOrganism($organism);
        $this->assertEquals($oExpectedSequence, $oParseEmblManager->getSequence());

        $aExpectedAuthors = [];
        $oAuthor = new Author();
        $oAuthor->setPrimAcc("AB012345");
        $oAuthor->setRefno(1);
        // Initials keep their period, as in the file and in GenBank records.
        $oAuthor->setAuthor("Smith J.");
        $aExpectedAuthors[] = $oAuthor;
        $oAuthor = new Author();
        $oAuthor->setPrimAcc("AB012345");
        $oAuthor->setRefno(1);
        $oAuthor->setAuthor("Doe A.");
        $aExpectedAuthors[] = $oAuthor;
        $this->assertEquals($aExpectedAuthors, $oParseEmblManager->getAuthors());

        $aExpectedReferences = [];
        $oReference = new Reference();
        $oReference->setPrimAcc("AB012345");
        $oReference->setRefno(1);
        $oReference->setBaseRange("1-120");
        $oReference->setTitle("A test reference for the EMBL parser");
        $oReference->setPubmed("12345678");
        $oReference->setJournal("J. Test Biol. 1(1):1-10(2020).");
        $aExpectedReferences[] = $oReference;
        $this->assertEquals($aExpectedReferences, $oParseEmblManager->getReferences());

        $aExpectedKeywords = [];
        $oKeyword = new Keyword();
        $oKeyword->setPrimAcc("AB012345");
        $oKeyword->setKeywords("test gene");
        $aExpectedKeywords[] = $oKeyword;
        $oKeyword = new Keyword();
        $oKeyword->setPrimAcc("AB012345");
        $oKeyword->setKeywords("beta-glucosidase");
        $aExpectedKeywords[] = $oKeyword;
        $this->assertEquals($aExpectedKeywords, $oParseEmblManager->getKeywords());

        $aExpectedFeatures = [];
        $oFeature = new Feature();
        $oFeature->setPrimAcc("AB012345");
        $oFeature->setFtKey("source");
        $oFeature->setFtFrom(1);
        $oFeature->setFtTo(120);
        $oFeature->setFtQual("organism");
        $oFeature->setFtValue("Homo sapiens");
        $oFeature->setStrand("+");
        $oFeature->setFtLocation("1..120");
        $aExpectedFeatures[] = $oFeature;
        $oFeature = new Feature();
        $oFeature->setPrimAcc("AB012345");
        $oFeature->setFtKey("source");
        $oFeature->setFtFrom(1);
        $oFeature->setFtTo(120);
        $oFeature->setFtQual("mol_type");
        $oFeature->setFtValue("mRNA");
        $oFeature->setStrand("+");
        $oFeature->setFtLocation("1..120");
        $aExpectedFeatures[] = $oFeature;
        $oFeature = new Feature();
        $oFeature->setPrimAcc("AB012345");
        $oFeature->setFtKey("source");
        $oFeature->setFtFrom(1);
        $oFeature->setFtTo(120);
        $oFeature->setFtQual("db_xref");
        $oFeature->setFtValue("taxon:9606");
        $oFeature->setStrand("+");
        $oFeature->setFtLocation("1..120");
        $aExpectedFeatures[] = $oFeature;
        $oFeature = new Feature();
        $oFeature->setPrimAcc("AB012345");
        $oFeature->setFtKey("CDS");
        $oFeature->setFtFrom(1);
        $oFeature->setFtTo(120);
        $oFeature->setFtQual("gene");
        $oFeature->setFtValue("TESTG");
        $oFeature->setStrand("+");
        $oFeature->setFtLocation("1..120");
        $aExpectedFeatures[] = $oFeature;
        $oFeature = new Feature();
        $oFeature->setPrimAcc("AB012345");
        $oFeature->setFtKey("CDS");
        $oFeature->setFtFrom(1);
        $oFeature->setFtTo(120);
        $oFeature->setFtQual("codon_start");
        $oFeature->setFtValue("1");
        $oFeature->setStrand("+");
        $oFeature->setFtLocation("1..120");
        $aExpectedFeatures[] = $oFeature;
        $oFeature = new Feature();
        $oFeature->setPrimAcc("AB012345");
        $oFeature->setFtKey("CDS");
        $oFeature->setFtFrom(1);
        $oFeature->setFtTo(120);
        $oFeature->setFtQual("product");
        $oFeature->setFtValue("test protein");
        $oFeature->setStrand("+");
        $oFeature->setFtLocation("1..120");
        $aExpectedFeatures[] = $oFeature;
        $this->assertEquals($aExpectedFeatures, $oParseEmblManager->getFeatures());

        $oExpectedSrcForm = new SrcForm();
        $this->assertEquals($oExpectedSrcForm, $oParseEmblManager->getSrcForm());

        $oGbSequence = new GbSequence();
        $oGbSequence->setPrimAcc("AB012345");
        $oGbSequence->setTopology("LINEAR");
        $oGbSequence->setDivision("HUM");
        $oGbSequence->setVersion("AB012345.2");
        $this->assertEquals($oGbSequence, $oParseEmblManager->getGbSequence());
    }

    /**
     * Regression test: a join() location's bounds used to be computed by exploding the whole,
     * comma-separated location on ".." without splitting on the commas first, so a spliced
     * feature's ftTo ended up being the numeric prefix of a completely unrelated substring
     * (e.g. "10,50" cast to (int) as "10") instead of spanning every segment. A complement()
     * wrapper, previously dropped with no trace at all, must now be readable as the feature's
     * strand.
     */
    public function testEmblJoinLocationSpansEverySegment()
    {
        $aLines = [
            "ID   AB012345; SV 2; linear; mRNA; STD; HUM; 120 BP.\n",
            "AC   AB012345;\n",
            "FT   CDS             complement(join(1..10,50..60))\n",
            "FT                   /product=\"test protein\"\n",
        ];

        $oParser = new ParseEmblManager();
        $oParser->parseDataFile($aLines);

        $aFeatures = $oParser->getFeatures();
        $this->assertCount(1, $aFeatures);
        $this->assertEquals(1, $aFeatures[0]->getFtFrom());
        $this->assertEquals(60, $aFeatures[0]->getFtTo());
        $this->assertEquals("-", $aFeatures[0]->getStrand());
    }

    /**
     * A location with no complement() wrapper is on the direct/sense strand.
     */
    public function testEmblLocationWithoutComplementIsPlusStrand()
    {
        $aLines = [
            "ID   AB012345; SV 2; linear; mRNA; STD; HUM; 120 BP.\n",
            "AC   AB012345;\n",
            "FT   CDS             1..60\n",
            "FT                   /product=\"test protein\"\n",
        ];

        $oParser = new ParseEmblManager();
        $oParser->parseDataFile($aLines);

        $this->assertEquals("+", $oParser->getFeatures()[0]->getStrand());
    }

    /**
     * Regression test: parseAccession() used to drop the first accession of *every* AC line,
     * when only the very first accession of the very first AC line (the one duplicating the ID
     * line's entry name) should be skipped. A continuation AC line's first accession is a
     * genuine secondary accession and must be kept.
     */
    public function testEmblKeepsEveryAccessionOfContinuationLines()
    {
        $aLines = [
            "ID   AB012345; SV 2; linear; mRNA; STD; HUM; 120 BP.\n",
            "AC   AB012345;\n",
            "AC   AB023456; AB034567;\n",
        ];

        $oParser = new ParseEmblManager();
        $oParser->parseDataFile($aLines);

        $aAccessions = array_map(
            fn($oAccession) => $oAccession->getAccession(),
            $oParser->getAccession()
        );
        $this->assertEquals(["AB023456", "AB034567"], $aAccessions);
    }

    /**
     * A join() wrapped onto a second FT line used to be cut after its first line, the rest of it
     * landing in the qualifiers. EMBL now reads its feature table with GenBank's own code.
     */
    public function testEmblLocationWrappedOverSeveralLinesIsKeptWhole()
    {
        $aLines = [
            "ID   AB012345; SV 2; linear; mRNA; STD; HUM; 300 BP.\n",
            "AC   AB012345;\n",
            "FT   CDS             join(1..30,101..130,\n",
            "FT                   201..230)\n",
            "FT                   /codon_start=1\n",
            "FT                   /product=\"test protein\"\n",
            "XX\n",
            "//\n",
        ];

        $oParser = new ParseEmblManager();
        $oParser->parseDataFile($aLines);
        $aFeatures = $oParser->getFeatures();

        $this->assertCount(2, $aFeatures);
        $this->assertEquals("join(1..30,101..130,201..230)", $aFeatures[0]->getFtLocation());
        $this->assertEquals([1, 230], [$aFeatures[0]->getFtFrom(), $aFeatures[0]->getFtTo()]);
        $this->assertEquals(["codon_start", "1"], [$aFeatures[0]->getFtQual(), $aFeatures[0]->getFtValue()]);
        $this->assertEquals(["product", "test protein"], [$aFeatures[1]->getFtQual(), $aFeatures[1]->getFtValue()]);
    }

    /**
     * The GenBank qualifier fixes now apply to EMBL : a "/" or "=" inside a value is data, a
     * doubled "" is an escaped quote, a flag qualifier has no value, a feature with no qualifier
     * keeps its row without swallowing the next one, and a wrapped /translation gains no space.
     */
    public function testEmblQualifierValuesAreReadAsInGenbank()
    {
        $aLines = [
            "ID   AB012345; SV 2; linear; mRNA; STD; HUM; 120 BP.\n",
            "AC   AB012345;\n",
            "FT   gene            1..120\n",
            "FT                   /note=\"5'/3' ends; Km=2 mM; the \"\"TEST\"\" gene\"\n",
            "FT                   /pseudo\n",
            "FT   misc_feature    10..20\n",
            "FT   CDS             1..120\n",
            "FT                   /translation=\"MKVLAAGIVG\n",
            "FT                   LLLAA\"\n",
            "XX\n",
            "//\n",
        ];

        $oParser = new ParseEmblManager();
        $oParser->parseDataFile($aLines);
        $aRows = array_map(function ($oFeature) {
            return [$oFeature->getFtKey(), $oFeature->getFtQual(), $oFeature->getFtValue()];
        }, $oParser->getFeatures());

        $this->assertEquals([
            ["gene", "note", "5'/3' ends; Km=2 mM; the \"TEST\" gene"],
            ["gene", "pseudo", ""],
            ["misc_feature", "", ""],
            ["CDS", "translation", "MKVLAAGIVGLLLAA"],
        ], $aRows);
    }

    /**
     * An RC line used to shift every line after it (RP was only looked for right after RN), and
     * only the first RA and RL lines were read : a long author list or a submission address was
     * cut. RG (consortium) and RX MEDLINE were ignored.
     */
    public function testEmblReferenceBlockIsReadWhole()
    {
        $aLines = [
            "ID   AB012345; SV 2; linear; mRNA; STD; HUM; 120 BP.\n",
            "AC   AB012345;\n",
            "RN   [2]\n",
            "RC   Erratum in a later issue.\n",
            "RP   1-120\n",
            "RX   DOI; 10.1000/xyz123.\n",
            "RX   PUBMED; 12345678.\n",
            "RG   The Test Consortium\n",
            "RA   Smith J., Doe A., Martin B.-C.,\n",
            "RA   Dupont E.;\n",
            "RT   \"A test reference\n",
            "RT   spanning two lines\";\n",
            "RL   Submitted (01-JAN-2020) to the INSDC.\n",
            "RL   Dept. of Testing, Test University, Paris, FRANCE.\n",
            "XX\n",
            "//\n",
        ];

        $oParser = new ParseEmblManager();
        $oParser->parseDataFile($aLines);
        $oReference = $oParser->getReferences()[0];

        $this->assertEquals(2, $oReference->getRefno());
        $this->assertEquals("Erratum in a later issue.", $oReference->getComments());
        $this->assertEquals("1-120", $oReference->getBaseRange());
        $this->assertEquals("12345678", $oReference->getPubmed());
        $this->assertEquals("A test reference spanning two lines", $oReference->getTitle());
        $this->assertEquals(
            "Submitted (01-JAN-2020) to the INSDC. Dept. of Testing, Test University, Paris, FRANCE.",
            $oReference->getJournal()
        );
        $this->assertEquals(
            ["The Test Consortium", "Smith J.", "Doe A.", "Martin B.-C.", "Dupont E."],
            array_map(function ($oAuthor) {
                return $oAuthor->getAuthor();
            }, $oParser->getAuthors())
        );
    }

    /**
     * Before release 87 (2006), the ID line had no sequence version : "ID   entryname  dataclass;
     * [circular] molecule; division; length BP." Its first word is the entry name, not the
     * accession : HSERPG used to be stored as the primary accession and X01234 was lost.
     */
    public function testEmblIdLineOfTheLayoutBeforeRelease87()
    {
        $oParser = new ParseEmblManager();
        $oParser->parseDataFile([
            "ID   HSERPG     standard; circular DNA; HUM; 3398 BP.\n",
            "AC   X01234;\n",
            "//\n",
        ]);

        $this->assertEquals("X01234", $oParser->getSequence()->getPrimAcc());
        $this->assertEquals("X01234", $oParser->getGbSequence()->getPrimAcc());
        $this->assertEquals("HSERPG", $oParser->getSequence()->getEntryName());
        $this->assertEquals(3398, $oParser->getSequence()->getSeqLength());
        $this->assertEquals("DNA", $oParser->getSequence()->getMolType());
        $this->assertEquals("CIRCULAR", $oParser->getGbSequence()->getTopology());
        $this->assertEquals("HUM", $oParser->getGbSequence()->getDivision());
    }

    /**
     * A record cut short after its SQ line, with no "//", used to crash on a null line.
     */
    public function testARecordCutShortInItsSequenceIsReadAsFarAsItGoes()
    {
        $oParser = new ParseEmblManager();
        $oParser->parseDataFile([
            "ID   X56734; SV 1; linear; mRNA; STD; PLN; 20 BP.\n",
            "SQ   Sequence 20 BP; 5 A; 5 C; 5 G; 5 T; 0 other;\n",
            "     acgtacgtac gtacgtacgt                                              20\n",
        ]);

        $this->assertEquals("acgtacgtacgtacgtacgt", $oParser->getSequence()->getSequence());
    }

    /**
     * getEntryId() split the old ID line on ";" only : the entry was indexed under "HSERPG     standard",
     * and neither fetch("HSERPG") nor a lookup by accession found it.
     */
    public function testEmblEntryIdOfTheLayoutBeforeRelease87IsThePrimaryAccession()
    {
        $aFlines = ["ID   HSERPG     standard; circular DNA; HUM; 3398 BP.\n", "AC   X01234; X01235;\n", "//\n"];

        $this->assertEquals("X01234", ParseEmblManager::getEntryId($aFlines, $aFlines[0]));
        $this->assertEquals("HSERPG", ParseEmblManager::getEntryId(["ID   HSERPG     standard; DNA;\n"], "ID   HSERPG     standard; DNA;\n"));
        $aCurrent = ["ID   X56734; SV 1; linear; mRNA; STD; PLN; 1859 BP.\n", "AC   X56734;\n"];
        $this->assertEquals("X56734", ParseEmblManager::getEntryId($aCurrent, $aCurrent[0]));
    }

    /**
     * A keyword wrapped over two KW lines became two keywords ("complete" and "genome").
     */
    public function testAKeywordWrappedOverTwoLinesIsOneKeyword()
    {
        $oParser = new ParseEmblManager();
        $oParser->parseDataFile([
            "ID   X01234; SV 1; linear; mRNA; STD; HUM; 10 BP.\n",
            "AC   X01234;\n",
            "KW   first; complete\n",
            "KW   genome; last.\n",
            "//\n",
        ]);

        $this->assertEquals(
            ["first", "complete genome", "last"],
            array_map(fn($oKeyword) => $oKeyword->getKeywords(), $oParser->getKeywords())
        );
    }

    /**
     * A DT line without its "(Rel. XX, Created)" part raised a TypeError.
     */
    public function testADateLineWithoutReleaseDoesNotCrash()
    {
        $oParser = new ParseEmblManager();
        $oParser->parseDataFile([
            "ID   X56734; SV 1; linear; mRNA; STD; PLN; 20 BP.\n",
            "DT   15-JAN-2020\n",
            "DT   16-JAN-2020 (Rel. 1, Created)\n",
        ]);

        $this->assertEquals("16-JAN-2020", $oParser->getSequence()->getDate());
    }
}
