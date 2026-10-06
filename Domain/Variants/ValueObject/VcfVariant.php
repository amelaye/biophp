<?php
/**
 * Immutable value object describing one VCF variant record
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 6 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Variants\ValueObject;

use Amelaye\BioPHP\Domain\Variants\Exception\InvalidVcfRecordException;

/**
 * POS is 1-based, VCF's own convention (unlike BED) ; 0 and length + 1 are valid too and mark a
 * telomere (VCF 4.3, POS column), typically on a breakend record. getReference()/getAlternates() are kept as raw
 * strings, deliberately not wrapped in a DnaSequence : VCF's ALT column can legitimately hold a
 * symbolic allele ("<DEL>", "<INS>") or breakend notation ("]13:123456]T") for a structural variant,
 * neither of which is a valid DNA alphabet string, so forcing that validation here would wrongly
 * reject real VCF input.
 * Class VcfVariant
 * @package Amelaye\BioPHP\Domain\Variants\ValueObject
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class VcfVariant
{
    /**
     * @var     string
     */
    private string $chrom;

    /**
     * @var     int         1-based ; 0 or length + 1 for a telomere
     */
    private int $position;

    /**
     * @var     string|null
     */
    private ?string $id = null;

    /**
     * @var     string
     */
    private string $reference;

    /**
     * @var     string[]    Empty when ALT is "." (no alternate allele)
     */
    private array $alternates;

    /**
     * @var     float|null
     */
    private ?float $quality = null;

    /**
     * @var     string|null
     */
    private ?string $filter = null;

    /**
     * @var     array<string,string|bool>  A flag-only INFO key (no "=value") maps to true
     */
    private array $info;

    /**
     * VcfVariant constructor.
     * @param   string              $sChrom
     * @param   int                 $iPosition      1-based, 0 for a telomere
     * @param   string|null         $sId
     * @param   string              $sReference
     * @param   string[]            $aAlternates
     * @param   float|null          $fQuality
     * @param   string|null         $sFilter
     * @param   array               $aInfo
     * @throws  InvalidVcfRecordException
     */
    public function __construct(
        string $sChrom,
        int $iPosition,
        ?string $sId,
        string $sReference,
        array $aAlternates,
        ?float $fQuality,
        ?string $sFilter,
        array $aInfo = []
    ) {
        if ($sChrom === "") {
            throw InvalidVcfRecordException::emptyChrom();
        }

        if ($iPosition < 0) {
            throw InvalidVcfRecordException::negativePosition($iPosition);
        }

        if ($sReference === "") {
            throw InvalidVcfRecordException::emptyReference();
        }

        $this->chrom = $sChrom;
        $this->position = $iPosition;
        $this->id = $sId;
        $this->reference = $sReference;
        $this->alternates = array_values($aAlternates);
        $this->quality = $fQuality;
        $this->filter = $sFilter;
        $this->info = $aInfo;
    }

    /**
     * @return  string
     */
    public function getChrom(): string
    {
        return $this->chrom;
    }

    /**
     * @return  int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @return  string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return  string
     */
    public function getReference(): string
    {
        return $this->reference;
    }

    /**
     * @return  string[]
     */
    public function getAlternates(): array
    {
        return $this->alternates;
    }

    /**
     * @return  float|null
     */
    public function getQuality(): ?float
    {
        return $this->quality;
    }

    /**
     * @return  string|null
     */
    public function getFilter(): ?string
    {
        return $this->filter;
    }

    /**
     * @return  array<string,string|bool>
     */
    public function getInfo(): array
    {
        return $this->info;
    }

    /**
     * @return  bool        True only when FILTER is exactly "PASS" ; null (missing) is not a pass
     */
    public function isPass(): bool
    {
        return $this->filter === "PASS";
    }
}
