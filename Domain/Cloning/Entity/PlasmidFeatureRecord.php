<?php
/**
 * Doctrine Entity persisting a PlasmidFeature annotation
 * Freely inspired by BioPHP's project biophp.org
 * Created 6 October 2026
 * Last modified 6 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Cloning\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * The storage shape of a PlasmidFeature. Coordinates keep the value object's convention : 1-based
 * inclusive, with start greater than end meaning the feature crosses the origin. $position records
 * the feature's rank within its plasmid, because Plasmid preserves the order it was built with.
 * Class PlasmidFeatureRecord
 * @package Amelaye\BioPHP\Domain\Cloning\Entity
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
#[ORM\Entity]
#[ORM\Table(name: "plasmid_feature")]
class PlasmidFeatureRecord
{
    /**
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    /**
     * @var PlasmidRecord|null
     */
    #[ORM\ManyToOne(targetEntity: PlasmidRecord::class, inversedBy: "features")]
    #[ORM\JoinColumn(name: "plasmid_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private ?PlasmidRecord $plasmid = null;

    /**
     * @var int         Zero-based rank of the feature within the plasmid's feature list
     */
    #[ORM\Column(type: Types::INTEGER)]
    private int $position = 0;

    /**
     * @var string
     */
    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $name = "";

    /**
     * @var string      One of FeatureType::VALID_TYPES
     */
    #[ORM\Column(type: Types::STRING, length: 32)]
    private string $type = "";

    /**
     * @var int         1-based inclusive
     */
    #[ORM\Column(type: Types::INTEGER)]
    private int $startPosition = 1;

    /**
     * @var int         1-based inclusive
     */
    #[ORM\Column(type: Types::INTEGER)]
    private int $endPosition = 1;

    /**
     * @var string      One of Strand::VALID_STRANDS
     */
    #[ORM\Column(type: Types::STRING, length: 8)]
    private string $strand = "";

    /**
     * @var string|null     Strict "#RRGGBB" hexadecimal notation
     */
    #[ORM\Column(type: Types::STRING, length: 7, nullable: true)]
    private ?string $color = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $note = null;

    /**
     * @var string|null
     */
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $externalId = null;

    /**
     * @var array       Only scalars, null and arrays of the same, recursively
     */
    #[ORM\Column(type: Types::JSON)]
    private array $metadata = [];

    /**
     * @var int|null    GFF3 phase (0, 1 or 2), CDS only ; GenBank /codon_start minus 1
     */
    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $phase = null;

    /**
     * @return  int|null    Null until the record has been flushed
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return  PlasmidRecord|null
     */
    public function getPlasmid(): ?PlasmidRecord
    {
        return $this->plasmid;
    }

    /**
     * @param   PlasmidRecord|null  $oPlasmid
     * @return  $this
     */
    public function setPlasmid(?PlasmidRecord $oPlasmid): self
    {
        $this->plasmid = $oPlasmid;
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
     * @return  string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param   string  $sName
     * @return  $this
     */
    public function setName(string $sName): self
    {
        $this->name = $sName;
        return $this;
    }

    /**
     * @return  string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param   string  $sType
     * @return  $this
     */
    public function setType(string $sType): self
    {
        $this->type = $sType;
        return $this;
    }

    /**
     * @return  int
     */
    public function getStartPosition(): int
    {
        return $this->startPosition;
    }

    /**
     * @param   int     $iStartPosition
     * @return  $this
     */
    public function setStartPosition(int $iStartPosition): self
    {
        $this->startPosition = $iStartPosition;
        return $this;
    }

    /**
     * @return  int
     */
    public function getEndPosition(): int
    {
        return $this->endPosition;
    }

    /**
     * @param   int     $iEndPosition
     * @return  $this
     */
    public function setEndPosition(int $iEndPosition): self
    {
        $this->endPosition = $iEndPosition;
        return $this;
    }

    /**
     * @return  string
     */
    public function getStrand(): string
    {
        return $this->strand;
    }

    /**
     * @param   string  $sStrand
     * @return  $this
     */
    public function setStrand(string $sStrand): self
    {
        $this->strand = $sStrand;
        return $this;
    }

    /**
     * @return  string|null
     */
    public function getColor(): ?string
    {
        return $this->color;
    }

    /**
     * @param   string|null     $sColor
     * @return  $this
     */
    public function setColor(?string $sColor): self
    {
        $this->color = $sColor;
        return $this;
    }

    /**
     * @return  string|null
     */
    public function getNote(): ?string
    {
        return $this->note;
    }

    /**
     * @param   string|null     $sNote
     * @return  $this
     */
    public function setNote(?string $sNote): self
    {
        $this->note = $sNote;
        return $this;
    }

    /**
     * @return  string|null
     */
    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    /**
     * @param   string|null     $sExternalId
     * @return  $this
     */
    public function setExternalId(?string $sExternalId): self
    {
        $this->externalId = $sExternalId;
        return $this;
    }

    /**
     * @return  array
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * @param   array   $aMetadata
     * @return  $this
     */
    public function setMetadata(array $aMetadata): self
    {
        $this->metadata = $aMetadata;
        return $this;
    }

    /**
     * @return  int|null
     */
    public function getPhase(): ?int
    {
        return $this->phase;
    }

    /**
     * @param   int|null    $iPhase
     * @return  $this
     */
    public function setPhase(?int $iPhase): self
    {
        $this->phase = $iPhase;
        return $this;
    }
}
