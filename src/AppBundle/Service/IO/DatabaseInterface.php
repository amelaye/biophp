<?php
/**
 * Biological Databases Managing
 * Inspired by BioPHP's project biophp.org
 * Created 11 february 2019
 * Last modified 10 april 2019
 */
namespace AppBundle\Service\IO;

/**
 * Interface RecordingOnLocalDb
 * @package AppBundle\Interfaces
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
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