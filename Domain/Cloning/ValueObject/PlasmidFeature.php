<?php
/**
 * Immutable value object describing one annotated region of a Plasmid
 * Freely inspired by BioPHP's project biophp.org
 * Created 24 September 2026
 * Last modified 6 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Cloning\ValueObject;

use Amelaye\BioPHP\Domain\Cloning\Exception\InvalidFeatureCoordinatesException;

/**
 * Coordinates are 1-based inclusive, the convention used by GenBank and EMBL feature tables. Start
 * and end are only validated to be at least 1 here : since a lone feature does not know the length
 * of the plasmid it will belong to, the upper bound is checked by Plasmid when the feature is
 * attached. A start strictly greater than end is valid and means the feature crosses the origin.
 * The optional phase is GFF3's column 8 (0, 1 or 2 : how many bases to skip from the feature's 5'
 * end, in its own orientation, to reach the first complete codon), equivalent to GenBank's
 * /codon_start minus 1. It is only meaningful on a CDS, the only type that accepts one.
 * Class PlasmidFeature
 * @package Amelaye\BioPHP\Domain\Cloning\ValueObject
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class PlasmidFeature
{
    /**
     * @var     string
     */
    private string $name;

    /**
     * @var     string          One of FeatureType::VALID_TYPES
     */
    private string $type;

    /**
     * @var     int             1-based inclusive
     */
    private int $start;

    /**
     * @var     int             1-based inclusive
     */
    private int $end;

    /**
     * @var     string          One of Strand::VALID_STRANDS
     */
    private string $strand;

    /**
     * @var     string|null     Strict "#RRGGBB" hexadecimal notation
     */
    private ?string $color = null;

    /**
     * @var     string|null
     */
    private ?string $note = null;

    /**
     * @var     string|null
     */
    private ?string $externalId = null;

    /**
     * @var     array           Only scalars, null and arrays of the same, recursively
     */
    private array $metadata;

    /**
     * @var     int|null        0, 1 or 2 ; CDS only
     */
    private ?int $phase = null;

    /**
     * PlasmidFeature constructor.
     * @param   string      $sName          Must not be empty
     * @param   string      $sType          One of FeatureType::VALID_TYPES
     * @param   int         $iStart         1-based inclusive, at least 1
     * @param   int         $iEnd           1-based inclusive, at least 1
     * @param   string      $sStrand        One of Strand::VALID_STRANDS, defaults to Strand::NONE
     * @param   string|null $sColor         Strict "#RRGGBB" notation, or null
     * @param   string|null $sNote
     * @param   string|null $sExternalId
     * @param   array|null  $aMetadata      Only scalars, null and arrays thereof ; a source format's
     * original type or attributes that don't map onto a dedicated property (e.g. a GenBank feature
     * key) belong here rather than being folded into $sNote
     * @param   int|null    $iPhase         0, 1 or 2, only on a CDS ; null when unknown
     */
    public function __construct(
        string $sName,
        string $sType,
        int $iStart,
        int $iEnd,
        string $sStrand = Strand::NONE,
        ?string $sColor = null,
        ?string $sNote = null,
        ?string $sExternalId = null,
        ?array $aMetadata = null,
        ?int $iPhase = null
    ) {
        if (trim($sName) === "") {
            throw new \InvalidArgumentException("Plasmid feature name must not be empty.");
        }

        if (!in_array($sType, FeatureType::VALID_TYPES, true)) {
            throw new \InvalidArgumentException(
                sprintf('Feature "%s" has invalid type "%s".', $sName, $sType)
            );
        }

        if (!in_array($sStrand, Strand::VALID_STRANDS, true)) {
            throw new \InvalidArgumentException(
                sprintf('Feature "%s" has invalid strand "%s".', $sName, $sStrand)
            );
        }

        if ($iStart < 1) {
            throw InvalidFeatureCoordinatesException::belowOrigin("start", $iStart);
        }

        if ($iEnd < 1) {
            throw InvalidFeatureCoordinatesException::belowOrigin("end", $iEnd);
        }

        if ($sColor !== null && !preg_match('/^#[0-9A-Fa-f]{6}$/', $sColor)) {
            throw new \InvalidArgumentException(
                sprintf('Feature "%s" has invalid color "%s", expected strict "#RRGGBB" notation.', $sName, $sColor)
            );
        }

        if ($iPhase !== null) {
            if ($sType !== FeatureType::CDS) {
                throw new \InvalidArgumentException(
                    sprintf('Feature "%s" of type "%s" cannot carry a phase ; only a CDS can.', $sName, $sType)
                );
            }
            if ($iPhase < 0 || $iPhase > 2) {
                throw new \InvalidArgumentException(
                    sprintf('Feature "%s" has invalid phase %d, expected 0, 1 or 2.', $sName, $iPhase)
                );
            }
        }

        $aMetadata = $aMetadata ?? [];
        $this->assertSerializableMetadata($sName, $aMetadata);

        $this->name = $sName;
        $this->type = $sType;
        $this->start = $iStart;
        $this->end = $iEnd;
        $this->strand = $sStrand;
        $this->color = $sColor;
        $this->note = $sNote;
        $this->externalId = $sExternalId;
        $this->metadata = $aMetadata;
        $this->phase = $iPhase;
    }

    /**
     * @return  string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return  string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return  int     1-based inclusive
     */
    public function getStart(): int
    {
        return $this->start;
    }

    /**
     * @return  int     1-based inclusive
     */
    public function getEnd(): int
    {
        return $this->end;
    }

    /**
     * @return  string
     */
    public function getStrand(): string
    {
        return $this->strand;
    }

    /**
     * @return  string|null
     */
    public function getColor(): ?string
    {
        return $this->color;
    }

    /**
     * @return  string|null
     */
    public function getNote(): ?string
    {
        return $this->note;
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
     * @return  int|null    0, 1 or 2 ; null when unknown or not a CDS
     */
    public function getPhase(): ?int
    {
        return $this->phase;
    }

    /**
     * @return  bool        True when start is strictly after end, meaning the feature crosses the
     * origin of the circular molecule it belongs to.
     */
    public function crossesOrigin(): bool
    {
        return $this->start > $this->end;
    }

    /**
     * Number of symbols covered by the feature. Requires the length of the plasmid the feature
     * belongs to, since an origin-crossing feature wraps past the last symbol back to the first.
     * @param   int         $iPlasmidLength     Length of the plasmid sequence this feature is on
     * @return  int
     */
    public function getLength(int $iPlasmidLength): int
    {
        if ($this->crossesOrigin()) {
            return ($iPlasmidLength - $this->start + 1) + $this->end;
        }

        return $this->end - $this->start + 1;
    }

    /**
     * Rejects metadata holding anything but scalars, null, or arrays of the same, recursively.
     * @param   string      $sName          The feature's name, for the exception message
     * @param   array       $aMetadata
     */
    private function assertSerializableMetadata(string $sName, array $aMetadata): void
    {
        foreach ($aMetadata as $mValue) {
            if ($mValue === null || is_scalar($mValue)) {
                continue;
            }

            if (is_array($mValue)) {
                $this->assertSerializableMetadata($sName, $mValue);
                continue;
            }

            throw new \InvalidArgumentException(
                sprintf('Feature "%s" metadata must only contain scalars or arrays, got %s.', $sName, gettype($mValue))
            );
        }
    }
}
