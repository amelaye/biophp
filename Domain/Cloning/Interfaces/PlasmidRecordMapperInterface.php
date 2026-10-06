<?php
/**
 * Plasmid persistence mapping Interface
 * Freely inspired by BioPHP's project biophp.org
 * Created 6 October 2026
 * Last modified 6 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Cloning\Interfaces;

use Amelaye\BioPHP\Domain\Cloning\Aggregate\Plasmid;
use Amelaye\BioPHP\Domain\Cloning\Entity\PlasmidRecord;

/**
 * Interface PlasmidRecordMapperInterface - converts between the immutable Plasmid aggregate and its
 * Doctrine storage shape, so the aggregate never has to be a Doctrine entity itself.
 * @package Amelaye\BioPHP\Domain\Cloning\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface PlasmidRecordMapperInterface
{
    /**
     * @param   Plasmid         $oPlasmid
     * @return  PlasmidRecord   A new, unmanaged record, features included ; persisting it cascades
     * to the features
     */
    public function toRecord(Plasmid $oPlasmid): PlasmidRecord;

    /**
     * @param   PlasmidRecord   $oRecord
     * @return  Plasmid
     * @throws  \InvalidArgumentException       When the stored data no longer satisfies the
     * aggregate's invariants (e.g. an unknown feature type)
     */
    public function toPlasmid(PlasmidRecord $oRecord): Plasmid;
}
