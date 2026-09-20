<?php
/**
 * Cuts the lines of a data file into its records
 * Freely inspired by BioPHP's project biophp.org
 * Created 20 September 2026
 * Last modified 20 September 2026
 */
namespace Amelaye\BioPHP\Domain\Database\Service;

use Amelaye\BioPHP\Domain\Database\Factory\DatabaseRecorderFactory;

/**
 * Class EntryReader
 * @package Amelaye\BioPHP\Domain\Database\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
abstract class EntryReader
{
    /**
     * A record is gathered before it is identified : a parser reads the identifier out of the
     * lines it is handed, so handing it the whole file would identify every record of that file
     * as its first one. Lines sitting before any record start - the header a data file may open
     * with - belong to no record. A format closing no record leaves the previous one open.
     * @param   array       $aFileLines     The lines of the data file
     * @param   string      $sDbFormat      Database format, e.g. "GENBANK"
     * @return  \Generator                  Yields, for each record : "id", "lines" and "line_no"
     * @throws  \Exception
     */
    public static function read(array $aFileLines, string $sDbFormat): \Generator
    {
        $aEntryLines = null;
        $sStartLine  = "";
        $iStartNo    = 0;

        foreach ($aFileLines as $iLineNo => $sLine) {
            if (DatabaseRecorderFactory::getEntryStart($sDbFormat, $sLine)) {
                if ($aEntryLines !== null) {
                    yield self::entry($aEntryLines, $sStartLine, $iStartNo, $sDbFormat);
                }
                $aEntryLines = [];
                $sStartLine  = $sLine;
                $iStartNo    = $iLineNo;
            }

            if ($aEntryLines === null) {
                continue;
            }
            $aEntryLines[] = $sLine;

            if (DatabaseRecorderFactory::getEntryEnd($sDbFormat, $sLine)) {
                yield self::entry($aEntryLines, $sStartLine, $iStartNo, $sDbFormat);
                $aEntryLines = null;
            }
        }

        if ($aEntryLines !== null) {
            yield self::entry($aEntryLines, $sStartLine, $iStartNo, $sDbFormat);
        }
    }

    /**
     * @param   array       $aEntryLines
     * @param   string      $sStartLine
     * @param   int         $iStartNo
     * @param   string      $sDbFormat
     * @return  array
     * @throws  \Exception
     */
    private static function entry(array $aEntryLines, string $sStartLine, int $iStartNo, string $sDbFormat): array
    {
        return [
            "id"      => DatabaseRecorderFactory::getEntryId($sDbFormat, $aEntryLines, $sStartLine),
            "lines"   => $aEntryLines,
            "line_no" => $iStartNo,
        ];
    }
}
