<?php
namespace Tests\Domain\Alignment\Service;

use Amelaye\BioPHP\Domain\Alignment\Exception\InvalidAlignmentInputException;
use Amelaye\BioPHP\Domain\Alignment\Interfaces\MoleculeAwareScoringInterface;
use Amelaye\BioPHP\Domain\Alignment\Interfaces\SubstitutionScoringInterface;
use Amelaye\BioPHP\Domain\Alignment\Service\NeedlemanWunschAligner;
use Amelaye\BioPHP\Domain\Alignment\Service\SemiGlobalAligner;
use Amelaye\BioPHP\Domain\Alignment\Service\SimpleMatchMismatchScoring;
use Amelaye\BioPHP\Domain\Alignment\Service\SmithWatermanAligner;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\AminoAcidSequence;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\RnaSequence;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * A DNA against an RNA scored T and U as a mismatch, a nucleic acid against a protein was scored
 * letter by letter, and PAM250 read the A, C, G and T of a DNA as amino acids : none raised an
 * error, all three aligners alike.
 */
class AlignmentInputGuardTest extends TestCase
{
    public static function aligners(): array
    {
        return [
            "Needleman-Wunsch" => [new NeedlemanWunschAligner()],
            "Smith-Waterman" => [new SmithWatermanAligner()],
            "semi-global" => [new SemiGlobalAligner()],
        ];
    }

    #[DataProvider("aligners")]
    public function testADnaIsNotAlignedAgainstAnRna($oAligner)
    {
        $this->expectException(InvalidAlignmentInputException::class);
        $this->expectExceptionMessage("Cannot align a DNA sequence against a RNA sequence.");

        $oAligner->align(new DnaSequence("ACGT"), new RnaSequence("ACGU"), new SimpleMatchMismatchScoring(), -2);
    }

    #[DataProvider("aligners")]
    public function testANucleicAcidIsNotAlignedAgainstAProtein($oAligner)
    {
        $this->expectException(InvalidAlignmentInputException::class);
        $this->expectExceptionMessage("Cannot align a DNA sequence against a PROTEIN sequence.");

        $oAligner->align(new DnaSequence("ACGT"), new AminoAcidSequence("ACGT"), new SimpleMatchMismatchScoring(), -2);
    }

    #[DataProvider("aligners")]
    public function testAScoringMadeForAnotherMoleculeIsRefused($oAligner)
    {
        $oProteinOnly = new class implements SubstitutionScoringInterface, MoleculeAwareScoringInterface {
            public function supportsMolType(string $sMolType): bool
            {
                return $sMolType === "PROTEIN";
            }

            public function score(string $sFirstSymbol, string $sSecondSymbol): int
            {
                return $sFirstSymbol === $sSecondSymbol ? 1 : -1;
            }
        };

        $this->expectException(InvalidAlignmentInputException::class);
        $this->expectExceptionMessage("is not made for DNA sequences.");

        $oAligner->align(new DnaSequence("ACGT"), new DnaSequence("ACGT"), $oProteinOnly, -2);
    }

    #[DataProvider("aligners")]
    public function testTwoSequencesOfTheSameMoleculeAreStillAligned($oAligner)
    {
        $oResult = $oAligner->align(new RnaSequence("ACGU"), new RnaSequence("ACGU"), new SimpleMatchMismatchScoring(), -2);
        $this->assertEquals(4, $oResult->getScore());

        $oResult = $oAligner->align(new AminoAcidSequence("MKV"), new AminoAcidSequence("MKV"), new SimpleMatchMismatchScoring(), -2);
        $this->assertEquals(3, $oResult->getScore());
    }
}
