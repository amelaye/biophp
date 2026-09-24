<?php
/**
 * Typed restriction enzyme catalog Interface
 * Freely inspired by BioPHP's project biophp.org
 * Created 24 September 2026
 * Last modified 24 September 2026
 */
namespace Amelaye\BioPHP\Domain\Sequence\Interfaces;

use Amelaye\BioPHP\Domain\Sequence\Exception\UnknownRestrictionEnzymeException;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\RestrictionEnzymeDefinition;

/**
 * Interface RestrictionEnzymeCatalogInterface - gives typed, read-only access to restriction enzyme
 * definitions merged from the Type II, Type IIb and Type IIs endonuclease sources, without exposing
 * the private storage of any single one of them.
 * @package Amelaye\BioPHP\Domain\Sequence\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface RestrictionEnzymeCatalogInterface
{
    /**
     * Looks a restriction enzyme up by its canonical name or one of its aliases, case-insensitively.
     * @param   string      $sName      Canonical name or alias
     * @return  RestrictionEnzymeDefinition|null   Null when no enzyme matches
     */
    public function findByName(string $sName): ?RestrictionEnzymeDefinition;

    /**
     * Same as findByName(), but throws instead of returning null.
     * @param   string      $sName      Canonical name or alias
     * @return  RestrictionEnzymeDefinition
     * @throws  UnknownRestrictionEnzymeException  When no enzyme matches $sName
     */
    public function getByName(string $sName): RestrictionEnzymeDefinition;

    /**
     * Returns every enzyme belonging to the given family, sorted by canonical name.
     * @param   string      $sFamily    One of RestrictionEnzymeDefinition::VALID_FAMILIES
     * @return  RestrictionEnzymeDefinition[]   Empty when the family is unknown or has no member
     */
    public function findByFamily(string $sFamily): array;

    /**
     * Returns every enzyme whose recognition sequence, cleavage marks stripped, matches $sSequence
     * case-insensitively. Several enzymes (isoschizomers) may share the same recognition sequence.
     * @param   string      $sSequence  The recognition sequence to search for, without cleavage marks
     * @return  RestrictionEnzymeDefinition[]   Sorted by canonical name, empty when nothing matches
     */
    public function findByRecognitionSequence(string $sSequence): array;
}
