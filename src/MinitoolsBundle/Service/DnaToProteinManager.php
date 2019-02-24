<?php
/**
 * DNA To Protein Functions
 * @author Amélie DUVERNET akka Amelaye
 * Inspired by BioPHP's project biophp.org
 * Created 24 february 2019
 * Last modified 24 february 2019
 */
namespace MinitoolsBundle\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use AppBundle\Entity\Sequence;
use AppBundle\Service\ParseGenbankManager;
use AppBundle\Service\ParseSwissprotManager;
use AppBundle\Entity\Database;

class DnaToProteinManager
{
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

    function RevComp_DNA($seq){
        $seq= strtoupper($seq);
        $original=  array("(A)","(T)","(G)","(C)","(Y)","(R)","(W)","(S)","(K)","(M)","(D)","(V)","(H)","(B)");
        $complement=array("t","a","c","g","r","y","w","s","m","k","h","b","d","v");
        $seq = preg_replace ($original, $complement, $seq);
        $seq= strtoupper ($seq);
        return $seq;
    }

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

    function str_is_int($str) {
        $var=intval($str);
        return ("$str"=="$var");
    }
}