<?php
/**
 * Immutable value object holding the outcome of reading a FASTQ file
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Sequencing\Result;

use Amelaye\BioPHP\Domain\Sequencing\ValueObject\FastqRecord;

/**
 * Class FastqImportResult
 * @package Amelaye\BioPHP\Domain\Sequencing\Result
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class FastqImportResult
{
    /**
     * @var     FastqRecord[]
     */
    private array $records;

    /**
     * @var     string[]
     */
    private array $warnings;

    /**
     * FastqImportResult constructor.
     * @param   FastqRecord[]   $aRecords
     * @param   string[]        $aWarnings
     */
    public function __construct(array $aRecords, array $aWarnings = [])
    {
        foreach ($aRecords as $oRecord) {
            if (!$oRecord instanceof FastqRecord) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'FastqImportResult records must be FastqRecord instances, got %s.',
                        is_object($oRecord) ? get_class($oRecord) : gettype($oRecord)
                    )
                );
            }
        }

        $this->records = array_values($aRecords);
        $this->warnings = array_values($aWarnings);
    }

    /**
     * @return  FastqRecord[]
     */
    public function getRecords(): array
    {
        return $this->records;
    }

    /**
     * @return  string[]
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }
}
