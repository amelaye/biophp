<?php
/**
 * Doctrine Entity persisting a VcfVariant
 * Freely inspired by BioPHP's project biophp.org
 * Created 6 October 2026
 * Last modified 6 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Variants\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * The storage shape of a VcfVariant. The reference and alternates stay raw strings, as in the value
 * object : an ALT may be a symbolic allele or a breakend, so neither is stored as a DNA sequence.
 * Class VcfVariantRecord
 * @package Amelaye\BioPHP\Domain\Variants\Entity
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
#[ORM\Entity]
#[ORM\Table(name: "vcf_variant")]
#[ORM\Index(name: "idx_vcf_variant_locus", columns: ["chrom", "position"])]
class VcfVariantRecord
{
    /**
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    /**
     * @var string
     */
    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $chrom = "";

    /**
     * @var int         1-based, VCF's own convention
     */
    #[ORM\Column(type: Types::INTEGER)]
    private int $position = 1;

    /**
     * @var string|null     The VCF ID column (e.g. an rsID) ; named apart from the primary key
     */
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $variantId = null;

    /**
     * @var string
     */
    #[ORM\Column(type: Types::TEXT)]
    private string $reference = "";

    /**
     * @var string[]    Empty when ALT is "."
     */
    #[ORM\Column(type: Types::JSON)]
    private array $alternates = [];

    /**
     * @var float|null
     */
    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $quality = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $filter = null;

    /**
     * @var array<string,string|bool>   A flag-only INFO key (no "=value") maps to true
     */
    #[ORM\Column(type: Types::JSON)]
    private array $info = [];

    /**
     * @return  int|null    Null until the record has been flushed
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return  string
     */
    public function getChrom(): string
    {
        return $this->chrom;
    }

    /**
     * @param   string  $sChrom
     * @return  $this
     */
    public function setChrom(string $sChrom): self
    {
        $this->chrom = $sChrom;
        return $this;
    }

    /**
     * @return  int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @param   int     $iPosition
     * @return  $this
     */
    public function setPosition(int $iPosition): self
    {
        $this->position = $iPosition;
        return $this;
    }

    /**
     * @return  string|null
     */
    public function getVariantId(): ?string
    {
        return $this->variantId;
    }

    /**
     * @param   string|null     $sVariantId
     * @return  $this
     */
    public function setVariantId(?string $sVariantId): self
    {
        $this->variantId = $sVariantId;
        return $this;
    }

    /**
     * @return  string
     */
    public function getReference(): string
    {
        return $this->reference;
    }

    /**
     * @param   string  $sReference
     * @return  $this
     */
    public function setReference(string $sReference): self
    {
        $this->reference = $sReference;
        return $this;
    }

    /**
     * @return  string[]
     */
    public function getAlternates(): array
    {
        return $this->alternates;
    }

    /**
     * @param   string[]    $aAlternates
     * @return  $this
     */
    public function setAlternates(array $aAlternates): self
    {
        $this->alternates = $aAlternates;
        return $this;
    }

    /**
     * @return  float|null
     */
    public function getQuality(): ?float
    {
        return $this->quality;
    }

    /**
     * @param   float|null  $fQuality
     * @return  $this
     */
    public function setQuality(?float $fQuality): self
    {
        $this->quality = $fQuality;
        return $this;
    }

    /**
     * @return  string|null
     */
    public function getFilter(): ?string
    {
        return $this->filter;
    }

    /**
     * @param   string|null     $sFilter
     * @return  $this
     */
    public function setFilter(?string $sFilter): self
    {
        $this->filter = $sFilter;
        return $this;
    }

    /**
     * @return  array<string,string|bool>
     */
    public function getInfo(): array
    {
        return $this->info;
    }

    /**
     * @param   array<string,string|bool>   $aInfo
     * @return  $this
     */
    public function setInfo(array $aInfo): self
    {
        $this->info = $aInfo;
        return $this;
    }
}
