<!--
Developped by: 	Jose Manuel Gonz�lez Hern�ndez
			    Universidad de La Laguna, Tenerife, Spain

Smith-Waterman alignment is used. More info in the script.
-->

<html><head><title>Alignment of two DNA or RNA sequences</title></head>
<body bgcolor=FFFFFF>
<?php
// WHEN INFO IS REQUESTED ($_GET["action"]=="info", PRINT INFO AND FINISH
if ($_GET["action"]=="info"){
    print_info(); // this function is located at the end of the script
    die();        // after the info, die
}

// IF NOTHING HAS BEEN POSTED, PRINT THE FORM AND FINISH
if (!$_POST){
    print_form(); // this function is located at the end of the script
    die();        // after the form, die
}

// GET SEQUENCES AND PREPARE THEM
$seqa=strtoupper($_POST["sequence"]);
$seqb=strtoupper($_POST["sequence2"]);
$seqa=preg_replace("/\W|\d/", "", $seqa); // remove useless characters
$seqb=preg_replace("/\W|\d/", "", $seqb); // remove useless characters
$seqa=preg_replace("/U/", "T", $seqa);    // from RNA to DNA
$seqb=preg_replace("/U/", "T", $seqb);    // from RNA to DNA
$seqa=preg_replace("/X/", "N", $seqa);    // substitute X -> N
$seqb=preg_replace("/X/", "N", $seqb);    // substitute X -> N

// LIMIT SEQUENCE LENGTH TO LIMIT MEMORY USAGE
// THIS SCRIPT CREATES A BIG ARRAY THAT REQUIRES A HUGE AMOUNT OF MEMORY
// DO NOT USED SEQUENCES LONGER THAN 700 BASES EACH (1400 BASES FOR BOTH SEQUENCES)
// IN THIS DEMO, THE LIMIT HAS BEEN SET UP IN 300 BASES
$limit=300;
if ((strlen($seqa)+strlen($seqb))>$limit){die ("Error:<br>The maximum length of code accepted for both sequences is $limit nucleotides");}

// CHECK WHETHER THEY ARE DNA OR PROTEIN, AND ALIGN SEQUENCES
if ((substr_count($seqa,"A")+substr_count($seqa,"C")+substr_count($seqa,"G")+substr_count($seqa,"T"))>(strlen($seqa)/2)){
    // if A+C+G+T is at least half of the sequence, it is a DNA
    $alignment=align_DNA($seqa,$seqb);
}else{
    // else is protein
    $alignment=align_proteins($seqa,$seqb);
}

// EXTRACT DATA FROM ALIGNMENT
$align_seqa=$alignment["seqa"];
$align_seqb=$alignment["seqb"];

// COMPARE ALIGNMENTS
$compare=compare_alignment($align_seqa,$align_seqb);

// PRINT RESULTS
print "<div align=right><a href=\"".$_SERVER["PHP_SELF"]."\"><b>New alignment</b></a></div>\n";
print "<center><H2>Alignment of two DNA sequences</H2>\n";
print "<table><tr><td><HR><pre>\n";
$i=0;
while($i<strlen($align_seqa)){
    $ii=$i+100;
    if ($ii>strlen($align_seqa)){$ii=strlen($align_seqa);}
    print substr($align_seqa,$i,100)."  $ii\n";
    print substr($compare,$i,100)."\n";
    print substr($align_seqb,$i,100)."  $ii\n\n";
    $i+=100;
}
print "</pre><hr>\n";
print "</td></tr><tr><td align=center>";
print "<table><tr><td>";
print $_POST["id1"]."<br><input type=text value=\"$align_seqa\" size=100><p>";
print $_POST["id2"]."<br><input type=text value=\"$align_seqb\" size=100><p>";
print "</td></tr></table><hr>";
print "</td></tr></table></center>\n";
print "<p></body></html>";
// END PRINT RESULTS


// ########################################################################################
// ###############################     Functions       ####################################
// ########################################################################################
function align_DNA($seqa,$seqb){
    $match = 2;
    $mismatch = -1;
    $gap = -4;

    $a = preg_split('//', $seqa, -1, PREG_SPLIT_NO_EMPTY);
    $b = preg_split('//', $seqb, -1, PREG_SPLIT_NO_EMPTY);
    $maxa=sizeof($a);
    $maxb=sizeof($b);
    $lenn=max ($maxa,$maxb);

    // Creaci�n de la matriz
    // He reducido el c�digo para hacerlo mas simple y rapido, pero tan solo ahorra un 20% del tiempo
    // Con matrices muy grandes, PHP no sabe trabajar muy bien (es poco eficaz).
    $mx=0;
    for ($i=0;$i<$maxa;$i++){
        for ($j=0;$j<$maxb;$j++){
            if($b[$j]==$a[$i]){
                $x=$matriz[$j-1][$i-1]+$match;
            }else{
                $x=max (0,$matriz[$j-1][$i-1]-1,$matriz[$j][$i-1]-4,$matriz[$j-1][$i]-4);
            }
            $matriz[$j][$i]=$x;
            if ($mx<$x){$mx=$x; $mj=$j; $mi=$i;}
        }
    }
    // Matriz terminada

    $j=$mj;
    $i=$mi;

    $matrizz[$j][$i]=1;
    while ($i>0 or $j>0):
        $aa=$matriz[$j-1][$i-1];
        $ab=$matriz[$j][$i-1];
        $ac=$matriz[$j-1][$i];
        if($aa<>'//' or $aa==0){
            if($aa>=$ab and $aa>=$ac){
                $j=$j-1;
                $i=$i-1;
            }
            if($ab>$aa){$i=$i-1;}
            if($ac>$aa){$j=$j-1;}
        }else{
            if($ab<>'//'){$i=$i-1;}
            if($ac<>'//'){$j=$j-1;}
        }
        if($j<0){$j=0;}
        if($i<0){$i=0;}
        $matrizz[$j][$i]=1;
    endwhile;

    $j=$mj;
    $i=$mi;

    while ($i<strlen($seqa)-1 or $j<strlen($seqb)-1):
        $aa=$matriz[$j+1][$i+1];
        $ab=$matriz[$j][$i+1];
        $ac=$matriz[$j+1][$i];
        if($aa<>'//'){
            if($aa>=$ab and $aa>=$ac){
                $j=$j+1;
                $i=$i+1;
            }
            if($ab>$aa){$i=$i+1;}
            if($ac>$aa){$j=$j+1;}
        }else{
            if($ab<>'//'){$i=$i+1;}
            if($ac<>'//'){$j=$j+1;}
        }
        if($j>$lenn){$j=$lenn;}
        if($i>$lenn){$i=$lenn;}
        $matrizz[$j][$i]=1;
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
        }
        if($matrizz[$j][$i+1]==1){
            $t=1;
            $sseqa.=$a[$i];
            $sseqb.="-";
            $i=$i+1;
        }
        if($matrizz[$j+1][$i]==1){
            $t=1;
            $sseqa.="-";
            $sseqb.=$b[$j];
            $j=$j+1;
        }
    endwhile;

    if($matrizz[$j+1][$i+1]==1){
        $sseqa.=$a[$i];
        $sseqb.=$b[$j];
        $i=$i+1;
        $j=$j+1;
        $t=1;
    }
    if($t==0 and $matrizz[$j][$i+1]==1){
        $sseqa.=$a[$i];
        $sseqb.="-";
        $i=$i+1;
    }
    if($t==0 and $matrizz[$j+1][$i]==1){
        $sseqa.="-";
        $sseqb.=$b[$j];
        $j=$j+1;
    }
    if($i+1==$maxa){
        for ($ii=$j;$ii<$maxb;$ii++){$sseqb.=$b[$ii];}
        $sseqa.=$a[$i];
        for ($ii=$j;$ii<$maxb-1;$ii++){$sseqa.="-";}
    }
    if($j+1==$maxb){
        for ($ii=$i;$ii<$maxa;$ii++){$sseqa.=$a[$ii];}
        $sseqb.=$b[$j];
        for ($ii=$i;$ii<$maxa-1;$ii++){$sseqb.="-";}
    }


    // tengo que quitar la �ltima letra del alineamiento, de lo contrario, se repite la ultima posici�n
    // por que se da la repetici�n?
    $results["seqa"]=substr($sseqa,0,strlen($sseqa)-1);
    $results["seqb"]=substr($sseqb,0,strlen($sseqb)-1);
    //$results["seqa"]=$sseqa;
    //$results["seqb"]=$sseqb;


    return $results;
}
// ########################################################################################
function align_proteins($seqa,$seqb){
    $PAM250["CC"]=12;

    $PAM250["SC"]=0;$PAM250["SS"]=2;
    $PAM250["CS"]=0;

    $PAM250["TC"]=-2;$PAM250["TS"]=1;$PAM250["TT"]=3;
    $PAM250["CT"]=-2;$PAM250["ST"]=1;

    $PAM250["PC"]=-3;$PAM250["PS"]=1;$PAM250["PT"]=0;$PAM250["PP"]=6;
    $PAM250["CP"]=-3;$PAM250["SP"]=1;$PAM250["TP"]=0;

    $PAM250["AC"]=-2;$PAM250["AS"]=1;$PAM250["AT"]=1;$PAM250["AP"]=1;$PAM250["AA"]=2;
    $PAM250["CA"]=-2;$PAM250["SA"]=1;$PAM250["TA"]=1;$PAM250["PA"]=1;

    $PAM250["GC"]=-3;$PAM250["GS"]=1;$PAM250["GT"]=0;$PAM250["GP"]=-1;$PAM250["GA"]=1;$PAM250["GG"]=5;
    $PAM250["CG"]=-3;$PAM250["SG"]=1;$PAM250["TG"]=0;$PAM250["PG"]=-1;$PAM250["AG"]=1;

    $PAM250["NC"]=-4;$PAM250["NS"]=1;$PAM250["NT"]=0;$PAM250["NP"]=-1;$PAM250["NA"]=0;$PAM250["NG"]=0;$PAM250["NN"]=2;
    $PAM250["CN"]=-4;$PAM250["SN"]=1;$PAM250["TN"]=0;$PAM250["PN"]=-1;$PAM250["AN"]=0;$PAM250["GN"]=0;

    $PAM250["DC"]=-5;$PAM250["DS"]=0;$PAM250["DT"]=0;$PAM250["DP"]=-1;$PAM250["DA"]=0;$PAM250["DG"]=1;$PAM250["DN"]=2;$PAM250["DD"]=4;
    $PAM250["CD"]=-5;$PAM250["SD"]=0;$PAM250["TD"]=0;$PAM250["PD"]=-1;$PAM250["AD"]=0;$PAM250["GD"]=1;$PAM250["ND"]=2;

    $PAM250["EC"]=-5;$PAM250["ES"]=0;$PAM250["ET"]=0;$PAM250["EP"]=-1;$PAM250["EA"]=0;$PAM250["EG"]=0;$PAM250["EN"]=1;$PAM250["ED"]=3;$PAM250["EE"]=4;
    $PAM250["CE"]=-5;$PAM250["SE"]=0;$PAM250["TE"]=0;$PAM250["PE"]=-1;$PAM250["AE"]=0;$PAM250["GE"]=0;$PAM250["NE"]=1;$PAM250["DE"]=3;

    $PAM250["QC"]=-5;$PAM250["QS"]=-1;$PAM250["QT"]=-1;$PAM250["QP"]=0;$PAM250["QA"]=0;$PAM250["QG"]=-1;$PAM250["QN"]=1;$PAM250["QD"]=2;$PAM250["QE"]=2;$PAM250["QQ"]=4;
    $PAM250["CQ"]=-5;$PAM250["SQ"]=-1;$PAM250["TQ"]=-1;$PAM250["PQ"]=0;$PAM250["AQ"]=0;$PAM250["GQ"]=-1;$PAM250["NQ"]=1;$PAM250["DQ"]=2;$PAM250["EQ"]=2;

    $PAM250["HC"]=-3;$PAM250["HS"]=-1;$PAM250["HT"]=-1;$PAM250["HP"]=0;$PAM250["HA"]=-1;$PAM250["HG"]=-2;$PAM250["HN"]=2;$PAM250["HD"]=1;$PAM250["HE"]=1;$PAM250["HQ"]=3;$PAM250["HH"]=6;
    $PAM250["CH"]=-3;$PAM250["SH"]=-1;$PAM250["TH"]=-1;$PAM250["PH"]=0;$PAM250["AH"]=-1;$PAM250["GH"]=-2;$PAM250["NH"]=2;$PAM250["DH"]=1;$PAM250["EH"]=1;$PAM250["QH"]=3;

    $PAM250["RC"]=-4;$PAM250["RS"]=0;$PAM250["RT"]=-1;$PAM250["RP"]=0;$PAM250["RA"]=-2;$PAM250["RG"]=-3;$PAM250["RN"]=0;$PAM250["RD"]=-1;$PAM250["RE"]=-1;$PAM250["RQ"]=1;$PAM250["RH"]=2;$PAM250["RR"]=6;
    $PAM250["CR"]=-4;$PAM250["SR"]=0;$PAM250["TR"]=-1;$PAM250["PR"]=0;$PAM250["AR"]=-2;$PAM250["GR"]=-3;$PAM250["NR"]=0;$PAM250["DR"]=-1;$PAM250["ER"]=-1;$PAM250["QR"]=1;$PAM250["HR"]=2;

    $PAM250["KC"]=-5;$PAM250["KS"]=0;$PAM250["KT"]=0;$PAM250["KP"]=-1;$PAM250["KA"]=-1;$PAM250["KG"]=-2;$PAM250["KN"]=1;$PAM250["KD"]=0;$PAM250["KE"]=0;$PAM250["KQ"]=1;$PAM250["KH"]=0;$PAM250["KR"]=3;$PAM250["KK"]=5;
    $PAM250["CK"]=-5;$PAM250["SK"]=0;$PAM250["TK"]=0;$PAM250["PK"]=-1;$PAM250["AK"]=-1;$PAM250["GK"]=-2;$PAM250["NK"]=1;$PAM250["DK"]=0;$PAM250["EK"]=0;$PAM250["QK"]=1;$PAM250["HK"]=0;$PAM250["RK"]=3;

    $PAM250["MC"]=-5;$PAM250["MS"]=-2;$PAM250["MT"]=-1;$PAM250["MP"]=-2;$PAM250["MA"]=-1;$PAM250["MG"]=-3;$PAM250["MN"]=-2;$PAM250["MD"]=-3;$PAM250["ME"]=-2;$PAM250["MQ"]=-1;$PAM250["MH"]=-2;$PAM250["MR"]=0;$PAM250["MK"]=0;$PAM250["MM"]=6;
    $PAM250["CM"]=-5;$PAM250["SM"]=-2;$PAM250["TM"]=-1;$PAM250["PM"]=-2;$PAM250["AM"]=-1;$PAM250["GM"]=-3;$PAM250["NM"]=-2;$PAM250["DM"]=-3;$PAM250["EM"]=-2;$PAM250["QM"]=-1;$PAM250["HM"]=-2;$PAM250["RM"]=0;$PAM250["KM"]=0;

    $PAM250["IC"]=-2;$PAM250["IS"]=-1;$PAM250["IT"]=0;$PAM250["IP"]=-2;$PAM250["IA"]=-1;$PAM250["IG"]=-3;$PAM250["IN"]=-2;$PAM250["ID"]=-2;$PAM250["IE"]=-2;$PAM250["IQ"]=-2;$PAM250["IH"]=-2;$PAM250["IR"]=-2;$PAM250["IK"]=-2;$PAM250["IM"]=2;$PAM250["II"]=5;
    $PAM250["CI"]=-2;$PAM250["SI"]=-1;$PAM250["TI"]=0;$PAM250["PI"]=-2;$PAM250["AI"]=-1;$PAM250["GI"]=-3;$PAM250["NI"]=-2;$PAM250["DI"]=-2;$PAM250["EI"]=-2;$PAM250["QI"]=-2;$PAM250["HI"]=-2;$PAM250["RI"]=-2;$PAM250["KI"]=-2;$PAM250["MI"]=2;

    $PAM250["LC"]=-6;$PAM250["LS"]=-3;$PAM250["LT"]=-2;$PAM250["LP"]=-3;$PAM250["LA"]=-2;$PAM250["LG"]=-4;$PAM250["LN"]=-3;$PAM250["LD"]=-4;$PAM250["LE"]=-3;$PAM250["LQ"]=-2;$PAM250["LH"]=-2;$PAM250["LR"]=-3;$PAM250["LK"]=-3;$PAM250["LM"]=4;$PAM250["LI"]=2;$PAM250["LL"]=6;
    $PAM250["CL"]=-6;$PAM250["SL"]=-3;$PAM250["TL"]=-2;$PAM250["PL"]=-3;$PAM250["AL"]=-2;$PAM250["GL"]=-4;$PAM250["NL"]=-3;$PAM250["DL"]=-4;$PAM250["EL"]=-3;$PAM250["QL"]=-2;$PAM250["HL"]=-2;$PAM250["RL"]=-3;$PAM250["KL"]=-3;$PAM250["ML"]=4;$PAM250["IL"]=2;

    $PAM250["VC"]=-2;$PAM250["VS"]=-1;$PAM250["VT"]=0;$PAM250["VP"]=-1;$PAM250["VA"]=0;$PAM250["VG"]=-1;$PAM250["VN"]=-2;$PAM250["VD"]=-2;$PAM250["VE"]=-2;$PAM250["VQ"]=-2;$PAM250["VH"]=-2;$PAM250["VR"]=-2;$PAM250["VK"]=-2;$PAM250["VM"]=2;$PAM250["VI"]=4;$PAM250["VL"]=2;$PAM250["VV"]=4;
    $PAM250["CV"]=-2;$PAM250["SV"]=-1;$PAM250["TV"]=0;$PAM250["PV"]=-1;$PAM250["AV"]=0;$PAM250["GV"]=-1;$PAM250["NV"]=-2;$PAM250["DV"]=-2;$PAM250["EV"]=-2;$PAM250["QV"]=-2;$PAM250["HV"]=-2;$PAM250["RV"]=-2;$PAM250["KV"]=-2;$PAM250["MV"]=2;$PAM250["IV"]=4;$PAM250["LV"]=2;

    $PAM250["FC"]=-4;$PAM250["FS"]=-3;$PAM250["FT"]=-3;$PAM250["FP"]=-5;$PAM250["FA"]=-4;$PAM250["FG"]=-5;$PAM250["FN"]=-4;$PAM250["FD"]=-6;$PAM250["FE"]=-5;$PAM250["FQ"]=-5;$PAM250["FH"]=-2;$PAM250["FR"]=-4;$PAM250["FK"]=-5;$PAM250["FM"]=0;$PAM250["FI"]=1;$PAM250["FL"]=2;$PAM250["FV"]=-1;$PAM250["FF"]=9;
    $PAM250["CF"]=-4;$PAM250["SF"]=-3;$PAM250["TF"]=-3;$PAM250["PF"]=-5;$PAM250["AF"]=-4;$PAM250["GF"]=-5;$PAM250["NF"]=-4;$PAM250["DF"]=-6;$PAM250["EF"]=-5;$PAM250["QF"]=-5;$PAM250["HF"]=-2;$PAM250["RF"]=-4;$PAM250["KF"]=-5;$PAM250["MF"]=0;$PAM250["IF"]=1;$PAM250["LF"]=2;$PAM250["VF"]=-1;

    $PAM250["YC"]=0;$PAM250["YS"]=-3;$PAM250["YT"]=-3;$PAM250["YP"]=-5;$PAM250["YA"]=-3;$PAM250["YG"]=-5;$PAM250["YN"]=-2;$PAM250["YD"]=-4;$PAM250["YE"]=-4;$PAM250["YQ"]=-4;$PAM250["YH"]=0;$PAM250["YR"]=-4;$PAM250["YK"]=-4;$PAM250["YM"]=-2;$PAM250["YI"]=-1;$PAM250["YL"]=-1;$PAM250["YV"]=-2;$PAM250["YF"]=7;$PAM250["YY"]=10;
    $PAM250["CY"]=0;$PAM250["SY"]=-3;$PAM250["TY"]=-3;$PAM250["PY"]=-5;$PAM250["AY"]=-3;$PAM250["GY"]=-5;$PAM250["NY"]=-2;$PAM250["DY"]=-4;$PAM250["EY"]=-4;$PAM250["QY"]=-4;$PAM250["HY"]=0;$PAM250["RY"]=-4;$PAM250["KY"]=-4;$PAM250["MY"]=-2;$PAM250["IY"]=-1;$PAM250["LY"]=-1;$PAM250["VY"]=-2;$PAM250["FY"]=7;

    $PAM250["WC"]=-8;$PAM250["WS"]=-2;$PAM250["WT"]=-5;$PAM250["WP"]=-6;$PAM250["WA"]=-6;$PAM250["WG"]=-7;$PAM250["WN"]=-4;$PAM250["WD"]=-7;$PAM250["WE"]=-7;$PAM250["WQ"]=-5;$PAM250["WH"]=3;$PAM250["WR"]=2;$PAM250["WK"]=-3;$PAM250["WM"]=-4;$PAM250["WI"]=-5;$PAM250["WL"]=-2;$PAM250["WV"]=-6;$PAM250["WF"]=0;$PAM250["WY"]=0;$PAM250["WW"]=17;
    $PAM250["CW"]=-8;$PAM250["SW"]=-2;$PAM250["TW"]=-5;$PAM250["PW"]=-6;$PAM250["AW"]=-6;$PAM250["GW"]=-7;$PAM250["NW"]=-4;$PAM250["DW"]=-7;$PAM250["EW"]=-7;$PAM250["QW"]=-5;$PAM250["HW"]=3;$PAM250["RW"]=2;$PAM250["KW"]=-3;$PAM250["MW"]=-4;$PAM250["IW"]=-5;$PAM250["LW"]=-2;$PAM250["VW"]=-6;$PAM250["FW"]=0;$PAM250["YW"]=0;

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
// ########################################################################################
// The form includes javascript code
function print_form (){
    ?>

    <html>
    <head>
        <title>Alignment of two DNA or RNA sequences</title>
        <script language="JavaScript">
            <!--
            function tidyup() {
                // sequence1
                str=document.mydna.sequence.value.toUpperCase();
                str=str.split(/\d|\W/).join("");
                if (!str) {document.mydna.sequence.value=''};
                var revstr=' ';   var k=0;   for (i =0; i<str.length; i++) {      revstr+=str.charAt(i);      k+=1;      if (k==Math.floor(k/10)*10) {revstr+=' '};      if (k==Math.floor(k/60)*60) {revstr+=k+'\n '};   };  document.mydna.sequence.value=revstr;

                // sequence2
                str=document.mydna.sequence2.value.toUpperCase();
                str=str.split(/\d|\W/).join("");
                if (!str) {document.mydna.sequence2.value=''};
                var revstr=' ';   var k=0;   for (i =0; i<str.length; i++) {      revstr+=str.charAt(i);      k+=1;      if (k==Math.floor(k/10)*10) {revstr+=' '};      if (k==Math.floor(k/60)*60) {revstr+=k+'\n '};   };
                document.mydna.sequence2.value=revstr;}

            // Get complementary of sequence 1
            function strcomp1() {
                str=document.mydna.sequence.value.toUpperCase();
                str=Removeuseless(str);
                str=getrev(str);
                str=getComplement(str);
                document.mydna.sequence.value=str;
                tidyup()
            }

            // Get complementary of sequence 2
            function strcomp2() {
                str=document.mydna.sequence2.value.toUpperCase();
                str=Removeuseless(str);
                str=getrev(str);
                str=getComplement(str);
                document.mydna.sequence2.value=str;
                tidyup()
            }

            function Removeuseless(str) {
                str = str.split(/\d|\W/).join("");
                return str;
            }

            function getrev(str) {
                var revstr=' ';
                var k=0;
                for (i = str.length-1; i>=0; i--) {
                    revstr+=str.charAt(i);
                    k+=1;
                };
                return revstr;
            }

            function getComplement(str) {
                str = str.split("A").join("t");
                str = str.split("T").join("a");
                str = str.split("G").join("c");
                str = str.split("C").join("g");
                str=str.toUpperCase();
                return str;
            };
            //-->
        </script>
    </head>
    <body bgcolor="white" text="black">

    <center>
        <form name="mydna" method="post" action="<? print $_SERVER["PHP_SELF"]; ?>">
            <table cellpadding="5">
                <tbody>
                <tr>
                    <td>

                        <b><font size="6">Alignment of two DNA, RNA or protein sequences</font></b>&nbsp; <br>

                        <div align="right"><a href="javascript: tidyup ()">Tidy Up Sequences</a></div>
                        <br>
                        <input type=text name=id1 value="Sequence 1" size=30>
                        <a href="javascript: strcomp1 ()" onmouseover="window.status='Complementary sequence'; return true" onmouseout="window.status=''; return true"><font size=-1>C</font></a>
                        <br>
                        <textarea name="sequence" cols="75" rows="3">GGAGTGAGGG GAGCAGTTGG CTGAAGATGG TCCCCGCCGA GGGACCGGTG GGCGACGGCG 60
    AGCTGTGGCA GACCTGGCTT CCTAACCACG TCCGTGTTCT TGCGGCTCCG GGAGGGACTG 120   </textarea> <br>
                        <input type=text name=id2 value="Sequence 2" size=30>
                        <a href="javascript: strcomp2 ()" onmouseover="window.status='Complementary sequence'; return true" onmouseout="window.status=''; return true"><font size=-1>C</font></a>
                        <br>
                        <textarea name="sequence2" cols="75" rows="3">CGCATGCGGA GTGAGGGGAG CAGTTGGGAA CAGATGGTCC CCGCCGAGGG ACCGGTGGGC 60
    GACGGCCAGC TGTGGCAGAC CTGGCTTCCT AACCACGGAA CGTTCTTTCC GCTCCGGGAG 120    </textarea>

                        <center>
                            <div align=Right><a href="<? print $_SERVER["PHP_SELF"]; ?>">Info</a></div><br><input type="submit" value="Align sequences">
                        </center>

                    </td></tr></tbody>
            </table>
        </form>
        Freely downloable PHP script at <a href=http://www.biophp.org/minitools/seq_alignment>biophp.org</a>

    </center>


    </body>
    </html>


    <?
} // end of form
// ########################################################################################
function print_info (){
    ?>


    <html>
    <head>
        <title>Alignment of two DNA or protein sequences</title>
    </head>
    <body style="background-color: rgb(255, 255, 255);">
    <center>
        <table>
            <tbody>
            <tr>
                <td style="vertical-align: top;">
                    <h1 align="center">Alignment of two DNA sequences</h1>
                    <div style="text-align: right;"><a href="<? print $_SERVER["PHP_SELF"]; ?>">Start using this tool</a><br>
                    </div>
                    <br>
                    This script has been adapted to PHP scripting languaje from the
                    original version written in Visual Basic for Applications and available on a
                    Excel page <a href="http://webpages.ull.es/users/jmhernan/">here </a>.
                    <br>
                    <br>
                    The alignment method is the Smith-Waterman type (Smith, T. F., &amp; M. S.
                    Waterman. 1981. Identification of common molecular subsequences.
                    Journal of Molecular Biology 147:195-197. <a
                        href="http://www.ncbi.nlm.nih.gov/entrez/query.fcgi?cmd=Retrieve&amp;db=pubmed&amp;dopt=Abstract&amp;list_uids=7265238">PubMed</a>).
                    To run the program, paste the
                    DNA or RNA sequences in the form and submit the data. <br>
                    <br>
                    Alignment is shown on the response page, and sequences with gabs are
                    at the bottom.
                    <p>%nbsp;
                    <p>Developed by: <br>
                    </p>
                    <p>Dr. <b>Jose Manuel Gonz&aacute;lez Hern&aacute;ndez</b> <br>
                        Departamento de Microbiolog&iacute;a y Biolog&iacute;a Celular <br>
                        Facultad de Farmacia <br>
                        Universidad de La Laguna <br>
                        La Laguna, Tenerife <br>
                        Spain </p>
                    <p>For suggestions or problems, <a href="http://www.in-silico.com/contact.php">contact us</a> </p>
                    <div style="text-align: center;">
                        <hr style="width: 100%; height: 2px;"><br>
                        <a href="http://www.in-silico.com">www.in-silico.com</a><br>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </center>
    </body>
    </html>


    <?
} // end of info
?>

