<?php
namespace Tests\Domain\Database\Factory;

use Amelaye\BioPHP\Domain\Database\Factory\DatabaseParserFactory;
use Amelaye\BioPHP\Domain\Database\Interfaces\ParseDatabaseInterface;
use Amelaye\BioPHP\Domain\Parser\Service\ParseAaindexManager;
use Amelaye\BioPHP\Domain\Parser\Service\ParseBlocksManager;
use Amelaye\BioPHP\Domain\Parser\Service\ParseEmblManager;
use Amelaye\BioPHP\Domain\Parser\Service\ParseEntrezManager;
use Amelaye\BioPHP\Domain\Parser\Service\ParseEpdManager;
use Amelaye\BioPHP\Domain\Parser\Service\ParseExpasyEnzymeManager;
use Amelaye\BioPHP\Domain\Parser\Service\ParseGenbankManager;
use Amelaye\BioPHP\Domain\Parser\Service\ParsePdbManager;
use Amelaye\BioPHP\Domain\Parser\Service\ParseGenomeManager;
use Amelaye\BioPHP\Domain\Parser\Service\ParseHgbaseManager;
use Amelaye\BioPHP\Domain\Parser\Service\ParseKeggCompoundManager;
use Amelaye\BioPHP\Domain\Parser\Service\ParseKeggEnzymeManager;
use Amelaye\BioPHP\Domain\Parser\Service\ParseKeggGenomeManager;
use Amelaye\BioPHP\Domain\Parser\Service\ParseKeggOrthologManager;
use Amelaye\BioPHP\Domain\Parser\Service\ParseKeggReactionManager;
use Amelaye\BioPHP\Domain\Parser\Service\ParseNcbiLitManager;
use Amelaye\BioPHP\Domain\Parser\Service\ParsePdbstrManager;
use Amelaye\BioPHP\Domain\Parser\Service\ParsePirManager;
use Amelaye\BioPHP\Domain\Parser\Service\ParsePmdManager;
use Amelaye\BioPHP\Domain\Parser\Service\ParsePrfManager;
use Amelaye\BioPHP\Domain\Parser\Service\ParsePrintsManager;
use Amelaye\BioPHP\Domain\Parser\Service\ParseProdomManager;
use Amelaye\BioPHP\Domain\Parser\Service\ParsePrositeManager;
use Amelaye\BioPHP\Domain\Parser\Service\ParseSwissprotManager;
use Amelaye\BioPHP\Domain\Parser\Service\ParseTransfacCellManager;
use Amelaye\BioPHP\Domain\Parser\Service\ParseTransfacClassManager;
use Amelaye\BioPHP\Domain\Parser\Service\ParseTransfacFactorManager;
use Amelaye\BioPHP\Domain\Parser\Service\ParseTransfacGeneManager;
use Amelaye\BioPHP\Domain\Parser\Service\ParseTransfacMatrixManager;
use Amelaye\BioPHP\Domain\Parser\Service\ParseTransfacSiteManager;
use Amelaye\BioPHP\Domain\Parser\Service\ParseUnigeneManager;
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
            ["GENOME", ParseGenomeManager::class],
            ["ENTREZ", ParseEntrezManager::class],
            ["TRANSFAC_MATRIX", ParseTransfacMatrixManager::class],
            ["TRANSFAC_GENE", ParseTransfacGeneManager::class],
            ["TRANSFAC_CLASS", ParseTransfacClassManager::class],
            ["TRANSFAC_CELL", ParseTransfacCellManager::class],
            ["TRANSFAC_FACTOR", ParseTransfacFactorManager::class],
            ["TRANSFAC_SITE", ParseTransfacSiteManager::class],
            ["KEGG_COMPOUND", ParseKeggCompoundManager::class],
            ["KEGG_REACTION", ParseKeggReactionManager::class],
            ["KEGG_ENZYME", ParseKeggEnzymeManager::class],
            ["KEGG_ORTHOLOG", ParseKeggOrthologManager::class],
            ["KEGG_GENOME", ParseKeggGenomeManager::class]
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
            "AAINDEX", "PRODOM", "NCBI_LIT", "PMD", "HGBASE", "PRF", "PIR", "EPD", "GENOME",
            "ENTREZ", "TRANSFAC_MATRIX", "TRANSFAC_GENE", "TRANSFAC_CLASS", "TRANSFAC_CELL",
            "TRANSFAC_FACTOR", "TRANSFAC_SITE", "KEGG_COMPOUND", "KEGG_REACTION", "KEGG_ENZYME",
            "KEGG_ORTHOLOG", "KEGG_GENOME"
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
        $this->expectExceptionMessage('Unknown database format ! "XNA" is not one of GENBANK, SWISSPROT, EMBL, PDB, PROSITE, EXPASY_ENZYME, PDBSTR, UNIGENE, PRINTS, BLOCKS, AAINDEX, PRODOM, NCBI_LIT, PMD, HGBASE, PRF, PIR, EPD, GENOME, ENTREZ, TRANSFAC_MATRIX, TRANSFAC_GENE, TRANSFAC_CLASS, TRANSFAC_CELL, TRANSFAC_FACTOR, TRANSFAC_SITE, KEGG_COMPOUND, KEGG_REACTION, KEGG_ENZYME, KEGG_ORTHOLOG, KEGG_GENOME.');

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
