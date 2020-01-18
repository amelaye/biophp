<?php
/**
 * Global database parsing
 * Freely inspired by BioPHP's project biophp.org
 * Created 24 november 2019
 * Last modified 18 january 2020
 */
namespace App\Domain\Database\Interfaces;

use App\Domain\Sequence\Entity\Sequence;

/**
 * Interface ParseDatabaseInterface
 * @package App\Domain\Database\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface ParseDatabaseInterface
{
    /**
     * ParseGenbankManager constructor.
     */
    public function __construct();


    /**
     * Parses a GenBank data file and returns a Seq object containing parsed data.
     * @param   array       $aFlines
     * @return  Sequence    $oSequence
     */
    public function parseDataFile($aFlines);
}