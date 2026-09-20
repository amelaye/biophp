<?php
namespace Tests\Domain\Database\Service;

use Amelaye\BioPHP\Domain\Database\Entity\Collection;
use Amelaye\BioPHP\Domain\Database\Entity\ParsedRecord;
use Amelaye\BioPHP\Domain\Database\Factory\DatabaseParserFactory;
use Amelaye\BioPHP\Domain\Database\Service\RecordManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tests\Domain\SqliteEntityManagerTrait;

class RecordManagerTest extends TestCase
{
    use SqliteEntityManagerTrait;

    /**
     * One sample data file per registered format.
     */
    private const SAMPLES = [
        "GENBANK"          => "human.seq",
        "SWISSPROT"        => "basicswiss.txt",
        "EMBL"             => "sample.embl",
        "PDB"              => "sample.pdb",
        "PROSITE"          => "sample.prosite",
        "EXPASY_ENZYME"    => "sample.expasy",
        "PDBSTR"           => "sample.pdbstr",
        "UNIGENE"          => "sample.unigene",
        "PRINTS"           => "sample.prints",
        "BLOCKS"           => "sample.blocks",
        "AAINDEX"          => "sample.aaindex",
        "PRODOM"           => "sample.prodom",
        "NCBI_LIT"         => "sample.ncbilit",
        "PMD"              => "sample.pmd",
        "HGBASE"           => "sample.hgbase",
        "PRF"              => "sample.prf",
        "PIR"              => "sample.pir",
        "EPD"              => "sample.epd",
        "GENOME"           => "sample.genome",
        "ENTREZ"           => "sample.entrez",
        "TRANSFAC_MATRIX"  => "sample.transfacmatrix",
        "TRANSFAC_GENE"    => "sample.transfacgene",
        "TRANSFAC_CLASS"   => "sample.transfacclass",
        "TRANSFAC_CELL"    => "sample.transfaccell",
        "TRANSFAC_FACTOR"  => "sample.transfacfactor",
        "TRANSFAC_SITE"    => "sample.transfacsite",
        "KEGG_COMPOUND"    => "sample.keggcompound",
        "KEGG_REACTION"    => "sample.keggreaction",
        "KEGG_ENZYME"      => "sample.keggenzyme",
        "KEGG_ORTHOLOG"    => "sample.keggortholog",
        "KEGG_GENOME"      => "sample.kegggenome",
    ];

    private function createManager($oEm): RecordManager
    {
        return new RecordManager($oEm, dirname(__DIR__, 4) . "/data/");
    }

    public function testEveryRegisteredFormatHasASample()
    {
        $aFormats = DatabaseParserFactory::getFormats();
        sort($aFormats);
        $aSampled = array_keys(self::SAMPLES);
        sort($aSampled);

        $this->assertSame($aFormats, $aSampled, "A parser was added or removed without updating the samples.");
    }

    #[DataProvider("formatProvider")]
    public function testEveryFormatIsStoredAndReadBack(string $sFormat, string $sSample)
    {
        $oEm      = $this->createEntityManagerWithSchema();
        $oManager = $this->createManager($oEm);

        $iCount = $oManager->storeFile("coll_" . $sFormat, $sFormat, $sSample);
        $this->assertGreaterThan(0, $iCount);

        $aStored = $oEm->getRepository(ParsedRecord::class)->findAll();
        $this->assertNotEmpty($aStored);

        $oEm->clear();
        foreach ($aStored as $oStored) {
            $oFound = $oManager->find($sFormat, $oStored->getEntryId());
            $this->assertNotNull($oFound);
            $this->assertNotEmpty($oFound->getData());
            $this->assertEquals($oStored->getData(), $oFound->getData());
            $this->assertSame("coll_" . $sFormat, $oFound->getCollection()->getNomCollection());
        }
    }

    public static function formatProvider(): array
    {
        $aCases = [];
        foreach (self::SAMPLES as $sFormat => $sSample) {
            $aCases[$sFormat] = [$sFormat, $sSample];
        }

        return $aCases;
    }

    public function testEveryRecordOfAMultiRecordFileIsStored()
    {
        $oEm      = $this->createEntityManagerWithSchema();
        $oManager = $this->createManager($oEm);

        $this->assertSame(5, $oManager->storeFile("enzymes", "EXPASY_ENZYME", "enzyme.dat"));
        $this->assertCount(5, $oEm->getRepository(ParsedRecord::class)->findAll());
        $this->assertNotNull($oManager->find("EXPASY_ENZYME", "1.1.1.2"));
    }

    public function testStoringARecordTwiceUpdatesIt()
    {
        $oEm      = $this->createEntityManagerWithSchema();
        $oManager = $this->createManager($oEm);
        $aLines   = file(dirname(__DIR__, 4) . "/data/sample.keggcompound");

        $oFirst  = $oManager->store("KEGG_COMPOUND", $aLines);
        $oSecond = $oManager->store("KEGG_COMPOUND", $aLines);

        $this->assertSame($oFirst->getId(), $oSecond->getId());
        $this->assertCount(1, $oEm->getRepository(ParsedRecord::class)->findAll());
    }

    public function testFindReturnsNullForAnUnknownRecord()
    {
        $oManager = $this->createManager($this->createEntityManagerWithSchema());

        $this->assertNull($oManager->find("GENBANK", "NOPE"));
    }

    public function testAnUnknownFormatIsRefused()
    {
        $oManager = $this->createManager($this->createEntityManagerWithSchema());

        $this->expectException(\Exception::class);
        $oManager->storeFile("coll", "NOT_A_FORMAT", "sample.embl");
    }

    public function testStoringNoFileIsRefused()
    {
        $oManager = $this->createManager($this->createEntityManagerWithSchema());

        $this->expectException(\InvalidArgumentException::class);
        $oManager->storeFile("coll", "EMBL");
    }

    public function testAMissingFileIsRefused()
    {
        $oManager = $this->createManager($this->createEntityManagerWithSchema());

        $this->expectException(\RuntimeException::class);
        $oManager->storeFile("coll", "EMBL", "no-such-file.embl");
    }

    public function testTheCollectionIsCreatedOnceAndReused()
    {
        $oEm      = $this->createEntityManagerWithSchema();
        $oManager = $this->createManager($oEm);

        $oManager->storeFile("shared", "EMBL", "sample.embl");
        $oManager->storeFile("shared", "KEGG_COMPOUND", "sample.keggcompound");

        $this->assertCount(1, $oEm->getRepository(Collection::class)->findBy(["nomCollection" => "shared"]));
    }
}
