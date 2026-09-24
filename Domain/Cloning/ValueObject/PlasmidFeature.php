<?php
/**
 * Immutable value object describing one annotated region of a Plasmid
 * Freely inspired by BioPHP's project biophp.org
 * Created 24 September 2026
 * Last modified 24 September 2026
 */
namespace Amelaye\BioPHP\Domain\Cloning\ValueObject;

use Amelaye\BioPHP\Domain\Cloning\Exception\InvalidFeatureCoordinatesException;

/**
 * Coordinates are 1-based inclusive, the convention used by GenBank and EMBL feature tables. Start
 * and end are only validated to be at least 1 here : since a lone feature does not know the length
 * of the plasmid it will belong to, the upper bound is checked by Plasmid when the feature is
 * attached. A start strictly greater than end is valid and means the feature crosses the origin.
 * Class PlasmidFeature
 * @package Amelaye\BioPHP\Domain\Cloning\ValueObject
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class PlasmidFeature
{
    /**
     * @var     string
     */
    private $name;

    /**
     * @var     string          One of FeatureType::VALID_TYPES
     */
    private $type;

    /**
     * @var     int             1-based inclusive
     */
    private $start;

    /**
     * @var     int             1-based inclusive
     */
    private $end;

    /**
     * @var     string          One of Strand::VALID_STRANDS
     */
    private $strand;

    /**
     * @var     string|null     Strict "#RRGGBB" hexadecimal notation
     */
    private $color;

    /**
     * @var     string|null
     */
    private $note;

    /**
     * @var     string|null
     */
    private $externalId;

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
     */
    public function __construct(
        string $sName,
        string $sType,
        int $iStart,
        int $iEnd,
        string $sStrand = Strand::NONE,
        ?string $sColor = null,
        ?string $sNote = null,
        ?string $sExternalId = null
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

        $this->name = $sName;
        $this->type = $sType;
        $this->start = $iStart;
        $this->end = $iEnd;
        $this->strand = $sStrand;
        $this->color = $sColor;
        $this->note = $sNote;
        $this->externalId = $sExternalId;
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
}
