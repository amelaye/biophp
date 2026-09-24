<?php
/**
 * Immutable value object describing a restriction enzyme, independent of any API DTO or Doctrine entity
 * Freely inspired by BioPHP's project biophp.org
 * Created 24 September 2026
 * Last modified 24 September 2026
 */
namespace Amelaye\BioPHP\Domain\Sequence\ValueObject;

/**
 * Wraps the normalized properties of a restriction enzyme (name, aliases, family, recognition and
 * computing patterns, cleavage positions). It never computes an overhang: that requires a target
 * sequence and belongs to the digestion services that compose this value object.
 * Class RestrictionEnzymeDefinition
 * @package Amelaye\BioPHP\Domain\Sequence\ValueObject
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class RestrictionEnzymeDefinition
{
    /**
     * Recognizes and cuts within its own recognition sequence, at a fixed position.
     */
    const TYPE_II = "TYPE_II";

    /**
     * Cuts on both sides of its recognition sequence, outside of it.
     */
    const TYPE_IIB = "TYPE_IIB";

    /**
     * Recognizes an asymmetric sequence and cuts at a defined distance outside of it.
     */
    const TYPE_IIS = "TYPE_IIS";

    /**
     * @var     string[]        The families a RestrictionEnzymeDefinition may belong to
     */
    const VALID_FAMILIES = [self::TYPE_II, self::TYPE_IIB, self::TYPE_IIS];

    /**
     * @var     string          Canonical name
     */
    private $name;

    /**
     * @var     string[]        Isoschizomers or other names sharing the same recognition pattern
     */
    private $aliases;

    /**
     * @var     string          One of the VALID_FAMILIES constants
     */
    private $family;

    /**
     * @var     string          Human notation of the recognition sequence, with cleavage marks (' and _)
     */
    private $recognitionPattern;

    /**
     * @var     string          Recognition pattern meant for pattern searches
     */
    private $computingPattern;

    /**
     * @var     int             Length of the recognition pattern
     */
    private $recognitionLength;

    /**
     * @var     int             Cleavage position on the upper strand
     */
    private $cleavagePositionUpper;

    /**
     * @var     int             Cleavage position on the lower strand, relative to the upper one
     */
    private $cleavagePositionLower;

    /**
     * @var     int             Number of non-N bases within the recognition pattern
     */
    private $nonAmbiguousBaseCount;

    /**
     * RestrictionEnzymeDefinition constructor.
     * @param   string          $sName                      Canonical name, must not be empty
     * @param   string[]        $aAliases                   Isoschizomers or other names, may be empty
     * @param   string          $sFamily                    One of self::VALID_FAMILIES
     * @param   string          $sRecognitionPattern        Human notation, with cleavage marks
     * @param   string          $sComputingPattern          Pattern meant for pattern searches
     * @param   int             $iRecognitionLength         Must be strictly positive
     * @param   int             $iCleavagePositionUpper     Cleavage position on the upper strand
     * @param   int             $iCleavagePositionLower     Cleavage position on the lower strand
     * @param   int             $iNonAmbiguousBaseCount     Must not be negative
     */
    public function __construct(
        string $sName,
        array $aAliases,
        string $sFamily,
        string $sRecognitionPattern,
        string $sComputingPattern,
        int $iRecognitionLength,
        int $iCleavagePositionUpper,
        int $iCleavagePositionLower,
        int $iNonAmbiguousBaseCount
    ) {
        if (trim($sName) === "") {
            throw new \InvalidArgumentException("Restriction enzyme name must not be empty.");
        }

        if (!in_array($sFamily, self::VALID_FAMILIES, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Restriction enzyme "%s" has invalid family "%s", expected one of: %s.',
                    $sName,
                    $sFamily,
                    implode(", ", self::VALID_FAMILIES)
                )
            );
        }

        if ($iRecognitionLength <= 0) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Restriction enzyme "%s" has invalid recognition length %d, expected a strictly positive integer.',
                    $sName,
                    $iRecognitionLength
                )
            );
        }

        if ($iNonAmbiguousBaseCount < 0) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Restriction enzyme "%s" has invalid non-ambiguous base count %d, expected zero or more.',
                    $sName,
                    $iNonAmbiguousBaseCount
                )
            );
        }

        $this->name = $sName;
        $this->aliases = array_values(array_unique(array_filter(array_map("trim", $aAliases), function (string $sAlias) {
            return $sAlias !== "";
        })));
        $this->family = $sFamily;
        $this->recognitionPattern = $sRecognitionPattern;
        $this->computingPattern = $sComputingPattern;
        $this->recognitionLength = $iRecognitionLength;
        $this->cleavagePositionUpper = $iCleavagePositionUpper;
        $this->cleavagePositionLower = $iCleavagePositionLower;
        $this->nonAmbiguousBaseCount = $iNonAmbiguousBaseCount;
    }

    /**
     * @return  string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return  string[]
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }

    /**
     * @return  string
     */
    public function getFamily(): string
    {
        return $this->family;
    }

    /**
     * @return  string
     */
    public function getRecognitionPattern(): string
    {
        return $this->recognitionPattern;
    }

    /**
     * @return  string
     */
    public function getComputingPattern(): string
    {
        return $this->computingPattern;
    }

    /**
     * @return  int
     */
    public function getRecognitionLength(): int
    {
        return $this->recognitionLength;
    }

    /**
     * @return  int
     */
    public function getCleavagePositionUpper(): int
    {
        return $this->cleavagePositionUpper;
    }

    /**
     * @return  int
     */
    public function getCleavagePositionLower(): int
    {
        return $this->cleavagePositionLower;
    }

    /**
     * @return  int
     */
    public function getNonAmbiguousBaseCount(): int
    {
        return $this->nonAmbiguousBaseCount;
    }

    /**
     * Returns the recognition sequence stripped of its cleavage marks (' and _).
     * @return  string
     */
    public function getCleanRecognitionSequence(): string
    {
        return str_replace(["'", "_"], "", $this->recognitionPattern);
    }

    /**
     * Tells whether the recognition sequence contains an IUPAC ambiguous base (anything but A, C, G,
     * T or U). Only uppercase letters are inspected, since the recognition pattern notation uses
     * uppercase for bases and lowercase only for the literal "or" separating alternative sites.
     * @return  bool
     */
    public function hasAmbiguousBases(): bool
    {
        if (!preg_match_all('/[A-Z]/', $this->getCleanRecognitionSequence(), $aMatches)) {
            return false;
        }

        foreach ($aMatches[0] as $sBase) {
            if (strpos("ACGTU", $sBase) === false) {
                return true;
            }
        }

        return false;
    }
}
