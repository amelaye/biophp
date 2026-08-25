<?php
namespace Tests\Domain\Parser;

use Amelaye\BioPHP\Domain\Database\Entity\Collection;
use Amelaye\BioPHP\Domain\Database\Entity\CollectionElement;
use Amelaye\BioPHP\Domain\Database\Service\DatabaseManager;
use Amelaye\BioPHP\Domain\Parser\ParseUnigeneManager;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class ParseUnigeneManagerTest extends TestCase
{
    /**
     * @return ParseUnigeneManager
     */
    private function fetch()
    {
        $collection = new Collection();
        $collection->setId(1);
        $collection->setNomCollection("unigenedb");

        $collectionElement = new CollectionElement();
        $collectionElement->setIdElement("Sbi.1");
        $collectionElement->setFileName("sample.unigene");
        $collectionElement->setDbFormat("UNIGENE");
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
            ->with(['idElement' => "Sbi.1"])
            ->willReturn($collectionElement);

        $databaseManager = new DatabaseManager($mockedEm, "./data/");

        return $databaseManager->fetch("Sbi.1");
    }

    public function testFormatMetadata()
    {
        $aFlines = file("./data/sample.unigene");

        $this->assertEquals("UNIGENE", ParseUnigeneManager::getFormat());
        $this->assertTrue(ParseUnigeneManager::isEntryStart($aFlines[0]));
        $this->assertEquals("Sbi.1", ParseUnigeneManager::getEntryId($aFlines, $aFlines[0]));
    }

    public function testFetch()
    {
        $oParser = $this->fetch();

        $this->assertInstanceOf(ParseUnigeneManager::class, $oParser);
        $this->assertEquals("Sbi.1", $oParser->getClusterId());
        $this->assertEquals(12, $oParser->getSeqCount());
    }

    /**
     * A TITLE spanning several lines is joined back into one string.
     */
    public function testMultilineTitleIsJoined()
    {
        $oParser = $this->fetch();

        $this->assertEquals(
            "ESTs, Moderately similar to putative pyrophosphate-fructose-6-phosphate "
            . "1-phosphotransferase [Arabidopsis thaliana] [A.thaliana]",
            $oParser->getTitle()
        );
    }

    public function testExpressionIsSplitOnSemicolons()
    {
        $oParser = $this->fetch();

        $this->assertEquals(
            [
                "Embryos germinated for 24 hr",
                "10- to 14-day-old light-grown seedlings",
                "Leaves"
            ],
            $oParser->getExpression()
        );
    }

    /**
     * PROTSIM lines are kept as written, their layout being too variable to decompose.
     */
    public function testProtSimsAreKeptRaw()
    {
        $oParser = $this->fetch();

        $this->assertEquals(
            ["ORG=Arabidopsis thaliana; PROTGI=15221156; PROTID=ref:NP_172664.1; PCT=79.41; ALN=68"],
            $oParser->getProtSims()
        );
    }

    /**
     * A line longer than a hundred characters used to be cut in two by fetch(), the tail being
     * handed back as a line whose label column held the middle of a word - so it matched nothing
     * and was silently dropped. recording() reads the same files with file(), which never had
     * that cap : both paths now read a file the same way.
     */
    public function testALineLongerThanAHundredCharactersIsReadWhole()
    {
        $collection = new Collection();
        $collection->setId(1);
        $collection->setNomCollection("unigenedb");

        $collectionElement = new CollectionElement();
        $collectionElement->setIdElement("Sbi.2");
        $collectionElement->setFileName("sample_longline.unigene");
        $collectionElement->setDbFormat("UNIGENE");
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
            ->with(['idElement' => "Sbi.2"])
            ->willReturn($collectionElement);

        $databaseManager = new DatabaseManager($mockedEm, "./data/");
        $oParser = $databaseManager->fetch("Sbi.2");

        $sTitle = "ESTs, Moderately similar to putative pyrophosphate-fructose-6-phosphate "
            . "1-phosphotransferase [Arabidopsis thaliana] [A.thaliana]";

        $this->assertGreaterThan(100, strlen($sTitle));
        $this->assertEquals($sTitle, $oParser->getTitle());
        $this->assertEquals(7, $oParser->getSeqCount());
    }
}
