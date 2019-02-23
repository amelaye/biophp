<?php

// author    Joseba Bikandi
// license   GNU GPL v2
// source code available at  biophp.org

?>
<html><head><title>Melting Temperature (Tm) Calculation</title></head><body bgcolor=FFFFFF>
<center><table border=0><tr><td>
                <center><h2>Melting Temperature (Tm) Calculation</h2></center>
                <?php
                error_reporting(0);
                $primer="";
                $primer=strtoupper($_GET["primer"]);
                $primer=preg_replace("/\W|[^ATGCYRWSKMDVHBN]|\d/","",$primer);

                ?>
                <form method=get action=<? print $_SERVER["PHP_SELF"]; ?>>
                    <b>Primer </b>(6-50 bases):<br>
                    <input type=text name=primer value="<? print $primer; ?>" size=40>
                    <input type=submit value="Calculate Tm">
                    <?
                    if ($primer!="" and strlen($primer)>=6 and strlen($primer)<=50){
                        print "<table width=100%><tr><td bgcolor=DDDDFF><pre>LENGTH                   ".strlen($primer)."\n";
                        $cg=round(100*CountCG($primer)/strlen($primer),1);
                        print "C+G%                     $cg\n";
                        Mol_wt($primer);
                        print "</pre></td></tr></table>\n";
                    }

                    ?>
                    <table width=100%>
                        <tr>
                            <td valign=top>
                                <input type=checkbox name=basic value=1<? if ($_GET["basic"]==1){print " checked";} ?>>
                            </td>
                            <td valign=top>
                                <a href=?formula=basic>Basic Tm</a>
                                <br><font size=-1> Degenerated nucleotides are allowed</a></font>
                            </td>
                            <?
                            if($primer!="" and $_GET["basic"]==1){
                                print "<tr><td>&nbsp;</td><td  bgcolor=DDDDFF><pre>";
                                if (strlen($primer)!=CountATCG($primer)){
                                    print "Minimun        <font color=880000><b>".Tm_min($primer)." &deg;C</b></font>\n";
                                    print "Maximum        <font color=880000><b>".Tm_max($primer)." &deg;C</b></font>";
                                }else{
                                    print "Tm:                 <font color=880000><b>".Tm_min($primer)." &deg;C</b></font>";
                                }
                                print "</pre></td>";
                            }
                            ?>
                        </tr>
                        <tr>
                            <td valign=top>
                                <input type=checkbox name=NearestNeighbor value=1<? if ($_GET["NearestNeighbor"]==1){print " checked";} ?>>
                            </td>
                            <td valign=top>
                                <a href=?formula=Base-Stacking>Base-Stacking Tm</a>
                                <br><font size=-1> Degenerated nucleotides are NOT allowed</a></font>
                                <br>Primer concentration: <input type=text name=cp value=<? if ($_GET["cp"]==""){print "200";}else{print $_GET["cp"];} ?> size=4> nM
                                <br>Salt concentration:   <input type=text name=cs value=<? if ($_GET["cs"]==""){print "50";}else{print $_GET["cs"];} ?> size=4> mM
                                <br>Mg<font size=-2><sup>2+</sup></font> concentration:     <input type=text name=cmg value=<? if ($_GET["cmg"]==""){print "0";}else{print $_GET["cmg"];} ?> size=4> mM
                            </td>
                            <?
                            if($primer!="" and $_GET["NearestNeighbor"]==1){
                                print "<tr><td>&nbsp;</td><td  bgcolor=DDDDFF><pre>";
                                tm_Base_Stacking($primer,$_GET["cp"],$_GET["cs"],$_GET["cmg"]);
                                print "</pre></td>";
                            }
                            ?>
                        </tr>
                    </table>
                </form>
                <hr>
                <font size=-1>
                    Source code is freely downloable at <a href=http://www.biophp.org/minitools/melting_temperature/>biophp.org</a>
                </font>

            </td></tr></table></center>
<? if ($_GET["formula"]=="basic"){ ?>
<hr>
<h2>Basic Melting Temperature (Tm) Calculations</h2>
Two standard approximation calculations are used.
<p>For sequences less than 14 nucleotides
    the formula is:
<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Tm= (wA+xT) * 2 + (yG+zC) * 4
<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;where w,x,y,z are the number of the bases A,T,G,C in the sequence, respectively.
<p>For sequences longer than 13 nucleotides, the equation used is
<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Tm= 64.9 +41*(yG+zC-16.4)/(wA+xT+yG+zC)
<p>When degenerated nucleotides are included in the primer sequence (Y,R,W,S,K,M,D,V,H,B or N), those nucleotides will be internally substituted prior to minimum and maximum Tm calculation.
<p><pre>    Example:
     Primer sequence:                            CTCT<b>RY</b>CT<b>WS</b>CTCTCT
     Sequence for minimum Tm calculation:        CTCT<b>AT</b>CT<b>AG</b>CTCTCT
     Sequence for maximum Tm calculation:        CTCT<b>GC</b>CT<b>AG</b>CTCTCT</pre>
<p><b>ASSUMPTIONS:</b>
<p>Both equations assume that the annealing occurs under the standard conditions of 50 nM primer, 50
    mM Na<sup><font size=-2>+</font></sup>, and pH 7.0.
    <? } ?>
    <? if ($_GET["formula"]=="Base-Stacking"){ ?>
<hr>
<h2>Base-Stacking Melting Temperature (Tm) Calculations</h2>
This aproximation uses Thermodynamical concepts to compute T<sub>m</sub>.
The following references were used to develop the script:

<p>SantaLucia J. A unified view of polymer, dumbbell, and oligonucleotide DNA nearest-neighbor
    thermodynamics. Proc Natl Acad Sci U S A. 1998 Feb 17;95(4):1460-5.
    <a href=http://www.ncbi.nlm.nih.gov/sites/entrez?Db=pubmed&Cmd=ShowDetailView&TermToSearch=9465037>NCBI</a>

<p>von Ahsen N, Oellerich M, Armstrong VW, Sch�tz E. Application of a thermodynamic nearest-neighbor
    model to estimate nucleic acid stability and optimize probe design: prediction of melting points
    of multiple mutations of apolipoprotein B-3500 and factor V with a hybridization probe genotyping
    assay on the LightCycler. Clin Chem. 1999 Dec;45(12):2094-101.
    <a href=http://www.ncbi.nlm.nih.gov/sites/entrez?Db=pubmed&Cmd=ShowDetailView&TermToSearch=10585340>NCBI</a>

    <? } ?>
</body></html>


<?
function tm_Base_Stacking($c,$conc_primer,$conc_salt,$conc_mg){

    if (CountATCG($c)!= strlen($c)){print "The oligonucleotide is not valid";return;}
    $h=$s=0;
    // from table at http://www.ncbi.nlm.nih.gov/pmc/articles/PMC19045/table/T2/ (SantaLucia, 1998)
    // enthalpy values
    $array_h["AA"]= -7.9;
    $array_h["AC"]= -8.4;
    $array_h["AG"]= -7.8;
    $array_h["AT"]= -7.2;
    $array_h["CA"]= -8.5;
    $array_h["CC"]= -8.0;
    $array_h["CG"]=-10.6;
    $array_h["CT"]= -7.8;
    $array_h["GA"]= -8.2;
    $array_h["GC"]= -9.8;
    $array_h["GG"]= -8.0;
    $array_h["GT"]= -8.4;
    $array_h["TA"]= -7.2;
    $array_h["TC"]= -8.2;
    $array_h["TG"]= -8.5;
    $array_h["TT"]= -7.9;
    // entropy values
    $array_s["AA"]=-22.2;
    $array_s["AC"]=-22.4;
    $array_s["AG"]=-21.0;
    $array_s["AT"]=-20.4;
    $array_s["CA"]=-22.7;
    $array_s["CC"]=-19.9;
    $array_s["CG"]=-27.2;
    $array_s["CT"]=-21.0;
    $array_s["GA"]=-22.2;
    $array_s["GC"]=-24.4;
    $array_s["GG"]=-19.9;
    $array_s["GT"]=-22.4;
    $array_s["TA"]=-21.3;
    $array_s["TC"]=-22.2;
    $array_s["TG"]=-22.7;
    $array_s["TT"]=-22.2;

    // effect on entropy by salt correction; von Ahsen et al 1999
    // Increase of stability due to presence of Mg;
    $salt_effect= ($conc_salt/1000)+(($conc_mg/1000) * 140);
    // effect on entropy
    $s+=0.368 * (strlen($c)-1)* log($salt_effect);

    // terminal corrections. Santalucia 1998
    $firstnucleotide=substr($c,0,1);
    if ($firstnucleotide=="G" or $firstnucleotide=="C"){$h+=0.1; $s+=-2.8;}
    if ($firstnucleotide=="A" or $firstnucleotide=="T"){$h+=2.3; $s+=4.1;}

    $lastnucleotide=substr($c,strlen($c)-1,1);
    if ($lastnucleotide=="G" or $lastnucleotide=="C"){$h+=0.1; $s+=-2.8;}
    if ($lastnucleotide=="A" or $lastnucleotide=="T"){$h+=2.3; $s+=4.1;}

    // compute new H and s based on sequence. Santalucia 1998
    for($i=0; $i<strlen($c)-1; $i++){
        $subc=substr($c,$i,2);
        $h+=$array_h[$subc];
        $s+=$array_s[$subc];
    }
    $tm=((1000*$h)/($s+(1.987*log($conc_primer/2000000000))))-273.15;
    print "Tm:                 <font color=880000><b>".round($tm,1)." &deg;C</b></font>";
    print  "\n<font color=008800>  Enthalpy: ".round($h,2)."\n  Entropy:  ".round($s,2)."</font>";
}

function Mol_wt($primer){
    $upper_mwt=molwt($primer,"DNA","upperlimit");
    $lower_mwt=molwt($primer,"DNA","lowerlimit");
    if ($upper_mwt==$lower_mwt){
        print "Molecular weight:        $upper_mwt";
    }else{
        print "Upper Molecular weight:  $upper_mwt\nLower Molecular weight:  $lower_mwt";
    }
}
function CountCG($c){
    $cg=substr_count($c,"G")+substr_count($c,"C");
    return $cg;
}

function CountATCG($c){
    $cg=substr_count($c,"A")+substr_count($c,"T")+substr_count($c,"G")+substr_count($c,"C");
    return $cg;
}


function Tm_min($primer){
    $primer_len=strlen($primer);
    $primer2=preg_replace("/A|T|Y|R|W|K|M|D|V|H|B|N/","A",$primer);
    $n_AT=substr_count($primer2,"A");
    $primer2=preg_replace("/C|G|S/","G",$primer);
    $n_CG=substr_count($primer2,"G");

    if ($primer_len > 0) {
        if ($primer_len < 14) {
            return round(2 * ($n_AT) + 4 * ($n_CG));
        }else{
            return round(64.9 + 41*(($n_CG-16.4)/$primer_len),1);
        }
    }
}

function Tm_max($primer){
    $primer_len=strlen($primer);
    $primer=primer_max($primer);
    $n_AT=substr_count($primer,"A");
    $n_CG=substr_count($primer,"G");
    if ($primer_len > 0) {
        if ($primer_len < 14) {
            return round(2 * ($n_AT) + 4 * ($n_CG));
        }else{
            return round(64.9 + 41*(($n_CG-16.4)/$primer_len),1);
        }
    }
}

function primer_min($primer){
    $primer=preg_replace("/A|T|Y|R|W|K|M|D|V|H|B|N/","A",$primer);
    $primer=preg_replace("/C|G|S/","G",$primer);
    return $primer;
}

function primer_max($primer){
    $primer=preg_replace("/A|T|W/","A",$primer);
    $primer=preg_replace("/C|G|Y|R|S|K|M|D|V|H|B|N/","G",$primer);
    return $primer;
}
function molwt($sequence,$moltype,$limit)
{

    // the following are single strand molecular weights / base
    $rna_A_wt = 329.245;
    $rna_C_wt = 305.215;
    $rna_G_wt = 345.245;
    $rna_U_wt = 306.195;

    $dna_A_wt = 313.245;
    $dna_C_wt = 289.215;
    $dna_G_wt = 329.245;
    $dna_T_wt = 304.225;

    $water = 18.015;

    $dna_wts = array('A' => array($dna_A_wt, $dna_A_wt),  // Adenine
        'C' => array($dna_C_wt, $dna_C_wt),  // Cytosine
        'G' => array($dna_G_wt, $dna_G_wt),  // Guanine
        'T' => array($dna_T_wt, $dna_T_wt),  // Thymine
        'M' => array($dna_C_wt, $dna_A_wt),  // A or C
        'R' => array($dna_A_wt, $dna_G_wt),  // A or G
        'W' => array($dna_T_wt, $dna_A_wt),  // A or T
        'S' => array($dna_C_wt, $dna_G_wt),  // C or G
        'Y' => array($dna_C_wt, $dna_T_wt),  // C or T
        'K' => array($dna_T_wt, $dna_G_wt),  // G or T
        'V' => array($dna_C_wt, $dna_G_wt),  // A or C or G
        'H' => array($dna_C_wt, $dna_A_wt),  // A or C or T
        'D' => array($dna_T_wt, $dna_G_wt),  // A or G or T
        'B' => array($dna_C_wt, $dna_G_wt),  // C or G or T
        'X' => array($dna_C_wt, $dna_G_wt),  // G, A, T or C
        'N' => array($dna_C_wt, $dna_G_wt)   // G, A, T or C
    );

    $rna_wts = array('A' => array($rna_A_wt, $rna_A_wt),  // Adenine
        'C' => array($rna_C_wt, $rna_C_wt),  // Cytosine
        'G' => array($rna_G_wt, $rna_G_wt),  // Guanine
        'U' => array($rna_U_wt, $rna_U_wt),  // Uracil
        'M' => array($rna_C_wt, $rna_A_wt),  // A or C
        'R' => array($rna_A_wt, $rna_G_wt),  // A or G
        'W' => array($rna_U_wt, $rna_A_wt),  // A or U
        'S' => array($rna_C_wt, $rna_G_wt),  // C or G
        'Y' => array($rna_C_wt, $rna_U_wt),  // C or U
        'K' => array($rna_U_wt, $rna_G_wt),  // G or U
        'V' => array($rna_C_wt, $rna_G_wt),  // A or C or G
        'H' => array($rna_C_wt, $rna_A_wt),  // A or C or U
        'D' => array($rna_U_wt, $rna_G_wt),  // A or G or U
        'B' => array($rna_C_wt, $rna_G_wt),  // C or G or U
        'X' => array($rna_C_wt, $rna_G_wt),  // G, A, U or C
        'N' => array($rna_C_wt, $rna_G_wt)   // G, A, U or C
    );

    $all_na_wts = array('DNA' => $dna_wts, 'RNA' => $rna_wts);
    //print_r($all_na_wts);
    $na_wts = $all_na_wts[$moltype];

    $mwt = 0;
    $NA_len = strlen($sequence);

    if($limit=="lowerlimit"){$wlimit=1;}
    if($limit=="upperlimit"){$wlimit=0;}

    for ($i = 0; $i < $NA_len; $i++) {
        $NA_base = substr($sequence, $i, 1);
        $mwt += $na_wts[$NA_base][$wlimit];
    }
    $mwt += $water;

    return $mwt;
}

?>
