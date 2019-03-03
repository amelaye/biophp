<?php
/**
 * Formulas Functions
 * @author Amélie DUVERNET akka Amelaye
 * Inspired by BioPHP's project biophp.org
 * Created 3 march  2019
 * Last modified 3 march 2019
 * RIP Pasha, gone 27 february 2019 =^._.^= ∫
 */
namespace MinitoolsBundle\Service;

class RandomSequencesManager
{

// Generate a random DNA sequence
//    $a, $c, $g and $t are the number of nucleotides A, C, G or T
// Usage example:
//     $seq = randon_DNA(200,200,200,200);
    public function randon_DNA($a,$c,$g,$t)
    {
        return str_shuffle(str_repeat("A",$a).str_repeat("C",$c).str_repeat("G",$g).str_repeat("T",$t));
    }


// Generate a random protein sequence
//    $a, $c, $g and $t are the number of nucleotides A, C, G or T
// Usage example:
//     $seq = randon_prot(100,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100)
    public function randon_prot($A,$C,$D,$E,$F,$G,$H,$I,$K,$L,$M,$N,$P,$Q,$R,$S,$T,$V,$W,$Y)
    {
        return str_shuffle(str_repeat("A",$A).str_repeat("C",$C).str_repeat("D",$D).str_repeat("E",$E).
            str_repeat("F",$F).str_repeat("G",$G).str_repeat("H",$H).str_repeat("I",$I).
            str_repeat("K",$K).str_repeat("L",$L).str_repeat("M",$M).str_repeat("N",$N).
            str_repeat("P",$P).str_repeat("Q",$Q).str_repeat("R",$R).str_repeat("S",$S).
            str_repeat("T",$T).str_repeat("V",$V).str_repeat("W",$W).str_repeat("Y",$Y));
    }
}