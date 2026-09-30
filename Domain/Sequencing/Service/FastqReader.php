<?php
/**
 * Reads a FASTQ file into FastqRecord instances
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 30 September 2026
 */
namespace Amelaye\BioPHP\Domain\Sequencing\Service;

use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;
use Amelaye\BioPHP\Domain\Sequencing\Interfaces\FastqReaderInterface;
use Amelaye\BioPHP\Domain\Sequencing\Result\FastqImportResult;
use Amelaye\BioPHP\Domain\Sequencing\ValueObject\FastqRecord;

/**
 * A standalone, persistence-independent text reader, in the same spirit as Domain\Cloning's
 * GffFeatureReader : FASTQ has no entry delimiters DatabaseParserFactory could recognize, and no
 * Doctrine entity to persist into. Each record is exactly 4 lines - identifier ("@..."), sequence,
 * separator ("+..."), quality - the format FASTQ has always used ; a sequence or quality wrapped
 * across several lines, the way multi-line FASTA is sometimes written, is not valid FASTQ and is not
 * supported here. A record that does not fit this shape, or whose sequence/quality content is
 * rejected by FastqRecord's own validation, is skipped and reported in
 * FastqImportResult::getWarnings() rather than thrown, exactly like GffFeatureReader treats a
 * malformed GFF3 line.
 * Class FastqReader
 * @package Amelaye\BioPHP\Domain\Sequencing\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class FastqReader implements FastqReaderInterface
{
    private const LINES_PER_RECORD = 4;

    /**
     * @param   string[]    $aLines
     * @return  FastqImportResult
     */
    public function read(array $aLines): FastqImportResult
    {
        $aLines = array_values($aLines);
        $iTotalLines = count($aLines);
        $aRecords = [];
        $aWarnings = [];

        for ($i = 0; $i < $iTotalLines; $i += self::LINES_PER_RECORD) {
            $iRecordNumber = (int) ($i / self::LINES_PER_RECORD) + 1;
            $iFirstLineNumber = $i + 1;

            if ($i + self::LINES_PER_RECORD > $iTotalLines) {
                $aWarnings[] = sprintf(
                    'Skipped incomplete record starting at line %d: expected %d lines, only %d remain.',
                    $iFirstLineNumber,
                    self::LINES_PER_RECORD,
                    $iTotalLines - $i
                );
                break;
            }

            $sHeaderLine = rtrim((string) $aLines[$i], "\r\n");
            $sSequenceLine = rtrim((string) $aLines[$i + 1], "\r\n");
            $sSeparatorLine = rtrim((string) $aLines[$i + 2], "\r\n");
            $sQualityLine = rtrim((string) $aLines[$i + 3], "\r\n");

            if ($sHeaderLine === "" || $sHeaderLine[0] !== "@") {
                $aWarnings[] = sprintf(
                    'Skipped record %d: line %d must start with "@".',
                    $iRecordNumber,
                    $iFirstLineNumber
                );
                continue;
            }

            if ($sSeparatorLine === "" || $sSeparatorLine[0] !== "+") {
                $aWarnings[] = sprintf(
                    'Skipped record %d: line %d must start with "+".',
                    $iRecordNumber,
                    $iFirstLineNumber + 2
                );
                continue;
            }

            try {
                $aRecords[] = new FastqRecord(
                    substr($sHeaderLine, 1),
                    new DnaSequence($sSequenceLine),
                    $sQualityLine
                );
            } catch (\InvalidArgumentException $ex) {
                $aWarnings[] = sprintf('Skipped record %d: %s', $iRecordNumber, $ex->getMessage());
            }
        }

        return new FastqImportResult($aRecords, $aWarnings);
    }
}
