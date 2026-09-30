<?php
/**
 * Reads a BED annotation file into PlasmidFeature instances
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 30 September 2026
 */
namespace Amelaye\BioPHP\Domain\Cloning\Service;

use Amelaye\BioPHP\Domain\Cloning\Interfaces\BedFeatureReaderInterface;
use Amelaye\BioPHP\Domain\Cloning\Result\BedImportResult;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\FeatureType;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\PlasmidFeature;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\Strand;

/**
 * A standalone, persistence-independent text reader, in the same spirit as GffFeatureReader : one
 * interval per line, no entry delimiters, no Doctrine entity to persist into.
 *
 * BED's chromStart/chromEnd are zero-based and half-open ([chromStart, chromEnd)), unlike the
 * 1-based inclusive convention PlasmidFeature and the rest of Domain\Cloning use ; they are
 * converted once here (1-based start = chromStart + 1, 1-based end = chromEnd - the exclusive
 * 0-based upper bound and the inclusive 1-based one are numerically identical). chromStart must be
 * strictly less than chromEnd : a zero-length BED interval has no meaningful 1-based equivalent
 * (naively converting it would produce start > end, which this codebase's convention reserves for
 * an origin-crossing feature - a silent, wrong reinterpretation this reader refuses to make).
 *
 * BED carries no feature-type column the way GFF3's third column does, so every feature maps to
 * FeatureType::MISC_FEATURE ; the original chromosome/contig name and score, which have no
 * dedicated PlasmidFeature property, are kept in metadata rather than discarded. Only the first six
 * columns (chrom, chromStart, chromEnd, name, score, strand - "BED6") are read : thickStart/
 * thickEnd/itemRgb and the block/exon columns ("BED12") describe sub-feature structure this reader
 * does not attempt to reconstruct, the same simplification GenbankPlasmidMapper already documents
 * for a GenBank join() location and GffFeatureReader for a spliced Parent/ID relationship. Only the
 * first 3 columns (chrom, chromStart, chromEnd - "BED3") are required ; a missing name falls back to
 * "chrom:chromStart-chromEnd" rather than being rejected, since BED's own spec treats name as
 * optional.
 * Class BedFeatureReader
 * @package Amelaye\BioPHP\Domain\Cloning\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class BedFeatureReader implements BedFeatureReaderInterface
{
    private const MINIMUM_COLUMN_COUNT = 3;

    /**
     * @param   string[]    $aLines
     * @return  BedImportResult
     */
    public function read(array $aLines): BedImportResult
    {
        $aFeatures = [];
        $aWarnings = [];

        foreach (array_values($aLines) as $iIndex => $sRawLine) {
            $sLine = rtrim((string) $sRawLine, "\r\n");
            $iLineNumber = $iIndex + 1;

            if ($sLine === "" || $sLine[0] === "#") {
                continue;
            }

            if (str_starts_with($sLine, "track") || str_starts_with($sLine, "browser")) {
                continue;
            }

            $aColumns = explode("\t", $sLine);

            if (count($aColumns) < self::MINIMUM_COLUMN_COUNT) {
                $aWarnings[] = sprintf(
                    'Skipped line %d: expected at least %d tab-separated BED columns, got %d.',
                    $iLineNumber,
                    self::MINIMUM_COLUMN_COUNT,
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

        return new BedImportResult($aFeatures, $aWarnings);
    }

    /**
     * @param   string[]    $aColumns   At least the 3 required BED columns, in order
     * @return  PlasmidFeature
     */
    private function mapColumns(array $aColumns): PlasmidFeature
    {
        $sChrom = $aColumns[0];
        $sChromStart = $aColumns[1];
        $sChromEnd = $aColumns[2];
        $sName = $aColumns[3] ?? null;
        $sScore = $aColumns[4] ?? null;
        $sStrandColumn = $aColumns[5] ?? null;

        if (!ctype_digit($sChromStart) || !ctype_digit($sChromEnd)) {
            throw new \InvalidArgumentException(
                sprintf('non-numeric chromStart/chromEnd ("%s"..."%s").', $sChromStart, $sChromEnd)
            );
        }

        $iChromStart = (int) $sChromStart;
        $iChromEnd = (int) $sChromEnd;

        if ($iChromStart >= $iChromEnd) {
            throw new \InvalidArgumentException(
                sprintf(
                    'chromStart (%d) must be strictly less than chromEnd (%d) ; BED uses a'
                    . ' zero-based, half-open interval, and PlasmidFeature has no meaningful'
                    . ' equivalent of a zero-length one.',
                    $iChromStart,
                    $iChromEnd
                )
            );
        }

        if ($sStrandColumn === "+") {
            $sStrand = Strand::FORWARD;
        } elseif ($sStrandColumn === "-") {
            $sStrand = Strand::REVERSE;
        } else {
            $sStrand = Strand::NONE;
        }

        $sFeatureName = ($sName !== null && $sName !== "")
            ? $sName
            : sprintf("%s:%d-%d", $sChrom, $iChromStart, $iChromEnd);

        return new PlasmidFeature(
            $sFeatureName,
            FeatureType::MISC_FEATURE,
            $iChromStart + 1,
            $iChromEnd,
            $sStrand,
            null,
            null,
            null,
            [
                "bedChrom" => $sChrom,
                "bedScore" => $sScore,
            ]
        );
    }
}
