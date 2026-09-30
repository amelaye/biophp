<?php
/**
 * Reads a VCF file into VcfVariant instances
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 30 September 2026
 */
namespace Amelaye\BioPHP\Domain\Variants\Service;

use Amelaye\BioPHP\Domain\Variants\Interfaces\VcfReaderInterface;
use Amelaye\BioPHP\Domain\Variants\Result\VcfImportResult;
use Amelaye\BioPHP\Domain\Variants\ValueObject\VcfVariant;

/**
 * A standalone, persistence-independent text reader, the same spirit as GffFeatureReader and
 * BedFeatureReader : "##" meta-information lines and the single "#CHROM..." column-header line are
 * skipped, data lines are tab-separated, a malformed one is reported as a warning and skipped rather
 * than crashing the whole read. Only the 8 mandatory fixed columns (CHROM, POS, ID, REF, ALT, QUAL,
 * FILTER, INFO) are read ; FORMAT and per-sample genotype columns, when present, are ignored -
 * modeling genotypes/samples is a substantially bigger undertaking than a tabular reader, out of
 * scope here the same way BED12's block columns are out of scope for BedFeatureReader.
 * Class VcfReader
 * @package Amelaye\BioPHP\Domain\Variants\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class VcfReader implements VcfReaderInterface
{
    private const MINIMUM_COLUMN_COUNT = 8;

    private const MISSING_VALUE = ".";

    /**
     * @param   string[]    $aLines
     * @return  VcfImportResult
     */
    public function read(array $aLines): VcfImportResult
    {
        $aVariants = [];
        $aWarnings = [];

        foreach (array_values($aLines) as $iIndex => $sRawLine) {
            $sLine = rtrim((string) $sRawLine, "\r\n");
            $iLineNumber = $iIndex + 1;

            if ($sLine === "" || $sLine[0] === "#") {
                continue;
            }

            $aColumns = explode("\t", $sLine);

            if (count($aColumns) < self::MINIMUM_COLUMN_COUNT) {
                $aWarnings[] = sprintf(
                    'Skipped line %d: expected at least %d tab-separated VCF columns, got %d.',
                    $iLineNumber,
                    self::MINIMUM_COLUMN_COUNT,
                    count($aColumns)
                );
                continue;
            }

            try {
                $aVariants[] = $this->mapColumns($aColumns);
            } catch (\InvalidArgumentException $ex) {
                $aWarnings[] = sprintf('Skipped line %d: %s', $iLineNumber, $ex->getMessage());
            }
        }

        return new VcfImportResult($aVariants, $aWarnings);
    }

    /**
     * @param   string[]    $aColumns   At least the 8 mandatory VCF columns, in order
     * @return  VcfVariant
     */
    private function mapColumns(array $aColumns): VcfVariant
    {
        [$sChrom, $sPos, $sId, $sRef, $sAlt, $sQual, $sFilter, $sInfo] = $aColumns;

        if (!ctype_digit($sPos)) {
            throw new \InvalidArgumentException(sprintf('non-numeric POS ("%s").', $sPos));
        }

        if ($sQual !== self::MISSING_VALUE && !is_numeric($sQual)) {
            throw new \InvalidArgumentException(sprintf('non-numeric QUAL ("%s").', $sQual));
        }

        $sId = $sId === self::MISSING_VALUE ? null : $sId;
        $aAlternates = $sAlt === self::MISSING_VALUE ? [] : explode(",", $sAlt);
        $fQual = $sQual === self::MISSING_VALUE ? null : (float) $sQual;
        $sFilter = $sFilter === self::MISSING_VALUE ? null : $sFilter;

        return new VcfVariant(
            $sChrom,
            (int) $sPos,
            $sId,
            $sRef,
            $aAlternates,
            $fQual,
            $sFilter,
            $this->parseInfo($sInfo)
        );
    }

    /**
     * Parses the semicolon-separated INFO column into key/value pairs ; a flag-only key (no
     * "=value") maps to true.
     * @param   string      $sRaw
     * @return  array<string,string|bool>
     */
    private function parseInfo(string $sRaw): array
    {
        if ($sRaw === self::MISSING_VALUE || $sRaw === "") {
            return [];
        }

        $aInfo = [];

        foreach (explode(";", $sRaw) as $sPair) {
            if ($sPair === "") {
                continue;
            }

            $aParts = explode("=", $sPair, 2);
            $aInfo[$aParts[0]] = $aParts[1] ?? true;
        }

        return $aInfo;
    }
}
