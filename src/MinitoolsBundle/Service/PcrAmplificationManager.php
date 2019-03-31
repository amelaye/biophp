<?php
/**
 * PCR Amplification Functions
 * Inspired by BioPHP's project biophp.org
 * Created 26 february 2019
 * Last modified 31 march 2019
 */
namespace MinitoolsBundle\Service;

/**
 * Class PcrAmplificationManager
 * @package MinitoolsBundle\Service
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class PcrAmplificationManager
{
    /**
     * Amplify the sequence
     * @param   string      $sStartPattern
     * @param   string      $sEndPattern
     * @param   string      $sSequence
     * @param   int         $iMaxLength
     * @return  array
     * @throws  \Exception
     */
    public function amplify($sStartPattern, $sEndPattern, $sSequence, $iMaxLength)
    {
        try {
            $aResults = [];

            // SPLIT SEQUENCE BASED IN $start_pattern (start positions of amplicons)
            $aFragments = preg_split("/($sStartPattern)/", $sSequence,-1,PREG_SPLIT_DELIM_CAPTURE);
            $iMaxFragments = sizeof($aFragments);
            $iPosition = strlen($aFragments[0]);

            for($m = 1; $m < $iMaxFragments; $m += 2) {
                $sSubfragmentToMaximum = substr($aFragments[$m + 1],0,$iMaxLength);
                $sFragments2 = preg_split("/($sEndPattern)/", $sSubfragmentToMaximum,-1,PREG_SPLIT_DELIM_CAPTURE);

                if (sizeof($sFragments2) > 1) {
                    $iLenFragment = strlen($aFragments[$m].$sFragments2[0].$sFragments2[1]);
                    $aResults[$iPosition] = $iLenFragment;
                }
                $iPosition += strlen($aFragments[$m]) + strlen($aFragments[$m+1]);
            }
            return($aResults);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * If one missmatch is allowed, create new pattern
     * example: pattern="ACGT"; to allow one missmatch pattern=".CGT|A.GT|AC.T|ACG."
     * @param   string      $sPattern
     * @return  string
     * @throws  \Exception
     */
    function includeN($sPattern)
    {
        try {
            $sNewPattern = "";
            if (strlen($sPattern) > 2) {
                $sNewPattern = ".".substr($sPattern,1);
                $iPos = 1;
                while($iPos < strlen($sPattern)) {
                    $sNewPattern .= "|".substr($sPattern,0,$iPos).".".substr($sPattern,$iPos + 1);
                    $iPos++;
                }
            }
            return ($sNewPattern);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
}