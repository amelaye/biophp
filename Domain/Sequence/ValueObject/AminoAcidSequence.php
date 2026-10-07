<?php
/**
 * Immutable value object wrapping an amino acid sequence string
 * Freely inspired by BioPHP's project biophp.org
 * Created 25 August 2026
 * Last modified 7 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Sequence\ValueObject;

use Amelaye\BioPHP\Domain\Sequence\Exception\InvalidSequenceException;

/**
 * Accepts the twenty amino acids in single-letter format, the two genetically encoded ones
 * (U selenocysteine, O pyrrolysine), the IUPAC ambiguity codes (B : D or N, Z : E or Q, J : I or L),
 * X for an unknown residue and the asterisk for a stop codon.
 * Class AminoAcidSequence
 * @package Amelaye\BioPHP\Domain\Sequence\ValueObject
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class AminoAcidSequence extends AbstractMolecularSequence
{
    /**
     * @inheritDoc
     */
    protected const ALPHABET = "ACDEFGHIKLMNPQRSTVWYBZJUOX*";

    /**
     * @inheritDoc
     */
    protected const MOL_TYPE = "PROTEIN";

    /**
     * The symbol marking the end of the translation.
     */
    public const STOP = "*";

    /**
     * Tells whether the chain holds a stop codon.
     * @return bool
     */
    public function hasStop() : bool
    {
        return strpos($this->getValue(), self::STOP) !== false;
    }

    /**
     * Returns the residues preceding the first stop codon, the whole chain when there is none.
     * @return  AminoAcidSequence
     * @throws  InvalidSequenceException
     */
    public function truncateAtStop() : self
    {
        $iPosition = strpos($this->getValue(), self::STOP);
        if ($iPosition === false) {
            return $this;
        }

        return new self(substr($this->getValue(), 0, $iPosition));
    }

    /**
     * Tells whether the chain holds an unknown residue.
     * @return bool
     */
    public function hasUnknownResidue() : bool
    {
        return $this->countSymbol("X") > 0;
    }
}
