<?php
/**
 * Traits for oligos formatting
 * Freely inspired by BioPHP's project biophp.org
 * Created 29 june 2019
 * Last modified 29 june 2019
 */
namespace AppBundle\Traits;

/**
 * Trait OligoTrait
 * @package AppBundle\Traits
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
trait OligoTrait
{
    /**
     * Place sequence and reverse complement of sequence in one line
     * @param $sSequence
     * @param $dnaComplements
     */
    public function createInversion(&$sSequence, $dnaComplements)
    {
        $seqRevert = strrev($sSequence);
        foreach ($dnaComplements as $nucleotide => $complement) {
            $seqRevert = str_replace($nucleotide, strtolower($complement), $seqRevert);
        }
        $sSequence .= " ".strtoupper($seqRevert);
    }

    /**
     * Removes non-coding characters
     * @param       string      $sSequence
     * @return      string
     * @throws      \Exception
     */
    public function removeNonCodingProt($sSequence)
    {
        try {
            $sSequence = strtoupper($sSequence);
            // remove non-coding characters([^ARNDCEQGHILKMFPSTWYVX\*])
            $sSequence = preg_replace("([^ARNDCEQGHILKMFPSTWYVX\*])", "", $sSequence);
            return $sSequence;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
}
