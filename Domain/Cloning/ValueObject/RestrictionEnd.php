<?php
/**
 * Immutable value object describing the overhang left by a restriction cut
 * Freely inspired by BioPHP's project biophp.org
 * Created 24 September 2026
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Cloning\ValueObject;

/**
 * The overhang sequence, when there is one, is always given as the top-strand bases spanning the
 * upper and lower cut positions, read 5' -> 3' on the strand the cut positions were computed from.
 * For a 5' overhang those bases are literally the single-stranded protrusion; for a 3' overhang they
 * are the bases the opposite strand protrudes past, which is what RestrictionEndCompatibilityManager
 * needs to test complementarity either way. UNKNOWN represents an end nothing could be determined
 * for, which must never be treated as compatible or incompatible by mistake.
 * Class RestrictionEnd
 * @package Amelaye\BioPHP\Domain\Cloning\ValueObject
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class RestrictionEnd
{
    const BLUNT = "BLUNT";
    const FIVE_PRIME = "FIVE_PRIME";
    const THREE_PRIME = "THREE_PRIME";
    const UNKNOWN = "UNKNOWN";

    /**
     * @var     string[]
     */
    const VALID_TYPES = [self::BLUNT, self::FIVE_PRIME, self::THREE_PRIME, self::UNKNOWN];

    /**
     * @var     string
     */
    private ?string $type = null;

    /**
     * @var     string|null
     */
    private ?string $overhangSequence = null;

    /**
     * RestrictionEnd constructor.
     * @param   string          $sType                  One of self::VALID_TYPES
     * @param   string|null     $sOverhangSequence       Required for FIVE_PRIME/THREE_PRIME, must be
     * null for BLUNT and UNKNOWN
     */
    public function __construct(string $sType, ?string $sOverhangSequence = null)
    {
        if (!in_array($sType, self::VALID_TYPES, true)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid restriction end type "%s", expected one of: %s.', $sType, implode(", ", self::VALID_TYPES))
            );
        }

        if (in_array($sType, [self::BLUNT, self::UNKNOWN], true) && $sOverhangSequence !== null) {
            throw new \InvalidArgumentException(sprintf('A %s restriction end must not carry an overhang sequence.', $sType));
        }

        if (in_array($sType, [self::FIVE_PRIME, self::THREE_PRIME], true) && ($sOverhangSequence === null || $sOverhangSequence === "")) {
            throw new \InvalidArgumentException(sprintf('A %s restriction end requires a non-empty overhang sequence.', $sType));
        }

        $this->type = $sType;
        $this->overhangSequence = $sOverhangSequence;
    }

    /**
     * @return  self
     */
    public static function blunt(): self
    {
        return new self(self::BLUNT);
    }

    /**
     * @return  self
     */
    public static function unknown(): self
    {
        return new self(self::UNKNOWN);
    }

    /**
     * @param   string      $sOverhangSequence      Must not be empty
     * @return  self
     */
    public static function fivePrime(string $sOverhangSequence): self
    {
        return new self(self::FIVE_PRIME, $sOverhangSequence);
    }

    /**
     * @param   string      $sOverhangSequence      Must not be empty
     * @return  self
     */
    public static function threePrime(string $sOverhangSequence): self
    {
        return new self(self::THREE_PRIME, $sOverhangSequence);
    }

    /**
     * @return  string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return  string|null
     */
    public function getOverhangSequence(): ?string
    {
        return $this->overhangSequence;
    }

    /**
     * @return  bool
     */
    public function isBlunt(): bool
    {
        return $this->type === self::BLUNT;
    }

    /**
     * @return  bool        False only for UNKNOWN
     */
    public function isDeterminate(): bool
    {
        return $this->type !== self::UNKNOWN;
    }
}
