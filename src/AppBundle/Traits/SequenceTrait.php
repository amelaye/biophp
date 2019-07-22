<?php
/**
 * Traits for sequences formatting
 * Freely inspired by BioPHP's project biophp.org
 * Created 22 july 2019
 * Last modified  22 july  2019
 */
namespace AppBundle\Traits;

/**
 * Trait SequenceTrait
 * @package AppBundle\Traits
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
trait SequenceTrait
{
    /**
     * Computes the reverse complement of a sequence (only ACGT nucleotides are used)
     * @param $sSequence
     * @return mixed|string
     */
    function revComp($sSequence) {
        $sSequence = strrev($sSequence);
        $sSequence = str_replace("A", "t", $sSequence);
        $sSequence = str_replace("T", "a", $sSequence);
        $sSequence = str_replace("G", "c", $sSequence);
        $sSequence = str_replace("C", "g", $sSequence);
        $sSequence = strtoupper ($sSequence);
        return $sSequence;
    }
}
