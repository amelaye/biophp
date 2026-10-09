<?php
/**
 * GFF3 feature-file reading Interface
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Cloning\Interfaces;

use Amelaye\BioPHP\Domain\Cloning\Result\GffImportResult;

/**
 * Interface GffFeatureReaderInterface - reads a GFF3 annotation file into PlasmidFeature instances,
 * independently of any specific Plasmid.
 * @package Amelaye\BioPHP\Domain\Cloning\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface GffFeatureReaderInterface
{
    /**
     * @param   string[]    $aLines     The GFF3 file's lines, in order, newline included or not
     * @return  GffImportResult
     */
    public function read(array $aLines): GffImportResult;
}
