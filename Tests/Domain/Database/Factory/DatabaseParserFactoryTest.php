<?php
namespace Tests\Domain\Database\Factory;

use Amelaye\BioPHP\Domain\Database\Factory\DatabaseParserFactory;
use Amelaye\BioPHP\Domain\Database\Interfaces\ParseDatabaseInterface;
use Amelaye\BioPHP\Domain\Parser\ParseAaindexManager;
use Amelaye\BioPHP\Domain\Parser\ParseBlocksManager;
use Amelaye\BioPHP\Domain\Parser\ParseEmblManager;
use Amelaye\BioPHP\Domain\Parser\ParseEpdManager;
use Amelaye\BioPHP\Domain\Parser\ParseExpasyEnzymeManager;
use Amelaye\BioPHP\Domain\Parser\ParseGenbankManager;
use Amelaye\BioPHP\Domain\Parser\ParsePdbManager;
use Amelaye\BioPHP\Domain\Parser\ParseGenomeManager;
use Amelaye\BioPHP\Domain\Parser\ParseHgbaseManager;
use Amelaye\BioPHP\Domain\Parser\ParseNcbiLitManager;
use Amelaye\BioPHP\Domain\Parser\ParsePdbstrManager;
use Amelaye\BioPHP\Domain\Parser\ParsePirManager;
use Amelaye\BioPHP\Domain\Parser\ParsePmdManager;
use Amelaye\BioPHP\Domain\Parser\ParsePrfManager;
use Amelaye\BioPHP\Domain\Parser\ParsePrintsManager;
use Amelaye\BioPHP\Domain\Parser\ParseProdomManager;
use Amelaye\BioPHP\Domain\Parser\ParsePrositeManager;
use Amelaye\BioPHP\Domain\Parser\ParseSwissprotManager;
use Amelaye\BioPHP\Domain\Parser\ParseUnigeneManager;
use PHPUnit\Framework\TestCase;

class DatabaseParserFactoryTest extends TestCase
{
    /**
     * @return array
     */
    public static function formatProvider(): array
    {
        return [
            ["GENBANK", ParseGenbankManager::class],
            ["SWISSPROT", ParseSwissprotManager::class],
            ["EMBL", ParseEmblManager::class],
            ["PDB", ParsePdbManager::class],
            ["PROSITE", ParsePrositeManager::class],
            ["EXPASY_ENZYME", ParseExpasyEnzymeManager::class],
            ["PDBSTR", ParsePdbstrManager::class],
            ["UNIGENE", ParseUnigeneManager::class],
            ["PRINTS", ParsePrintsManager::class],
            ["BLOCKS", ParseBlocksManager::class],
            ["AAINDEX", ParseAaindexManager::class],
            ["PRODOM", ParseProdomManager::class],
            ["NCBI_LIT", ParseNcbiLitManager::class],
            ["PMD", ParsePmdManager::class],
            ["HGBASE", ParseHgbaseManager::class],
            ["PRF", ParsePrfManager::class],
            ["PIR", ParsePirManager::class],
            ["EPD", ParseEpdManager::class],
            ["GENOME", ParseGenomeManager::class]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('formatProvider')]
    public function testGetParserClassResolvesEveryKnownFormat($sFormat, $sExpected)
    {
        $this->assertEquals($sExpected, DatabaseParserFactory::getParserClass($sFormat));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('formatProvider')]
    public function testCreateParserBuildsTheMatchingParser($sFormat, $sExpected)
    {
        $oParser = DatabaseParserFactory::createParser($sFormat);

        $this->assertInstanceOf($sExpected, $oParser);
        $this->assertInstanceOf(ParseDatabaseInterface::class, $oParser);
    }

    /**
     * A parser carries the state of the record it has read, so two reads must never share one.
     */
    public function testCreateParserReturnsAFreshInstanceEveryTime()
    {
        $oFirst  = DatabaseParserFactory::createParser("GENBANK");
        $oSecond = DatabaseParserFactory::createParser("GENBANK");

        $this->assertNotSame($oFirst, $oSecond);
    }

    public function testGetFormatsListsEveryRegisteredParser()
    {
        $aExpected = [
            "GENBANK", "SWISSPROT", "EMBL", "PDB", "PROSITE", "EXPASY_ENZYME",
            "PDBSTR", "UNIGENE", "PRINTS", "BLOCKS",
            "AAINDEX", "PRODOM", "NCBI_LIT", "PMD", "HGBASE", "PRF", "PIR", "EPD", "GENOME"
        ];

        $this->assertEquals($aExpected, DatabaseParserFactory::getFormats());
    }

    /**
     * Two parsers claiming the same name would make resolution depend on registration order.
     */
    public function testEveryRegisteredFormatNameIsUnique()
    {
        $aFormats = DatabaseParserFactory::getFormats();

        $this->assertEquals($aFormats, array_unique($aFormats));
    }

    public function testGetParserClassThrowsOnAnUnknownFormat()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unknown database format ! "XNA" is not one of GENBANK, SWISSPROT, EMBL, PDB, PROSITE, EXPASY_ENZYME, PDBSTR, UNIGENE, PRINTS, BLOCKS, AAINDEX, PRODOM, NCBI_LIT, PMD, HGBASE, PRF, PIR, EPD, GENOME.');

        DatabaseParserFactory::getParserClass("XNA");
    }

    public function testCreateParserThrowsOnAnUnknownFormat()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/Unknown database format !/');

        DatabaseParserFactory::createParser("XNA");
    }

    /**
     * Format resolution is case sensitive : the collection records store the name upper cased.
     */
    public function testGetParserClassDoesNotGuessTheCase()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/Unknown database format !/');

        DatabaseParserFactory::getParserClass("genbank");
    }
}
