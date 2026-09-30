<?php
/**
 * Immutable value object holding the outcome of mapping a GenBank record to a Plasmid
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 30 September 2026
 */
namespace Amelaye\BioPHP\Domain\Cloning\Result;

use Amelaye\BioPHP\Domain\Cloning\Aggregate\Plasmid;

/**
 * The mapping always either succeeds with a Plasmid (skipping any feature it could not represent) or
 * throws (for an unsupported topology) ; getWarnings() lists every feature that was skipped and why,
 * so nothing is ever silently dropped without being reported.
 * Class GenbankImportResult
 * @package Amelaye\BioPHP\Domain\Cloning\Result
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
final class GenbankImportResult
{
    /**
     * @var     Plasmid
     */
    private $plasmid;

    /**
     * @var     string[]
     */
    private $warnings;

    /**
     * GenbankImportResult constructor.
     * @param   Plasmid     $oPlasmid
     * @param   string[]    $aWarnings
     */
    public function __construct(Plasmid $oPlasmid, array $aWarnings = [])
    {
        $this->plasmid = $oPlasmid;
        $this->warnings = array_values($aWarnings);
    }

    /**
     * @return  Plasmid
     */
    public function getPlasmid(): Plasmid
    {
        return $this->plasmid;
    }

    /**
     * @return  string[]
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }
}
