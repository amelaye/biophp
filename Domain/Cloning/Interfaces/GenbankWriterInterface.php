<?php
/**
 * GenBank flat-file writing Interface
 * Freely inspired by BioPHP's project biophp.org
 * Created 30 September 2026
 * Last modified 30 September 2026
 */
namespace Amelaye\BioPHP\Domain\Cloning\Interfaces;

use Amelaye\BioPHP\Domain\Cloning\Aggregate\Plasmid;

/**
 * Interface GenbankWriterInterface - serializes a Plasmid into GenBank flat-file text. The
 * counterpart of GenbankPlasmidMapper, not of ParseGenbankManager directly : it writes a Plasmid
 * aggregate, not the raw Feature entities the legacy parser produces.
 * @package Amelaye\BioPHP\Domain\Cloning\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface GenbankWriterInterface
{
    /**
     * @param   Plasmid     $oPlasmid
     * @return  string      A GenBank flat-file record, terminated with "//"
     */
    public function write(Plasmid $oPlasmid): string;
}
