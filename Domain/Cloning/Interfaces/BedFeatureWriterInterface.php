<?php
/**
 * BED feature-file writing Interface
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Cloning\Interfaces;

use Amelaye\BioPHP\Domain\Cloning\ValueObject\PlasmidFeature;

/**
 * Interface BedFeatureWriterInterface - the inverse of BedFeatureReaderInterface : serializes
 * PlasmidFeature instances into BED text.
 * @package Amelaye\BioPHP\Domain\Cloning\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface BedFeatureWriterInterface
{
    /**
     * @param   string              $sChrom         The BED "chrom" column (e.g. the plasmid's name)
     * @param   PlasmidFeature[]    $aFeatures
     * @return  string      A BED document, one line per feature
     */
    public function write(string $sChrom, array $aFeatures): string;
}
