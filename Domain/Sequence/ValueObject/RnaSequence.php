<?php
/**
 * Immutable value object wrapping an RNA sequence string
 * Freely inspired by BioPHP's project biophp.org
 * Created 25 August 2026
 * Last modified 25 August 2026
 */
namespace Amelaye\BioPHP\Domain\Sequence\ValueObject;

/**
 * Accepts the four ribonucleotides and the IUPAC degenerated symbols already tolerated by
 * SequenceTrait::cleanSequence().
 * Class RnaSequence
 * @package Amelaye\BioPHP\Domain\Sequence\ValueObject
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class RnaSequence extends AbstractNucleicSequence
{
    /**
     * @inheritDoc
     */
    protected const ALPHABET = "ACGUMRWSYKVHDBXN";

    /**
     * @inheritDoc
     */
    protected const MOL_TYPE = "RNA";

    /**
     * @inheritDoc
     */
    protected const COMPLEMENTS = [
        "A" => "U", "C" => "G", "G" => "C", "U" => "A",
        "M" => "K", "R" => "Y", "W" => "W", "S" => "S",
        "Y" => "R", "K" => "M", "V" => "B", "H" => "D",
        "D" => "H", "B" => "V", "X" => "X", "N" => "N"
    ];

    /**
     * Reverse transcribes the sequence, uracil becoming thymine.
     * @return  DnaSequence
     * @throws  InvalidSequenceException
     */
    public function toDna() : DnaSequence
    {
        return new DnaSequence(strtr($this->getValue(), ["U" => "T"]));
    }
}
