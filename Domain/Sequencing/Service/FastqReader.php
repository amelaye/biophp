<?php
/**
 * Reads a FASTQ file into FastqRecord instances
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 8 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Sequencing\Service;

use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;
use Amelaye\BioPHP\Domain\Sequencing\Interfaces\FastqReaderInterface;
use Amelaye\BioPHP\Domain\Sequencing\Result\FastqImportResult;
use Amelaye\BioPHP\Domain\Sequencing\ValueObject\FastqRecord;

/**
 * A standalone, persistence-independent text reader, in the same spirit as Domain\Cloning's
 * GffFeatureReader : FASTQ has no entry delimiters DatabaseParserFactory could recognize, and no
 * Doctrine entity to persist into. A record is an identifier line ("@..."), its sequence, a
 * separator line ("+...") and its quality string. Most files write the sequence and the quality on
 * one line each, but the original Sanger format let both wrap over several lines, and readers are
 * expected to accept it (Cock et al., Nucleic Acids Res. 2010, 38:1767) : the sequence runs until
 * the "+" line, the quality until it is as long as the sequence. A quality line may itself start
 * with "@" or "+", so a quality line is only taken while it keeps the quality no longer than the
 * sequence ; a quality left short is reported, and the next line read as the next record. A record
 * that does not fit this shape, or whose sequence/quality content is rejected by FastqRecord's own
 * validation, is skipped and reported in FastqImportResult::getWarnings() rather than thrown,
 * exactly like GffFeatureReader treats a malformed GFF3 line. Blank lines between records are
 * ignored.
 * Qualities are read as Phred+33 (see FastqRecord). A file whose every quality symbol is "@" (ASCII
 * 64) or above, some of them past "J" (Phred 41, the top of Illumina 1.8+), looks like the older
 * Phred+64 encoding (Illumina 1.3 to 1.7) : its records are still read, but a warning says their
 * scores would be 31 too high.
 * Class FastqReader
 * @package Amelaye\BioPHP\Domain\Sequencing\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class FastqReader implements FastqReaderInterface
{
    /**
     * @param   string[]    $aLines
     * @return  FastqImportResult
     */
    public function read(array $aLines): FastqImportResult
    {
        $aLines = array_map(function ($sLine) {
            return rtrim((string) $sLine, "\r\n");
        }, array_values($aLines));
        $iTotalLines = count($aLines);
        $aRecords = [];
        $aWarnings = [];
        $iRecordNumber = 0;
        $i = 0;

        while ($i < $iTotalLines) {
            if (trim($aLines[$i]) === "") {
                $i++;
                continue;
            }
            $iRecordNumber++;
            $iFirstLineNumber = $i + 1;

            if ($aLines[$i][0] !== "@") {
                $aWarnings[] = sprintf(
                    'Skipped record %d: line %d must start with "@".',
                    $iRecordNumber,
                    $iFirstLineNumber
                );
                $i = $this->nextHeader($aLines, $i + 1);
                continue;
            }
            $sIdentifier = substr($aLines[$i], 1);
            $i++;

            // The sequence, possibly wrapped, runs until the "+" separator line.
            $sSequence = "";
            while ($i < $iTotalLines && ($aLines[$i] === "" || $aLines[$i][0] !== "+")) {
                if (!preg_match('/^[A-Za-z\-.*]*$/', $aLines[$i])) {
                    $aWarnings[] = sprintf(
                        'Skipped record %d: line %d must start with "+".',
                        $iRecordNumber,
                        $i + 1
                    );
                    // A line opening on "@" is no sequence : it is the header of the next
                    // record, the current one being cut short, and is read again as such.
                    $i = $this->nextHeader($aLines, $i);
                    continue 2;
                }
                $sSequence .= $aLines[$i];
                $i++;
            }
            if ($i >= $iTotalLines) {
                $aWarnings[] = sprintf(
                    'Skipped incomplete record starting at line %d: no "+" separator line follows its sequence.',
                    $iFirstLineNumber
                );
                break;
            }
            $i++;

            // The quality, possibly wrapped, runs until it is as long as the sequence. Its first
            // line always belongs to the record, even too long : the record is then reported with
            // its real quality length, and that line is not read as the next record.
            $sQuality = "";
            while ($i < $iTotalLines && strlen($sQuality) < strlen($sSequence)
                && ($sQuality === "" || strlen($sQuality) + strlen($aLines[$i]) <= strlen($sSequence))) {
                $sQuality .= $aLines[$i];
                $i++;
            }
            if ($sQuality === "" && $sSequence !== "" && $i >= $iTotalLines) {
                $aWarnings[] = sprintf(
                    'Skipped incomplete record starting at line %d: its quality line is missing.',
                    $iFirstLineNumber
                );
                break;
            }

            try {
                $aRecords[] = new FastqRecord($sIdentifier, new DnaSequence($sSequence), $sQuality);
            } catch (\InvalidArgumentException $ex) {
                $aWarnings[] = sprintf('Skipped record %d: %s', $iRecordNumber, $ex->getMessage());
            }
        }

        $sQualities = implode("", array_map(fn(FastqRecord $oRecord) => $oRecord->getQuality(), $aRecords));
        if ($sQualities !== "" && min(array_map('ord', str_split($sQualities))) >= ord("@")
            && max(array_map('ord', str_split($sQualities))) > ord("J")) {
            $aWarnings[] = 'Every quality symbol is "@" or above, some past "J" : the file looks Phred+64'
                . ' encoded (Illumina 1.3 to 1.7). Its qualities were read as Phred+33, 31 too high.';
        }

        return new FastqImportResult($aRecords, $aWarnings);
    }

    /**
     * After a malformed record, the index of the next line that may open a record.
     * @param   string[]    $aLines
     * @param   int         $i
     * @return  int
     */
    private function nextHeader(array $aLines, int $i): int
    {
        while ($i < count($aLines) && substr($aLines[$i], 0, 1) !== "@") {
            $i++;
        }

        return $i;
    }
}
