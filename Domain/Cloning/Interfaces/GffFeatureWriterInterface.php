<?php
/**
 * GFF3 feature-file writing Interface
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 6 October 2026
 */
declare(strict_types=1);

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
     * @param   int|null            $iSequenceLength    Length of the circular molecule the features lie
     * on ; required to write an origin-crossing feature, which GFF3 encodes as end + this length on
     * a landmark flagged Is_circular=true
     * @return  string      A complete GFF3 document, "##gff-version 3" pragma included
     */
    public function write(string $sSeqId, array $aFeatures, ?int $iSequenceLength = null): string;
}
