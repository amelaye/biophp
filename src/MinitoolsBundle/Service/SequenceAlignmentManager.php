<?php
/**
 * Inspired by BioPHP's project biophp.org
 * Created 28 february 2019
 * Last modified 20 april 2019
 * RIP Pasha, gone 27 february 2019 =^._.^= ∫
 */
namespace MinitoolsBundle\Service;

/**
 * Class SequenceAlignmentManager : Sequence Alignment Functions
 * @package MinitoolsBundle\Service
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class SequenceAlignmentManager
{
    /**
     * @var array
     */
    private $pam250Matrix;

    /**
     * SequenceAlignmentManager constructor.
     * @param   array   $pam250Matrix
     */
    public function __construct($pam250Matrix)
    {
        $this->pam250Matrix = $pam250Matrix;
    }


    /**
     * @param $seqa
     * @param $seqb
     * @return mixed
     */
    public function alignDNA($seqa, $seqb)
    {
        $match = 2;
        //$mismatch = -1;
        //$gap = -4;
        $matriz = array();

        $a = preg_split('//', $seqa, -1, PREG_SPLIT_NO_EMPTY);
        $b = preg_split('//', $seqb, -1, PREG_SPLIT_NO_EMPTY);
        $maxa = sizeof($a);
        $maxb = sizeof($b);
        $lenn = max($maxa,$maxb);

        // Creaci�n de la matriz
        // He reducido el c�digo para hacerlo mas simple y rapido, pero tan solo ahorra un 20% del tiempo
        // Con matrices muy grandes, PHP no sabe trabajar muy bien (es poco eficaz).
        $mx = 0;
        for($i = 0; $i < $maxa; $i ++) {
            for ($j = 0; $j < $maxb; $j++) {
                if($b[$j] == $a[$i]) {
                    if(!isset($matriz[$j-1][$i - 1])) {
                        $x = 0 + $match;
                    } else {
                        $x = $matriz[$j - 1][$i - 1] + $match;
                    }
                } else {
                    if(isset($matriz[$j-1][$i-1])) {
                        $value2 = $matriz[$j-1][$i-1] - 1;
                    } else {
                        $value2 = - 1;
                    }

                    if(isset($matriz[$j][$i-1])) {
                        $value3 = $matriz[$j][$i-1] - 4;
                    } else {
                        $value3 = -4;
                    }

                    if(isset($matriz[$j-1][$i])){
                        $value4 = $matriz[$j-1][$i] - 4;
                    } else {
                        $value4 = -4;
                    }

                    $x = max(0, $value2, $value3, $value4);

                }
                $matriz[$j][$i] = $x;
                if ($mx < $x) {
                    $mx = $x;
                    $mj = $j;
                    $mi = $i;
                }
            }
        }
        // Matriz terminada

        $j = $mj;
        $i = $mi;

        $matrizz[$j][$i] = 1;

        while ($i > 0 || $j > 0) {
            if(isset($matriz[$j-1][$i-1]))
                $aa = $matriz[$j-1][$i-1];
            else
                $aa = 0;

            if(isset($matriz[$j][$i-1]))
                $ab = $matriz[$j][$i-1];
            else
                $ab = 0;

            if(isset($matriz[$j-1][$i]))
                $ac = $matriz[$j-1][$i];
            else
                $ac = 0;

            if($aa != '//' || $aa == 0) {
                if($aa >= $ab && $aa >= $ac) {
                    $j = $j-1;
                    $i = $i-1;
                }
                if($ab > $aa) {
                    $i = $i-1;
                }
                if($ac > $aa) {
                    $j = $j-1;
                }
            } else {
                if($ab != '//') {
                    $i = $i-1;
                }
                if($ac != '//') {
                    $j = $j-1;
                }
            }
            if($j < 0) {
                $j = 0;
            }
            if($i<0) {
                $i = 0;
            }
            $matrizz[$j][$i] = 1;
        }


        $j = $mj;
        $i = $mi;

        while($i < strlen($seqa)-1 || $j < strlen($seqb)-1) {
            if(isset($matriz[$j+1][$i+1]))
                $aa = $matriz[$j+1][$i+1];
            else
                $aa = 0;

            if(isset($matriz[$j][$i+1]))
                $ab = $matriz[$j][$i+1];
            else
                $ab = 0;

            if(isset($matriz[$j+1][$i]))
                $ac = $matriz[$j+1][$i];
            else
                $ac = 0;

            if($aa != '//') {
                if($aa >= $ab && $aa >= $ac) {
                    $j = $j+1;
                    $i = $i+1;
                }
                if($ab > $aa) {
                    $i = $i+1;
                }
                if($ac > $aa) {
                    $j=$j+1;
                }
            } else {
                if($ab != '//') {
                    $i = $i+1;
                }
                if($ac != '//') {
                    $j = $j+1;
                }
            }
            if($j > $lenn) {
                $j = $lenn;
            }
            if($i > $lenn) {
                $i = $lenn;
            }
            $matrizz[$j][$i] = 1;
        }

        $j = 0;
        $i = 0;
        $t = 1;

        $sseqa = "";
        $sseqb = "";

        while ($i < strlen($seqa)-2 && $j < strlen($seqb)-2 && $t = 1) {
            $t = 0;
            if(isset($matrizz[$j+1][$i+1]) && $matrizz[$j+1][$i+1] == 1) {
                $t = 1;
                $sseqa .= $a[$i];
                $sseqb .= $b[$j];
                $i = $i+1;
                $j = $j+1;
            }
            if(isset($matrizz[$j][$i+1]) && $matrizz[$j][$i+1] == 1) {
                $t = 1;
                $sseqa .= $a[$i];
                $sseqb .= "-";
                $i = $i+1;
            }
            if(isset($matrizz[$j+1][$i]) && $matrizz[$j+1][$i] == 1) {
                $t = 1;
                $sseqa .= "-";
                $sseqb .= $b[$j];
                $j = $j+1;
            }
        }


        if($matrizz[$j+1][$i+1] == 1) {
            $sseqa .= $a[$i];
            $sseqb .= $b[$j];
            $i = $i+1;
            $j = $j+1;
            $t = 1;
        }
        if($t == 0 && $matrizz[$j][$i+1] == 1) {
            $sseqa .= $a[$i];
            $sseqb .= "-";
            $i = $i+1;
        }
        if($t == 0 && $matrizz[$j+1][$i] == 1) {
            $sseqa .= "-";
            $sseqb .= $b[$j];
            $j = $j+1;
        }
        if($i+1 == $maxa) {
            for($ii = $j; $ii < $maxb; $ii++) {
                $sseqb .= $b[$ii];
            }
            $sseqa .= $a[$i];
            for($ii = $j; $ii < $maxb-1; $ii++) {
                $sseqa .= "-";
            }
        }
        if($j+1 == $maxb) {
            for($ii = $i; $ii < $maxa; $ii++) {
                $sseqa .= $a[$ii];
            }
            $sseqb .= $b[$j];
            for($ii = $i; $ii < $maxa-1; $ii++) {
                $sseqb .= "-";
            }
        }

        // tengo que quitar la �ltima letra del alineamiento, de lo contrario, se repite la ultima posici�n
        // por que se da la repetici�n?
        $results["seqa"] = substr($sseqa,0,strlen($sseqa)-1);
        $results["seqb"] = substr($sseqb,0,strlen($sseqb)-1);

        return $results;
    }


    /**
     * @param $seqa
     * @param $seqb
     * @return mixed
     */
    public function alignProteins($seqa, $seqb)
    {
        $PAM250 = $this->pam250Matrix;

        $gap = -50;
        $arraya = preg_split('//', $seqa, -1, PREG_SPLIT_NO_EMPTY);
        $arrayb = preg_split('//', $seqb, -1, PREG_SPLIT_NO_EMPTY);
        $maxa = sizeof($arraya);
        $maxb = sizeof($arrayb);
        $a = $arraya;
        $lenn = $maxa;
        if($maxb > $lenn) {
            $lenn = $maxb;
        }
        $b = $arrayb;
        for($i = 0; $i < $maxa; $i++) {
            $matriz[0][$i] = $PAM250["$a[$i]$b[0]"];
        }
        for($i = 0; $i < $maxb; $i++){
            $matriz[$i][0] = $PAM250["$b[$i]$a[0]"];
        }
        for($i = 1; $i < $maxa; $i++) {
            for($j = 1; $j < $maxb; $j++) {
                if($b[$j] == $a[$i]) {
                    $x = $matriz[$j-1][$i-1] + $PAM250["$b[$j]$a[$i]"];//$x=$matriz[$j-1][$i-1]+$match;
                } else {
                    $x = $matriz[$j-1][$i-1] + $PAM250["$b[$j]$a[$i]"];//$x=$matriz[$j-1][$i-1]+$mismatch;
                    $y = $matriz[$j][$i-1] + $gap;
                    if($y > $x) {
                        $x = $y;
                    }
                    $y = $matriz[$j-1][$i] + $gap;
                    if($y > $x) {
                        $x = $y;
                    }
                    if($x < 0) {
                        $x = 0;
                    }
                }
                $matriz[$j][$i] = $x;
                $x = 0;
            } // end for $j
        }
        $mx = 0;
        for($i = 0; $i < $maxa; $i++) {
            for ($j = 0; $j < $maxb; $j++) {
                if($mx < $matriz[$j][$i]) {
                    $mx = $matriz[$j][$i];
                    $mj = $j;
                    $mi = $i;
                }
            }
        }
        $j = $mj;
        $i = $mi;
        $matrizz[$j][$i] = 1;                           // matrixx(n, m) = 1
        while ($i > 0 || $j > 0) {
            $aa = $matriz[$j-1][$i-1];                  // a = matrix(n - 1, m - 1)
            $ab = $matriz[$j][$i-1];                    // b = matrix(n, m - 1)
            $ac = $matriz[$j-1][$i];                    // c = matrix(n - 1, m)
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
            $matrizz[$j][$i] = 1;                       // matrixx(n, m) = 1
        }

        $j = $mj; //n = mn
        $i = $mi; //m = mm

        while($i < strlen($seqa)-1 || $j < strlen($seqb)-1) {
            $aa = $matriz[$j+1][$i+1];                  //a = matrix(n + 1, m + 1)
            $ab = $matriz[$j][$i+1];                    //b = matrix(n, m + 1)
            $ac = $matriz[$j+1][$i];                    //c = matrix(n + 1, m)
            if($aa != '//' || $aa == 0) {               //If a <> "" Then
                if($aa >= $ab && $aa >= $ac) {          //If a >= b And a >= c Then
                    $j = $j + 1;                        // n = n - 1: m = m - 1
                    $i = $i + 1;
                }
                if($ab > $aa) {                         //If b > a Then m = m - 1
                    $i = $i + 1;
                }
                if($ac > $aa) {                         //If c > a Then n = n - 1
                    $j = $j + 1;
                }
            } else {                                    //If a = "" Then
                if($ab != '//' or $ab == 0) {           //    If b <> "" Then m = m - 1
                    $i = $i + 1;
                }
                if($ac != '//' or $ac == 0) {           //    If c <> "" Then n = n - 1
                    $j = $j + 1;
                }
            }
            if($j > $lenn) {                            //If n > lenn Then n = lenn
                $j = $lenn;
            }
            if($i > $lenn) {                            //If m > lenn Then m = lenn
                $i = $lenn;
            }
            $matrizz[$j][$i] = 1;                       //matrixx(n, m) = 1
        }


        $j = 0;
        $i = 0;
        $t = 1;
        while ($i < strlen($seqa)-2 && $j < strlen($seqb)-2 && $t = 1) {
            $t = 0;
            if($matrizz[$j+1][$i+1] == 1) {
                $t = 1;
                $sseqa .= $a[$i];
                $sseqb .= $b[$j];
                $i = $i + 1;
                $j = $j + 1;
            }
            if($matrizz[$j][$i+1] == 1) {
                $t = 1;
                $sseqa .= $a[$i];
                $sseqb .= "-";
                $i = $i + 1;
            }
            if($matrizz[$j+1][$i] == 1) {
                $t = 1;
                $sseqa .= "-";
                $sseqb .= $b[$j];
                $j = $j + 1;
            }
        }

        if($matrizz[$j+1][$i+1] == 1) {
            $sseqa .= $a[$i];
            $sseqb .= $b[$j];
            $i = $i + 1;
            $j = $j + 1;
            $t = 1;
        }
        if($t == 0) {
            if($matrizz[$j][$i+1] == 1) {
                $sseqa .= $a[$i];
                $sseqb .= "-";
                $i = $i + 1;
            }
        }
        if($t == 0) {
            if($matrizz[$j+1][$i] ==1 ) {
                $sseqa .= "-";
                $sseqb .= $b[$j];
                $j = $j + 1;
            }
        }
        if($j+1 == $maxb) {
            for($ii = $i; $ii < $maxa; $ii++) {
                $sseqa .= $a[$ii];
            }
            $sseqb .= $b[$j];
            for($ii = $i; $ii < $maxa-1; $ii++) {
                $sseqb .= "-";
            }
        }
        if($i+1 == $maxa) {
            for($ii = $j; $ii < $maxb; $ii++) {
                $sseqb .= $b[$ii];
            }
            $sseqa .= $a[$i];
            for($ii = $j; $ii < $maxb-1; $ii++) {
                $sseqa .= "-";
            }
        }
        $results["seqa"] = $sseqa;
        $results["seqb"] = $sseqb;
        return $results;
    }

    /**
     * @param $seqa
     * @param $seqb
     * @return string
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