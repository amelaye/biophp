<?php
/**
 * Doctrine Entity persisting a Plasmid aggregate
 * Freely inspired by BioPHP's project biophp.org
 * Created 6 October 2026
 * Last modified 6 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Cloning\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * The storage shape of a Plasmid. The immutable Plasmid aggregate stays independent of Doctrine :
 * convert between the two with PlasmidRecordMapper rather than persisting the aggregate itself.
 * Class PlasmidRecord
 * @package Amelaye\BioPHP\Domain\Cloning\Entity
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
#[ORM\Entity]
#[ORM\Table(name: "plasmid")]
class PlasmidRecord
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
    private string $name = "";

    /**
     * @var string      The circular sequence, upper case, as held by CircularDnaSequence
     */
    #[ORM\Column(type: Types::TEXT)]
    private string $sequence = "";

    /**
     * @var string|null
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

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
     * @var Collection<int, PlasmidFeatureRecord>   Kept in the order of PlasmidFeatureRecord::$position
     */
    #[ORM\OneToMany(
        targetEntity: PlasmidFeatureRecord::class,
        mappedBy: "plasmid",
        cascade: ["persist", "remove"],
        orphanRemoval: true
    )]
    #[ORM\OrderBy(["position" => "ASC"])]
    private Collection $features;

    /**
     * PlasmidRecord constructor.
     */
    public function __construct()
    {
        $this->features = new ArrayCollection();
    }

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
    public function getSequence(): string
    {
        return $this->sequence;
    }

    /**
     * @param   string  $sSequence
     * @return  $this
     */
    public function setSequence(string $sSequence): self
    {
        $this->sequence = $sSequence;
        return $this;
    }

    /**
     * @return  string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param   string|null     $sDescription
     * @return  $this
     */
    public function setDescription(?string $sDescription): self
    {
        $this->description = $sDescription;
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
     * @return  Collection<int, PlasmidFeatureRecord>
     */
    public function getFeatures(): Collection
    {
        return $this->features;
    }

    /**
     * Attaches $oFeature and keeps both sides of the association in sync.
     * @param   PlasmidFeatureRecord    $oFeature
     * @return  $this
     */
    public function addFeature(PlasmidFeatureRecord $oFeature): self
    {
        if (!$this->features->contains($oFeature)) {
            $this->features->add($oFeature);
            $oFeature->setPlasmid($this);
        }

        return $this;
    }
}
