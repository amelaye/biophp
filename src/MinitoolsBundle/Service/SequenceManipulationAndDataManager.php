<?php
/**
 * Sequence Manipulation and DATA Functions
 * @author Amélie DUVERNET akka Amelaye
 * Inspired by BioPHP's project biophp.org
 * Created 1st march  2019
 * Last modified 1st march 2019
 * RIP Pasha, gone 27 february 2019 =^._.^= ∫
 */
namespace MinitoolsBundle\Service;

class SequenceManipulationAndDataManager
{
    function Complement($seq){
        // change the sequence to upper case
        $seq = strtoupper ($seq);
        // the system used to get the complementary sequence is simple but fas
        $seq=str_replace("A", "t", $seq);
        $seq=str_replace("T", "a", $seq);
        $seq=str_replace("G", "c", $seq);
        $seq=str_replace("C", "g", $seq);
        $seq=str_replace("Y", "r", $seq);
        $seq=str_replace("R", "y", $seq);
        $seq=str_replace("W", "w", $seq);
        $seq=str_replace("S", "s", $seq);
        $seq=str_replace("K", "m", $seq);
        $seq=str_replace("M", "k", $seq);
        $seq=str_replace("D", "h", $seq);
        $seq=str_replace("V", "b", $seq);
        $seq=str_replace("H", "d", $seq);
        $seq=str_replace("B", "v", $seq);
        // change the sequence to upper case again for output
        $seq = strtoupper ($seq);
        return $seq;
    }

    function remove_non_coding($seq) {
        // change the sequence to upper case
        $seq=strtoupper($seq);
        // remove non-words (\W), con coding ([^ATGCYRWSKMDVHBN]) and digits (\d) from sequence
        $seq=preg_replace("/\W|[^ATGCYRWSKMDVHBN]|\d/","",$seq);
        // replace all X by N (to normalized sequences)
        $seq=preg_replace("/X/","N",$seq);
        return $seq;
    }
    function Display_both_strands($seq) {
        // get the complementary sequence
        $revcomp=Complement($seq);
        $result="";
        $i=0;
        while ($i<strlen($seq)){
            if(strlen($seq)<($i+70)){$j=strlen($seq);}else{$j=$i;}
            $result.=substr($seq,$i,70)."\t$j\n";
            $result.=substr($revcomp,$i,70)."\t$j\n";
            $result.="\n"; //line break
            $i+=70;
        }
        return $result;
    }
    function GC_content($seq) {
        $number_of_G=substr_count($seq,"G");
        $number_of_C=substr_count($seq,"C");
        $gc_porcentaje=round(100*($number_of_G+$number_of_C)/strlen($seq),2);
        return "G+C %: $gc_porcentaje\n\n";
    }
    function toRNA($seq) {
        // replace T by U
        $seq=preg_replace("/T/","U",$seq);
        $seq=chunk_split($seq, 70);
        return $seq;
    }
    function ACGT_content($seq) {
        $result="Nucleotide composition";
        $result.="\nA: ".substr_count($seq,"A");
        $result.="\nC: ".substr_count($seq,"C");
        $result.="\nG: ".substr_count($seq,"G");
        $result.="\nT: ".substr_count($seq,"T");
        if (substr_count($seq,"Y") > 0) {
            $result .= "\nY: ".substr_count($seq,"Y");
        }
        if (substr_count($seq,"R") > 0) {
            $result .= "\nR: ".substr_count($seq,"R");
        }
        if (substr_count($seq,"W") > 0) {
            $result .= "\nW: ".substr_count($seq,"W");
        }
        if (substr_count($seq,"S") > 0) {
            $result .= "\nS: ".substr_count($seq,"S");
        }
        if (substr_count($seq,"K") > 0) {
            $result .= "\nK: ".substr_count($seq,"K");
        }
        if (substr_count($seq,"M") > 0) {
            $result .= "\nM: ".substr_count($seq,"M");
        }
        if (substr_count($seq,"D") > 0) {
            $result .= "\nD: ".substr_count($seq,"D");
        }
        if (substr_count($seq,"V") > 0) {
            $result .= "\nV: ".substr_count($seq,"V");
        }
        if (substr_count($seq,"H") > 0) {
            $result .= "\nH: ".substr_count($seq,"H");
        }
        if (substr_count($seq,"B") > 0) {
            $result .= "\nB: ".substr_count($seq,"B");
        }
        if (substr_count($seq,"N") > 0) {
            $result .= "\nN: ".substr_count($seq,"N");
        }
        $result.="\n\n";
        return $result;
    }
}