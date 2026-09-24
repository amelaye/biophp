<?php
/**
 * Immutable aggregate of a circular DNA sequence and its ordered, possibly overlapping features
 * Freely inspired by BioPHP's project biophp.org
 * Created 24 September 2026
 * Last modified 24 September 2026
 */
namespace Amelaye\BioPHP\Domain\Cloning\ValueObject;

use Amelaye\BioPHP\Domain\Cloning\Exception\InvalidFeatureCoordinatesException;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\CircularDnaSequence;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;

/**
 * A Plasmid composes a CircularDnaSequence with the PlasmidFeature annotations that make sense of
 * it, independently of any Doctrine entity, form or graphical rendering. Every method that changes
 * the aggregate returns a new Plasmid; the feature list order given at construction is preserved.
 * Class Plasmid
 * @package Amelaye\BioPHP\Domain\Cloning\ValueObject
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class Plasmid
{
    /**
     * @var     string
     */
    private $name;

    /**
     * @var     CircularDnaSequence
     */
    private $sequence;

    /**
     * @var     PlasmidFeature[]    Ordered as given at construction, or after a withFeature() append
     */
    private $features;

    /**
     * @var     string|null
     */
    private $description;

    /**
     * @var     string|null
     */
    private $externalId;

    /**
     * @var     array           Only scalars, null and arrays of the same, recursively
     */
    private $metadata;

    /**
     * Plasmid constructor.
     * @param   string                  $sName              Must not be empty
     * @param   CircularDnaSequence     $oSequence
     * @param   PlasmidFeature[]        $aFeatures          Every coordinate must fit within
     * $oSequence's length
     * @param   string|null             $sDescription
     * @param   string|null             $sExternalId
     * @param   array|null              $aMetadata          Only scalars, null and arrays thereof
     * @throws  InvalidFeatureCoordinatesException     When a feature coordinate exceeds the sequence
     * length
     */
    public function __construct(
        string $sName,
        CircularDnaSequence $oSequence,
        array $aFeatures = [],
        ?string $sDescription = null,
        ?string $sExternalId = null,
        ?array $aMetadata = null
    ) {
        if (trim($sName) === "") {
            throw new \InvalidArgumentException("Plasmid name must not be empty.");
        }

        foreach ($aFeatures as $oFeature) {
            if (!$oFeature instanceof PlasmidFeature) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Plasmid features must be PlasmidFeature instances, got %s.',
                        is_object($oFeature) ? get_class($oFeature) : gettype($oFeature)
                    )
                );
            }
        }

        $aMetadata = $aMetadata ?? [];
        $this->assertSerializableMetadata($aMetadata);

        $this->name = $sName;
        $this->sequence = $oSequence;
        $this->features = array_values($aFeatures);
        $this->description = $sDescription;
        $this->externalId = $sExternalId;
        $this->metadata = $aMetadata;

        foreach ($this->features as $oFeature) {
            $this->assertFeatureFitsSequence($oFeature);
        }
    }

    /**
     * @return  string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return  CircularDnaSequence
     */
    public function getSequence(): CircularDnaSequence
    {
        return $this->sequence;
    }

    /**
     * @return  int
     */
    public function getLength(): int
    {
        return $this->sequence->getLength();
    }

    /**
     * @return  PlasmidFeature[]
     */
    public function getFeatures(): array
    {
        return $this->features;
    }

    /**
     * @return  string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return  string|null
     */
    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    /**
     * @return  array
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Returns a new Plasmid with $oFeature appended to the feature list.
     * @param   PlasmidFeature  $oFeature
     * @return  self
     * @throws  InvalidFeatureCoordinatesException     When a coordinate exceeds the sequence length
     */
    public function withFeature(PlasmidFeature $oFeature): self
    {
        $this->assertFeatureFitsSequence($oFeature);

        $aFeatures = $this->features;
        $aFeatures[] = $oFeature;

        return new self($this->name, $this->sequence, $aFeatures, $this->description, $this->externalId, $this->metadata);
    }

    /**
     * @param   string      $sType      One of FeatureType::VALID_TYPES
     * @return  PlasmidFeature[]        In the same relative order as getFeatures()
     */
    public function getFeaturesByType(string $sType): array
    {
        return array_values(array_filter(
            $this->features,
            function (PlasmidFeature $oFeature) use ($sType) {
                return $oFeature->getType() === $sType;
            }
        ));
    }

    /**
     * True when at least two features share the exact same name. Duplicate names are tolerated (two
     * genuinely distinct sites can be named alike by the source data) but reported for the caller to
     * decide what to do about them.
     * @return  bool
     */
    public function hasDuplicateFeatureNames(): bool
    {
        $aNames = array_map(
            function (PlasmidFeature $oFeature) {
                return $oFeature->getName();
            },
            $this->features
        );

        return count($aNames) !== count(array_unique($aNames));
    }

    /**
     * Extracts the symbols covered by $oFeature, wrapping past the origin when the feature crosses
     * it. A REVERSE strand feature is returned as the reverse complement of the upper strand slice.
     * @param   PlasmidFeature  $oFeature
     * @return  DnaSequence
     * @throws  InvalidFeatureCoordinatesException     When a coordinate exceeds the sequence length
     */
    public function extractFeatureSequence(PlasmidFeature $oFeature): DnaSequence
    {
        $this->assertFeatureFitsSequence($oFeature);

        $oFragment = $this->sequence->sliceCircular(
            $oFeature->getStart() - 1,
            $oFeature->getLength($this->getLength())
        );

        if ($oFeature->getStrand() === Strand::REVERSE) {
            return $oFragment->reverseComplement();
        }

        return $oFragment;
    }

    /**
     * Returns a new Plasmid whose sequence, and every feature's coordinates, are recomputed so that
     * $iNewOrigin becomes the new position 1. Every feature keeps the same length and the same
     * symbols; only its start and end, and possibly whether it crosses the origin, change.
     * @param   int         $iNewOrigin     1-based inclusive position of the new origin
     * @return  self
     */
    public function rotateToOrigin(int $iNewOrigin): self
    {
        $iLength = $this->getLength();
        $iZeroBasedOrigin = $this->sequence->positionModulo($iNewOrigin - 1);

        $oRotatedSequence = $this->sequence->rotateTo($iZeroBasedOrigin);

        $aRotatedFeatures = array_map(
            function (PlasmidFeature $oFeature) use ($iZeroBasedOrigin, $iLength) {
                return new PlasmidFeature(
                    $oFeature->getName(),
                    $oFeature->getType(),
                    $this->shiftCoordinate($oFeature->getStart(), $iZeroBasedOrigin, $iLength),
                    $this->shiftCoordinate($oFeature->getEnd(), $iZeroBasedOrigin, $iLength),
                    $oFeature->getStrand(),
                    $oFeature->getColor(),
                    $oFeature->getNote(),
                    $oFeature->getExternalId()
                );
            },
            $this->features
        );

        return new self(
            $this->name,
            $oRotatedSequence,
            $aRotatedFeatures,
            $this->description,
            $this->externalId,
            $this->metadata
        );
    }

    /**
     * Recomputes a single 1-based inclusive coordinate after the sequence has been rotated so that
     * the old zero-based position $iZeroBasedOrigin becomes the new position 0.
     * @param   int         $iOneBasedPosition
     * @param   int         $iZeroBasedOrigin
     * @param   int         $iLength
     * @return  int         1-based inclusive
     */
    private function shiftCoordinate(int $iOneBasedPosition, int $iZeroBasedOrigin, int $iLength): int
    {
        $iZeroBased = $iOneBasedPosition - 1;
        $iShifted = (($iZeroBased - $iZeroBasedOrigin) % $iLength + $iLength) % $iLength;

        return $iShifted + 1;
    }

    /**
     * @param   PlasmidFeature  $oFeature
     * @throws  InvalidFeatureCoordinatesException     When start or end exceeds the sequence length
     */
    private function assertFeatureFitsSequence(PlasmidFeature $oFeature): void
    {
        $iLength = $this->sequence->getLength();

        if ($oFeature->getStart() > $iLength) {
            throw InvalidFeatureCoordinatesException::beyondSequenceLength(
                $oFeature->getName(),
                "start",
                $oFeature->getStart(),
                $iLength
            );
        }

        if ($oFeature->getEnd() > $iLength) {
            throw InvalidFeatureCoordinatesException::beyondSequenceLength(
                $oFeature->getName(),
                "end",
                $oFeature->getEnd(),
                $iLength
            );
        }
    }

    /**
     * Rejects metadata holding anything but scalars, null, or arrays of the same, recursively.
     * @param   array       $aMetadata
     */
    private function assertSerializableMetadata(array $aMetadata): void
    {
        foreach ($aMetadata as $mValue) {
            if ($mValue === null || is_scalar($mValue)) {
                continue;
            }

            if (is_array($mValue)) {
                $this->assertSerializableMetadata($mValue);
                continue;
            }

            throw new \InvalidArgumentException(
                sprintf('Plasmid metadata must only contain scalars or arrays, got %s.', gettype($mValue))
            );
        }
    }
}
