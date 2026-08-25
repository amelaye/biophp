<?php
/**
 * Immutable value object shared by the nucleic acid sequences
 * Freely inspired by BioPHP's project biophp.org
 * Created 25 August 2026
 * Last modified 25 August 2026
 */
namespace Amelaye\BioPHP\Domain\Sequence\ValueObject;

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
     * Proportion of guanine and cytosine in the sequence, expressed as a percentage. The
     * degenerated symbol S, which stands for G or C, is counted as well.
     * @return  float                       0 when the sequence is empty
     */
    public function getGcContent() : float
    {
        if ($this->isEmpty()) {
            return 0.0;
        }

        $iGc = $this->countSymbol("G") + $this->countSymbol("C") + $this->countSymbol("S");

        return ($iGc / $this->getLength()) * 100;
    }
}
