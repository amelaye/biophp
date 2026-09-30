<?php
/**
 * BED feature-file reading Interface
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 30 September 2026
 */
namespace Amelaye\BioPHP\Domain\Cloning\Interfaces;

use Amelaye\BioPHP\Domain\Cloning\Result\BedImportResult;

/**
 * Interface BedFeatureReaderInterface - reads a BED annotation file into PlasmidFeature instances,
 * independently of any specific Plasmid.
 * @package Amelaye\BioPHP\Domain\Cloning\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface BedFeatureReaderInterface
{
    /**
     * @param   string[]    $aLines     The BED file's lines, in order, newline included or not
     * @return  BedImportResult
     */
    public function read(array $aLines): BedImportResult;
}
