<?php
/**
 * PCR Amplification Functions
 * Inspired by BioPHP's project biophp.org
 * Created 26 february 2019
 * Last modified 29 june 2019
 */
namespace MinitoolsBundle\Service;

use AppBundle\Bioapi\Bioapi;

/**
 * Class PcrAmplificationManager
 * @package MinitoolsBundle\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class PcrAmplificationManager
{
    /**
     * @var Bioapi
     */
    private $bioapi;

    /**
     * PcrAmplificationManager constructor.
     * @param Bioapi $bioapi
     */
    public function __construct(Bioapi $bioapi)
    {
        $this->bioapi = $bioapi;
    }

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
    public function includeN($sPattern)
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
            return $sNewPattern;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * SET PATTERNS FROM PRIMERS
     * Change N to point in primers
     * @param   string      $primer1
     * @param   string      $primer2
     * @param   bool        $bAllowMismatch
     * @return  string
     * @throws  \Exception
     */
    public function createStartPattern($primer1, $primer2, $bAllowMismatch)
    {
        $sPattern1 = str_replace("N", ".", $primer1);
        $sPattern2 = str_replace("N", ".", $primer2);

        if ((bool)$bAllowMismatch) {
            $sPattern1 = $this->includeN($primer1);
            $sPattern2 = $this->includeN($primer2);
        }
        $sStartPattern = "$sPattern1|$sPattern2"; // SET PATTERN
        return $sStartPattern;
    }

    /**
     * SET PATTERNS FROM PRIMERS
     * Change N to point in primers
     * @param   string  $sStartPattern
     * @return  string
     */
    public function createEndPattern($sStartPattern)
    {
        $seqRevert = strrev($sStartPattern);
        foreach ($this->bioapi->getDNAComplement() as $nucleotide => $complement) {
            $seqRevert = str_replace($nucleotide, strtolower($complement), $seqRevert);
        }
        $sEndPattern = strtoupper($seqRevert);

        return $sEndPattern;
    }
}