<?php
/**
 * Serializes variants into VCF text
 * Freely inspired by BioPHP's project biophp.org
 * Created 9 October 2026
 * Last modified 9 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Variants\Service;

use Amelaye\BioPHP\Domain\Variants\Interfaces\VcfWriterInterface;
use Amelaye\BioPHP\Domain\Variants\ValueObject\VcfVariant;

/**
 * VCF 4.3 : "##fileformat=VCFv4.3", the extra meta lines, "#CHROM POS ID REF ALT QUAL FILTER INFO",
 * then a tab-separated line per variant. A missing value (no ID, no ALT, no QUAL, no FILTER, no INFO)
 * is ".". The ALT alleles are joined by commas, the INFO entries by semicolons, a flag written by its
 * key alone, and an INFO value has its ";", "=", ":", "%" and line or tab characters percent-encoded
 * (the reader decodes the same set) - its commas are not, since they separate the items of a list.
 * Positions are written as they are held, 1-based. VcfReader reads back what this writes.
 * Class VcfWriter
 * @package Amelaye\BioPHP\Domain\Variants\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class VcfWriter implements VcfWriterInterface
{
    private const FILE_FORMAT = "##fileformat=VCFv4.3";

    private const COLUMNS = "#CHROM\tPOS\tID\tREF\tALT\tQUAL\tFILTER\tINFO";

    private const MISSING = ".";

    /**
     * @var     string[]    What strtr() replaces in one pass, so a code it adds is never encoded again
     */
    private const INFO_ENCODING = [
        "%" => "%25", ":" => "%3A", ";" => "%3B", "=" => "%3D", "\r" => "%0D", "\n" => "%0A", "\t" => "%09",
    ];

    /**
     * @param   VcfVariant[]    $aVariants
     * @param   string[]        $aMetaLines
     * @return  string
     */
    public function write(array $aVariants, array $aMetaLines = []): string
    {
        $sOutput = self::FILE_FORMAT . "\n";
        foreach ($aMetaLines as $sMeta) {
            $sMeta = (string) $sMeta;
            $this->assertOneLine($sMeta, "A VCF meta line");
            $sOutput .= (str_starts_with($sMeta, "##") ? "" : "##") . $sMeta . "\n";
        }
        $sOutput .= self::COLUMNS . "\n";

        foreach ($aVariants as $oVariant) {
            if (!$oVariant instanceof VcfVariant) {
                throw new \InvalidArgumentException("VcfWriter writes VcfVariant instances only.");
            }
            $sOutput .= $this->writeVariant($oVariant) . "\n";
        }

        return $sOutput;
    }

    /**
     * @param   VcfVariant  $oVariant
     * @return  string      The variant's tab-separated line, without its newline
     */
    private function writeVariant(VcfVariant $oVariant): string
    {
        foreach ([$oVariant->getChrom(), (string) $oVariant->getId(), (string) $oVariant->getFilter()] as $sField) {
            if (preg_match('/[\s]/', $sField) === 1) {
                throw new \InvalidArgumentException(sprintf('A VCF field must not contain a blank, "%s" given.', $sField));
            }
        }

        $aAlternates = $oVariant->getAlternates();
        $fQuality = $oVariant->getQuality();

        return implode("\t", [
            $oVariant->getChrom(),
            (string) $oVariant->getPosition(),
            $oVariant->getId() ?? self::MISSING,
            $oVariant->getReference(),
            $aAlternates === [] ? self::MISSING : implode(",", $aAlternates),
            $fQuality === null ? self::MISSING : (string) $fQuality,
            $oVariant->getFilter() ?? self::MISSING,
            $this->writeInfo($oVariant->getInfo()),
        ]);
    }

    /**
     * @param   array<string,string|bool>   $aInfo
     * @return  string
     */
    private function writeInfo(array $aInfo): string
    {
        $aItems = [];
        foreach ($aInfo as $sKey => $mValue) {
            $sKey = (string) $sKey;
            if ($sKey === "" || preg_match('/[\s;=]/', $sKey) === 1) {
                throw new \InvalidArgumentException(sprintf('"%s" is not an INFO key.', $sKey));
            }
            if ($mValue === false) {
                continue;
            }
            $aItems[] = $mValue === true ? $sKey : $sKey . "=" . $this->encodeInfoValue((string) $mValue);
        }

        return $aItems === [] ? self::MISSING : implode(";", $aItems);
    }

    /**
     * @param   string  $sLine
     * @param   string  $sWhat
     * @throws  \InvalidArgumentException
     */
    private function assertOneLine(string $sLine, string $sWhat): void
    {
        if (strpos($sLine, "\n") !== false || strpos($sLine, "\r") !== false) {
            throw new \InvalidArgumentException($sWhat . " must not contain a newline.");
        }
    }

    /**
     * A "%2C" is left as it is : VcfReader keeps an encoded comma encoded, so a value read from a
     * file holds "%2C" for it, and encoding its "%" again would write "%252C", the text "%2C"
     * instead of a comma.
     *
     * @param   string  $sValue
     * @return  string
     */
    private function encodeInfoValue(string $sValue): string
    {
        $aParts = preg_split('/(%2C)/i', $sValue, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($aParts as $iKey => $sPart) {
            if ($iKey % 2 === 0) {
                $aParts[$iKey] = strtr($sPart, self::INFO_ENCODING);
            }
        }

        return implode("", $aParts);
    }
}
