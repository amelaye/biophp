<?php
/**
 * Immutable value object holding one FASTQ sequencing read and its per-base quality
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Sequencing\ValueObject;

use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;
use Amelaye\BioPHP\Domain\Sequencing\Exception\InvalidFastqRecordException;

/**
 * Quality is decoded as Phred+33 (Sanger / Illumina 1.8+), the encoding every current sequencer
 * produces : getPhredScores()[$i] = ord(quality[$i]) - 33, ranging from 0 to 93. The older
 * Phred+64 encoding (pre-1.8 Illumina) is out of scope - it has not shipped from a sequencer since
 * 2011 and supporting both would require the caller to specify which one applies per file, for no
 * benefit to any format this project currently reads or writes.
 * Class FastqRecord
 * @package Amelaye\BioPHP\Domain\Sequencing\ValueObject
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class FastqRecord
{
    private const PHRED_OFFSET = 33;

    /**
     * @var     string
     */
    private string $identifier;

    /**
     * @var     DnaSequence
     */
    private DnaSequence $sequence;

    /**
     * @var     string
     */
    private string $quality;

    /**
     * FastqRecord constructor.
     * @param   string          $sIdentifier    The read ID, without the leading "@"
     * @param   DnaSequence     $oSequence
     * @param   string          $sQuality       One Phred+33 symbol per base of $oSequence, same length
     * @throws  InvalidFastqRecordException
     */
    public function __construct(string $sIdentifier, DnaSequence $oSequence, string $sQuality)
    {
        if ($sIdentifier === "") {
            throw InvalidFastqRecordException::emptyIdentifier();
        }

        if (strlen($sQuality) !== $oSequence->getLength()) {
            throw InvalidFastqRecordException::mismatchedLength($oSequence->getLength(), strlen($sQuality));
        }

        $iLength = strlen($sQuality);
        for ($i = 0; $i < $iLength; $i++) {
            $iCode = ord($sQuality[$i]);
            if ($iCode < self::PHRED_OFFSET || $iCode > 126) {
                throw InvalidFastqRecordException::invalidQualitySymbol($sQuality[$i], $i);
            }
        }

        $this->identifier = $sIdentifier;
        $this->sequence = $oSequence;
        $this->quality = $sQuality;
    }

    /**
     * @return  string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return  DnaSequence
     */
    public function getSequence(): DnaSequence
    {
        return $this->sequence;
    }

    /**
     * @return  string      Raw Phred+33-encoded quality string
     */
    public function getQuality(): string
    {
        return $this->quality;
    }

    /**
     * @return  int
     */
    public function getLength(): int
    {
        return $this->sequence->getLength();
    }

    /**
     * @return  int[]       One Phred quality score (0-93) per base, in read order
     */
    public function getPhredScores(): array
    {
        $aScores = [];
        $iLength = strlen($this->quality);
        for ($i = 0; $i < $iLength; $i++) {
            $aScores[] = ord($this->quality[$i]) - self::PHRED_OFFSET;
        }
        return $aScores;
    }

    /**
     * @return  float       Arithmetic mean of getPhredScores() ; 0.0 for a zero-length read
     */
    public function getMeanPhredScore(): float
    {
        $aScores = $this->getPhredScores();
        return $aScores === [] ? 0.0 : array_sum($aScores) / count($aScores);
    }
}
