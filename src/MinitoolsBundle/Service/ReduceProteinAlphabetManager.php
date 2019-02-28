<?php
/**
 * Reduce Protein Alphabet Functions
 * @author Amélie DUVERNET akka Amelaye
 * Inspired by BioPHP's project biophp.org
 * Created 27 february 2019 - RIP Pasha =^._.^= ∫
 * Last modified 27 february 2019
 */
namespace MinitoolsBundle\Service;

class ReduceProteinAlphabetManager
{
    public function __construct($protein_colors)
    {
        $this->protein_colors = $protein_colors;
    }

    public function printReducedCodeInfo($type)
    {
        if ($type == 20) {
            $message = "<b>Complete alphabet";
        }
        if ($type == 2) {
            $message =  "<b>Two letters alphabet</b>";
            $message .= "\nAGTSNQDEHRKP => P: Hydrophilic\nCMFILVWY =>H: Hydrophobic";
        }
        if ($type == 5) {
            $message = "<b>Five letters alphabet: Chemical / structural properties</b>";
            $message .= "\nIVL   => A: Aliphatic\nFYWH  => R: Aromatic\nKRDE  => C: Charged\nGACS  => T: Tiny\nTMQNP => D: Diverse";
        }
        if ($type == 6) {
            $message =  "<b>Six letters alphabet: Chemical / structural properties #2</b>";
            $message .= "\nIVL   => A: Aliphatic\nFYWH  => R: Aromatic\nKR    => C: Pos. charged\nDE    => C: Neg. charged\nGACS  => T: Tiny\nTMQNP => D: Diverse";
        }
        if ($type == "3IMG") {
            print "<b>3 IMGT amino acid hydropathy alphabet</b>\nIVLFCMAW => P: Hydrophilic\nGTSYPM => N: Neutral\nDNEQKR =>H: Hydrophobic";
        }
        if ($type=="5IMG"){
            print "<b>5 IMGT amino acid volume alphabet</b>\nGAS   => G: 60-90\nCDPNT => C: 108-117\nEVQH  => E: 138-154\nMILKR => M: 162-174\nFYW   => F: 189-228";
        }
        if ($type=="11IMG"){
            print "<b>11 IMGT amino acid chemical characteristics alphabet</b>\nAVIL => A: Aliphatic\nF    => F: Phenylalanine\nCM   => C: Sulfur\nG    => G: Glycine\nST   => S: Hydroxyl\nW    => W: Tryptophan\nY    => Y: Tyrosine\nP    => P: Proline\nDE   => A: Acidic\nNQ   => N: Amide\nHKR  => H: Basic";
        }
        if ($type=="Murphy15"){
            print "<b>Murphy et al, 2000; 15 letters alphabet</b>\nLVIM => L: Large hydrophobic\nC    => C\nA    => A\nG    => G\nS    => S\nT    => T\nP    => P\nFY   => F: Hydrophobic/aromatic sidechains\nW    => W\nE    => E\nD    => D\nN    => N\nQ    => Q\nKR   => K: Long-chain positively charged\nH    => H";
        }
        if ($type=="Murphy10"){
            print "<b>Murphy et al, 2000; 10 letters alphabet</b>\nLVIM => L: Large hydrophobic\nC    => C\nA    => A\nG    => G\nST   => S: Polar\nP    => P\nFYW  => F:Hydrophobic/aromatic sidechains\nEDNQ => E: Charged / polar\nKR   => K: Long-chain positively charged\nH    =>H";
        }
        if ($type=="Murphy8"){
            print "<b>Murphy et al, 2000; 8 letters alphabet</b>\nLVIMC => L: Hydrophobic\nAG    => A\nST    => S: Polar\nP     => P\nFYW   => F: Hydrophobic/aromatic sidechains\nEDNQ  => E\nKR    => K: Long-chain positively charged\nH     => H";
        }
        if ($type=="Murphy4"){
            print "<b>Murphy et al, 2000; 4 letters alphabet</b>\nLVIMC   => L: Hydrophobic\nAGSTP   => A\nFYW     => F: Hydrophobic/aromatic sidechains\nEDNQKRH =>E";
        }
        if ($type=="Murphy2"){
            print "<b>Murphy et al, 2000; 2 letters alphabet</b>\nLVIMCAGSTPFYW => P: Hydrophobic\nEDNQKRH       => E: Hydrophilic";
        }
        if ($type=="Wang5"){
            print "<b>Wang & Wang, 1999; 5 letters alphabet</b>\nCMFILVWY => I\nATH      => A\nGP       => G\nDE       => E\nSNQRK    => K";
        }
        if ($type=="Wang5v"){
            print "<b>Wang & Wang, 1999; 5 letters variant alphabet</b>\nCMFI => I\nLVWY => L\nATGS => A\nNQDE => E\nHPRK => K";
        }
        if ($type=="Wang3"){
            print "<b>Wang & Wang, 1999; 3 letters alphabet</b>\nCMFILVWY => I\nATHGPR   => A\nDESNQK   => E";
        }
        if ($type=="Wang2"){
            print "<b>Wang & Wang, 1999; 2 letters alphabet</b>\nCMFILVWY     => I\nATHGPRDESNQK => A";
        }
        if ($type=="Li10"){
            print "<b>Li et al, 2003; 10 letters alphabet</b>\nC   => C\nFYW => Y\nML  => L\nIV  => V\nG   => G\nP   => P\nATS => S\nNH  => N\nQED => E\nRK  => K";
        }
        if ($type=="Li5"){                                       // YIGSE
            print "<b>Li et al, 2003; 5 letters alphabet</b>\nCFYW    => Y\nMLIV    => I\nG       => G\nPATS    => S\nNHQEDRK => E";
        }
        if ($type=="Li4"){                                        // YISE
            print "<b>Li et al, 2003; 4 letters alphabet</b>\nCFYW    => Y\nMLIV    => I\nGPATS   => S\nNHQEDRK => E";
        }
        if ($type=="Li3"){                                       // ISE
            print "<b>Li et al, 2003; 3 letters alphabet</b>\nCFYWMLIV => I\nGPATS    => S\nNHQEDRK  => E";
        }
    }


    /**
     * Get colored html code for $seq by using the $seq2 (the reduced sequence)
     * as a reference, and according to the $type of reduction selected
     * returns an html code
     * @param $seq
     * @param $seq2
     * @param $type
     * @return string
     */
    public function color($seq, $seq2, $type)
    {
        $letters_array = $this->protein_colors;

        $new_seq="";

        for($i=0;$i<strlen($seq);$i++){
            $letter_seq=substr($seq,$i,1);
            $letter_seq2=substr($seq2,$i,1);
            if ($letters_array[$type][$letter_seq2]!=""){
                $new_seq.="<font color=".strtolower($letters_array[$type][$letter_seq2]).">$letter_seq</font>";
            }else{
                $new_seq.=$letter_seq;
            }
        }

        return $new_seq;
    }


// #######################################################################
// Reduce alphabet for $seq by using the predefined $type type of reduction
//  returns a reduced sequence
    function reduce_alphabet($seq,$type){
        if ($type==20){
            // Not reduced, so nothing to do
        }
        if ($type==2){                                                     // PH
            $seq=preg_replace("/A|G|T|S|N|Q|D|E|H|R|K|P/","p",$seq);   // Hydrophilic
            $seq=preg_replace("/C|M|F|I|L|V|W|Y/","h",$seq);           // Hydrophobic
            $seq=strtoupper($seq);
            return $seq;
        }
        if ($type==5){                                        // ARCTD
            $seq=preg_replace("/I|V|L/","a",$seq);        // Aliphatic
            $seq=preg_replace("/F|Y|W|H/","r",$seq);      // Aromatic
            $seq=preg_replace("/K|R|D|E/","c",$seq);      // Charged
            $seq=preg_replace("/G|A|C|S/","t",$seq);      // Tiny
            $seq=preg_replace("/T|M|Q|N|P/","d",$seq);    // Diverse
            $seq=strtoupper($seq);
            return $seq;
        }
        if ($type==6){                                        // ARPNTD
            $seq=preg_replace("/I|V|L/","a",$seq);        // Aliphatic
            $seq=preg_replace("/F|Y|W|H/","r",$seq);      // Aromatic
            $seq=preg_replace("/K|R/","p",$seq);          // Pos. charged
            $seq=preg_replace("/D|E/","n",$seq);          // Neg. charged
            $seq=preg_replace("/G|A|C|S/","t",$seq);      // Tiny
            $seq=preg_replace("/T|M|Q|N|P/","d",$seq);    // Diverse
            $seq=strtoupper($seq);
            return $seq;
        }
        if ($type=="3IMG"){                                     // PNH
            $seq=preg_replace("/D|N|E|Q|K|R/","p",$seq);      // Hydrophilic
            $seq=preg_replace("/G|T|S|Y|P|M/","n",$seq);      // Neutral
            $seq=preg_replace("/I|V|L|F|C|M|A|W/","h",$seq);  // Hydrophobic
            $seq=strtoupper($seq);
            return $seq;
        }
        if ($type=="5IMG"){                                    // GCEMF  (IMGT amino acid volume)
            $seq=preg_replace("/G|A|S/","g",$seq);         // 60-90
            $seq=preg_replace("/C|D|P|N|T/","c",$seq);     // 108-117
            $seq=preg_replace("/E|V|Q|H/","e",$seq);       // 138-154
            $seq=preg_replace("/M|I|L|K|R/","m",$seq);     // 162-174
            $seq=preg_replace("/F|Y|W/","f",$seq);         // 189-228
            $seq=strtoupper($seq);
            return $seq;
        }
        if ($type=="11IMG"){                                // AFCGSWYPDNH
            $seq=preg_replace("/A|V|I|L/","a",$seq);       // Aliphatic
            $seq=preg_replace("/F/","f",$seq);          // Phenylalanine
            $seq=preg_replace("/C|M/","c",$seq);         // Sulfur
            $seq=preg_replace("/G/","g",$seq);          // Glycine
            $seq=preg_replace("/S|T/","s",$seq);         // Hydroxyl
            $seq=preg_replace("/W/","w",$seq);          // Tryptophan
            $seq=preg_replace("/Y/","y",$seq);          // Tyrosine
            $seq=preg_replace("/P/","p",$seq);          // Proline
            $seq=preg_replace("/D|E/","d",$seq);         // Acidic
            $seq=preg_replace("/N|Q/","n",$seq);         // Amide
            $seq=preg_replace("/H|K|R/","h",$seq);        // Basic
            $seq=strtoupper($seq);
            return $seq;
        }
        if ($type=="Murphy15"){                             // LCAGSTPFWEDNQKH
            $seq=preg_replace("/L|V|I|M/","l",$seq);    // Large hydrophobic
            $seq=preg_replace("/C/","c",$seq);
            $seq=preg_replace("/A/","a",$seq);
            $seq=preg_replace("/G/","g",$seq);
            $seq=preg_replace("/S/","s",$seq);
            $seq=preg_replace("/T/","t",$seq);
            $seq=preg_replace("/P/","p",$seq);
            $seq=preg_replace("/F|Y/","f",$seq);       // Hydrophobic/aromatic sidechains
            $seq=preg_replace("/W/","w",$seq);
            $seq=preg_replace("/E/","e",$seq);
            $seq=preg_replace("/D/","d",$seq);
            $seq=preg_replace("/N/","n",$seq);
            $seq=preg_replace("/Q/","q",$seq);
            $seq=preg_replace("/K|R/","k",$seq);       // Long-chain positively charged
            $seq=preg_replace("/H/","h",$seq);
            $seq=strtoupper($seq);
            return $seq;
        }
        if ($type=="Murphy10"){                            // LCAGSPFEKH
            $seq=preg_replace("/L|V|I|M/","l",$seq);   // Large hydrophobic
            $seq=preg_replace("/C/","c",$seq);
            $seq=preg_replace("/A/","a",$seq);
            $seq=preg_replace("/G/","g",$seq);
            $seq=preg_replace("/S|T/","s",$seq);       // Polar
            $seq=preg_replace("/P/","p",$seq);
            $seq=preg_replace("/F|Y|W/","f",$seq);     // Hydrophobic/aromatic sidechains
            $seq=preg_replace("/E|D|N|Q/","e",$seq);   // Charged / polar
            $seq=preg_replace("/K|R/","k",$seq);       // Long-chain positively charged
            $seq=preg_replace("/H/","h",$seq);
            $seq=strtoupper($seq);
            return $seq;
        }
        if ($type=="Murphy8"){                              // LASPFEKH
            $seq=preg_replace("/L|V|I|M|C/","l",$seq);  // Hydrophobic
            $seq=preg_replace("/A|G/","a",$seq);
            $seq=preg_replace("/S|T/","s",$seq);        // Polar
            $seq=preg_replace("/P/","p",$seq);
            $seq=preg_replace("/F|Y|W/","f",$seq);      // Hydrophobic/aromatic sidechains
            $seq=preg_replace("/E|D|N|Q/","e",$seq);
            $seq=preg_replace("/K|R/","k",$seq);        // Long-chain positively charged
            $seq=preg_replace("/H/","h",$seq);
            $seq=strtoupper($seq);
            return $seq;
        }
        if ($type=="Murphy4"){                                // LAFE
            $seq=preg_replace("/L|V|I|M|C/","l",$seq);    // Hydrophobic
            $seq=preg_replace("/A|G|S|T|P/","a",$seq);
            $seq=preg_replace("/F|Y|W/","f",$seq);        // Hydrophobic/aromatic sidechains
            $seq=preg_replace("/E|D|N|Q|K|R|H/","e",$seq);
            $seq=strtoupper($seq);
            return $seq;
        }
        if ($type=="Murphy2"){                                              //PE
            $seq=preg_replace("/L|V|I|M|C|A|G|S|T|P|F|Y|W/","p",$seq);  //Hydrophobic
            $seq=preg_replace("/E|D|N|Q|K|R|H/","e",$seq);              //Hydrophilic
            $seq=strtoupper($seq);
            return $seq;
        }
        if ($type=="Wang5"){                                   // IAGEK
            $seq=preg_replace("/C|M|F|I|L|V|W|Y/","i",$seq);
            $seq=preg_replace("/A|T|H/","a",$seq);
            $seq=preg_replace("/G|P/","g",$seq);
            $seq=preg_replace("/D|E/","e",$seq);
            $seq=preg_replace("/S|N|Q|R|K/","k",$seq);
            $seq=strtoupper($seq);
            return $seq;
        }
        if ($type=="Wang5v"){                                   // ILAEK
            $seq=preg_replace("/C|M|F|I/","i",$seq);
            $seq=preg_replace("/L|V|W|Y/","l",$seq);
            $seq=preg_replace("/A|T|G|S/","a",$seq);
            $seq=preg_replace("/N|Q|D|E/","e",$seq);
            $seq=preg_replace("/H|P|R|K/","k",$seq);
            $seq=strtoupper($seq);
            return $seq;
        }
        if ($type=="Wang3"){                                     // IAE
            $seq=preg_replace("/C|M|F|I|L|V|W|Y/","i",$seq);
            $seq=preg_replace("/A|T|H|G|P|R/","a",$seq);
            $seq=preg_replace("/D|E|S|N|Q|K/","e",$seq);
            $seq=strtoupper($seq);
            return $seq;
        }
        if ($type=="Wang2"){                                     // IA
            $seq=preg_replace("/C|M|F|I|L|V|W|Y/","i",$seq);
            $seq=preg_replace("/A|T|H|G|P|R|D|E|S|N|Q|K/","a",$seq);
            $seq=strtoupper($seq);
            return $seq;
        }
        if ($type=="Li10"){                                      // CYLVGPSNEK
            $seq=preg_replace("/C/","c",$seq);
            $seq=preg_replace("/F|Y|W/","y",$seq);
            $seq=preg_replace("/M|L/","l",$seq);
            $seq=preg_replace("/I|V/","v",$seq);
            $seq=preg_replace("/G/","g",$seq);
            $seq=preg_replace("/P/","p",$seq);
            $seq=preg_replace("/A|T|S/","s",$seq);
            $seq=preg_replace("/N|H/","n",$seq);
            $seq=preg_replace("/Q|E|D/","e",$seq);
            $seq=preg_replace("/R|K/","k",$seq);
            $seq=strtoupper($seq);
            return $seq;
        }
        if ($type=="Li5"){                                       // YIGSE
            $seq=preg_replace("/C|F|Y|W/","y",$seq);
            $seq=preg_replace("/M|L|I|V/","i",$seq);
            $seq=preg_replace("/G/","g",$seq);
            $seq=preg_replace("/P|A|T|S/","s",$seq);
            $seq=preg_replace("/N|H|Q|E|D|R|K/","e",$seq);
            $seq=strtoupper($seq);
            return $seq;
        }
        if ($type=="Li4"){                                        // YISE
            $seq=preg_replace("/C|F|Y|W/","y",$seq);
            $seq=preg_replace("/M|L|I|V/","i",$seq);
            $seq=preg_replace("/G|P|A|T|S/","s",$seq);
            $seq=preg_replace("/N|H|Q|E|D|R|K/","e",$seq);
            $seq=strtoupper($seq);
            return $seq;
        }
        if ($type=="Li3"){                                       // ISE
            $seq=preg_replace("/C|F|Y|W|M|L|I|V/","i",$seq);
            $seq=preg_replace("/G|P|A|T|S/","s",$seq);
            $seq=preg_replace("/N|H|Q|E|D|R|K/","e",$seq);
            $seq=strtoupper($seq);
            return $seq;
        }
    }

// #######################################################################
// removes non coding characters from $seq
// returns the filtered sequence
    function remove_non_coding_prot($seq) {
        // change the sequence to upper case
        $seq=strtoupper($seq);
        // remove non-coding characters([^ARNDCEQGHILKMFPSTWYVX\*])
        $seq = preg_replace ("([^ARNDCEQGHILKMFPSTWYVX\*])", "", $seq);
        return $seq;
    }

// #######################################################################
// Reduce the alphabet for $seq by using the user defined persoalized alphabet
//  returns the reduced sequence
    function reduce_alphabet_custom($seq,$custom_alphabet){

        $custom_alphabet=strtolower($custom_alphabet);
        // array with reduced code
        $a=preg_split("//",$custom_alphabet,-1,PREG_SPLIT_NO_EMPTY);
        // array with aminoacids
        $b=preg_split("//","ARNDCEQGHILKMFPSTWYV",-1,PREG_SPLIT_NO_EMPTY);

        foreach($a as $key=> $val){
            // replace aminoacids by reduced codes
            $seq=preg_replace("/".$b[$key]."/",$val,$seq);
        }
        $seq=strtoupper($seq);
        return $seq;

    }

// #######################################################################
// Get colored html code for $seq by using the $seq2 (the reduced sequence)
// as a reference, and according to the personalized alphabet included in the form
// returns an html code
    function color_custom($seq,$seq2,$custom_alphabet){
        // get array with letters
        $a=preg_split("//",$custom_alphabet,-1,PREG_SPLIT_NO_EMPTY);
        $a=array_unique($a);
        //print_r($a);

        // define generic color
        //   the assigment order is the one shown in the list
        //   When few colors are needed, the first ones in the list are used
        $generic_colors=array(
            0=>"FF0000",
            1=>"00FF00",
            2=>"0000FF",
            3=>"FFFF00",
            4=>"FF00FF",
            5=>"00FFFF",
            6=>"FF8888",
            7=>"88FF88",
            8=>"8888FF",
            9=>"FFFF88",
            10=>"FF88FF",
            11=>"88FFFF",
            12=>"FF3366",
            13=>"33FF66",
            14=>"3366FF",
            15=>"FF6633",
            16=>"66FF33",
            17=>"6633FF",
            18=>"880000",
            19=>"008800"
            // black is not used

        );

        // asign colors to the array
        foreach($a as $key=> $val){
            // assigment of color
            $letters[$val]=$generic_colors[$key];
        }

        $new_seq="";
        for($i=0;$i<strlen($seq);$i++){
            $letter_seq=substr($seq,$i,1);
            $letter_seq2=substr($seq2,$i,1);
            if ($letters[$letter_seq2]!=""){
                $new_seq.="<font color=".strtolower($letters[$letter_seq2]).">$letter_seq</font>";
            }else{
                $new_seq.=$letter_seq;
            }
        }

        return $new_seq;
    }

}