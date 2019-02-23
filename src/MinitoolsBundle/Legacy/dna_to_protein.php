<?php

// author    Joseba Bikandi
// license   GNU GPL v2
// source code available at  biophp.org

error_reporting(0);

// when info is requested, show info  (in the bottom of the page) and die
if ($_GET["action"]=="info"){print_info(); die();}

//If nothing is posted, print form (in the bottom of the page) and die
if (!$_POST){print_form();die();}

// When info has been posted, go ahead

print "<html><head></head><body bgcolor=white>\n<center>\n<h2>DNA to protein translation</h2>\n</center>";

// OBTAIN POSTED INFORMATION
// Get the sequence
$sequence = strtoupper($_POST["sequence"]);

// remove non-coding chacacters from sequence
$sequence = preg_replace ("(\W|\d)", "", $sequence);

// Get the genetic code to be used for translation
$genetic_code = $_POST["genetic_code"];

// Get the frames to be translated
$frames = $_POST["frames"];

// When usage of custom genetic code is requested,
if($_POST["usemycode"]==1){
    $mycode=strtoupper($_POST["mycode"]);
    $mycode= preg_replace ("([^FLIMVSPTAY*HQNKDECWRG\*])", "", $mycode);
    if(strlen($mycode)!=64){die ("Error:<br>The custom code is not correct (is not 64 characters long).<HR>");}
    $genetic_code="custom";
}
// minimum protein size
$protsize=$_POST["protsize"];
if (str_is_int($protsize)!=1 or $protsize<10){
    die("Error:<br>Minimum size of protein sequence is not correct (minimum size is 10).");
}
// TRANSLATE
if ($genetic_code=="custom"){
    // Translate in  5-3 direction
    $frame[1]=translate_DNA_to_protein_customcode(substr ($sequence, 0, floor(strlen($sequence)/3)*3),$mycode);
    if ($frames>1){
        $frame[2]=translate_DNA_to_protein_customcode(substr ($sequence, 1,floor((strlen($sequence)-1)/3)*3),$mycode);
        $frame[3]=translate_DNA_to_protein_customcode(substr ($sequence, 2,floor((strlen($sequence)-2)/3)*3),$mycode);
    }
    // Translate the complementary  sequence
    if ($frames>3){
        // Get  complementary
        $rvsequence= RevComp_DNA($sequence);
        //calculate frames 4-6
        $frame[4]=translate_DNA_to_protein_customcode(substr ($rvsequence, 0, floor(strlen($rvsequence)/3)*3),$mycode);
        $frame[5]=translate_DNA_to_protein_customcode(substr ($rvsequence, 1,floor((strlen($rvsequence)-1)/3)*3),$mycode);
        $frame[6]=translate_DNA_to_protein_customcode(substr ($rvsequence, 2,floor((strlen($rvsequence)-2)/3)*3),$mycode);
    }
}else{
    // Translate in  5-3 direction
    $frame[1]=translate_DNA_to_protein(substr ($sequence, 0, floor(strlen($sequence)/3)*3),$genetic_code);
    if ($frames>1){
        $frame[2]=translate_DNA_to_protein(substr ($sequence, 1,floor((strlen($sequence)-1)/3)*3),$genetic_code);
        $frame[3]=translate_DNA_to_protein(substr ($sequence, 2,floor((strlen($sequence)-2)/3)*3),$genetic_code);
    }
    // Translate the complementary  sequence
    if ($frames>3){
        // Get  complementary
        $rvsequence= RevComp_DNA($sequence);
        //calculate frames 4-6
        $frame[4]=translate_DNA_to_protein(substr ($rvsequence, 0,floor(strlen($rvsequence)/3)*3),$genetic_code);
        $frame[5]=translate_DNA_to_protein(substr ($rvsequence, 1,floor((strlen($rvsequence)-1)/3)*3),$genetic_code);
        $frame[6]=translate_DNA_to_protein(substr ($rvsequence, 2,floor((strlen($rvsequence)-2)/3)*3),$genetic_code);
    }
}

// SHOW TRANSLATIONS ALIGNED (when requested)
if ($_POST["show_aligned"]==1){show_translations_aligned($sequence,$rvsequence,$frame);}

// FIND ORFs
if ($_POST["search_orfs"]==1){$frame=find_ORF($frame, $protsize,$_POST["only_coding"],$_POST["trimmed"]);}


// OUTPUT ALL FRAMES (always)
print "<center><table><tr><td nowrap><pre>\n";
foreach ($frame as $n => $peptide_sequence){
    if ($_POST["dgaps"]==1){$peptide_sequence=chunk_split($peptide_sequence,1,'--');}
    print "<p><font size=+1><b>Frame $n</b></font><br>";
    print chunk_split($peptide_sequence,100,'<br>');
}
print "</td></tr></table>";

// ############################################################################
function find_ORF($frame, $protsize,$only_coding,$trimmed){
    foreach ($frame as $n => $peptide_sequence){
        $peptide_sequence=strtolower($peptide_sequence);
        $oligo=preg_split('/\*/',$peptide_sequence);
        foreach ($oligo as $m => $val){
            if (strlen($val)>=$protsize){
                if ($trimmed==1){
                    $oligo[$m]=substr($val,0,strpos($val,"m")).strtoupper(substr($val,strpos($val,"m")));
                }else{
                    $oligo[$m]=strtoupper($val);
                }
            }
        }
        $new_peptide_sequence="";
        foreach ($oligo as $m => $val){if($m!=0){$new_peptide_sequence.="*".$val;}else{$new_peptide_sequence.=$val;}}
        // to avoid showing no coding, remove them from output sequence
        if($only_coding==1){$new_peptide_sequence=preg_replace("/f|l|i|m|v|s|p|t|a|y|h|q|n|k|d|e|c|w|r|g|x]/","_",$new_peptide_sequence);}
        $frame[$n]=$new_peptide_sequence;
    }
    return $frame;
}

// ############################################################################
function RevComp_DNA($seq){
    $seq= strtoupper($seq);
    $original=  array("(A)","(T)","(G)","(C)","(Y)","(R)","(W)","(S)","(K)","(M)","(D)","(V)","(H)","(B)");
    $complement=array("t","a","c","g","r","y","w","s","m","k","h","b","d","v");
    $seq = preg_replace ($original, $complement, $seq);
    $seq= strtoupper ($seq);
    return $seq;
}
// ############################################################################
function translate_DNA_to_protein($seq,$genetic_code){

    // $aminoacids is the array of aminoacids
    $aminoacids=array("F","L","I","M","V","S","P","T","A","Y","*","H","Q","N","K","D","E","C","W","R","G","X");

    // $triplets is the array containning the genetic codes
    // Info has been extracted from http://www.ncbi.nlm.nih.gov/Taxonomy/Utils/wprintgc.cgi?mode

    // Standard genetic code
    $triplets[1]=array("(TTT |TTC )","(TTA |TTG |CT. )","(ATT |ATC |ATA )","(ATG )","(GT. )","(TC. |AGT |AGC )",
        "(CC. )","(AC. )","(GC. )","(TAT |TAC )","(TAA |TAG |TGA )","(CAT |CAC )",
        "(CAA |CAG )","(AAT |AAC )","(AAA |AAG )","(GAT |GAC )","(GAA |GAG )","(TGT |TGC )",
        "(TGG )","(CG. |AGA |AGG )","(GG. )","(\S\S\S )");
    // Vertebrate Mitochondrial
    $triplets[2]=array("(TTT |TTC )","(TTA |TTG |CT. )","(ATT |ATC |ATA )","(ATG )","(GT. )","(TC. |AGT |AGC )",
        "(CC. )","(AC. )","(GC. )","(TAT |TAC )","(TAA |TAG |AGA |AGG )","(CAT |CAC )",
        "(CAA |CAG )","(AAT |AAC )","(AAA |AAG )","(GAT |GAC )","(GAA |GAG )","(TGT |TGC )",
        "(TGG |TGA )","(CG. )","(GG. )","(\S\S\S )");
    // Yeast Mitochondrial
    $triplets[3]=array("(TTT |TTC )","(TTA |TTG )","(ATT |ATC )","(ATG |ATA )","(GT. )","(TC. |AGT |AGC )",
        "(CC. )","(AC. |CT. )","(GC. )","(TAT |TAC )","(TAA |TAG )","(CAT |CAC )",
        "(CAA |CAG )","(AAT |AAC )","(AAA |AAG )","(GAT |GAC )","(GAA |GAG )","(TGT |TGC )",
        "(TGG |TGA )","(CG. |AGA |AGG )","(GG. )","(\S\S\S )");
    // Mold, Protozoan and Coelenterate Mitochondrial. Mycoplasma, Spiroplasma
    $triplets[4]=array("(TTT |TTC )","(TTA |TTG |CT. )","(ATT |ATC |ATA )","(ATG )","(GT. )","(TC. |AGT |AGC )",
        "(CC. )","(AC. )","(GC. )","(TAT |TAC )","(TAA |TAG )","(CAT |CAC )",
        "(CAA |CAG )","(AAT |AAC )","(AAA |AAG )","(GAT |GAC )","(GAA |GAG )","(TGT |TGC )",
        "(TGG |TGA )","(CG. |AGA |AGG )","(GG. )","(\S\S\S )");
    // Invertebrate Mitochondrial
    $triplets[5]=array("(TTT |TTC )","(TTA |TTG |CT. )","(ATT |ATC )","(ATG |ATA )","(GT. )","(TC. |AG. )",
        "(CC. )","(AC. )","(GC. )","(TAT |TAC )","(TAA |TAG )","(CAT |CAC )",
        "(CAA |CAG )","(AAT |AAC )","(AAA |AAG )","(GAT |GAC )","(GAA |GAG )","(TGT |TGC )",
        "(TGG |TGA )","(CG. )","(GG. )","(\S\S\S )");
    // Ciliate Nuclear; Dasycladacean Nuclear; Hexamita Nuclear
    $triplets[6]=array("(TTT |TTC )","(TTA |TTG |CT. )","(ATT |ATC |ATA )","(ATG )","(GT. )","(TC. |AGT |AGC )",
        "(CC. )","(AC. )","(GC. )","(TAT |TAC )","(TGA )","(CAT |CAC )",
        "(CAA |CAG |TAA |TAG )","(AAT |AAC )","(AAA |AAG )","(GAT |GAC )","(GAA |GAG )","(TGT |TGC )",
        "(TGG )","(CG. |AGA |AGG )","(GG. )","(\S\S\S )");
    // Echinoderm Mitochondrial
    $triplets[9]=array("(TTT |TTC )","(TTA |TTG |CT. )","(ATT |ATC |ATA )","(ATG )","(GT. )","(TC. |AG. )",
        "(CC. )","(AC. )","(GC. )","(TAT |TAC )","(TAA |TAG )","(CAT |CAC )",
        "(CAA |CAG )","(AAA |AAT |AAC )","(AAG )","(GAT |GAC )","(GAA |GAG )","(TGT |TGC )",
        "(TGG |TGA )","(CG. )","(GG. )","(\S\S\S )");
    // Euplotid Nuclear
    $triplets[10]=array("(TTT |TTC )","(TTA |TTG |CT. )","(ATT |ATC |ATA )","(ATG )","(GT. )","(TC. |AGT |AGC )",
        "(CC. )","(AC. )","(GC. )","(TAT |TAC )","(TAA |TAG )","(CAT |CAC )",
        "(CAA |CAG )","(AAT |AAC )","(AAA |AAG )","(GAT |GAC )","(GAA |GAG )","(TGT |TGC |TGA )",
        "(TGG )","(CG. |AGA |AGG )","(GG. )","(\S\S\S )");
    // Bacterial and Plant Plastid
    $triplets[11]=array("(TTT |TTC )","(TTA |TTG |CT. )","(ATT |ATC |ATA )","(ATG )","(GT. )","(TC. |AGT |AGC )",
        "(CC. )","(AC. )","(GC. )","(TAT |TAC )","(TAA |TAG |TGA )","(CAT |CAC )",
        "(CAA |CAG )","(AAT |AAC )","(AAA |AAG )","(GAT |GAC )","(GAA |GAG )","(TGT |TGC )",
        "(TGG )","(CG. |AGA |AGG )","(GG. )","(\S\S\S )");
    // Alternative Yeast Nuclear
    $triplets[12]=array("(TTT |TTC )","(TTA |TTG |CTA |CTT |CTC )","(ATT |ATC |ATA )","(ATG )","(GT. )","(TC. |AGT |AGC |CTG )",
        "(CC. )","(AC. )","(GC. )","(TAT |TAC )","(TAA |TAG |TGA )","(CAT |CAC )",
        "(CAA |CAG )","(AAT |AAC )","(AAA |AAG )","(GAT |GAC )","(GAA |GAG )","(TGT |TGC )",
        "(TGG )","(CG. |AGA |AGG )","(GG. )","(\S\S\S )");
    // Ascidian Mitochondrial
    $triplets[13]=array("(TTT |TTC )","(TTA |TTG |CT. )","(ATT |ATC )","(ATG |ATA )","(GT. )","(TC. |AGT |AGC )",
        "(CC. )","(AC. )","(GC. )","(TAT |TAC )","(TAA |TAG )","(CAT |CAC )",
        "(CAA |CAG )","(AAT |AAC )","(AAA |AAG )","(GAT |GAC )","(GAA |GAG )","(TGT |TGC )",
        "(TGG |TGA )","(CG. )","(GG. |AGA |AGG )","(\S\S\S )");
    // Flatworm Mitochondrial
    $triplets[14]=array("(TTT |TTC )","(TTA |TTG |CT. )","(ATT |ATC |ATA )","(ATG )","(GT. )","(TC. |AG. )",
        "(CC. )","(AC. )","(GC. )","(TAT |TAC |TAA )","(TAG )","(CAT |CAC )",
        "(CAA |CAG )","(AAT |AAC |AAA )","(AAG )","(GAT |GAC )","(GAA |GAG )","(TGT |TGC )",
        "(TGG |TGA )","(CG. )","(GG. )","(\S\S\S )");
    // Blepharisma Macronuclear
    $triplets[15]=array("(TTT |TTC )","(TTA |TTG |CT. )","(ATT |ATC |ATA )","(ATG )","(GT. )","(TC. |AGT |AGC )",
        "(CC. )","(AC. )","(GC. )","(TAT |TAC )","(TAA |TGA )","(CAT |CAC )",
        "(CAA |CAG |TAG )","(AAT |AAC )","(AAA |AAG )","(GAT |GAC )","(GAA |GAG )","(TGT |TGC )",
        "(TGG )","(CG. |AGA |AGG )","(GG. )","(\S\S\S )");
    // Chlorophycean Mitochondrial
    $triplets[16]=array("(TTT |TTC )","(TTA |TTG |CT. |TAG )","(ATT |ATC |ATA )","(ATG )","(GT. )","(TC. |AGT |AGC )",
        "(CC. )","(AC. )","(GC. )","(TAT |TAC )","(TAA |TGA )","(CAT |CAC )",
        "(CAA |CAG )","(AAT |AAC )","(AAA |AAG )","(GAT |GAC )","(GAA |GAG )","(TGT |TGC )",
        "(TGG )","(CG. |AGA |AGG )","(GG. )","(\S\S\S )");
    // Trematode Mitochondrial
    $triplets[21]=array("(TTT |TTC )","(TTA |TTG |CT. )","(ATT |ATC )","(ATG |ATA )","(GT. )","(TC. |AG. )",
        "(CC. )","(AC. )","(GC. )","(TAT |TAC )","(TAA |TAG )","(CAT |CAC )",
        "(CAA |CAG )","(AAT |AAC |AAA )","(AAG )","(GAT |GAC )","(GAA |GAG )","(TGT |TGC )",
        "(TGG |TGA )","(CG. )","(GG. )","(\S\S\S )");
    // Scenedesmus obliquus mitochondrial
    $triplets[22]=array("(TTT |TTC )","(TTA |TTG |CT. |TAG )","(ATT |ATC |ATA )","(ATG )","(GT. )","(TCT |TCC |TCG |AGT |AGC )",
        "(CC. )","(AC. )","(GC. )","(TAT |TAC )","(TAA |TGA |TCA )","(CAT |CAC )",
        "(CAA |CAG )","(AAT |AAC )","(AAA |AAG )","(GAT |GAC )","(GAA |GAG )","(TGT |TGC )",
        "(TGG )","(CG. |AGA |AGG )","(GG. )","(\S\S\S )");
    // Thraustochytrium mitochondrial code
    $triplets[23]=array("(TTT |TTC )","(TTG |CT. )","(ATT |ATC |ATA )","(ATG )","(GT. )","(TC. |AGT |AGC )",
        "(CC. )","(AC. )","(GC. )","(TAT |TAC )","(TTA |TAA |TAG |TGA )","(CAT |CAC )",
        "(CAA |CAG )","(AAT |AAC )","(AAA |AAG )","(GAT |GAC )","(GAA |GAG )","(TGT |TGC )",
        "(TGG )","(CG. |AGA |AGG )","(GG. )","(\S\S\S )");

    // place a space after each triplete in the sequence
    $temp = chunk_split($seq,3,' ');

    // replace triplets by corresponding amnoacid
    $peptide = preg_replace ($triplets[$genetic_code], $aminoacids, $temp);

    // return peptide sequence
    return $peptide;
}

// ##############################################################################
// ##############   translation with custom genetic code   ######################
// ##############################################################################
function translate_DNA_to_protein_customcode($seq,$gc){
    // More info: http://www.ncbi.nlm.nih.gov/Taxonomy/Utils/wprintgc.cgi?mode

    // The sequence is chopped and @ is inserted after each triplete
    $temp=chunk_split($seq,3,' ');

    // each triplete replace by corresponding amnoacid
    $temp = str_replace ("TTT ",substr($gc, 0, 1)."  ",$temp);
    $temp = str_replace ("TTC ",substr($gc, 1, 1)."  ",$temp);
    $temp = str_replace ("TTA ",substr($gc, 2, 1)."  ",$temp);
    $temp = str_replace ("TTG ",substr($gc, 3, 1)."  ",$temp);
    $temp = str_replace ("TCT ",substr($gc, 4, 1)."  ",$temp);
    $temp = str_replace ("TCC ",substr($gc, 5, 1)."  ",$temp);
    $temp = str_replace ("TCA ",substr($gc, 6, 1)."  ",$temp);
    $temp = str_replace ("TCG ",substr($gc, 7, 1)."  ",$temp);
    $temp = str_replace ("TAT ",substr($gc, 8, 1)."  ",$temp);
    $temp = str_replace ("TAC ",substr($gc, 9, 1)."  ",$temp);
    $temp = str_replace ("TAA ",substr($gc,10, 1)."  ",$temp);
    $temp = str_replace ("TAG ",substr($gc,11, 1)."  ",$temp);
    $temp = str_replace ("TGT ",substr($gc,12, 1)."  ",$temp);
    $temp = str_replace ("TGC ",substr($gc,13, 1)."  ",$temp);
    $temp = str_replace ("TGA ",substr($gc,14, 1)."  ",$temp);
    $temp = str_replace ("TGG ",substr($gc,15, 1)."  ",$temp);
    $temp = str_replace ("CTT ",substr($gc,16, 1)."  ",$temp);
    $temp = str_replace ("CTC ",substr($gc,17, 1)."  ",$temp);
    $temp = str_replace ("CTA ",substr($gc,18, 1)."  ",$temp);
    $temp = str_replace ("CTG ",substr($gc,19, 1)."  ",$temp);
    $temp = str_replace ("CCT ",substr($gc,20, 1)."  ",$temp);
    $temp = str_replace ("CCC ",substr($gc,21, 1)."  ",$temp);
    $temp = str_replace ("CCA ",substr($gc,22, 1)."  ",$temp);
    $temp = str_replace ("CCG ",substr($gc,23, 1)."  ",$temp);
    $temp = str_replace ("CAT ",substr($gc,24, 1)."  ",$temp);
    $temp = str_replace ("CAC ",substr($gc,25, 1)."  ",$temp);
    $temp = str_replace ("CAA ",substr($gc,26, 1)."  ",$temp);
    $temp = str_replace ("CAG ",substr($gc,27, 1)."  ",$temp);
    $temp = str_replace ("CGT ",substr($gc,28, 1)."  ",$temp);
    $temp = str_replace ("CGC ",substr($gc,29, 1)."  ",$temp);
    $temp = str_replace ("CGA ",substr($gc,30, 1)."  ",$temp);
    $temp = str_replace ("CGG ",substr($gc,31, 1)."  ",$temp);
    $temp = str_replace ("ATT ",substr($gc,32, 1)."  ",$temp);
    $temp = str_replace ("ATC ",substr($gc,33, 1)."  ",$temp);
    $temp = str_replace ("ATA ",substr($gc,34, 1)."  ",$temp);
    $temp = str_replace ("ATG ",substr($gc,35, 1)."  ",$temp);
    $temp = str_replace ("ACT ",substr($gc,36, 1)."  ",$temp);
    $temp = str_replace ("ACC ",substr($gc,37, 1)."  ",$temp);
    $temp = str_replace ("ACA ",substr($gc,38, 1)."  ",$temp);
    $temp = str_replace ("ACG ",substr($gc,39, 1)."  ",$temp);
    $temp = str_replace ("AAT ",substr($gc,40, 1)."  ",$temp);
    $temp = str_replace ("AAC ",substr($gc,41, 1)."  ",$temp);
    $temp = str_replace ("AAA ",substr($gc,42, 1)."  ",$temp);
    $temp = str_replace ("AAG ",substr($gc,43, 1)."  ",$temp);
    $temp = str_replace ("AGT ",substr($gc,44, 1)."  ",$temp);
    $temp = str_replace ("AGC ",substr($gc,45, 1)."  ",$temp);
    $temp = str_replace ("AGA ",substr($gc,46, 1)."  ",$temp);
    $temp = str_replace ("AGG ",substr($gc,47, 1)."  ",$temp);
    $temp = str_replace ("GTT ",substr($gc,48, 1)."  ",$temp);
    $temp = str_replace ("GTC ",substr($gc,49, 1)."  ",$temp);
    $temp = str_replace ("GTA ",substr($gc,50, 1)."  ",$temp);
    $temp = str_replace ("GTG ",substr($gc,51, 1)."  ",$temp);
    $temp = str_replace ("GCT ",substr($gc,52, 1)."  ",$temp);
    $temp = str_replace ("GCC ",substr($gc,53, 1)."  ",$temp);
    $temp = str_replace ("GCA ",substr($gc,54, 1)."  ",$temp);
    $temp = str_replace ("GCG ",substr($gc,55, 1)."  ",$temp);
    $temp = str_replace ("GAT ",substr($gc,56, 1)."  ",$temp);
    $temp = str_replace ("GAC ",substr($gc,57, 1)."  ",$temp);
    $temp = str_replace ("GAA ",substr($gc,58, 1)."  ",$temp);
    $temp = str_replace ("GAG ",substr($gc,59, 1)."  ",$temp);
    $temp = str_replace ("GGT ",substr($gc,60, 1)."  ",$temp);
    $temp = str_replace ("GGC ",substr($gc,61, 1)."  ",$temp);
    $temp = str_replace ("GGA ",substr($gc,62, 1)."  ",$temp);
    $temp = str_replace ("GGG ",substr($gc,63, 1)."  ",$temp);
    // no matching triplets -> X
    $temp = preg_replace ("(\S\S\S )", "X  ", $temp);
    $temp = substr ($temp, 0, strlen($r)-2);
    $prot = preg_replace ("/ /","",$temp);
    return $prot;
}

// ##############################################################################
// ##############   show translation aligned   ######################
// ##############################################################################
function show_translations_aligned($sequence,$rvsequence,$frame){
    $scale="         10        20        30        40        50        60        70        80        90         ";
    $barr="         |         |         |         |         |         |         |         |         |          ";


    foreach ($frame as $n => $peptide_sequence){
        $chunked_frames[$n]=chunk_split($peptide_sequence,1,'  ');
    }
    print "<center><table><tr><td nowrap><pre>\n";
    // Show translation of of sequence in 5'->3' direction
    print "<b>Translation of requested code (5'->3')</b>\n\n  $scale\n$barr\n";
    $i=0;
    while($i<strlen($sequence)){
        print substr($sequence,$i,100)."  ";
        if ($i<strlen($sequence)-$i){print $i+100;}
        print "\n";
        print substr($chunked_frames[1],$i,100)."\n";
        print substr(" ".$chunked_frames[2],$i,100)."\n";
        print substr("  ".$chunked_frames[3],$i,100)."\n\n";
        $i+=100;
    }
    // Show translation of complementary sequence
    //    only when requested corresponding frames has been obtained
    if ($frame[6]){
        print "<b>Translation of requested code (complementary DNA chain)</b>\n\n  $scale\n$barr\n";
        $i=0;
        while($i<strlen($rvsequence)){
            print substr($rvsequence,$i,100)."  ";
            if ($i<strlen($sequence)-$i){print $i+100;}
            print "\n";
            print substr($chunked_frames[4],$i,100)."\n";
            print substr(" ".$chunked_frames[5],$i,100)."\n";
            print substr("  ".$chunked_frames[6],$i,100)."\n\n";
            $i+=100;
        }
    }
    print "</td></tr></table></center>\n<HR>\n";

}
// ##############################################################################
function str_is_int($str) {
    $var=intval($str);
    return ("$str"=="$var");
}

// ##############################################################################
// #######################       Main Page/Form         #########################
// ##############################################################################
function print_form(){
    ?>
    <html><head><title>DNA to protein translation</title>

        <script language="JavaScript"><!--

            // Created by Joseba Bikandi
            // Improvements of the code and suggestions are wellcome
            // email: oipbibij@lg.ehu.es

            function Removeuseless(str) {
                str = str.split(/\d/).join("");
                str = str.split(/\W/).join("");
                return str;
            }


            function strrev() {
                str=document.mydna.sequence.value.toUpperCase();

                if (!str) {document.mydna.sequence.value=''};
                var revstr=' ';
                var k=0;
                for (i = str.length-1; i>=0; i--) {
                    revstr+=str.charAt(i);
                    k+=1;
                };
                document.mydna.sequence.value=revstr;
                tidyup();
            }


            function tidyup() {
                str=document.mydna.sequence.value.toUpperCase();
                str=Removeuseless(str);

                if (!str) {document.mydna.sequence.value=''};
                var revstr=' ';
                var k=0;
                for (i =0; i<str.length; i++) {
                    revstr+=str.charAt(i);
                    k+=1;
                    if (k==Math.floor(k/10)*10) {revstr+=' '};
                    if (k==Math.floor(k/60)*60) {revstr+=k+'\n '};
                };

                document.mydna.sequence.value=revstr;
            }


            function Complement() {
                var str=document.mydna.sequence.value.toUpperCase();
                str = str.split("A").join("t");
                str = str.split("T").join("a");
                str = str.split("G").join("c");
                str = str.split("C").join("g");
                str=str.toUpperCase();
                document.mydna.sequence.value=str;
                tidyup();
            };

            function Clear() {
                document.mydna.sequence.value='';
            };


            //--></script>

    </head><body bgcolor=white text=black onLoad="tidyup ()">
    <center>

        <table cellpadding=5><tr><td>
                    <form name=mydna method=post action="<? print $_SERVER["PHP_SELF"]; ?>">
                        <h2>DNA to protein translation (<a href=?action=info>info</a>)</h2>
                        <p>
                            <a href="javascript: tidyup ()">Tidy Up</a> &nbsp;
                            <a href="javascript: strrev ()">Reverse</a> &nbsp;
                            <a href="javascript: Complement ()">Complement</a> &nbsp;
                            <a href="javascript: Clear() ">Clear</a>
                            <br>
                            <textarea name=sequence cols=75 rows=10> GGAGTGAGGG GAGCAGTTGG GCCAAGATGG CGGCCGCCGA GGGACCGGTG GGCGACGCGG 60
 GAGTGAGGGG AGCAGTTGGG CCAAGATGGC GGCCGCCGAG GGACCGGTGG GCGACGGGGG 120
 AGTGAGGGGA GCAGTTGGGC CAAGATGGCG GCCGCCGAGG GACCGGTGGG CGACGGCGGA 180
 GTGAGGGGAG CAGTTGGGCC AAGATGGCGG CCGCCGAGGG ACCGGTGGGC GACGGGGAGT 240
 GAGGGGAGCA GTTGGGCCAA GATGGCGGCC GCCGAGGGAC CGGTGGGCGA CGCGGGAGTG 300</textarea>
                        <div align=center>
                            <input type=submit value="&nbsp; Translate to Protein &nbsp;">
                        </div><p>
                            <table width=100% border=1 bgcolor=DDDDFF cellpadding=5><tr><td>
                                        Translate frames:
                                        <select name=frames>
                                            <option>1
                                            <option value=3 selected>1-3
                                            <option value=6>1-6
                                        </select>
                                        <BR><input type=checkbox name=dgaps value=1> Output the amino acids with double gaps (--)
                                        <hr>
                                        <input type=checkbox name=show_aligned value=1> Show Translations Aligned
                                        <hr>
                                        <input type=checkbox name=search_orfs value=1> Search for ORFs
                        <p>&nbsp;&nbsp;&nbsp;&nbsp;Minimum size of protein sequence <input type=text size=5 name=protsize value=50>
                            and <input type=checkbox name=only_coding value=1> do not show non-coding
                            <br>
                            &nbsp;&nbsp;&nbsp;&nbsp;<input type=checkbox name=trimmed value=1> ORFs trimmed to MET-to-Stop <a href="?action=info#Met">info</a>

                        <hr>
                        Genetic code: <br><select name=genetic_code>
                            <option value=1 selected>Standard
                            <option value=2>Vertebrate Mitochondrial
                            <option value=3>Yeast Mitochondrial
                            <option value=4>Mold, Protozoan and Coelenterate Mitochondrial. Mycoplasma, Spiroplasma
                            <option value=5>Invertebrate Mitochondrial
                            <option value=6>Ciliate Nuclear; Dasycladacean Nuclear; Hexamita Nuclear
                            <option value=9>Echinoderm Mitochondrial
                            <option value=10>Euplotid Nuclear
                            <option value=11>Bacterial and Plant Plastid
                            <option value=12>Alternative Yeast Nuclear
                            <option value=13>Ascidian Mitochondrial
                            <option value=14>Flatworm Mitochondrial
                            <option value=15>Blepharisma Macronuclear
                            <option value=16>Chlorophycean Mitochondrial
                            <option value=21>Trematode Mitochondrial
                            <option value=22>Scenedesmus obliquus mitochondrial
                            <option value=23>Thraustochytrium mitochondrial code
                        </select>
                        <br>

                        Use custom genetic code<input type=checkbox name=usemycode value=1>
                        <br>
                        <input type=text name=mycode size=64 value=FFLLSSSSYY**CC*WLLLLPPPPHHQQRRRRIIIMTTTTNNKKSSRRVVVVAAAADDEEGGGG>  <a href="?action=info#custom">info</a>
                </td></tr></table>
        </td></tr></table>
        Source code is available at <a href=http://www.biophp.org/minitools/dna_to_protein>BioPHP.org</a>
    </center>
    </body></html>



    <?
}
// ##############################################################################
// #######################        Information           #########################
// ##############################################################################
function print_info(){
    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
    <html>
    <head>
        <title>Custom genetic code</title>
        <meta http-equiv="content-type"
              content="text/html; charset=ISO-8859-1">
    </head>
    <body bgcolor=FFFFFF>
    <span style="font-weight: bold;"></span>
    <table cellpadding="2" cellspacing="2" border="0"
           style="text-align: left; width: 650px; margin-left: auto; margin-right: auto;">
        <tbody>
        <tr>
            <td style="vertical-align: top;">
                <h1 style="text-align: center;">DNA to protein translation tool<br>
                </h1>
                <div style="text-align: right;"><a href="<? print $_SERVER["PHP_SELF"]; ?>">Start using
                        this tool</a><br>
                </div>
                <br>
                This tool works similarly to other ones available online or programs
                allowing this feature. Genetic codes used in this service are those ones
                compiled by <a
                    href="http://www.ncbi.nlm.nih.gov/Taxonomy/Utils/wprintgc.cgi?mode">Andrzej
                    (Anjay) Elzanowski and Jim Ostell</a>. <br>
                <br>
                DNA sequence may be added as shown in the example sequence or in any
                other format (number, spaces and line feeds are removed). <span
                    style="font-weight: bold;">JavaScript</span> enable browser will be
                able to perform small tasks as for example tiding up the sequence and
                getting reverse or complement sequences.<br>
                <br>
                Translation to protein will be performed by using one of the predefined
                genetic codes, or by using <a href="#custom">custom genetic code</a>.
                Minimum size of protein sequence for Open Reading Frames (ORF) is
                customizable, and they can be trimmed to <a href="#Met">MET-to-Stop</a>.
                Showing translation alignment is optional, and aminoacids will be
                displaied as a <a href="#aminoacids">1-letter&nbsp; aminoacids code</a>.<br>
                <br>
                After translation, in the response page ORFs are shown as arrow. In
                order to check ORFs represented by those arrows, click on them and a new
                browser window will be opened showing in red letters the DNA sequence
                corresponding to that specific ORF and translated protein. This feature
                requires a <span style="font-weight: bold;">JavaScript</span> enable
                browser. <br>
                <br>
                <big><big><u><b><br>
                            </b></u></big></big><big
                    style="font-weight: bold; text-decoration: underline;"><big><a
                            name="custom"></a></big></big><big><big><u><b> How to use custom
                                genetic codes</b></u></big></big><br>
                <br>
                The genetic code used to translate a sequence into protein may be
                customized. <br>
                <br>
                This service allows introducing the genetic code as a string, where
                each character corresponds to one aminoacid and   asteriscs represents
                termination codes. In the example bellow is shown the standard genetic
                code and the corresponding triplets.<br>
                <br>
                <table cellpadding="2" cellspacing="2" border="0" align="center">
                    <tbody>
                    <tr>
                        <td valign="top" bgcolor="#ccffff"><b><big>Standard genetic
                                    code</big></b><br>
                            <pre><br>  Aminoacid/Termination <b><font color="#ff0000">F</font>FLLSSSSYY<font
                                        color="#ff0000">*</font>*CC*WLLLLPPPPHHQQRRRRIIIMTTTTNNKKSSRRVVVVAAAADDEEGGGG</b><br><br>  -- Base1              <font
                                    color="#ff0000"><b>T</b></font>TTTTTTTTT<font color="#ff0000"><b>T</b></font>TTTTTCCCCCCCCCCCCCCCCAAAAAAAAAAAAAAAAGGGGGGGGGGGGGGGG<br>  -- Base2              <font
                                    color="#ff0000"><b>T</b></font>TTTCCCCAA<font color="#ff0000"><b>A</b></font>AGGGGTTTTCCCCAAAAGGGGTTTTCCCCAAAAGGGGTTTTCCCCAAAAGGGG<br>  -- Base3              <font
                                    color="#ff0000"><b>T</b></font>CAGTCAGTC<font color="#ff0000"><b>A</b></font>GTCAGTCAGTCAGTCAGTCAGTCAGTCAGTCAGTCAGTCAGTCAGTCAGTCAG<br><br><br><b>Explanation</b><br><br></pre>
                            <small>In the first line, the first character ("F")
                                represents Phenylalanine,which is encoded by the triplet TTT (first
                                character of "Base1", <br>
                                first character of "Base2" and&nbsp;first character of "Base3")<br>
                                <br>
                                The eleventh character ("*") represents a termination code, which is
                                encoded by the triplet TAA.<br>
                                <br>
                            </small></td>
                    </tr>
                    </tbody>
                </table>
                <br>
                <br>
                The custom genetic code provided must be 64 characters long.
                Correspondence between characters and aminoacids may follow the <a
                    href="?action=info#aminoacids">system</a>
                used in this service or may be different, but it will be always case
                insentitive. <br>
                <br>
                <big style="font-weight: bold; text-decoration: underline;"><big><a
                            name="Met"></a>Methionine as a initiation code</big></big><br>
                <br>
                When searching "ORFs trimmed to MET-to-Stop", they will be shown the
                longest ORFs available (from methionine to Stop), so that within the
                ORF&nbsp; there may be several methionines, as for example in the
                aminoacid secuence bellow:<br>
                <br>
                <div style="text-align: center;">
      <pre><span style="color: rgb(204, 0, 0);">M</span>QVVLITLSDVNSTTWGSRISLGY<span
              style="color: rgb(204, 0, 0);">M</span>AACFRVREVELVKNL<span
              style="color: rgb(204, 0, 0);">M</span><span
              style="color: rgb(204, 0, 0);">M</span>TGVVLQFTVDFPPSNSEFPH<span
              style="color: rgb(204, 0, 0);">M</span>LGNSNTISPFIPISAT</pre>
                </div>
                <br>
                <br>
                <br>
                <big style="font-weight: bold; text-decoration: underline;"><big><a
                            name="aminoacids"></a>1-letter aminoacid codes </big></big><br>
                <br>
                <pre style="color: rgb(0, 0, 0);"><strong>    A  alanine                         P  proline<br>    B  aspartate or asparagine         Q  glutamine<br>    C  cysteine                        R  arginine<br>    D  aspartate                       S  serine<br>    E  glutamate                       T  threonine<br>    F  phenylalanine                   U  selenocysteine<br>    G  glycine                         V  valine<br>    H  histidine                       W  tryptophan<br>    I  isoleucine                      Y  tyrosine<br>    K  lysine                          Z  glutamate or glutamine<br>    L  leucine                         X  any<br>    M  methionine                      *  translation stop<br>    N  asparagine                      -  gap of indeterminate length<br><br></strong><span
                        style="font-weight: bold;"><br><br>      </span><br>      </pre>
                <div style="text-align: center;"><br>Source code is available at <a href=http://www.biophp.org/minitools/dna_to_protein>BioPHP.org</a></div>
                <pre style="color: rgb(0, 0, 0);"><br></pre>
                <br>
                <br>
                <br>
                <br>
                <br>
                <br>
                <br>
                <br>
                <br>
                <br>
                <br>
            </td>
        </tr>
        </tbody>
    </table>
    <pre style="color: rgb(0, 0, 0);"><br></pre>
    <br>
    <br>
    <br>
    <br>
    </body>
    </html>

    <?

}
?>
