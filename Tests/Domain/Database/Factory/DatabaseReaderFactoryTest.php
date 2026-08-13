<?php
namespace Tests\Domain\Database\Factory;

use Amelaye\BioPHP\Domain\Database\Factory\DatabaseReaderFactory;
use Amelaye\BioPHP\Domain\Database\Service\ParseEmblManager;
use Amelaye\BioPHP\Domain\Database\Service\ParseExpasyEnzymeManager;
use Amelaye\BioPHP\Domain\Database\Service\ParseGenbankManager;
use Amelaye\BioPHP\Domain\Database\Service\ParsePdbManager;
use Amelaye\BioPHP\Domain\Database\Service\ParsePrositeManager;
use Amelaye\BioPHP\Domain\Database\Service\ParseSwissprotManager;
use PHPUnit\Framework\TestCase;

class DatabaseReaderFactoryTest extends TestCase
{
    public function testReadDatabaseGenbank()
    {
        $oService = DatabaseReaderFactory::readDatabase("GENBANK", file("./data/human.seq"));
        $this->assertInstanceOf(ParseGenbankManager::class, $oService);
    }

    public function testReadDatabaseSwissprot()
    {
        $oService = DatabaseReaderFactory::readDatabase("SWISSPROT", file("./data/basicswiss.txt"));
        $this->assertInstanceOf(ParseSwissprotManager::class, $oService);
    }

    public function testReadDatabaseEmbl()
    {
        $oService = DatabaseReaderFactory::readDatabase("EMBL", file("./data/sample.embl"));
        $this->assertInstanceOf(ParseEmblManager::class, $oService);
    }

    public function testReadDatabasePdb()
    {
        $oService = DatabaseReaderFactory::readDatabase("PDB", file("./data/sample.pdb"));
        $this->assertInstanceOf(ParsePdbManager::class, $oService);
    }

    public function testReadDatabaseProsite()
    {
        $oService = DatabaseReaderFactory::readDatabase("PROSITE", file("./data/sample.prosite"));
        $this->assertInstanceOf(ParsePrositeManager::class, $oService);
    }

    public function testReadDatabaseExpasyEnzyme()
    {
        $oService = DatabaseReaderFactory::readDatabase("EXPASY_ENZYME", file("./data/sample.expasy"));
        $this->assertInstanceOf(ParseExpasyEnzymeManager::class, $oService);
    }

    public function testReadDatabaseThrowsOnUnknownFormat()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/Unknown database format !/');
        DatabaseReaderFactory::readDatabase("UNKNOWN_FORMAT", []);
    }
}
