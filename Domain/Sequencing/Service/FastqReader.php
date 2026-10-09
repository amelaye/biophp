<?php
/**
 * Reads a FASTQ file into FastqRecord instances
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 9 October 2026
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
            // The "+" line may repeat the title, which then has to be the "@" one (Cock et al. 2010)
            $sRepeatedTitle = substr($aLines[$i], 1);
            if ($sRepeatedTitle !== "" && $sRepeatedTitle !== $sIdentifier) {
                $aWarnings[] = sprintf(
                    'Record %d: the "+" line repeats "%s", which is not the title "%s" of the "@" line.',
                    $iRecordNumber,
                    $sRepeatedTitle,
                    $sIdentifier
                );
            }
            $i++;

            // The quality, possibly wrapped, runs until it is as long as the sequence. Its first
            // line always belongs to the record, even too long or blank : the record is then reported
            // with its real quality length, and that line is not read as the next record.
            $sQuality = "";
            $bFirstLine = true;
            while ($i < $iTotalLines && strlen($sQuality) < strlen($sSequence)
                && ($bFirstLine || (strlen($sQuality) + strlen($aLines[$i]) <= strlen($sSequence)
                    && !$this->opensARecord($aLines, $i)))) {
                $sQuality .= $aLines[$i];
                $i++;
                if ($bFirstLine && $sQuality === "") {
                    // A blank quality line under a sequence : the record is cut short, and the line
                    // after it is the next header, not the rest of this quality
                    break;
                }
                $bFirstLine = false;
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
        // Phred+64 stops at "h" or "i" (Q40, Q41) : a file reaching further, up to "~", is Phred+33 of a
        // long-read instrument (PacBio HiFi, Q93)
        if ($sQualities !== "" && min(array_map('ord', str_split($sQualities))) >= ord("@")
            && max(array_map('ord', str_split($sQualities))) > ord("J")
            && max(array_map('ord', str_split($sQualities))) <= ord("i")) {
            $aWarnings[] = 'Every quality symbol is "@" or above, some past "J" : the file looks Phred+64'
                . ' encoded (Illumina 1.3 to 1.7). Its qualities were read as Phred+33, 31 too high.';
        }

        return new FastqImportResult($aRecords, $aWarnings);
    }

    /**
     * Tells whether a line opening on "@" is the header of the next record rather than a quality
     * line that happens to start with "@" : a header is followed by sequence lines, then by the
     * "+" line. A quality too short for its sequence would otherwise swallow that header.
     * @param   array       $aLines
     * @param   int         $i          The index of the line to examine
     * @return  bool
     */
    private function opensARecord(array $aLines, int $i): bool
    {
        if (substr($aLines[$i], 0, 1) !== "@") {
            return false;
        }
        for ($j = $i + 1; $j < count($aLines); $j++) {
            if (substr($aLines[$j], 0, 1) === "+") {
                return $j > $i + 1;
            }
            if (!preg_match('/^[A-Za-z\-.*]*$/', $aLines[$j])) {
                return false;
            }
        }

        return false;
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
