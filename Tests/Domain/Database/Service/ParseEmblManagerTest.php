<?php
namespace Tests\Domain\Database\Service;

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
            'Mammalia', 'Eutheria', 'Euarchontoglires', 'Primates', 'Haplorrhini', 'Catarrhini', 'Hominidae', 'Homo.'];
        $oExpectedSequence->setOrganism($organism);
        $this->assertEquals($oExpectedSequence, $oParseEmblManager->getSequence());

        $aExpectedAuthors = [];
        $oAuthor = new Author();
        $oAuthor->setPrimAcc("AB012345");
        $oAuthor->setRefno(1);
        $oAuthor->setAuthor("Smith J");
        $aExpectedAuthors[] = $oAuthor;
        $oAuthor = new Author();
        $oAuthor->setPrimAcc("AB012345");
        $oAuthor->setRefno(1);
        $oAuthor->setAuthor("Doe A");
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
        $aExpectedFeatures[] = $oFeature;
        $oFeature = new Feature();
        $oFeature->setPrimAcc("AB012345");
        $oFeature->setFtKey("source");
        $oFeature->setFtFrom(1);
        $oFeature->setFtTo(120);
        $oFeature->setFtQual("mol_type");
        $oFeature->setFtValue("mRNA");
        $aExpectedFeatures[] = $oFeature;
        $oFeature = new Feature();
        $oFeature->setPrimAcc("AB012345");
        $oFeature->setFtKey("source");
        $oFeature->setFtFrom(1);
        $oFeature->setFtTo(120);
        $oFeature->setFtQual("db_xref");
        $oFeature->setFtValue("taxon:9606");
        $aExpectedFeatures[] = $oFeature;
        $oFeature = new Feature();
        $oFeature->setPrimAcc("AB012345");
        $oFeature->setFtKey("CDS");
        $oFeature->setFtFrom(1);
        $oFeature->setFtTo(120);
        $oFeature->setFtQual("gene");
        $oFeature->setFtValue("TESTG");
        $aExpectedFeatures[] = $oFeature;
        $oFeature = new Feature();
        $oFeature->setPrimAcc("AB012345");
        $oFeature->setFtKey("CDS");
        $oFeature->setFtFrom(1);
        $oFeature->setFtTo(120);
        $oFeature->setFtQual("codon_start");
        $oFeature->setFtValue("1");
        $aExpectedFeatures[] = $oFeature;
        $oFeature = new Feature();
        $oFeature->setPrimAcc("AB012345");
        $oFeature->setFtKey("CDS");
        $oFeature->setFtFrom(1);
        $oFeature->setFtTo(120);
        $oFeature->setFtQual("product");
        $oFeature->setFtValue("test protein");
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
}
