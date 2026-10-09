<?php
/**
 * PAM250 substitution matrix scoring
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 9 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Alignment\Service;

use Amelaye\BioPHP\Api\Interfaces\Pam250MatrixDigitApiAdapter;
use Amelaye\BioPHP\Domain\Alignment\Exception\InvalidAlignmentInputException;
use Amelaye\BioPHP\Domain\Alignment\Interfaces\SubstitutionScoringInterface;

/**
 * Scores a pair of amino acid symbols using the PAM250 substitution matrix already exposed by bioapi
 * for other parts of this library (see Api\Pam250MatrixDigitApi), rather than a flat match/mismatch
 * rule. The matrix is fetched and flattened once, in the constructor, and is exhaustive for every
 * ordered pair of the twenty standard amino acids ; it has no entry for any other symbol
 * AminoAcidSequence tolerates - the ambiguous B, Z, J and X, selenocysteine U, pyrrolysine O and the
 * stop "*" - which score() reports as an error rather than guessing a value.
 * Class Pam250Scoring
 * @package Amelaye\BioPHP\Domain\Alignment\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class Pam250Scoring implements SubstitutionScoringInterface
{
    /**
     * @var     int[]       Flattened matrix, keyed by the two-letter residue pair code
     */
    private array $matrix;

    /**
     * Pam250Scoring constructor.
     * @param   Pam250MatrixDigitApiAdapter     $oPam250Adapter
     */
    public function __construct(Pam250MatrixDigitApiAdapter $oPam250Adapter)
    {
        $this->matrix = $oPam250Adapter::GetPam250MatrixArray($oPam250Adapter->getPam250Matrix());
    }

    /**
     * @param   string      $sFirstSymbol
     * @param   string      $sSecondSymbol
     * @return  int
     * @throws  InvalidAlignmentInputException     When the matrix has no entry for this pair
     */
    public function score(string $sFirstSymbol, string $sSecondSymbol): int
    {
        $sKey = strtoupper($sFirstSymbol) . strtoupper($sSecondSymbol);

        if (!array_key_exists($sKey, $this->matrix)) {
            throw InvalidAlignmentInputException::noScoreForSymbolPair($sFirstSymbol, $sSecondSymbol);
        }

        return $this->matrix[$sKey];
    }
}
