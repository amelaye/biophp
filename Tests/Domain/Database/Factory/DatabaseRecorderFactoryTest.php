<?php
namespace Tests\Domain\Database\Factory;

use Amelaye\BioPHP\Domain\Database\Factory\DatabaseRecorderFactory;
use PHPUnit\Framework\TestCase;

class DatabaseRecorderFactoryTest extends TestCase
{
    public function testGetEntryStartGenbank()
    {
        $flines = file("./data/human.seq");
        $this->assertTrue(DatabaseRecorderFactory::getEntryStart("GENBANK", $flines[0]));
        $this->assertFalse(DatabaseRecorderFactory::getEntryStart("GENBANK", $flines[1]));
    }

    public function testGetEntryStartSwissprot()
    {
        $flines = file("./data/basicswiss.txt");
        $this->assertTrue(DatabaseRecorderFactory::getEntryStart("SWISSPROT", $flines[0]));
    }

    public function testGetEntryStartEmbl()
    {
        $flines = file("./data/sample.embl");
        $this->assertTrue(DatabaseRecorderFactory::getEntryStart("EMBL", $flines[0]));
    }

    public function testGetEntryStartPdb()
    {
        $flines = file("./data/sample.pdb");
        $this->assertTrue(DatabaseRecorderFactory::getEntryStart("PDB", $flines[0]));
    }

    public function testGetEntryStartProsite()
    {
        $flines = file("./data/sample.prosite");
        $this->assertTrue(DatabaseRecorderFactory::getEntryStart("PROSITE", $flines[0]));
    }

    public function testGetEntryStartExpasyEnzyme()
    {
        $flines = file("./data/sample.expasy");
        $this->assertTrue(DatabaseRecorderFactory::getEntryStart("EXPASY_ENZYME", $flines[0]));
    }

    public function testGetEntryStartThrowsOnUnknownFormat()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/Unknown database format !/');
        DatabaseRecorderFactory::getEntryStart("UNKNOWN_FORMAT", "whatever");
    }

    public function testGetEntryIdGenbank()
    {
        $flines = file("./data/human.seq");
        $this->assertEquals("NM_031438", DatabaseRecorderFactory::getEntryId("GENBANK", $flines, $flines[0]));
    }

    public function testGetEntryIdSwissprot()
    {
        $flines = file("./data/basicswiss.txt");
        $this->assertEquals("1375", DatabaseRecorderFactory::getEntryId("SWISSPROT", $flines, $flines[0]));
    }

    public function testGetEntryIdEmbl()
    {
        $flines = file("./data/sample.embl");
        $this->assertEquals("AB012345", DatabaseRecorderFactory::getEntryId("EMBL", $flines, $flines[0]));
    }

    public function testGetEntryIdPdb()
    {
        $flines = file("./data/sample.pdb");
        $this->assertEquals("1TST", DatabaseRecorderFactory::getEntryId("PDB", $flines, $flines[0]));
    }

    public function testGetEntryIdProsite()
    {
        $flines = file("./data/sample.prosite");
        $this->assertEquals("PS90001", DatabaseRecorderFactory::getEntryId("PROSITE", $flines, $flines[0]));
    }

    public function testGetEntryIdExpasyEnzyme()
    {
        $flines = file("./data/sample.expasy");
        $this->assertEquals("1.1.1.2", DatabaseRecorderFactory::getEntryId("EXPASY_ENZYME", $flines, $flines[0]));
    }

    public function testGetEntryIdThrowsOnUnknownFormat()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/Unknown database format !/');
        DatabaseRecorderFactory::getEntryId("UNKNOWN_FORMAT", [], "whatever");
    }
}
