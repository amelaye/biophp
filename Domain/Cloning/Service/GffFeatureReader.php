<?php
/**
 * Reads a GFF3 annotation file into PlasmidFeature instances
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 30 September 2026
 */
namespace Amelaye\BioPHP\Domain\Cloning\Service;

use Amelaye\BioPHP\Domain\Cloning\Interfaces\GffFeatureReaderInterface;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\FeatureType;
use Amelaye\BioPHP\Domain\Cloning\Result\GffImportResult;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\PlasmidFeature;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\Strand;

/**
 * Deliberately independent of DatabaseParserFactory/ParseDbAbstractManager : that machinery is built
 * around GenBank-style databases, each entry delimited by its own start/end sentinel line and
 * ultimately persisted through Doctrine (DatabaseManager::recording()). GFF3 has neither shape - one
 * feature per line, no entry delimiters, no persistence - and forcing it through that abstraction
 * would fight it rather than reuse it. This class is a standalone, persistence-independent text
 * reader, in keeping with the rest of Domain/Cloning.
 *
 * Each of the nine standard tab-separated columns (seqid, source, type, start, end, score, strand,
 * phase, attributes) is read directly ; comment and pragma lines ("#..."), including the
 * "##gff-version" header, are skipped, and reading stops at a "##FASTA" pragma without attempting to
 * parse the embedded sequence it introduces - a GFF3 file's sequence, when present at all, is not
 * this reader's concern. GFF3 does not support an origin-crossing feature directly (start must not
 * exceed end), so a line violating that, like any other malformed line, is skipped and reported in
 * GffImportResult::getWarnings() rather than thrown. A GFF3 feature split across several lines that
 * share a Parent/ID relationship (a spliced gene's exons) is read as that many independent
 * PlasmidFeature instances, not reassembled into one ; this mirrors the equivalent, documented
 * limitation GenbankPlasmidMapper already accepts for a GenBank join() location.
 * Class GffFeatureReader
 * @package Amelaye\BioPHP\Domain\Cloning\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class GffFeatureReader implements GffFeatureReaderInterface
{
    private const FASTA_PRAGMA = "##FASTA";

    private const EXPECTED_COLUMN_COUNT = 9;

    /**
     * Sequence Ontology terms that map unambiguously onto a FeatureType, matched case-insensitively
     * since real-world GFF3 producers disagree on casing (e.g. "CDS" vs "cds"). Anything else,
     * including "gene" (a broader region than any single FeatureType here, exactly as GenbankPlasmidMapper
     * already treats it), falls back to FeatureType::MISC_FEATURE, with the original term preserved in
     * metadata rather than guessed at.
     * @var     array<string,string>
     */
    private const FEATURE_TYPE_BY_SO_TERM = [
        "cds" => FeatureType::CDS,
        "promoter" => FeatureType::PROMOTER,
        "terminator" => FeatureType::TERMINATOR,
        "origin_of_replication" => FeatureType::ORIGIN_OF_REPLICATION,
    ];

    /**
     * @param   string[]    $aLines
     * @return  GffImportResult
     */
    public function read(array $aLines): GffImportResult
    {
        $aFeatures = [];
        $aWarnings = [];

        foreach (array_values($aLines) as $iIndex => $sRawLine) {
            $sLine = rtrim((string) $sRawLine, "\r\n");
            $iLineNumber = $iIndex + 1;

            if ($sLine === "") {
                continue;
            }

            if ($sLine[0] === "#") {
                if ($sLine === self::FASTA_PRAGMA) {
                    break;
                }
                continue;
            }

            $aColumns = explode("\t", $sLine);

            if (count($aColumns) < self::EXPECTED_COLUMN_COUNT) {
                $aWarnings[] = sprintf(
                    'Skipped line %d: expected %d tab-separated GFF3 columns, got %d.',
                    $iLineNumber,
                    self::EXPECTED_COLUMN_COUNT,
                    count($aColumns)
                );
                continue;
            }

            try {
                $aFeatures[] = $this->mapColumns($aColumns);
            } catch (\InvalidArgumentException $ex) {
                $aWarnings[] = sprintf('Skipped line %d: %s', $iLineNumber, $ex->getMessage());
            }
        }

        return new GffImportResult($aFeatures, $aWarnings);
    }

    /**
     * @param   string[]    $aColumns   Exactly the 9 standard GFF3 columns, in order
     * @return  PlasmidFeature
     */
    private function mapColumns(array $aColumns): PlasmidFeature
    {
        [$sSeqId, $sSource, $sType, $sStart, $sEnd, $sScore, $sStrandColumn, , $sAttributesRaw] = $aColumns;

        if (!ctype_digit($sStart) || !ctype_digit($sEnd)) {
            throw new \InvalidArgumentException(
                sprintf('non-numeric start/end ("%s"..."%s").', $sStart, $sEnd)
            );
        }

        $iStart = (int) $sStart;
        $iEnd = (int) $sEnd;

        if ($iStart > $iEnd) {
            throw new \InvalidArgumentException(
                sprintf(
                    'start (%d) is after end (%d); GFF3 does not support an origin-crossing feature directly.',
                    $iStart,
                    $iEnd
                )
            );
        }

        $aAttributes = $this->parseAttributes($sAttributesRaw);

        $sName = $aAttributes["Name"][0] ?? $aAttributes["gene"][0] ?? $aAttributes["ID"][0] ?? $sType;
        $sFeatureType = self::FEATURE_TYPE_BY_SO_TERM[strtolower($sType)] ?? FeatureType::MISC_FEATURE;

        if ($sStrandColumn === "+") {
            $sStrand = Strand::FORWARD;
        } elseif ($sStrandColumn === "-") {
            $sStrand = Strand::REVERSE;
        } else {
            $sStrand = Strand::NONE;
        }

        return new PlasmidFeature(
            $sName,
            $sFeatureType,
            $iStart,
            $iEnd,
            $sStrand,
            null,
            $aAttributes["Note"][0] ?? null,
            null,
            [
                "gffSeqId" => $sSeqId,
                "gffSource" => $sSource,
                "gffType" => $sType,
                "gffScore" => $sScore,
            ]
        );
    }

    /**
     * Parses the ninth GFF3 column into its semicolon-separated, percent-decoded key=value pairs. A
     * key repeated with a comma-separated value list (e.g. "Parent=mRNA1,mRNA2") keeps every value.
     * @param   string      $sRaw
     * @return  array<string,string[]>
     */
    private function parseAttributes(string $sRaw): array
    {
        $aAttributes = [];

        foreach (explode(";", trim($sRaw)) as $sPair) {
            $sPair = trim($sPair);
            if ($sPair === "") {
                continue;
            }

            $aParts = explode("=", $sPair, 2);
            if (count($aParts) !== 2) {
                continue;
            }

            [$sKey, $sValue] = $aParts;
            foreach (explode(",", $sValue) as $sValuePart) {
                $aAttributes[$sKey][] = rawurldecode($sValuePart);
            }
        }

        return $aAttributes;
    }
}
