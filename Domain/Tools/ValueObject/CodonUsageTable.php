<?php
/**
 * Immutable value object wrapping a reference codon usage table
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 30 September 2026
 */
namespace Amelaye\BioPHP\Domain\Tools\ValueObject;

/**
 * Holds raw codon observation counts from a reference gene set (typically the highly expressed genes
 * of a host organism), keyed by codon in DNA notation. This is deliberately just a validated data
 * carrier : it knows nothing about which codons are synonymous with each other, since that grouping
 * comes from the genetic code, not from this table, and is CodonAdaptationIndexCalculator's concern.
 * A codon absent from the table is treated as observed zero times by getCount(), not as an error,
 * since a real reference set - especially a small one - can legitimately never have used some codon.
 * Class CodonUsageTable
 * @package Amelaye\BioPHP\Domain\Tools\ValueObject
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class CodonUsageTable
{
    /**
     * @var     array<string,int>
     */
    private $counts;

    /**
     * CodonUsageTable constructor.
     * @param   array<string,int>  $aCodonCounts   Codon (3 letters, A/C/G/T, case insensitive) => a
     * non-negative observation count
     */
    public function __construct(array $aCodonCounts)
    {
        $aNormalized = [];

        foreach ($aCodonCounts as $sCodon => $iCount) {
            $sNormalizedCodon = strtoupper((string) $sCodon);

            if (!preg_match('/^[ACGT]{3}$/', $sNormalizedCodon)) {
                throw new \InvalidArgumentException(
                    sprintf('Codon usage table has invalid codon "%s", expected exactly 3 of A/C/G/T.', $sCodon)
                );
            }

            if (!is_int($iCount) || $iCount < 0) {
                throw new \InvalidArgumentException(
                    sprintf('Codon usage table has invalid count for codon "%s": must be a non-negative integer.', $sNormalizedCodon)
                );
            }

            $aNormalized[$sNormalizedCodon] = $iCount;
        }

        $this->counts = $aNormalized;
    }

    /**
     * @param   string      $sCodon     3 letters, A/C/G/T, case insensitive
     * @return  int         0 when the codon was never observed in this table
     */
    public function getCount(string $sCodon): int
    {
        return $this->counts[strtoupper($sCodon)] ?? 0;
    }

    /**
     * @return  string[]    The codons this table has an explicit observation count for
     */
    public function getCodons(): array
    {
        return array_keys($this->counts);
    }
}
