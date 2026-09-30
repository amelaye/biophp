<?php
/**
 * Finds open reading frames across all six reading frames of a DNA sequence
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 30 September 2026
 */
namespace Amelaye\BioPHP\Domain\Tools\Service;

use Amelaye\BioPHP\Domain\Sequence\Interfaces\SequenceInterface;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;
use Amelaye\BioPHP\Domain\Tools\Interfaces\OrfFinderInterface;
use Amelaye\BioPHP\Domain\Tools\ValueObject\OpenReadingFrame;

/**
 * Migrated from biotools' Service/DnaToProteinManager.php::findORF(), which was not actually a
 * coordinate-returning ORF finder : it translated up to six frames into peptide strings and then
 * highlighted the coding stretches (uppercasing a "*"-delimited segment long enough, optionally
 * trimmed to its first Met) purely for display. That highlighting behavior stays a presentational
 * concern of biotools. This class reuses the same core idea - the longest ORF in each stop-delimited
 * segment starts at that segment's first Met - but implements it as a proper library service that
 * returns real OpenReadingFrame coordinates, via SequenceInterface::translateCodon() (the project's
 * own already-wired standard genetic code) rather than biotools' separate TripletApiAdapter/
 * TripletSpecieApiAdapter machinery, the same reuse CodonAdaptationIndexCalculator already makes.
 *
 * A trailing segment that reaches the end of a frame without hitting a stop codon is still reported,
 * with hasStopCodon() false on the ORF - a real, common case at the edge of a linear sequence or
 * contig, not an error. Multiple Met codons within the same stop-delimited segment only ever produce
 * one ORF, starting at the first (= longest possible) one, matching biotools' own behavior rather
 * than reporting every nested alternative start.
 * Class OrfFinder
 * @package Amelaye\BioPHP\Domain\Tools\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class OrfFinder implements OrfFinderInterface
{
    /**
     * @var     SequenceInterface
     */
    private $sequenceManager;

    /**
     * OrfFinder constructor.
     * @param   SequenceInterface   $oSequenceManager
     */
    public function __construct(SequenceInterface $oSequenceManager)
    {
        $this->sequenceManager = $oSequenceManager;
    }

    /**
     * @param   DnaSequence     $oSequence
     * @param   int             $iMinimumProteinLength
     * @return  OpenReadingFrame[]
     */
    public function findOrfs(DnaSequence $oSequence, int $iMinimumProteinLength = 1): array
    {
        $iLength = $oSequence->getLength();
        $sForwardValue = $oSequence->getValue();
        $sReverseValue = $oSequence->reverseComplement()->getValue();

        $aOrfs = [];

        for ($iFrameOffset = 0; $iFrameOffset < 3; $iFrameOffset++) {
            $aOrfs = array_merge(
                $aOrfs,
                $this->scanFrame($sForwardValue, $iFrameOffset, $iFrameOffset + 1, $iLength, $iMinimumProteinLength, false)
            );
            $aOrfs = array_merge(
                $aOrfs,
                $this->scanFrame($sReverseValue, $iFrameOffset, -($iFrameOffset + 1), $iLength, $iMinimumProteinLength, true)
            );
        }

        return $aOrfs;
    }

    /**
     * @param   string      $sSequenceValue     The forward sequence, or its reverse complement when
     * $bReverse is true
     * @param   int         $iOffset            0, 1 or 2 ; the frame's zero-based starting position
     * @param   int         $iFrame             The frame number to stamp on any ORF found
     * @param   int         $iOriginalLength    Length of the original, forward-strand sequence
     * @param   int         $iMinimumProteinLength
     * @param   bool        $bReverse
     * @return  OpenReadingFrame[]
     */
    private function scanFrame(
        string $sSequenceValue,
        int $iOffset,
        int $iFrame,
        int $iOriginalLength,
        int $iMinimumProteinLength,
        bool $bReverse
    ): array {
        $aOrfs = [];
        $iCodonCount = intdiv(strlen($sSequenceValue) - $iOffset, 3);
        $iFirstMetCodon = null;

        for ($iCodon = 0; $iCodon < $iCodonCount; $iCodon++) {
            $sAminoAcid = $this->sequenceManager->translateCodon(
                substr($sSequenceValue, $iOffset + $iCodon * 3, 3),
                1
            );

            if ($sAminoAcid === "*") {
                if ($iFirstMetCodon !== null && ($iCodon - $iFirstMetCodon) >= $iMinimumProteinLength) {
                    $aOrfs[] = $this->buildOrf(
                        $sSequenceValue,
                        $iOffset,
                        $iFrame,
                        $iFirstMetCodon,
                        $iCodon,
                        $iOriginalLength,
                        $bReverse,
                        true
                    );
                }
                $iFirstMetCodon = null;
                continue;
            }

            if ($sAminoAcid === "M" && $iFirstMetCodon === null) {
                $iFirstMetCodon = $iCodon;
            }
        }

        if ($iFirstMetCodon !== null && ($iCodonCount - $iFirstMetCodon) >= $iMinimumProteinLength) {
            $aOrfs[] = $this->buildOrf(
                $sSequenceValue,
                $iOffset,
                $iFrame,
                $iFirstMetCodon,
                $iCodonCount,
                $iOriginalLength,
                $bReverse,
                false
            );
        }

        return $aOrfs;
    }

    /**
     * @param   string      $sSequenceValue
     * @param   int         $iOffset
     * @param   int         $iFrame
     * @param   int         $iFirstMetCodon             Zero-based codon index of the first Met
     * @param   int         $iStopOrCodonCount          The stop codon's zero-based index when
     * $bHasStopCodon, otherwise the frame's total codon count
     * @param   int         $iOriginalLength
     * @param   bool        $bReverse
     * @param   bool        $bHasStopCodon
     * @return  OpenReadingFrame
     */
    private function buildOrf(
        string $sSequenceValue,
        int $iOffset,
        int $iFrame,
        int $iFirstMetCodon,
        int $iStopOrCodonCount,
        int $iOriginalLength,
        bool $bReverse,
        bool $bHasStopCodon
    ): OpenReadingFrame {
        $iLastPeptideCodon = $iStopOrCodonCount - 1;

        $sPeptide = "";
        for ($iCodon = $iFirstMetCodon; $iCodon <= $iLastPeptideCodon; $iCodon++) {
            $sPeptide .= $this->sequenceManager->translateCodon(
                substr($sSequenceValue, $iOffset + $iCodon * 3, 3),
                1
            );
        }

        // The span's last codon : the stop codon itself when present (its 3 bases belong in the
        // coordinates, the GenBank CDS convention), otherwise the last translated codon.
        $iLastCodonOfSpan = $bHasStopCodon ? $iStopOrCodonCount : $iLastPeptideCodon;

        if (!$bReverse) {
            $iStart = $iOffset + $iFirstMetCodon * 3 + 1;
            $iEnd = $iOffset + $iLastCodonOfSpan * 3 + 3;
        } else {
            $iFirstBasePosition = $iOffset + $iFirstMetCodon * 3;
            $iLastCodonBasePosition = $iOffset + $iLastCodonOfSpan * 3;
            $iStart = $iOriginalLength - 2 - $iLastCodonBasePosition;
            $iEnd = $iOriginalLength - $iFirstBasePosition;
        }

        return new OpenReadingFrame($iFrame, $iStart, $iEnd, $sPeptide, $bHasStopCodon);
    }
}
