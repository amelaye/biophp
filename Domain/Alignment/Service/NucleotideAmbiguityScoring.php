<?php
/**
 * IUPAC-aware nucleotide substitution scoring
 * Freely inspired by BioPHP's project biophp.org
 * Created 9 October 2026
 * Last modified 9 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Alignment\Service;

use Amelaye\BioPHP\Domain\Alignment\Exception\InvalidAlignmentInputException;
use Amelaye\BioPHP\Domain\Alignment\Interfaces\MoleculeAwareScoringInterface;
use Amelaye\BioPHP\Domain\Alignment\Interfaces\SubstitutionScoringInterface;

/**
 * Scores two nucleotides by the bases each may stand for. Two definite bases are a match or a
 * mismatch ; when one of them is an ambiguity code (R, Y, S, W, K, M, B, D, H, V, N, X), the pair is
 * a mismatch only if the two sets of bases share nothing (R against Y : A/G against C/T), and
 * otherwise scores the intermediate "ambiguous" value, 0 by default - a read that says N neither
 * confirms nor refutes the base facing it, where SimpleMatchMismatchScoring gives N against N a full
 * match and R against A a full mismatch. U is read as T, so DNA and RNA score alike. Case insensitive.
 * Class NucleotideAmbiguityScoring
 * @package Amelaye\BioPHP\Domain\Alignment\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class NucleotideAmbiguityScoring implements SubstitutionScoringInterface, MoleculeAwareScoringInterface
{
    /**
     * @var     string[]    The bases each IUPAC symbol stands for
     */
    private const BASES = [
        "A" => "A", "C" => "C", "G" => "G", "T" => "T", "U" => "T",
        "R" => "AG", "Y" => "CT", "S" => "CG", "W" => "AT", "K" => "GT", "M" => "AC",
        "B" => "CGT", "D" => "AGT", "H" => "ACT", "V" => "ACG", "N" => "ACGT", "X" => "ACGT",
    ];

    /**
     * @var     int
     */
    private int $matchScore;

    /**
     * @var     int
     */
    private int $mismatchScore;

    /**
     * @var     int
     */
    private int $ambiguousScore;

    /**
     * NucleotideAmbiguityScoring constructor.
     * @param   int         $iMatchScore        Two identical definite bases
     * @param   int         $iMismatchScore     Two bases, or sets of bases, with nothing in common
     * @param   int         $iAmbiguousScore    An ambiguity code facing a base (or a code) it may stand for
     */
    public function __construct(int $iMatchScore = 1, int $iMismatchScore = -1, int $iAmbiguousScore = 0)
    {
        $this->matchScore = $iMatchScore;
        $this->mismatchScore = $iMismatchScore;
        $this->ambiguousScore = $iAmbiguousScore;
    }

    /**
     * @param   string      $sMolType
     * @return  bool
     */
    public function supportsMolType(string $sMolType): bool
    {
        return $sMolType === "DNA" || $sMolType === "RNA";
    }

    /**
     * @param   string      $sFirstSymbol
     * @param   string      $sSecondSymbol
     * @return  int
     * @throws  InvalidAlignmentInputException     When a symbol is no nucleotide code
     */
    public function score(string $sFirstSymbol, string $sSecondSymbol): int
    {
        $sFirst = self::BASES[strtoupper($sFirstSymbol)] ?? null;
        $sSecond = self::BASES[strtoupper($sSecondSymbol)] ?? null;
        if ($sFirst === null || $sSecond === null) {
            throw InvalidAlignmentInputException::noScoreForSymbolPair($sFirstSymbol, $sSecondSymbol);
        }

        $bDefinite = strlen($sFirst) === 1 && strlen($sSecond) === 1;
        if ($bDefinite) {
            return $sFirst === $sSecond ? $this->matchScore : $this->mismatchScore;
        }

        $bShareABase = array_intersect(str_split($sFirst), str_split($sSecond)) !== [];

        return $bShareABase ? $this->ambiguousScore : $this->mismatchScore;
    }
}
