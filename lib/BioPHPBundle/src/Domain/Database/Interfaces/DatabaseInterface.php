<?php
/**
 * Biological Databases Managing
 * Inspired by BioPHP's project biophp.org
 * Created 11 february 2019
 * Last modified 19 january 2020
 */
namespace Amelaye\BioPHP\Domain\Database\Interfaces;

use App\Domain\Sequence\Entity\Sequence;

/**
 * Interface RecordingOnLocalDb
 * @package Amelaye\BioPHP\Domain\Database\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface DatabaseInterface
{
    /**
     * Retrieves all data from the specified sequence record and returns them in the
     * form of a Seq object.  This method invokes one of several parser methods.
     * @param       string          $sSeqId     The id of the seq obj.
     * @return      Sequence|bool
     * @throws      \Exception
     */
    public function fetch($sSeqId);


    /**
     * Records the new elements of a collection, reads a collection
     * @throws \Exception
     */
    public function recording();
}