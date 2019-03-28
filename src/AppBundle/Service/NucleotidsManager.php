<?php
/**
 * Nucleotids Functions
 * Inspired by BioPHP's project biophp.org
 * Created 19 march  2019
 * Last modified 19 march 2019
 * RIP Pasha, gone 27 february 2019 =^._.^= ∫
 */
namespace AppBundle\Service;

/**
 * Class NucleotidsManager
 * @package AppBundle\Service
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class NucleotidsManager
{
    /**
     * Will count number of A, C, G and T bases in the sequence
     * @param   string  $sSequence  is the sequence
     * @return  int
     * @throws \Exception
     */
    public function countACGT($sSequence)
    {
        try {
            $cg = substr_count($sSequence,"A")
                + substr_count($sSequence,"T")
                + substr_count($sSequence,"G")
                + substr_count($sSequence,"C");
            return $cg;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Will count number of degenerate nucleotides (Y, R, W, S, K, MD, V, H and B) in the sequence
     * @param   string $c
     * @return  int
     * @throws \Exception
     */
    public function countYRWSKMDVHB($c){
        try {
            $cg = substr_count($c,"Y")
                + substr_count($c,"R")
                + substr_count($c,"W")
                + substr_count($c,"S")
                + substr_count($c,"K")
                + substr_count($c,"M")
                + substr_count($c,"D")
                + substr_count($c,"V")
                + substr_count($c,"H")
                + substr_count($c,"B");
            return $cg;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * @param $c
     * @return int
     * @throws \Exception
     */
    public function countCG($c)
    {
        try {
            $cg = substr_count($c,"G")
                + substr_count($c,"C");
            return $cg;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
}