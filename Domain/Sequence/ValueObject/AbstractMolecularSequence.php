<?php
/**
 * Immutable value object wrapping a biological sequence string
 * Freely inspired by BioPHP's project biophp.org
 * Created 25 August 2026
 * Last modified 25 August 2026
 */
namespace Amelaye\BioPHP\Domain\Sequence\ValueObject;

/**
 * A molecular sequence is defined by its symbols only : two instances holding the same symbols are
 * interchangeable, so the class carries no identity and exposes no setter. Every transformation
 * returns a brand new instance.
 * Class AbstractMolecularSequence
 * @package Amelaye\BioPHP\Domain\Sequence\ValueObject
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
abstract class AbstractMolecularSequence implements \Stringable
{
    /**
     * Symbols accepted by the value object, overridden by each concrete class.
     */
    protected const ALPHABET = "";

    /**
     * Molecule type of the value object, overridden by each concrete class.
     */
    protected const MOL_TYPE = "";

    /**
     * @var string
     */
    private $value;

    /**
     * AbstractMolecularSequence constructor.
     * Whitespace is stripped and the remaining symbols are upper-cased before validation, so raw
     * lines coming from a GenBank, EMBL or FASTA file can be wrapped as they are read.
     * @param   string      $sSequence      The raw sequence string
     * @throws  InvalidSequenceException    When a symbol does not belong to the alphabet
     */
    public function __construct(string $sSequence)
    {
        $sValue = strtoupper((string) preg_replace('/\s+/', "", $sSequence));
        $this->assertAlphabet($sValue);
        $this->value = $sValue;
    }

    /**
     * The wrapped sequence, normalized.
     * @return string
     */
    public function getValue() : string
    {
        return $this->value;
    }

    /**
     * Allows the value object to be used wherever the library still expects a raw string.
     * @return string
     */
    public function __toString() : string
    {
        return $this->value;
    }

    /**
     * The molecule type, as used by SequenceManager (DNA, RNA, PROTEIN).
     * @return string
     */
    public function getMolType() : string
    {
        return static::MOL_TYPE;
    }

    /**
     * The symbols accepted by this kind of sequence.
     * @return string
     */
    public function getAlphabet() : string
    {
        return static::ALPHABET;
    }

    /**
     * Number of symbols held by the sequence.
     * @return int
     */
    public function getLength() : int
    {
        return strlen($this->value);
    }

    /**
     * @return bool
     */
    public function isEmpty() : bool
    {
        return $this->value === "";
    }

    /**
     * Two value objects are equal when they are of the same kind and hold the same symbols.
     * @param   AbstractMolecularSequence   $oOther
     * @return  bool
     */
    public function equals(AbstractMolecularSequence $oOther) : bool
    {
        return static::class === get_class($oOther) && $this->value === $oOther->getValue();
    }

    /**
     * Extracts a portion of the sequence, keeping the same kind of value object.
     * @param   int         $iStart         Zero-based position of the first symbol
     * @param   int|null    $iLength        Number of symbols, until the end when omitted
     * @return  static
     * @throws  InvalidSequenceException
     */
    public function subSequence(int $iStart, ?int $iLength = null) : static
    {
        return new static(substr($this->value, $iStart, $iLength));
    }

    /**
     * Returns the sequence read from the last symbol to the first one.
     * @return  static
     * @throws  InvalidSequenceException
     */
    public function reverse() : static
    {
        return new static(strrev($this->value));
    }

    /**
     * Counts how many times a symbol occurs in the sequence.
     * @param   string      $sSymbol        A single symbol, case insensitive
     * @return  int
     */
    public function countSymbol(string $sSymbol) : int
    {
        return substr_count($this->value, strtoupper($sSymbol));
    }

    /**
     * Tells whether every symbol of a string belongs to the alphabet of the value object, without
     * building an instance.
     * @param   string      $sSequence
     * @return  bool
     */
    public static function isValid(string $sSequence) : bool
    {
        try {
            new static($sSequence);
            return true;
        } catch (InvalidSequenceException $ex) {
            return false;
        }
    }

    /**
     * Rejects the first symbol which does not belong to the alphabet.
     * @param   string      $sSequence      The normalized sequence
     * @throws  InvalidSequenceException
     */
    private function assertAlphabet(string $sSequence) : void
    {
        $iLength = strlen($sSequence);
        for($i = 0; $i < $iLength; $i++) {
            $sSymbol = substr($sSequence, $i, 1);
            if (strpos(static::ALPHABET, $sSymbol) === false) {
                throw InvalidSequenceException::invalidSymbol(static::MOL_TYPE, $sSymbol, $i);
            }
        }
    }
}
