<?php
/**
 * Sequence Alignment Functions
 * @author Amélie DUVERNET akka Amelaye
 * Inspired by BioPHP's project biophp.org
 * Created 28 february 2019
 * Last modified 28 february 2019
 * RIP Pasha, gone 27 february 2019 =^._.^= ∫
 */
namespace MinitoolsBundle\Service;

class SequenceAlignmentManager
{
    public function alignDNA($seqa, $seqb)
    {
        $match = 2;
        $mismatch = -1;
        $gap = -4;
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
            for ($j=0; $j < $maxb; $j++) {
                if($b[$j] == $a[$i]) {
                    $x = $matriz[$j-1][$i-1] + $match;
                } else {
                    $x = max(0,$matriz[$j-1][$i-1]-1,$matriz[$j][$i-1]-4,$matriz[$j-1][$i]-4);
                }
                $matriz[$j][$i] = $x;
                if ($mx<$x) {
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
            $aa = $matriz[$j-1][$i-1];
            $ab = $matriz[$j][$i-1];
            $ac = $matriz[$j-1][$i];
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
            $aa = $matriz[$j+1][$i+1];
            $ab = $matriz[$j][$i+1];
            $ac = $matriz[$j+1][$i];
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

        while ($i < strlen($seqa)-2 && $j < strlen($seqb)-2 && $t = 1) {
            $t = 0;
            if($matrizz[$j+1][$i+1] == 1) {
                $t=1;
                $sseqa .= $a[$i];
                $sseqb .= $b[$j];
                $i = $i+1;
                $j = $j+1;
            }
            if($matrizz[$j][$i+1] == 1) {
                $t = 1;
                $sseqa .= $a[$i];
                $sseqb .= "-";
                $i = $i+1;
            }
            if($matrizz[$j+1][$i] == 1) {
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
        $PAM250 = [
            "CC" => 12,

            "SC" => 0, "SS" => 2,
            "CS" => 0,

            "TC" => -2, "TS" => 1, "TT" => 3,
            "CT" => -2, "ST" => 1,

            "PC" => -3, "PS" => 1, "PT" => 0, "PP" => 6,
            "CP" => -3, "SP" => 1, "TP" => 0,

            "AC" => -2, "AS" => 1, "AT" => 1, "AP" => 1, "AA" => 2,
            "CA" => -2, "SA" => 1, "TA" => 1, "PA" => 1,

            "GC" => -3, "GS" => 1, "GT" => 0, "GP" => -1, "GA" => 1, "GG" => 5,
            "CG" => -3, "SG" => 1, "TG" => 0, "PG" => -1, "AG" => 1,

            "NC" => -4, "NS" => 1, "NT" => 0, "NP" => -1, "NA" => 0, "NG" => 0, "NN" => 2,
            "CN" => -4, "SN" => 1, "TN" => 0, "PN" => -1, "AN" => 0, "GN" => 0,

            "DC" => -5, "DS" => 0, "DT" => 0, "DP" => -1, "DA" => 0, "DG" => 1, "DN" => 2, "DD" => 4,
            "CD" => -5, "SD" => 0, "TD" => 0, "PD" => -1, "AD" => 0, "GD" => 1, "ND" => 2,

            "EC" => -5, "ES" => 0, "ET" => 0, "EP" => -1, "EA" => 0, "EG" => 0, "EN" => 1, "ED" => 3, "EE" => 4,
            "CE" => -5, "SE" => 0, "TE" => 0, "PE" => -1, "AE" => 0, "GE" => 0, "NE" => 1, "DE" => 3,

            "QC" => -5, "QS" => -1, "QT" => -1, "QP" => 0, "QA" => 0, "QG" => -1, "QN" => 1, "QD" => 2, "QE" => 2, "QQ" => 4,
            "CQ" => -5, "SQ" => -1, "TQ" => -1, "PQ" => 0, "AQ" => 0, "GQ" => -1, "NQ" => 1, "DQ" => 2, "EQ" => 2,

            "HC" => -3, "HS" => -1, "HT" => -1, "HP" => 0, "HA" => -1, "HG" => -2, "HN" => 2, "HD" => 1, "HE" => 1, "HQ" => 3, "HH" => 6,
            "CH" => -3, "SH" => -1, "TH" => -1, "PH" => 0, "AH" => -1, "GH" => -2, "NH" => 2, "DH" => 1, "EH" => 1, "QH" => 3,

            "RC" => -4, "RS" => 0, "RT" => -1, "RP" => 0, "RA" => -2, "RG" => -3, "RN" => 0, "RD" => -1, "RE" => -1, "RQ" => 1, "RH" => 2, "RR" => 6,
            "CR" => -4, "SR" => 0, "TR" => -1, "PR" => 0, "AR" => -2, "GR" => -3, "NR" => 0, "DR" => -1, "ER" => -1, "QR" => 1, "HR" => 2,

            "KC" => -5, "KS" => 0, "KT" => 0, "KP" => -1, "KA" => -1, "KG" => -2, "KN" => 1, "KD" => 0, "KE" => 0, "KQ" => 1, "KH" => 0, "KR" => 3, "KK" => 5,
            "CK" => -5, "SK" => 0, "TK" => 0, "PK" => -1, "AK" => -1, "GK" => -2, "NK" => 1, "DK" => 0, "EK" => 0, "QK" => 1, "HK" => 0, "RK" => 3,

            "MC" => -5, "MS" => -2, "MT" => -1, "MP" => -2, "MA" => -1, "MG" => -3, "MN" => -2, "MD" => -3, "ME" => -2, "MQ" => -1, "MH" => -2, "MR" => 0, "MK" => 0, "MM" => 6,
            "CM" => -5, "SM" => -2, "TM" => -1, "PM" => -2, "AM" => -1, "GM" => -3, "NM" => -2, "DM" => -3, "EM" => -2, "QM" => -1, "HM" => -2, "RM" => 0, "KM" => 0,

            "IC" => -2, "IS" => -1, "IT" => 0, "IP" => -2, "IA" => -1, "IG" => -3, "IN" => -2, "ID" => -2, "IE" => -2, "IQ" => -2, "IH" => -2, "IR" => -2, "IK" => -2, "IM" => 2, "II" => 5,
            "CI" => -2, "SI" => -1, "TI" => 0, "PI" => -2, "AI" => -1, "GI" => -3, "NI" => -2, "DI" => -2, "EI" => -2, "QI" => -2, "HI" => -2, "RI" => -2, "KI" => -2, "MI" => 2,

            "LC" => -6, "LS" => -3, "LT" => -2, "LP" => -3, "LA" => -2, "LG" => -4, "LN" => -3, "LD" => -4, "LE" => -3, "LQ" => -2, "LH" => -2, "LR" => -3, "LK" => -3, "LM" => 4, "LI" => 2, "LL" => 6,
            "CL" => -6, "SL" => -3, "TL" => -2, "PL" => -3, "AL" => -2, "GL" => -4, "NL" => -3, "DL" => -4, "EL" => -3, "QL" => -2, "HL" => -2, "RL" => -3, "KL" => -3, "ML" => 4, "IL" => 2,

            "VC" => -2, "VS" => -1, "VT" => 0, "VP" => -1, "VA" => 0, "VG" => -1, "VN" => -2, "VD" => -2, "VE" => -2, "VQ" => -2, "VH" => -2, "VR" => -2, "VK" => -2, "VM" => 2, "VI" => 4, "VL" => 2, "VV" => 4,
            "CV" => -2, "SV" => -1, "TV" => 0, "PV" => -1, "AV" => 0, "GV" => -1, "NV" => -2, "DV" => -2, "EV" => -2, "QV" => -2, "HV" => -2, "RV" => -2, "KV" => -2, "MV" => 2, "IV" => 4, "LV" => 2,

            "FC" => -4, "FS" => -3, "FT" => -3, "FP" => -5, "FA" => -4, "FG" => -5, "FN" => -4, "FD" => -6, "FE" => -5, "FQ" => -5, "FH" => -2, "FR" => -4, "FK" => -5, "FM" => 0, "FI" => 1, "FL" => 2, "FV" => -1, "FF" => 9,
            "CF" => -4, "SF" => -3, "TF" => -3, "PF" => -5, "AF" => -4, "GF" => -5, "NF" => -4, "DF" => -6, "EF" => -5, "QF" => -5, "HF" => -2, "RF" => -4, "KF" => -5, "MF" => 0, "IF" => 1, "LF" => 2, "VF" => -1,

            "YC" => 0, "YS" => -3, "YT" => -3, "YP" => -5, "YA" => -3, "YG" => -5, "YN" => -2, "YD" => -4, "YE" => -4, "YQ" => -4, "YH" => 0, "YR" => -4, "YK" => -4, "YM" => -2, "YI" => -1, "YL" => -1, "YV" => -2, "YF" => 7, "YY" => 10,
            "CY" => 0, "SY" => -3, "TY" => -3, "PY" => -5, "AY" => -3, "GY" => -5, "NY" => -2, "DY" => -4, "EY" => -4, "QY" => -4, "HY" => 0, "RY" => -4, "KY" => -4, "MY" => -2, "IY" => -1, "LY" => -1, "VY" => -2, "FY" => 7,

            "WC" => -8, "WS" => -2, "WT" => -5, "WP" => -6, "WA" => -6, "WG" => -7, "WN" => -4, "WD" => -7, "WE" => -7, "WQ" => -5, "WH" => 3, "WR" => 2, "WK" => -3, "WM" => -4, "WI" => -5, "WL" => -2, "WV" => -6, "WF" => 0, "WY" => 0, "WW" => 17,
            "CW" => -8, "SW" => -2, "TW" => -5, "PW" => -6, "AW" => -6, "GW" => -7, "NW" => -4, "DW" => -7, "EW" => -7, "QW" => -5, "HW" => 3, "RW" => 2, "KW" => -3, "MW" => -4, "IW" => -5, "LW" => -2, "VW" => -6, "FW" => 0, "YW" => 0,

        ];

        $gap = -50;
        $arraya = preg_split('//', $seqa, -1, PREG_SPLIT_NO_EMPTY);
        $arrayb = preg_split('//', $seqb, -1, PREG_SPLIT_NO_EMPTY);
        $maxa=sizeof($arraya);
        $maxb=sizeof($arrayb);
        $a=$arraya;
        $lenn=$maxa;
        if($maxb>$lenn){
            $lenn=$maxb;
        }
        $b=$arrayb;
        for ($i=0;$i<$maxa;$i++){
            $matriz[0][$i]=$PAM250["$a[$i]$b[0]"];
        }
        for ($i=0;$i<$maxb;$i++){
            $matriz[$i][0]=$PAM250["$b[$i]$a[0]"];
        }
        for ($i=1;$i<$maxa;$i++){
            for ($j=1;$j<$maxb;$j++){
                if($b[$j]==$a[$i]){
                    $x=$matriz[$j-1][$i-1]+$PAM250["$b[$j]$a[$i]"];//$x=$matriz[$j-1][$i-1]+$match;
                }else{
                    $x=$matriz[$j-1][$i-1]+$PAM250["$b[$j]$a[$i]"];//$x=$matriz[$j-1][$i-1]+$mismatch;
                    $y=$matriz[$j][$i-1]+$gap;
                    if($y>$x){$x=$y;}
                    $y=$matriz[$j-1][$i]+$gap;
                    if($y>$x){$x=$y;}
                    if($x<0){$x=0;}
                }
                $matriz[$j][$i]=$x;
                $x=0;
            }//end for $j
        }
        $mx=0;
        for ($i=0;$i<$maxa;$i++){
            for ($j=0;$j<$maxb;$j++){
                if($mx<$matriz[$j][$i]){
                    $mx=$matriz[$j][$i];
                    $mj=$j;
                    $mi=$i;
                }
            }
        }
        $j=$mj;
        $i=$mi;
        $matrizz[$j][$i]=1;//matrixx(n, m) = 1
        while ($i>0 or $j>0):
            $aa=$matriz[$j-1][$i-1];//a = matrix(n - 1, m - 1)
            $ab=$matriz[$j][$i-1];//b = matrix(n, m - 1)
            $ac=$matriz[$j-1][$i];//c = matrix(n - 1, m)
            if($aa<>'//' or $aa==0){//If a <> "" Then
                if($aa>=$ab and $aa>=$ac){//If a >= b And a >= c Then
                    $j=$j-1;//    n = n - 1: m = m - 1
                    $i=$i-1;
                }
                if($ab>$aa){//If b > a Then m = m - 1
                    $i=$i-1;
                }
                if($ac>$aa){//If c > a Then n = n - 1
                    $j=$j-1;
                }
            }else{//If a = "" Then
                if($ab<>'//' or $ab==0){//    If b <> "" Then m = m - 1
                    $i=$i-1;
                }
                if($ac<>'//' or $ac==0){//    If c <> "" Then n = n - 1
                    $j=$j-1;
                }
            }//End If
            if($j<0){//If n = 0 Then n = 1
                $j=0;
            }
            if($i<0){//If m = 0 Then m = 1
                $i=0;
            }
            $matrizz[$j][$i]=1;//matrixx(n, m) = 1
        endwhile;
        $j=$mj;//n = mn
        $i=$mi;//m = mm

        while ($i<strlen($seqa)-1 or $j<strlen($seqb)-1):
            $aa=$matriz[$j+1][$i+1];////a = matrix(n + 1, m + 1)
            $ab=$matriz[$j][$i+1];//b = matrix(n, m + 1)
            $ac=$matriz[$j+1][$i];//c = matrix(n + 1, m)
            if($aa<>'//' or $aa==0){//If a <> "" Then
                if($aa>=$ab and $aa>=$ac){//If a >= b And a >= c Then
                    $j=$j+1;//    n = n - 1: m = m - 1
                    $i=$i+1;
                }
                if($ab>$aa){//If b > a Then m = m - 1
                    $i=$i+1;
                }
                if($ac>$aa){//If c > a Then n = n - 1
                    $j=$j+1;
                }
            }else{//If a = "" Then
                if($ab<>'//' or $ab==0){//    If b <> "" Then m = m - 1
                    $i=$i+1;
                }
                if($ac<>'//' or $ac==0){//    If c <> "" Then n = n - 1
                    $j=$j+1;
                }
            }
            if($j>$lenn){//If n > lenn Then n = lenn
                $j=$lenn;
            }
            if($i>$lenn){//If m > lenn Then m = lenn
                $i=$lenn;
            }
            $matrizz[$j][$i]=1;//matrixx(n, m) = 1
        endwhile;
        $j=0;
        $i=0;
        $t=1;
        while ($i<strlen($seqa)-2 and $j<strlen($seqb)-2 and $t=1):
            $t=0;
            if($matrizz[$j+1][$i+1]==1){
                $t=1;
                $sseqa.=$a[$i];
                $sseqb.=$b[$j];
                $i=$i+1;
                $j=$j+1;
            }//else{
            if($matrizz[$j][$i+1]==1){
                $t=1;
                $sseqa.=$a[$i];
                $sseqb.="-";
                $i=$i+1;
            }//else{
            if($matrizz[$j+1][$i]==1){
                $t=1;
                $sseqa.="-";
                $sseqb.=$b[$j];
                $j=$j+1;
            }//}}
        endwhile;
        if($matrizz[$j+1][$i+1]==1){
            $sseqa.=$a[$i];
            $sseqb.=$b[$j];
            $i=$i+1;
            $j=$j+1;
            $t=1;
        }
        if($t==0){
            if($matrizz[$j][$i+1]==1){
                $sseqa.=$a[$i];
                $sseqb.="-";
                $i=$i+1;
            }
        }
        if($t==0){
            if($matrizz[$j+1][$i]==1){
                $sseqa.="-";
                $sseqb.=$b[$j];
                $j=$j+1;
            }
        }
        if($j+1==$maxb){
            for ($ii=$i;$ii<$maxa;$ii++){
                $sseqa.=$a[$ii];
            }
            $sseqb.=$b[$j];
            for ($ii=$i;$ii<$maxa-1;$ii++){
                $sseqb.="-";
            }
        }
        if($i+1==$maxa){
            for ($ii=$j;$ii<$maxb;$ii++){
                $sseqb.=$b[$ii];
            }
            $sseqa.=$a[$i];
            for ($ii=$j;$ii<$maxb-1;$ii++){
                $sseqa.="-";
            }
        }
        $results["seqa"]=$sseqa;
        $results["seqb"]=$sseqb;
        return $results;
    }

// ########################################################################################
    function compare_alignment($seqa,$seqb){
        for ($i=0;$i<strlen($seqa);$i++){
            if(substr($seqa,$i,1)==substr($seqb,$i,1)){$compare.="|";}else{$compare.=" ";}
        }
        return $compare;
    }
}