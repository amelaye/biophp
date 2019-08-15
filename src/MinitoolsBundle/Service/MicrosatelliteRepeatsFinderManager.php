<?php
/**
 * Microsatellite Repeats Finder Fonctions
 * Inspired by BioPHP's project biophp.org
 * Created 26 february 2019
 * Last modified 31 march 2019
 */
namespace MinitoolsBundle\Service;

/**
 * Class MicrosatelliteRepeatsFinderManager
 * @package MinitoolsBundle\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class MicrosatelliteRepeatsFinderManager
{
    /**
     * This function will search for microsatellite repeats within a sequence. A microsatellite repeat is defined as a sequence
     * which shows a repeated pattern, as for example in sequence 'ACGTACGTACGTACGT', where 'ACGT' is repeated
     * 4 times. The function allows searching for this kind of subsequences within a sequence.
     * so that sequence AACCGGTT-AAGCGGTT-AACCGGAT-AACCGGTT may be considered as a microsatellite repeat
     * @param   string      $sSequence              is the sequence
     * @param   int         $iMinLength             are the range of oligo lengths to be searched; p.e. oligos with length 2 to 6
     * @param   int         $iMaxLength             are the range of oligo lengths to be searched; p.e. oligos with length 2 to 6
     * @param   int         $iMinRepeats            minimal number of time a sequence must be repeated to be considered as a microsatellite repeat
     * @param   int         $iMinLengthOMR          minimum length of tandem repeat; to avoid considering AAAA as a microsatellite repeat, set it to >4
     * @param   int         $iMismatchesAllowed     the porcentage of errors allowed when searching in the repetitive sequence
     * @return  array
     * @throws  \Exception
     */
    public function findMicrosatelliteRepeats($sSequence, $iMinLength, $iMaxLength, $iMinRepeats, $iMinLengthOMR, $iMismatchesAllowed)
    {
        try {
            $iLenSeq = strlen($sSequence);
            $iCounter = 0;
            $aResults = [];

            for ($i = 0; $i < $iLenSeq-3; $i++) {
                for($j = $iMinLength; $j < $iMaxLength+1; $j++) {
                    if(($i+$j) > $iLenSeq) {
                        break;
                    }
                    $sSubSeq = substr($sSequence,$i,$j);
                    $iLenSubSeq = strlen($sSubSeq);
                    $mismatches = floor($iLenSubSeq * $iMismatchesAllowed / 100);

                    switch($mismatches) {
                        case 1:
                            $sSubSeqPattern = $this->includeN1($sSubSeq,0);
                            break;
                        case 2:
                        case 3:
                            $sSubSeqPattern = $this->includeNPlus1($sSubSeq,0);
                            break;
                        default:
                            $sSubSeqPattern = $sSubSeq;
                    }

                    $iMatches = 1;
                    while(preg_match_all("/($sSubSeqPattern)/",substr($sSequence,($i+$j*$iMatches),$j),$out) == 1) {
                        $iMatches ++;
                    }

                    if($iMatches >= $iMinRepeats && ($j * $iMatches) >= $iMinLengthOMR) {
                        $aResults[$iCounter]["start_position"] = $i;
                        $aResults[$iCounter]["length"] = $j;
                        $aResults[$iCounter]["repeats"] = $iMatches;
                        $aResults[$iCounter]["sequence"] = substr($sSequence, $i,$j * $iMatches);
                        $iCounter ++;
                        $i += $j * $iMatches;
                    }
                }
            }
            return($aResults);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }


    /**
     * When a DNA sequence ("$primer") is provided to this function, as for example "acgt", this function will return
     * a pattern like ".cgt|a.gt|ac.t|acg.". This pattern may be useful to find within a DNA sequence
     * subsequences matching $primer, but allowing one missmach. The parameter $minus
     * is a numeric value which indicates number of bases always maching  the DNA sequence in 3' end.
     * For example, when $minus is 1, the pattern for "acgt" will be  ".cgt|a.gt|ac.t".
     * Check also IncludeN2 and IncludeN3.
     * @param   string      $sPrimer     DNA sequence (oligonucleotide, primer)
     * @param   int         $iMinus      indicates number of bases in 3' which will always much the DNA sequence.
     * @return  string                   pattern
     * @throws \Exception
     */
    public function includeN1($sPrimer, $iMinus)
    {
        try {
            $sCode = ".".substr($sPrimer,1);
            $iWpos = 1;
            while($iWpos < strlen($sPrimer) - $iMinus) {
                $sCode .= "|".substr($sPrimer,0,$iWpos).".".substr($sPrimer,$iWpos + 1);
                $iWpos ++;
            }
            return $sCode;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }


    /**
     * Similar to function IncludeN1. When a DNA sequence ("$primer") is provided to this function, as for example "acgt",
     * this function will return a pattern like "..gt|.c.t|.cg.|a..t|a.g.|ac..". This pattern may be useful to find within
     * a DNA sequence subsequences matching $primer, but allowing two missmaches. The parameter $minus
     * is a numeric value which indicates number of bases always maching  the DNA sequence in 3' end.
     * For example, when $minus is 1, the pattern for "acgt" will be  "..gt|.c.t|a..t".
     * Check also IncludeN1 and IncludeN3.
     * @param   string      $sPrimer     DNA sequence (oligonucleotide, primer)
     * @param   string      $iMinus      Number of bases in 3' which will always much the DNA sequence.
     * @return  string                   Pattern
     * @throws  \Exception
     */
    public function includeNPlus1($sPrimer, $iMinus)
    {
        try {
            $iMax = strlen($sPrimer) - $iMinus;
            $sCode = "";
            for($i = 0; $i < $iMax; $i++) {
                for($j = 0; $j < $iMax - $i - 1; $j++) {
                    $sCode .= "|".substr($sPrimer,0,$i).".";
                    $sResto = substr($sPrimer,$i+1);
                    $sCode .= substr($sResto,0,$j).".".substr($sResto,$j+1);
                }
            }

            $sCode = substr($sCode,1);
            return $sCode;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
}