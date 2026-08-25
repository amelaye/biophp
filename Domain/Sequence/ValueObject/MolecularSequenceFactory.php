<?php
/**
 * Builds sequence value objects out of the molecule types found in the parsed records
 * Freely inspired by BioPHP's project biophp.org
 * Created 25 August 2026
 * Last modified 25 August 2026
 */
namespace Amelaye\BioPHP\Domain\Sequence\ValueObject;

use Amelaye\BioPHP\Domain\Sequence\Entity\Sequence;

/**
 * The molecule type stored by the parsers is the raw one of the source file : "mRNA" or "ss-DNA"
 * for GenBank and EMBL, "PRT;" for Swiss-Prot. This factory maps those to the matching value
 * object instead of leaving the caller compare strings.
 * Class MolecularSequenceFactory
 * @package Amelaye\BioPHP\Domain\Sequence\ValueObject
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class MolecularSequenceFactory
{
    /**
     * Wraps a raw string according to a molecule type.
     * A record declared as RNA is not always written with uracil : GenBank and EMBL store their
     * mRNA entries with thymine, following the cDNA convention. When the symbols contradict the
     * declared type, the symbols win, so that a real record can always be wrapped.
     * @param   string      $sMolType       A raw molecule type, e.g. "DNA", "mRNA", "ss-DNA", "PRT;"
     * @param   string      $sSequence      The raw sequence string
     * @return  AbstractMolecularSequence
     * @throws  InvalidSequenceException    When the molecule type is unknown or a symbol is invalid
     */
    public static function fromMolType(string $sMolType, string $sSequence) : AbstractMolecularSequence
    {
        $sNormalized = strtoupper((string) preg_replace('/[^A-Za-z]/', "", $sMolType));

        if (strpos($sNormalized, "PRT") !== false
            || strpos($sNormalized, "PROT") !== false
            || $sNormalized == "AA") {
            return new AminoAcidSequence($sSequence);
        }

        $bIsRna = strpos($sNormalized, "RNA") !== false;
        $bIsDna = strpos($sNormalized, "DNA") !== false;

        if (!$bIsRna && !$bIsDna) {
            throw InvalidSequenceException::unsupportedMolType($sMolType);
        }

        $sSymbols = strtoupper((string) preg_replace('/\s+/', "", $sSequence));

        if (strpos($sSymbols, "T") !== false) {
            return new DnaSequence($sSequence);
        }
        if (strpos($sSymbols, "U") !== false) {
            return new RnaSequence($sSequence);
        }

        return $bIsRna ? new RnaSequence($sSequence) : new DnaSequence($sSequence);
    }

    /**
     * Wraps the sequence held by a parsed record, using its own molecule type.
     * @param   Sequence    $oSequence      A record coming from one of the database parsers
     * @return  AbstractMolecularSequence
     * @throws  InvalidSequenceException    When the molecule type is unknown or a symbol is invalid
     */
    public static function fromEntity(Sequence $oSequence) : AbstractMolecularSequence
    {
        return self::fromMolType((string) $oSequence->getMolType(), $oSequence->getSequence());
    }
}
