<?php
/**
 * GenBank record to Plasmid mapping Interface
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 30 September 2026
 */
namespace Amelaye\BioPHP\Domain\Cloning\Interfaces;

use Amelaye\BioPHP\Domain\Cloning\ValueObject\GenbankImportResult;
use Amelaye\BioPHP\Domain\Sequence\Entity\Feature;
use Amelaye\BioPHP\Domain\Sequence\Entity\GbSequence;
use Amelaye\BioPHP\Domain\Sequence\Entity\Sequence;

/**
 * Interface GenbankPlasmidMapperInterface - transforms an already-parsed GenBank record into a
 * Plasmid, controlled and reversible, never touching the parser itself.
 * @package Amelaye\BioPHP\Domain\Cloning\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface GenbankPlasmidMapperInterface
{
    /**
     * @param   Sequence        $oSequence      The parsed record's sequence and metadata
     * @param   GbSequence      $oGbSequence    The parsed record's GenBank-specific header, whose
     * topology decides whether mapping is even attempted
     * @param   Feature[]       $aFeatures      The parsed record's feature table rows, several per
     * feature (one per qualifier)
     * @return  GenbankImportResult
     */
    public function map(Sequence $oSequence, GbSequence $oGbSequence, array $aFeatures) : GenbankImportResult;
}
