<?php
/**
 * Immutable value object wrapping a DNA sequence string
 * Freely inspired by BioPHP's project biophp.org
 * Created 25 August 2026
 * Last modified 25 August 2026
 */
namespace Amelaye\BioPHP\Domain\Sequence\ValueObject;

/**
 * Accepts the four deoxyribonucleotides and the IUPAC degenerated symbols already tolerated by
 * SequenceTrait::cleanSequence().
 * Class DnaSequence
 * @package Amelaye\BioPHP\Domain\Sequence\ValueObject
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class DnaSequence extends AbstractNucleicSequence
{
    /**
     * @inheritDoc
     */
    protected const ALPHABET = "ACGTMRWSYKVHDBXN";

    /**
     * @inheritDoc
     */
    protected const MOL_TYPE = "DNA";

    /**
     * @inheritDoc
     */
    protected const COMPLEMENTS = [
        "A" => "T", "C" => "G", "G" => "C", "T" => "A",
        "M" => "K", "R" => "Y", "W" => "W", "S" => "S",
        "Y" => "R", "K" => "M", "V" => "B", "H" => "D",
        "D" => "H", "B" => "V", "X" => "X", "N" => "N"
    ];

    /**
     * Transcribes the sequence, thymine becoming uracil.
     * @return  RnaSequence
     * @throws  InvalidSequenceException
     */
    public function toRna() : RnaSequence
    {
        return new RnaSequence(strtr($this->getValue(), ["T" => "U"]));
    }
}
