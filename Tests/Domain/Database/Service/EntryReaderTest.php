<?php
namespace Tests\Domain\Database\Service;

use Amelaye\BioPHP\Domain\Database\Service\EntryReader;
use PHPUnit\Framework\TestCase;

class EntryReaderTest extends TestCase
{
    private function dataFile(string $sName): array
    {
        return file(dirname(__DIR__, 4) . "/data/" . $sName);
    }

    public function testEachRecordOfAFileIsYieldedWithItsIdAndStartLine()
    {
        $aEntries = iterator_to_array(EntryReader::read($this->dataFile("enzyme.dat"), "EXPASY_ENZYME"), false);

        $this->assertSame(["1.1.1.1", "1.1.1.2", "1.1.1.5"], array_slice(array_column($aEntries, "id"), 0, 3));
        $this->assertSame(24, $aEntries[0]["line_no"]);
        $this->assertCount(5, $aEntries);
    }

    public function testTheHeaderBeforeTheFirstRecordBelongsToNoRecord()
    {
        $aEntries = iterator_to_array(EntryReader::read($this->dataFile("enzyme.dat"), "EXPASY_ENZYME"), false);

        $this->assertStringStartsWith("ID   1.1.1.1", $aEntries[0]["lines"][0]);
    }

    public function testASingleRecordFileYieldsOneRecord()
    {
        $aEntries = iterator_to_array(EntryReader::read($this->dataFile("sample.embl"), "EMBL"), false);

        $this->assertCount(1, $aEntries);
        $this->assertSame(0, $aEntries[0]["line_no"]);
    }

    public function testAnUnknownFormatIsRefused()
    {
        $this->expectException(\Exception::class);
        iterator_to_array(EntryReader::read(["ID   X\n"], "NOT_A_FORMAT"));
    }
}
