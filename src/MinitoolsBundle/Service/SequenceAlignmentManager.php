<?php
/**
 * Inspired by BioPHP's project biophp.org
 * Created 28 february 2019
 * Last modified 20 august 2019
 * RIP Pasha, gone 27 february 2019 =^._.^= ∫
 */
namespace MinitoolsBundle\Service;

use AppBundle\Bioapi\Bioapi;

/**
 * Class SequenceAlignmentManager : Sequence Alignment Functions
 * @package MinitoolsBundle\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class SequenceAlignmentManager
{
    /**
     * @var array
     */
    private $pam250Matrix;

    /**
     * SequenceAlignmentManager constructor.
     * @param   Bioapi   $bioapi
     */
    public function __construct(Bioapi $bioapi)
    {
        $this->pam250Matrix = $bioapi->getPam250Matrix();
    }

    /**
     * First step before generate ADN matrix
     * @param       array       $aTempMatrix    Temporary matrix array
     * @param       int         $mj
     * @param       int         $mi
     * @param       int         $iMaxA
     * @param       int         $iMaxB
     * @param       array       $aSequenceA
     * @param       array       $aSequenceB
     * @param       int         $iMatch
     * @return      int
     * @throws      \Exception
     */
    public function step1(&$aTempMatrix, &$mj, &$mi, $iMaxA, $iMaxB, $aSequenceA, $aSequenceB, $iMatch)
    {
        try {
            $mx = 0;
            for($i = 0; $i < $iMaxA; $i ++) {
                for ($j = 0; $j < $iMaxB; $j++) {
                    if($aSequenceB[$j] == $aSequenceA[$i]) {
                        $x = (!isset($aTempMatrix[$j-1][$i - 1])) ? (0 + $iMatch) : ($aTempMatrix[$j - 1][$i - 1] + $iMatch);
                    } else {
                        $iValue2 = isset($aTempMatrix[$j-1][$i-1]) ? $aTempMatrix[$j-1][$i-1] - 1 : -1;
                        $iValue3 = isset($aTempMatrix[$j][$i-1]) ? $aTempMatrix[$j][$i-1] - 4 : -4;
                        $iValue4 = isset($aTempMatrix[$j-1][$i]) ? $aTempMatrix[$j-1][$i] - 4 : -4;
                        $x = max(0, $iValue2, $iValue3, $iValue4);
                    }
                    $aTempMatrix[$j][$i] = $x;
                    if ($mx < $x) {
                        $mx = $x;
                        $mj = $j;
                        $mi = $i;
                    }
                }
            }
            return $mx;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * First step before generate protein matrix
     * @param       array       $aMatrix
     * @param       int         $mj
     * @param       int         $mi
     * @param       int         $iMaxA
     * @param       int         $iMaxb
     * @param       array       $aSequenceA
     * @param       array       $aSequenceB
     * @return      int
     * @throws      \Exception
     */
    public function step1Protein(&$aMatrix, &$mj, &$mi, $iMaxA, $iMaxb, $aSequenceA, $aSequenceB)
    {
        try {
            $PAM250 = $this->pam250Matrix;
            $iGap = -50;

            for($i = 0; $i < $iMaxA; $i++) {
                $aMatrix[0][$i] = $PAM250["$aSequenceA[$i]$aSequenceB[0]"];
            }
            for($i = 0; $i < $iMaxb; $i++){
                $aMatrix[$i][0] = $PAM250["$aSequenceB[$i]$aSequenceA[0]"];
            }
            for($i = 1; $i < $iMaxA; $i++) {
                for($j = 1; $j < $iMaxb; $j++) {
                    if($aSequenceB[$j] == $aSequenceA[$i]) {
                        $x = $aMatrix[$j-1][$i-1] + $PAM250["$aSequenceB[$j]$aSequenceA[$i]"];//$x=$matriz[$j-1][$i-1]+$match;
                    } else {
                        $x = $aMatrix[$j-1][$i-1] + $PAM250["$aSequenceB[$j]$aSequenceA[$i]"];//$x=$matriz[$j-1][$i-1]+$mismatch;
                        $y = $aMatrix[$j][$i-1] + $iGap;
                        if($y > $x) {
                            $x = $y;
                        }
                        $y = $aMatrix[$j-1][$i] + $iGap;
                        if($y > $x) {
                            $x = $y;
                        }
                        if($x < 0) {
                            $x = 0;
                        }
                    }
                    $aMatrix[$j][$i] = $x;
                    $x = 0;
                } // end for $j
            }
            $mx = 0;
            for($i = 0; $i < $iMaxA; $i++) {
                for ($j = 0; $j < $iMaxb; $j++) {
                    if($mx < $aMatrix[$j][$i]) {
                        $mx = $aMatrix[$j][$i];
                        $mj = $j;
                        $mi = $i;
                    }
                }
            }
            return $mx;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * First step of the matrix array filling
     * @param       array       $aTempMatrix    Temporary matrix array
     * @param       int         $j
     * @param       int         $i
     * @return      array
     * @throws      \Exception
     */
    public function fillMatrix($aTempMatrix, $j, $i)
    {
        try {
            $aMyMatrix[$j][$i] = 1;                         // matrixx(n, m) = 1

            while ($i > 0 || $j > 0) {
                $aa = $aTempMatrix[$j-1][$i-1]  ?? 0;       // a = matrix(n - 1, m - 1)
                $ab = $aTempMatrix[$j][$i-1]    ?? 0;       // b = matrix(n, m - 1)
                $ac = $aTempMatrix[$j-1][$i]    ?? 0;       // c = matrix(n - 1, m)
                if($aa != '//' || $aa == 0) {               // If a <> "" Then
                    if($aa >= $ab && $aa >= $ac) {          // If a >= b And a >= c Then
                        $j = $j - 1;                        // n = n - 1: m = m - 1
                        $i = $i - 1;
                    }
                    if($ab > $aa) {                         // If b > a Then m = m - 1
                        $i = $i - 1;
                    }
                    if($ac > $aa) {                         // If c > a Then n = n - 1
                        $j = $j - 1;
                    }
                } else {                                    // If a = "" Then
                    if($ab != '//' || $ab == 0) {           // If b <> "" Then m = m - 1
                        $i = $i - 1;
                    }
                    if($ac != '//' || $ac == 0) {           // If c <> "" Then n = n - 1
                        $j = $j - 1;
                    }
                }
                if($j < 0) {                                // If n = 0 Then n = 1
                    $j = 0;
                }
                if($i < 0) {                                // If m = 0 Then m = 1
                    $i = 0;
                }
                $aMyMatrix[$j][$i] = 1;                     // matrixx(n, m) = 1
            }
            return $aMyMatrix;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }


    /**
     * Second step to fill the matrix
     * @param       array       $aMyMatrix      Matrix array to fill
     * @param       int         $i              Size of the first sequence
     * @param       int         $j              Size of the second sequence
     * @param       array       $aTempMatrix    Temporary matrix array
     * @param       string      $sSequenceA     First sequence
     * @param       string      $sSequenceB     Second sequence
     * @param       int         $iLength
     * @return      array
     * @throws      \Exception
     */
    public function fillMatrix2($aMyMatrix, $i, $j, $aTempMatrix, $sSequenceA, $sSequenceB, $iLength)
    {
        try {
            while($i < strlen($sSequenceA) - 1 || $j < strlen($sSequenceB) - 1) {
                $aa = $aTempMatrix[$j+1][$i+1]   ?? 0;                  //a = matrix(n + 1, m + 1)
                $ab = $aTempMatrix[$j][$i+1]     ?? 0;                  //b = matrix(n, m + 1)
                $ac = $aTempMatrix[$j+1][$i]     ?? 0;                  //c = matrix(n + 1, m)
                if($aa != '//' || $aa == 0) {                           //If a <> "" Then
                    if($aa >= $ab && $aa >= $ac) {                      //If a >= b And a >= c Then
                        $j = $j + 1;                                    // n = n - 1: m = m - 1
                        $i = $i + 1;
                    }
                    if($ab > $aa) {                                     //If b > a Then m = m - 1
                        $i = $i + 1;
                    }
                    if($ac > $aa) {                                     //If c > a Then n = n - 1
                        $j = $j + 1;
                    }
                } else {                                                //If a = "" Then
                    if($ab != '//' or $ab == 0) {                       //    If b <> "" Then m = m - 1
                        $i = $i + 1;
                    }
                    if($ac != '//' or $ac == 0) {                       //    If c <> "" Then n = n - 1
                        $j = $j + 1;
                    }
                }
                if($j > $iLength) {                                     //If n > lenn Then n = lenn
                    $j = $iLength;
                }
                if($i > $iLength) {                                     //If m > lenn Then m = lenn
                    $i = $iLength;
                }
                $aMyMatrix[$j][$i] = 1;                                 //matrixx(n, m) = 1
            }
            return $aMyMatrix;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * We get the last letter of the amino, his opposite, repeat the last position
     * @param       array       $aMatrix
     * @param       string      $sSequenceA
     * @param       string      $sSequenceB
     * @param       array       $aSequenceA
     * @param       array       $aSequenceB
     * @param       int         $iMaxA
     * @param       int         $iMaxB
     * @return      mixed
     * @throws      \Exception
     */
    public function generateResults($aMatrix, $sSequenceA, $sSequenceB, $aSequenceA, $aSequenceB, $iMaxA, $iMaxB, $bIsProt)
    {
        try {
            $i = $j = 0;
            $t = 1;

            $sSeqa = $sSeqb = "";

            while ($i < strlen($sSequenceA) - 2 && $j < strlen($sSequenceB) - 2 && $t = 1) {
                $t = 0;
                if(isset($aMatrix[$j+1][$i+1]) && $aMatrix[$j+1][$i+1] == 1) {
                    $t = 1;
                    $sSeqa .= $aSequenceA[$i];
                    $sSeqb .= $aSequenceB[$j];
                    $i = $i+1;
                    $j = $j+1;
                }
                if(isset($aMatrix[$j][$i+1]) && $aMatrix[$j][$i+1] == 1) {
                    $t = 1;
                    $sSeqa .= $aSequenceA[$i];
                    $sSeqb .= "-";
                    $i = $i+1;
                }
                if(isset($aMatrix[$j+1][$i]) && $aMatrix[$j+1][$i] == 1) {
                    $t = 1;
                    $sSeqa .= "-";
                    $sSeqb .= $aSequenceB[$j];
                    $j = $j+1;
                }
            }

            if($aMatrix[$j+1][$i+1] == 1) {
                $t = 1;
                $sSeqa .= $aSequenceA[$i];
                $sSeqb .= $aSequenceB[$j];
                $i = $i+1;
                $j = $j+1;
            }
            if($t == 0 && $aMatrix[$j][$i+1] == 1) {
                $sSeqa .= $aSequenceA[$i];
                $sSeqb .= "-";
                $i = $i+1;
            }
            if($t == 0 && $aMatrix[$j+1][$i] == 1) {
                $sSeqa .= "-";
                $sSeqb .= $aSequenceB[$j];
                $j = $j+1;
            }
            if($i+1 == $iMaxA) {
                for($ii = $j; $ii < $iMaxB; $ii++) {
                    $sSeqb .= $aSequenceB[$ii];
                }
                $sSeqa .= $aSequenceA[$i];
                for($ii = $j; $ii < $iMaxB-1; $ii++) {
                    $sSeqa .= "-";
                }
            }
            if($j+1 == $iMaxB) {
                for($ii = $i; $ii < $iMaxB; $ii++) {
                    $sSeqa .= $aSequenceA[$ii];
                }
                $sSeqb .= $aSequenceB[$j];
                for($ii = $i; $ii < $iMaxB-1; $ii++) {
                    $sSeqb .= "-";
                }
            }

            if(!$bIsProt) { // Case DNA
                $aResults["seqa"] = substr($sSeqa,0,strlen($sSeqa)-1);
                $aResults["seqb"] = substr($sSeqb,0,strlen($sSeqb)-1);
            } else { // Case protein
                $aResults["seqa"] = $sSeqa;
                $aResults["seqb"] = $sSeqb;
            }

            return $aResults;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Matrix Creation
     * That reduces the code to make it more simple and fast, but lag of 20%
     * With bigger matrixes, PHP can have a timeout error
     * @param       string      $sSequenceA     First sequence to analyse
     * @param       string      $sSequenceB     Second sequence to analyse
     * @return      array
     * @throws      \Exception
     */
    public function alignDNA($sSequenceA, $sSequenceB)
    {
        try {
            $iMatch = 2;
            $aMatrix = array();
            $j = $i = 0;

            $aSequenceA = preg_split('//', $sSequenceA, -1, PREG_SPLIT_NO_EMPTY);
            $aSequenceB = preg_split('//', $sSequenceB, -1, PREG_SPLIT_NO_EMPTY);
            $iMaxA = sizeof($aSequenceA);
            $iMaxB = sizeof($aSequenceB);
            $iLength = max($iMaxA, $iMaxB);

            $x = $this->step1($aMatrix, $j, $i, $iMaxA, $iMaxB, $aSequenceA, $aSequenceB, $iMatch); // Matrix created
            $aMyMatrix = $this->fillMatrix($aMatrix, $j, $i);
            $aMyMatrix = $this->fillMatrix2($aMyMatrix, $i, $j, $aMatrix, $sSequenceA, $sSequenceB, $iLength);
            $aResults = $this->generateResults($aMyMatrix, $sSequenceA, $sSequenceB, $aSequenceA, $aSequenceB, $iMaxA, $iMaxB, 0);

            return $aResults;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }


    /**
     * Matrix creation for Protein
     * @param       string      $sSequenceA
     * @param       string      $sSequenceB
     * @return      array
     * @throws      \Exception
     */
    public function alignProteins($sSequenceA, $sSequenceB)
    {
        try {
            $aMatrix = array();
            $j = $i = 0;

            $aSequenceA = preg_split('//', $sSequenceA, -1, PREG_SPLIT_NO_EMPTY);
            $aSequenceB = preg_split('//', $sSequenceB, -1, PREG_SPLIT_NO_EMPTY);
            $iMaxA = sizeof($aSequenceA);
            $iMaxB = sizeof($aSequenceB);

            $iLength = $iMaxA;
            if($iMaxB > $iLength) {
                $iLength = $iMaxB;
            }

            $mx = $this->step1Protein($aMatrix, $j, $i, $iMaxA, $iMaxB, $aSequenceA, $aSequenceB);
            $aMyMatrix = $this->fillMatrix($aMatrix, $j, $i);
            $aMyMatrix = $this->fillMatrix2($aMyMatrix, $i, $j, $aMatrix, $sSequenceA, $sSequenceB, $iLength);
            $aResults = $this->generateResults($aMyMatrix, $sSequenceA, $sSequenceB, $aSequenceA, $aSequenceB, $iMaxA, $iMaxB, 1);
            return $aResults;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * @param $seqa
     * @param $seqb
     * @return string
     * @todo : migrer vers fonction TWIG
     */
    public function compareAlignment($seqa,$seqb)
    {
        $compare = "";
        for($i = 0; $i < strlen($seqa); $i++) {
            if(substr($seqa,$i,1) == substr($seqb,$i,1)) {
                $compare .= "|";
            } else {
                $compare.=" ";
            }
        }
        return $compare;
    }
}