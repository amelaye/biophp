<?php
/**
 * PCR Amplification Functions
 * @author Amélie DUVERNET akka Amelaye
 * Inspired by BioPHP's project biophp.org
 * Created 26 february 2019
 * Last modified 26 february 2019
 */
namespace MinitoolsBundle\Service;

class PcrAmplificationManager
{
    /**
     * @param $start_pattern
     * @param $end_pattern
     * @param $sequence
     * @param $maxlength
     * @return mixed
     * @throws \Exception
     */
    public function amplify($start_pattern,$end_pattern,$sequence,$maxlength)
    {
        try {
            // SPLIT SEQUENCE BASED IN $start_pattern (start positions of amplicons)
            $fragments = preg_split("/($start_pattern)/", $sequence,-1,PREG_SPLIT_DELIM_CAPTURE);
            $maxfragments = sizeof($fragments);
            $position = strlen($fragments[0]);
            $mn = 0;
            for($m = 1; $m < $maxfragments; $m += 2) {
                $subfragment_to_maximum = substr($fragments[$m+1],0,$maxlength);
                $fragments2 = preg_split("/($end_pattern)/", $subfragment_to_maximum,-1,PREG_SPLIT_DELIM_CAPTURE);

                if (sizeof($fragments2) > 1) {
                    $lenfragment = strlen($fragments[$m].$fragments2[0].$fragments2[1]);
                    $results_array[$position]=$lenfragment;
                }
                $position += strlen($fragments[$m])+strlen($fragments[$m+1]);
            }
            return($results_array);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }


    /**
     * @param $p2
     * @return mixed|string
     * @throws \Exception
     * @todo : à supprimer
     */
    function revComp($p2)
    {
        try {
            $p2 = strrev($p2);
            $p2 = str_replace("A", "t", $p2);
            $p2 = str_replace("T", "a", $p2);
            $p2 = str_replace("G", "c", $p2);
            $p2 = str_replace("C", "g", $p2);
            $p2 = strtoupper($p2);
            return $p2;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }


    /**
     * @param $pattern
     * @return string
     * @throws \Exception
     */
    function includeN($pattern)
    {
        try {
            if (strlen($pattern) > 2) {
                $new_pattern = ".".substr($pattern,1);
                $pos = 1;
                while($pos < strlen($pattern)) {
                    $new_pattern .= "|".substr($pattern,0,$pos).".".substr($pattern,$pos+1);
                    $pos++;
                }
            }
            return ($new_pattern);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
}