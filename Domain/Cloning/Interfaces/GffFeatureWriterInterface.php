<?php
/**
 * GFF3 feature-file writing Interface
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 30 September 2026
 */
namespace Amelaye\BioPHP\Domain\Cloning\Interfaces;

use Amelaye\BioPHP\Domain\Cloning\ValueObject\PlasmidFeature;

/**
 * Interface GffFeatureWriterInterface - the inverse of GffFeatureReaderInterface : serializes
 * PlasmidFeature instances into GFF3 text.
 * @package Amelaye\BioPHP\Domain\Cloning\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface GffFeatureWriterInterface
{
    /**
     * @param   string              $sSeqId         The GFF3 "seqid" column (e.g. the plasmid's name)
     * @param   PlasmidFeature[]    $aFeatures
     * @return  string      A complete GFF3 document, "##gff-version 3" pragma included
     */
    public function write(string $sSeqId, array $aFeatures): string;
}
