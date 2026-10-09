<?php
/**
 * Chaos Game Representation of a DNA sequence
 * Freely inspired by BioPHP's project biophp.org
 * Created 9 October 2026
 * Last modified 9 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Tools\Service;

use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;
use Amelaye\BioPHP\Domain\Tools\Interfaces\ChaosGameRepresentationInterface;

/**
 * Migrated from biotools' ChaosGameRepresentationManager, whose computation was tangled with the
 * drawing of the images. The chaos game (Jeffrey, 1990) starts at the centre of a unit square whose
 * corners stand for the bases ; each base moves the point half-way to its corner. The corners are
 * biotools', y growing downward as in an image : C top left (0, 0), G top right (1, 0), A bottom left
 * (0, 1), T bottom right (1, 1). A symbol other than A, C, G or T moves nothing and leaves no point.
 *
 * The point after the last base of a word depends on that word only (the more recent a base, the
 * coarser the part of the square it fixes), so every oligonucleotide has a place of its own on a
 * 2^k x 2^k grid : its column bit is 1 for G and T, its row bit 1 for A and T, the last base giving
 * the most significant bit. The FCGR (Almeida et al., 2001) shades each cell by the count of its
 * oligonucleotide. The overlapping oligonucleotides are counted ; one holding a symbol other than A,
 * C, G, T is left out.
 * Class ChaosGameRepresentationCalculator
 * @package Amelaye\BioPHP\Domain\Tools\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class ChaosGameRepresentationCalculator implements ChaosGameRepresentationInterface
{
    private const MAX_OLIGO_LENGTH = 8;

    /**
     * @var     array<string,array{0:float,1:float}>    Each base's corner, [x, y]
     */
    private const CORNERS = [
        "C" => [0.0, 0.0],
        "G" => [1.0, 0.0],
        "A" => [0.0, 1.0],
        "T" => [1.0, 1.0],
    ];

    /**
     * @param   DnaSequence     $oSequence
     * @return  array<int,array{0:float,1:float}>
     */
    public function points(DnaSequence $oSequence): array
    {
        $fX = 0.5;
        $fY = 0.5;
        $aPoints = [];
        foreach (str_split($oSequence->getValue()) as $sBase) {
            if (!isset(self::CORNERS[$sBase])) {
                continue;
            }
            $fX = ($fX + self::CORNERS[$sBase][0]) / 2;
            $fY = ($fY + self::CORNERS[$sBase][1]) / 2;
            $aPoints[] = [$fX, $fY];
        }

        return $aPoints;
    }

    /**
     * @param   DnaSequence     $oSequence
     * @param   int             $iOligoLength
     * @param   bool            $bBothStrands
     * @return  array<string,int>
     * @throws  \InvalidArgumentException  When the oligonucleotide length is not 1 to 8
     */
    public function oligoCounts(DnaSequence $oSequence, int $iOligoLength, bool $bBothStrands = false): array
    {
        $this->assertOligoLength($iOligoLength);

        $aCounts = [];
        foreach ($this->everyOligo($iOligoLength) as $sOligo) {
            $aCounts[$sOligo] = 0;
        }

        $aStrands = [$oSequence->getValue()];
        if ($bBothStrands) {
            $aStrands[] = $oSequence->reverseComplement()->getValue();
        }
        foreach ($aStrands as $sStrand) {
            for ($i = 0, $iLast = strlen($sStrand) - $iOligoLength; $i <= $iLast; $i++) {
                $sOligo = substr($sStrand, $i, $iOligoLength);
                if (isset($aCounts[$sOligo])) {
                    $aCounts[$sOligo]++;
                }
            }
        }

        return $aCounts;
    }

    /**
     * @param   string  $sOligo
     * @return  array{0:int,1:int}
     * @throws  \InvalidArgumentException  When it is not 1 to 8 of A, C, G, T
     */
    public function cell(string $sOligo): array
    {
        $this->assertOligoLength(strlen($sOligo));
        if (preg_match('/^[ACGT]+$/', $sOligo) !== 1) {
            throw new \InvalidArgumentException(sprintf('"%s" is not made of A, C, G and T only.', $sOligo));
        }

        $iColumn = 0;
        $iRow = 0;
        $iLength = strlen($sOligo);
        for ($i = 0; $i < $iLength; $i++) {
            // the first base is the least significant bit, the last one the most significant
            $iBit = 1 << $i;
            $sBase = $sOligo[$i];
            if ($sBase === "G" || $sBase === "T") {
                $iColumn |= $iBit;
            }
            if ($sBase === "A" || $sBase === "T") {
                $iRow |= $iBit;
            }
        }

        return [$iColumn, $iRow];
    }

    /**
     * @param   DnaSequence     $oSequence
     * @param   int             $iOligoLength
     * @param   bool            $bBothStrands
     * @return  int[][]
     */
    public function frequencyMatrix(DnaSequence $oSequence, int $iOligoLength, bool $bBothStrands = false): array
    {
        $this->assertOligoLength($iOligoLength);
        $iSide = 1 << $iOligoLength;
        $aMatrix = array_fill(0, $iSide, array_fill(0, $iSide, 0));
        foreach ($this->oligoCounts($oSequence, $iOligoLength, $bBothStrands) as $sOligo => $iCount) {
            [$iColumn, $iRow] = $this->cell((string) $sOligo);
            $aMatrix[$iRow][$iColumn] = $iCount;
        }

        return $aMatrix;
    }

    /**
     * @param   int     $iOligoLength
     * @throws  \InvalidArgumentException
     */
    private function assertOligoLength(int $iOligoLength): void
    {
        if ($iOligoLength < 1 || $iOligoLength > self::MAX_OLIGO_LENGTH) {
            throw new \InvalidArgumentException(
                sprintf('The oligonucleotide length must be 1 to %d, %d given.', self::MAX_OLIGO_LENGTH, $iOligoLength)
            );
        }
    }

    /**
     * @param   int     $iLength
     * @return  string[]    Every word of A, C, G, T of that length
     */
    private function everyOligo(int $iLength): array
    {
        $aOligos = [""];
        for ($i = 0; $i < $iLength; $i++) {
            $aNext = [];
            foreach ($aOligos as $sPrefix) {
                foreach (["A", "C", "G", "T"] as $sBase) {
                    $aNext[] = $sPrefix . $sBase;
                }
            }
            $aOligos = $aNext;
        }

        return $aOligos;
    }
}
