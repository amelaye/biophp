<?php
/**
 * Immutable value object shared by the nucleic acid sequences
 * Freely inspired by BioPHP's project biophp.org
 * Created 25 August 2026
 * Last modified 7 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Sequence\ValueObject;

use Amelaye\BioPHP\Domain\Sequence\Exception\InvalidSequenceException;

/**
 * Holds what DNA and RNA have in common : a complement table covering the IUPAC degenerated
 * symbols, and the GC content.
 * Class AbstractNucleicSequence
 * @package Amelaye\BioPHP\Domain\Sequence\ValueObject
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
abstract class AbstractNucleicSequence extends AbstractMolecularSequence
{
    /**
     * Complement of each symbol of the alphabet, overridden by each concrete class.
     */
    protected const COMPLEMENTS = [];

    /**
     * Returns the genetic complement of the sequence, degenerated symbols included.
     * @return  static
     * @throws  InvalidSequenceException
     */
    public function complement() : static
    {
        $sComplement = "";
        $sValue      = $this->getValue();
        $iLength     = strlen($sValue);

        for($i = 0; $i < $iLength; $i++) {
            $sSymbol = substr($sValue, $i, 1);
            $sComplement .= static::COMPLEMENTS[$sSymbol];
        }

        return new static($sComplement);
    }

    /**
     * Returns the complement of the sequence read backwards, which is the strand facing it.
     * @return  static
     * @throws  InvalidSequenceException
     */
    public function reverseComplement() : static
    {
        return $this->complement()->reverse();
    }

    /**
     * Proportion of guanine and cytosine in the sequence, expressed as a percentage : see gcFraction().
     * @return  float                       0 when the sequence holds no base telling G/C from A/T
     */
    public function getGcContent() : float
    {
        return self::gcFraction($this->getValue()) * 100;
    }

    /**
     * Proportion of strong bases (G, C, and S standing for either) among the bases known to be
     * strong or weak (A, T, U, W and those) : an N, or any other ambiguity code mixing both, tells
     * nothing about it and is left out of the count, as Biopython's gc_fraction does by default.
     * "GCNN" gives 1, "GCSW" 0.75. The one definition every GC content of this library uses.
     * @param   string      $sSequence      Either case
     * @return  float                       Between 0 and 1 ; 0 when no base tells G/C from A/T
     */
    public static function gcFraction(string $sSequence) : float
    {
        $sUpper = strtoupper($sSequence);
        $iStrong = substr_count($sUpper, "G") + substr_count($sUpper, "C") + substr_count($sUpper, "S");
        $iWeak = substr_count($sUpper, "A") + substr_count($sUpper, "T") + substr_count($sUpper, "U")
            + substr_count($sUpper, "W");

        return $iStrong + $iWeak === 0 ? 0.0 : $iStrong / ($iStrong + $iWeak);
    }
}
